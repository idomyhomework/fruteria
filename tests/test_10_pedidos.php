<?php
// Ticket 10: Pedidos del Administrador.

// Un Cliente pide Fruta de la pasión y luego entra el Administrador. Devuelve el id del Pedido.
function pedido_hecho(string $nombre = 'Ana', string $kilos = '2'): int
{
    global $COOKIES;
    $COOKIES = [];   // navegador del Cliente
    anadir('Fruta de la pasión', $kilos);
    hacer_pedido_http($nombre);
    $COOKIES = [];   // navegador del Administrador
    login();
    return (int) bd_test()->query('SELECT MAX(id) FROM pedido')->fetchColumn();
}

function cambiar_estado_http(int|string $id, string $estado): array
{
    return post('/pedido_estado.php', ['id' => (string) $id, 'estado' => $estado]);
}

function estado_de(int $id): string
{
    $c = bd_test()->prepare('SELECT estado FROM pedido WHERE id = ?');
    $c->execute([$id]);
    return (string) $c->fetchColumn();
}

function test_inicio_tiene_pedidos_solo_para_el_administrador(): void
{
    [, $html] = get('/index.php');
    no_contiene($html, 'href="pedidos.php"');

    login();
    [, $html] = get('/index.php');
    contiene($html, 'href="pedidos.php"');
}

function test_pedidos_sin_login_redirige_al_catalogo(): void
{
    [$estado, , $cabeceras] = get('/pedidos.php');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No redirige al Catálogo');
}

function test_sin_pedidos_muestra_mensaje(): void
{
    login();
    [$estado, $html] = get('/pedidos.php');
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'Todavía no hay Pedidos');
}

function test_pedido_muestra_sus_datos_lineas_total_y_estado(): void
{
    $id = pedido_hecho('Ana', '2');   // 2 × 9,90 = 19,80
    $creado = bd_test()->query("SELECT creado FROM pedido WHERE id = $id")->fetchColumn();

    [, $html] = get('/pedidos.php');
    contiene($html, "Pedido nº $id");
    contiene($html, date('d/m/Y H:i', strtotime($creado)));
    contiene($html, 'Ana');
    contiene($html, '612345678');
    contiene($html, 'Fruta de la pasión');
    contiene($html, '2,00 kg');
    contiene($html, '9,90');
    contiene($html, '19,80');
    contiene($html, 'En espera');
    no_contiene($html, 'Todavía no hay Pedidos');
}

function test_pedidos_del_mas_nuevo_al_mas_antiguo(): void
{
    $primero = pedido_hecho('Ana');
    $segundo = pedido_hecho('Berta');
    [, $html] = get('/pedidos.php');
    $pos_primero = strpos($html, "Pedido nº $primero");
    $pos_segundo = strpos($html, "Pedido nº $segundo");
    comprobar($pos_primero !== false && $pos_segundo !== false, 'Falta algún Pedido');
    comprobar($pos_segundo < $pos_primero, 'El Pedido más nuevo no sale primero');
}

function test_pedido_no_final_tiene_select_cambiar_y_confirm(): void
{
    $id = pedido_hecho();
    [, $html] = get('/pedidos.php');
    contiene($html, 'action="pedido_estado.php"');
    contiene($html, 'name="estado"');
    contiene($html, 'Cambiar');
    contiene($html, "¿Marcar el Pedido nº $id como Entregado?");
    contiene($html, "¿Marcar el Pedido nº $id como Cancelado?");
}

function test_cada_cambio_permitido_funciona(): void
{
    $cambios = [
        ['en_espera', 'preparado', 'Preparado para entrega'],
        ['preparado', 'en_espera', 'En espera'],
        ['en_espera', 'entregado', 'Entregado'],
    ];
    $id = pedido_hecho();
    foreach ($cambios as [$desde, $hasta, $texto]) {
        comprobar(estado_de($id) === $desde, "Debería estar en $desde");
        [$estado, , $cabeceras] = cambiar_estado_http($id, $hasta);
        comprobar($estado === 303, "Estado $estado");
        comprobar(cabecera_http($cabeceras, 'Location') === 'pedidos.php', 'No vuelve a Pedidos');
        comprobar(estado_de($id) === $hasta, "No ha pasado de $desde a $hasta");
        [, $html] = get('/pedidos.php');
        contiene($html, "El Pedido nº $id ahora está $texto.");
    }

    // De Preparado para entrega a Entregado y a Cancelado.
    foreach (['entregado', 'cancelado'] as $final) {
        $id = pedido_hecho('Ana', '1');
        cambiar_estado_http($id, 'preparado');
        cambiar_estado_http($id, $final);
        comprobar(estado_de($id) === $final, "No ha pasado de preparado a $final");
    }
}

