<?php
// Ticket 02: búsqueda en el Catálogo.

function buscar(string $texto): string
{
    [, $html] = get('/catalogo.php?q=' . urlencode($texto));
    return $html;
}

function test_busqueda_parcial(): void
{
    insertar_producto('Plátano');
    insertar_producto('Manzana');

    $html = buscar('tano');
    contiene($html, 'Plátano');
    no_contiene($html, 'Manzana');
    no_contiene($html, 'Fruta de la pasión');
}

function test_busqueda_ignora_mayusculas(): void
{
    insertar_producto('Plátano');

    $html = buscar('PLÁT');
    contiene($html, 'Plátano');
    no_contiene($html, 'Fruta de la pasión');
}

function test_busqueda_ignora_acentos(): void
{
    insertar_producto('Plátano');

    $html = buscar('platano');
    contiene($html, 'Plátano');
    no_contiene($html, 'Fruta de la pasión');

    contiene(buscar('PASION'), 'Fruta de la pasión');
}

function test_busqueda_sin_resultados_muestra_mensaje(): void
{
    $html = buscar('kiwi');
    contiene($html, 'No se ha encontrado ningún Producto');
    no_contiene($html, 'Fruta de la pasión');
}

function test_busqueda_vacia_muestra_todo(): void
{
    insertar_producto('Manzana');

    $html = buscar('');
    contiene($html, 'Fruta de la pasión');
    contiene($html, 'Manzana');
}

function test_porcentaje_se_busca_literalmente(): void
{
    $html = buscar('%');
    contiene($html, 'No se ha encontrado ningún Producto');
}

function test_buscador_conserva_el_texto_escapado(): void
{
    $html = buscar('"><b>hola');
    contiene($html, 'value="&quot;&gt;&lt;b&gt;hola"');
    contiene($html, 'href="catalogo.php"'); // enlace para quitar la búsqueda
}