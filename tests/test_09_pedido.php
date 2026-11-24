<?php
// Ticket 09: hacer un Pedido desde el Carrito.

function hacer_pedido_http(string $nombre = 'Ana', string $telefono = '612345678'): array
{
    return post('/carrito.php', ['nombre' => $nombre, 'telefono' => $telefono]);
}

function pedidos_guardados(): int
{
    return (int) bd_test()->query('SELECT COUNT(*) FROM pedido')->fetchColumn();
}

function cantidad_de(string $nombre): string
{
    $c = bd_test()->prepare('SELECT cantidad FROM producto WHERE nombre = ?');
    $c->execute([$nombre]);
    return (string) $c->fetchColumn();
}

function test_carrito_tiene_formulario_hacer_pedido(): void
{
    anadir('Fruta de la pasión', '1');
    [, $html] = get('/carrito.php');
    contiene($html, 'name="nombre"');
    contiene($html, 'name="telefono"');
    contiene($html, 'Hacer pedido');
}

function test_pedido_valido_resta_cantidad_y_muestra_confirmacion(): void
{
    anadir('Fruta de la pasión', '2');   // 2 × 9,90 = 19,80
    [$estado, , $cabeceras] = hacer_pedido_http();
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'pedido.php', 'No va a la confirmación');

    comprobar(cantidad_de('Fruta de la pasión') === '3.00', 'No se han restado los kilos');

    $pedido = bd_test()->query('SELECT * FROM pedido')->fetch(PDO::FETCH_ASSOC);
    comprobar($pedido['nombre'] === 'Ana', 'nombre mal guardado');
    comprobar($pedido['telefono'] === '612345678', 'teléfono mal guardado');
    comprobar($pedido['estado'] === 'en_espera', 'Estado inicial incorrecto');

    // La Línea copia nombre y Precio (ADR 0001).
    $linea = bd_test()->query('SELECT * FROM linea_pedido')->fetch(PDO::FETCH_ASSOC);
    comprobar($linea['nombre'] === 'Fruta de la pasión', 'La Línea no copia el nombre');
    comprobar($linea['precio'] === '9.90', 'La Línea no copia el Precio');
    comprobar($linea['kilos'] === '2.00', 'La Línea no guarda los kilos');

    [, $html] = get('/pedido.php');
    contiene($html, "Pedido nº {$pedido['id']}");
    contiene($html, 'Fruta de la pasión');
    contiene($html, '19,80');
    contiene($html, 'Se paga al recoger');
}

function test_pedido_vacia_el_carrito(): void
{
    anadir('Fruta de la pasión', '1');
    hacer_pedido_http();
    [, $html] = get('/carrito.php');
    contiene($html, 'Carrito (0)');
    contiene($html, 'Tu Carrito está vacío');
}

function test_nombre_no_valido_se_rechaza_y_conserva_lo_escrito(): void
{
    anadir('Fruta de la pasión', '1');
    $largo = str_repeat('a', 51);
    foreach (['' => 'Escribe tu nombre', '   ' => 'Escribe tu nombre', $largo => 'como máximo 50 caracteres'] as $malo => $mensaje) {
        [$estado, $html] = hacer_pedido_http((string) $malo, '612 345 678');
        comprobar($estado === 200, "Estado $estado");
        contiene($html, $mensaje);
        contiene($html, 'value="612 345 678"');
        contiene($html, 'value="' . trim((string) $malo) . '"');
    }
    comprobar(pedidos_guardados() === 0, 'Se ha guardado un Pedido');
    comprobar(cantidad_de('Fruta de la pasión') === '5.00', 'Se han restado kilos');
    contiene($html, 'Carrito (1)');
}

function test_telefono_no_valido_se_rechaza_y_conserva_lo_escrito(): void
{
    anadir('Fruta de la pasión', '1');
    foreach (['', '12345678', '1234567890', '61234567a', '+3561234567', '+34 12345678'] as $malo) {
        [$estado, $html] = hacer_pedido_http('Ana', $malo);
        comprobar($estado === 200, "Estado $estado con '$malo'");
        contiene($html, 'El teléfono debe tener 9 cifras');
        contiene($html, 'value="Ana"');
        contiene($html, "value=\"$malo\"");
    }
    comprobar(pedidos_guardados() === 0, 'Se ha guardado un Pedido');
}