function test_pedido_final_no_tiene_select_ni_se_puede_cambiar(): void
{
    $id = pedido_hecho();
    cambiar_estado_http($id, 'entregado');
    [, $html] = get('/pedidos.php');
    no_contiene($html, 'name="estado"');

    foreach (['en_espera', 'preparado', 'cancelado'] as $otro) {
        cambiar_estado_http($id, $otro);
        comprobar(estado_de($id) === 'entregado', "Ha salido de entregado a $otro");
        [, $html] = get('/pedidos.php');
        contiene($html, "El Pedido nº $id no puede pasar de Entregado a");
    }

    $id = pedido_hecho();   // quedan 5 - 2 - 2 = 1 kg
    cambiar_estado_http($id, 'cancelado');   // vuelve a 3 kg
    foreach (['en_espera', 'preparado', 'entregado', 'cancelado'] as $otro) {
        cambiar_estado_http($id, $otro);
        comprobar(estado_de($id) === 'cancelado', "Ha salido de cancelado a $otro");
    }
    // Cancelar otra vez no devuelve los kilos dos veces.
    comprobar(cantidad_de('Fruta de la pasión') === '3.00', 'Los kilos se han devuelto dos veces');
}

function test_estado_o_pedido_desconocido_se_rechaza(): void
{
    $id = pedido_hecho();
    cambiar_estado_http($id, 'pagado');
    comprobar(estado_de($id) === 'en_espera', 'Ha cambiado a un Estado desconocido');
    [, $html] = get('/pedidos.php');
    contiene($html, 'Estado no válido.');

    foreach (['999', 'abc'] as $malo) {
        [$estado] = cambiar_estado_http($malo, 'preparado');
        comprobar($estado === 303, "Estado $estado");
        [, $html] = get('/pedidos.php');
        contiene($html, 'Ese Pedido no existe.');
    }
}

function test_cancelar_devuelve_los_kilos_y_salta_productos_eliminados(): void
{
    global $COOKIES;
    insertar_producto('Plátano', precio: '2.50', cantidad: '10.00');
    anadir('Plátano', '3');
    anadir('Fruta de la pasión', '2');
    hacer_pedido_http();
    $COOKIES = [];
    login();
    $id = (int) bd_test()->query('SELECT MAX(id) FROM pedido')->fetchColumn();
    comprobar(cantidad_de('Fruta de la pasión') === '3.00', 'No se han restado los kilos');

    bd_test()->exec("DELETE FROM producto WHERE nombre = 'Plátano'");
    [$estado] = cambiar_estado_http($id, 'cancelado');
    comprobar($estado === 303, "Estado $estado");
    comprobar(estado_de($id) === 'cancelado', 'No se ha cancelado');
    comprobar(cantidad_de('Fruta de la pasión') === '5.00', 'No se han devuelto los kilos');
    [, $html] = get('/pedidos.php');
    contiene($html, "El Pedido nº $id ahora está Cancelado.");
}

function test_entregar_no_devuelve_los_kilos(): void
{
    $id = pedido_hecho('Ana', '2');
    cambiar_estado_http($id, 'entregado');
    comprobar(cantidad_de('Fruta de la pasión') === '3.00', 'Entregar ha cambiado la Cantidad');
}

function test_pedido_sigue_mostrando_producto_eliminado(): void
{
    $id = pedido_hecho('Ana', '2');
    bd_test()->exec("DELETE FROM producto WHERE nombre = 'Fruta de la pasión'");
    [, $html] = get('/pedidos.php');
    contiene($html, "Pedido nº $id");
    contiene($html, 'Fruta de la pasión');
    contiene($html, '9,90');
    contiene($html, '19,80');
}

function test_cambiar_estado_sin_login_se_rechaza(): void
{
    global $COOKIES;
    $id = pedido_hecho();
    $COOKIES = [];   // sesión de Cliente
    [$estado, , $cabeceras] = cambiar_estado_http($id, 'cancelado');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No redirige al Catálogo');
    comprobar(estado_de($id) === 'en_espera', 'Ha cambiado el Estado sin login');
    comprobar(cantidad_de('Fruta de la pasión') === '3.00', 'Ha devuelto kilos sin login');
}