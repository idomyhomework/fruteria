<?php
// Ticket 04: cambiar la Cantidad desde el Catálogo.

// id de un Producto (solo para preparar la petición, como insertar_producto).
function id_de(string $nombre): int
{
    $c = bd_test()->prepare('SELECT id FROM producto WHERE nombre = ?');
    $c->execute([$nombre]);
    return (int) $c->fetchColumn();
}

function actualizar(string $nombre, string $cantidad, string $q = ''): array
{
    return post('/actualizar.php', ['id' => id_de($nombre), 'cantidad' => $cantidad, 'q' => $q]);
}

function test_cliente_no_ve_el_formulario_de_cantidad(): void
{
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'name="cantidad"');
    no_contiene($html, 'Actualizar');
}

function test_administrador_ve_cantidad_y_actualizar_en_cada_fila(): void
{
    insertar_producto('Plátano');
    login();
    [, $html] = get('/catalogo.php');

    foreach (['Fruta de la pasión', 'Plátano'] as $nombre) {
        $fila = fila($html, $nombre);
        contiene($fila, 'name="cantidad"');
        contiene($fila, 'Actualizar');
    }
}

function test_actualizar_pone_la_cantidad_nueva(): void
{
    login();
    [$estado, , $cabeceras] = actualizar('Fruta de la pasión', '7.5');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No vuelve al Catálogo');

    [, $html] = get('/catalogo.php');
    contiene($html, 'Cantidad actualizada');
    contiene(fila($html, 'Fruta de la pasión'), '7,50 kg'); // 7,5 en total, no 5 + 7,5
}

function test_cantidad_con_coma(): void
{
    login();
    actualizar('Fruta de la pasión', '2,25');
    [, $html] = get('/catalogo.php');
    contiene(fila($html, 'Fruta de la pasión'), '2,25 kg');
}

function test_actualizar_a_cero_muestra_agotado(): void
{
    login();
    actualizar('Fruta de la pasión', '0');
    [, $html] = get('/catalogo.php');
    contiene(fila($html, 'Fruta de la pasión'), 'Agotado');
}

function test_cantidades_no_validas_se_rechazan(): void
{
    login();
    foreach (['-1', 'abc', '1.234', '1,2,3', ''] as $mala) {
        actualizar('Fruta de la pasión', $mala);
        [, $html] = get('/catalogo.php');
        contiene($html, 'Cantidad no válida');
        contiene(fila($html, 'Fruta de la pasión'), '5,00 kg');
    }
}

function test_actualizar_mantiene_la_busqueda(): void
{
    login();
    [, $html] = get('/catalogo.php?q=' . urlencode('pasión'));
    contiene(fila($html, 'Fruta de la pasión'), 'name="q" value="pasión"');

    [, , $cabeceras] = actualizar('Fruta de la pasión', '3', 'pasión');
    comprobar(
        cabecera_http($cabeceras, 'Location') === 'catalogo.php?q=' . urlencode('pasión'),
        'Se pierde la búsqueda'
    );
}

function test_actualizar_sin_login_se_rechaza(): void
{
    [$estado] = actualizar('Fruta de la pasión', '99');
    comprobar($estado === 303, "Estado $estado");

    [, $html] = get('/catalogo.php');
    contiene($html, 'Inicia sesión como Administrador');
    contiene(fila($html, 'Fruta de la pasión'), '5,00 kg');
}

function test_producto_que_ya_no_existe_muestra_mensaje(): void
{
    login();
    [$estado] = post('/actualizar.php', ['id' => 9999, 'cantidad' => '1', 'q' => '']);
    comprobar($estado === 303, "Estado $estado");

    [, $html] = get('/catalogo.php');
    contiene($html, 'Ese Producto ya no existe');
}