function test_telefono_con_espacios_y_prefijo_se_acepta(): void
{
    anadir('Fruta de la pasión', '1');
    [$estado] = hacer_pedido_http('Ana', '+34 612 34 56 78');
    comprobar($estado === 303, "Estado $estado");
    $telefono = bd_test()->query('SELECT telefono FROM pedido')->fetchColumn();
    comprobar($telefono === '612345678', "Teléfono guardado: $telefono");
}

function test_mas_kilos_que_la_cantidad_rechaza_todo_el_pedido(): void
{
    insertar_producto('Plátano', precio: '2.50', cantidad: '10.00');
    anadir('Plátano', '3');
    anadir('Fruta de la pasión', '2');
    // Mientras tanto, alguien compra Fruta de la pasión: ya solo queda 1 kg.
    bd_test()->exec("UPDATE producto SET cantidad = 1 WHERE nombre = 'Fruta de la pasión'");

    [$estado, $html] = hacer_pedido_http();
    comprobar($estado === 200, "Estado $estado");
    contiene(fila($html, 'Fruta de la pasión'), 'Solo quedan 1,00 kg de Fruta de la pasión');
    contiene($html, 'value="Ana"');

    comprobar(pedidos_guardados() === 0, 'Se ha guardado un Pedido');
    comprobar(cantidad_de('Plátano') === '10.00', 'Se han restado los kilos del Plátano');
    comprobar(cantidad_de('Fruta de la pasión') === '1.00', 'Se han restado los kilos de la Fruta de la pasión');
    contiene($html, 'Carrito (2)');
    contiene(fila($html, 'Fruta de la pasión'), 'value="2,00"');
}

function test_carrito_vacio_no_se_puede_pedir(): void
{
    [, $html] = get('/carrito.php');
    no_contiene($html, 'Hacer pedido');

    [$estado, $html] = hacer_pedido_http();
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'Tu Carrito está vacío');
    comprobar(pedidos_guardados() === 0, 'Se ha guardado un Pedido');
}

function test_producto_eliminado_se_quita_antes_de_pedir(): void
{
    insertar_producto('Plátano');
    anadir('Fruta de la pasión', '1');
    anadir('Plátano', '1');
    bd_test()->exec("DELETE FROM producto WHERE nombre = 'Plátano'");

    // No se pide nada: el Cliente ve primero el Carrito con el aviso y el total nuevo.
    [$estado, $html] = hacer_pedido_http();
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'Plátano ya no está a la venta y se ha quitado del Carrito');
    contiene($html, 'value="Ana"');
    comprobar(pedidos_guardados() === 0, 'Se ha guardado un Pedido');
    contiene($html, 'Carrito (1)');
}

function test_borrar_el_producto_no_borra_la_linea_del_pedido(): void
{
    anadir('Fruta de la pasión', '1');
    hacer_pedido_http();
    bd_test()->exec("DELETE FROM producto WHERE nombre = 'Fruta de la pasión'");

    $linea = bd_test()->query('SELECT * FROM linea_pedido')->fetch(PDO::FETCH_ASSOC);
    comprobar($linea !== false, 'Se ha borrado la Línea');
    comprobar($linea['producto_id'] === null, 'producto_id no queda vacío');
    comprobar($linea['nombre'] === 'Fruta de la pasión', 'Se ha perdido el nombre');
}

function test_confirmacion_sin_pedido_vuelve_al_catalogo(): void
{
    [$estado, , $cabeceras] = get('/pedido.php');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No vuelve al Catálogo');
}

function test_administrador_no_puede_hacer_pedidos(): void
{
    login();
    [$estado, , $cabeceras] = hacer_pedido_http();
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No vuelve al Catálogo');
    comprobar(pedidos_guardados() === 0, 'Se ha guardado un Pedido');
}