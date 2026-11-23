<?php
// Ticket 01: página de inicio y Catálogo.

function test_inicio_muestra_nombre_y_enlace_al_catalogo(): void
{
    [$estado, $html] = get('/index.php');
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'Frutería Online');
    contiene($html, 'href="catalogo.php"');
}

function test_catalogo_recien_instalado_muestra_fruta_de_la_pasion(): void
{
    [$estado, $html] = get('/catalogo.php');
    comprobar($estado === 200, "Estado $estado");
    $f = fila($html, 'Fruta de la pasión');
    contiene($f, 'fruta');
    contiene($f, '9,90');
    contiene($f, '5,00');
}

function test_catalogo_ordenado_por_nombre(): void
{
    insertar_producto('Zanahoria', 'verdura');
    insertar_producto('Acelga', 'verdura');
    insertar_producto('Manzana');

    [, $html] = get('/catalogo.php');
    $posiciones = array_map(
        fn($n) => strpos($html, $n),
        ['Acelga', 'Fruta de la pasión', 'Manzana', 'Zanahoria']
    );
    $ordenadas = $posiciones;
    sort($ordenadas);
    comprobar(
        !in_array(false, $posiciones, true) && $posiciones === $ordenadas,
        'Los Productos no salen ordenados por nombre'
    );
}

function test_cantidad_cero_muestra_agotado(): void
{
    insertar_producto('Limón', cantidad: '0.00');

    [, $html] = get('/catalogo.php');
    contiene(fila($html, 'Limón'), 'Agotado');
    no_contiene(fila($html, 'Fruta de la pasión'), 'Agotado');
}

function test_nombre_con_html_se_muestra_escapado(): void
{
    insertar_producto('<script>alert(1)</script>');

    [, $html] = get('/catalogo.php');
    contiene($html, '&lt;script&gt;alert(1)&lt;/script&gt;');
    no_contiene($html, '<script>alert(1)</script>');
}