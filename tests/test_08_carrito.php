<?php
// Ticket 08: el Carrito del Cliente.

function anadir(string $nombre, string $kilos, string $q = ''): array
{
    return post('/carrito_anadir.php', ['id' => id_de($nombre), 'kilos' => $kilos, 'q' => $q]);
}

function test_cliente_ve_anadir_al_carrito_en_cada_fila_con_cantidad(): void
{
    insertar_producto('Plátano');
    [, $html] = get('/catalogo.php');

    foreach (['Fruta de la pasión', 'Plátano'] as $nombre) {
        $fila = fila($html, $nombre);
        contiene($fila, 'name="kilos"');
        contiene($fila, 'Añadir al carrito');
    }
}

function test_agotado_no_tiene_boton_anadir(): void
{
    insertar_producto('Plátano', cantidad: '0');
    [, $html] = get('/catalogo.php');
    no_contiene(fila($html, 'Plátano'), 'Añadir al carrito');
}

function test_anadir_vuelve_al_catalogo_con_aviso_y_cuenta_la_linea(): void
{
    [$estado, , $cabeceras] = anadir('Fruta de la pasión', '1,5');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No vuelve al Catálogo');

    [, $html] = get('/catalogo.php');
    contiene($html, 'Se ha añadido Fruta de la pasión al Carrito');
    contiene($html, 'Carrito (1)');
}

function test_anadir_mantiene_la_busqueda(): void
{
    [, , $cabeceras] = anadir('Fruta de la pasión', '1', 'pasión');
    comprobar(
        cabecera_http($cabeceras, 'Location') === 'catalogo.php?q=' . urlencode('pasión'),
        'Se pierde la búsqueda'
    );
}

function test_anadir_otra_vez_suma_los_kilos_en_la_misma_linea(): void
{
    anadir('Fruta de la pasión', '1,5');
    anadir('Fruta de la pasión', '2');

    [, $html] = get('/carrito.php');
    contiene($html, 'Carrito (1)');
    contiene(fila($html, 'Fruta de la pasión'), 'value="3,50"');
}

function test_kilos_no_validos_se_rechazan(): void
{
    foreach (['0', '0,00', '-1', 'abc', '1,234', '1,2,3', ''] as $malos) {
        anadir('Fruta de la pasión', $malos);
        [, $html] = get('/catalogo.php');
        contiene($html, 'Kilos no válidos');
        contiene($html, 'Carrito (0)');
    }
}

function test_mas_kilos_que_la_cantidad_se_rechaza(): void
{
    anadir('Fruta de la pasión', '5,01');
    [, $html] = get('/catalogo.php');
    contiene($html, 'Solo quedan 5,00 kg de Fruta de la pasión');
    contiene($html, 'Carrito (0)');

    // La suma también cuenta: 3 + 3 > 5.
    anadir('Fruta de la pasión', '3');
    anadir('Fruta de la pasión', '3');
    [, $html] = get('/carrito.php');
    contiene($html, 'Solo quedan 5,00 kg de Fruta de la pasión');
    contiene(fila($html, 'Fruta de la pasión'), 'value="3,00"');
}

function test_carrito_muestra_subtotales_y_total(): void
{
    insertar_producto('Plátano', precio: '2.50', cantidad: '10.00');
    anadir('Fruta de la pasión', '2');   // 2 × 9,90 = 19,80
    anadir('Plátano', '1,5');            // 1,5 × 2,50 = 3,75

    [, $html] = get('/carrito.php');
    contiene(fila($html, 'Fruta de la pasión'), '9,90');
    contiene(fila($html, 'Fruta de la pasión'), '19,80');
    contiene(fila($html, 'Plátano'), '3,75');
    contiene($html, '23,55');
}

function test_carrito_vacio_muestra_mensaje_y_enlace_al_catalogo(): void
{
    [, $html] = get('/carrito.php');
    contiene($html, 'Tu Carrito está vacío');
    contiene($html, 'href="catalogo.php"');
}

function test_cambiar_kilos_de_una_linea(): void
{
    anadir('Fruta de la pasión', '1');
    [$estado, , $cabeceras] = post('/carrito_cambiar.php', ['id' => id_de('Fruta de la pasión'), 'kilos' => '4,25']);
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'carrito.php', 'No vuelve al Carrito');

    [, $html] = get('/carrito.php');
    contiene(fila($html, 'Fruta de la pasión'), 'value="4,25"'); // 4,25 en total, no 1 + 4,25
}

function test_cambiar_con_kilos_no_validos_o_de_mas_no_cambia_nada(): void
{
    anadir('Fruta de la pasión', '1');
    foreach (['0' => 'Kilos no válidos', '6' => 'Solo quedan 5,00 kg'] as $malos => $aviso) {
        post('/carrito_cambiar.php', ['id' => id_de('Fruta de la pasión'), 'kilos' => $malos]);
        [, $html] = get('/carrito.php');
        contiene($html, $aviso);
        contiene(fila($html, 'Fruta de la pasión'), 'value="1,00"');
    }
}

function test_quitar_una_linea(): void
{
    anadir('Fruta de la pasión', '1');
    [$estado] = post('/carrito_quitar.php', ['id' => id_de('Fruta de la pasión')]);
    comprobar($estado === 303, "Estado $estado");

    [, $html] = get('/carrito.php');
    contiene($html, 'Carrito (0)');
    contiene($html, 'Tu Carrito está vacío');
}

function test_producto_eliminado_se_quita_del_carrito_con_aviso(): void
{
    insertar_producto('Plátano');
    anadir('Fruta de la pasión', '1');
    anadir('Plátano', '1');
    bd_test()->exec("DELETE FROM producto WHERE nombre = 'Plátano'");

    [, $html] = get('/carrito.php');
    contiene($html, 'Plátano ya no está a la venta y se ha quitado del Carrito');
    contiene($html, 'Carrito (1)');
    comprobar(fila($html, 'Plátano') === '', 'La Línea de Plátano sigue en el Carrito');
}

function test_administrador_no_ve_el_carrito(): void
{
    login();
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'Añadir al carrito');
    no_contiene($html, 'Carrito (');
}

function test_administrador_no_puede_usar_el_carrito(): void
{
    login();
    [$estado, , $cabeceras] = get('/carrito.php');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No vuelve al Catálogo');

    [$estado] = anadir('Fruta de la pasión', '1');
    comprobar($estado === 303, "Estado $estado");
    [, $html] = get('/catalogo.php');
    contiene($html, 'El Administrador no usa el Carrito');

    // Al cerrar sesión, como Cliente, el Carrito sigue vacío.
    post('/logout.php', ['volver' => 'catalogo.php']);
    [, $html] = get('/catalogo.php');
    contiene($html, 'Carrito (0)');
}