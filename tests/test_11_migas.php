<?php
// Ruta "Principal > Página" del Administrador en Catálogo, Inventario y Pedidos.

// El <nav class="migas"> de la página, o '' si no está.
function migas_de(string $html): string
{
    preg_match('#<nav class="migas".*?</nav>#s', $html, $m);
    return $m[0] ?? '';
}

function test_administrador_ve_la_ruta_en_catalogo_inventario_y_pedidos(): void
{
    login();
    foreach (['catalogo.php' => 'Catálogo', 'inventario.php' => 'Inventario', 'pedidos.php' => 'Pedidos'] as $ruta => $pagina) {
        [, $html] = get("/$ruta");
        $migas = migas_de($html);
        comprobar($migas !== '', "No hay ruta en $ruta");
        contiene($migas, 'href="index.php"');
        contiene($migas, 'Principal');
        contiene($migas, $pagina);
    }
}

function test_la_ruta_sustituye_a_los_enlaces_de_antes(): void
{
    login();
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'Ir al Inventario');

    [, $html] = get('/inventario.php');
    no_contiene($html, 'Volver al Catálogo');

    [, $html] = get('/pedidos.php');
    no_contiene($html, 'Volver al inicio');
}

function test_cliente_no_ve_la_ruta_en_el_catalogo(): void
{
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'class="migas"');
}
