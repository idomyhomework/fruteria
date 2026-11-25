<?php
// Ticket 05: eliminar un Producto desde el Catálogo.

function eliminar(string $nombre, string $q = ''): array
{
    return post('/eliminar.php', ['id' => id_de($nombre), 'q' => $q]);
}

function test_cliente_no_ve_el_boton_eliminar(): void
{
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'action="eliminar.php"');
}

function test_administrador_ve_eliminar_con_pregunta_en_cada_fila(): void
{
    insertar_producto('Plátano');
    login();
    [, $html] = get('/catalogo.php');

    foreach (['Fruta de la pasión', 'Plátano'] as $nombre) {
        $fila = fila($html, $nombre);
        contiene($fila, 'action="eliminar.php"');
        contiene($fila, "¿Eliminar $nombre?");
    }
}

function test_eliminar_quita_el_producto_del_catalogo(): void
{
    insertar_producto('Plátano');
    login();
    [$estado, , $cabeceras] = eliminar('Fruta de la pasión');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No vuelve al Catálogo');

    [, $html] = get('/catalogo.php');
    contiene($html, 'Producto eliminado');
    no_contiene($html, 'Fruta de la pasión');
    contiene($html, 'Plátano'); // solo se borra ese

    // Sigue borrado en la siguiente visita (nada lo vuelve a insertar).
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'Fruta de la pasión');
}

function test_eliminar_mantiene_la_busqueda(): void
{
    login();
    [, , $cabeceras] = eliminar('Fruta de la pasión', 'pasión');
    comprobar(
        cabecera_http($cabeceras, 'Location') === 'catalogo.php?q=' . urlencode('pasión'),
        'Se pierde la búsqueda'
    );
}

function test_eliminar_un_producto_ya_eliminado_muestra_mensaje(): void
{
    login();
    $id = id_de('Fruta de la pasión');
    post('/eliminar.php', ['id' => $id, 'q' => '']);
    get('/catalogo.php'); // consume el aviso "Producto eliminado"

    [$estado] = post('/eliminar.php', ['id' => $id, 'q' => '']);
    comprobar($estado === 303, "Estado $estado");
    [, $html] = get('/catalogo.php');
    contiene($html, 'Ese Producto ya no existe');
}

function test_eliminar_sin_login_se_rechaza(): void
{
    [$estado] = eliminar('Fruta de la pasión');
    comprobar($estado === 303, "Estado $estado");

    [, $html] = get('/catalogo.php');
    no_contiene($html, 'Inicia sesión como Administrador');
    contiene($html, 'Fruta de la pasión');
}

function test_eliminar_por_get_no_borra_nada(): void
{
    login();
    get('/eliminar.php?id=' . id_de('Fruta de la pasión'));
    [, $html] = get('/catalogo.php');
    contiene($html, 'Fruta de la pasión');
}