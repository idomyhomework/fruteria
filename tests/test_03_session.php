<?php
// Ticket 03: login y logout del Administrador.

function test_cliente_ve_el_formulario_de_login_en_el_catalogo(): void
{
    [, $html] = get('/catalogo.php');
    contiene($html, 'name="usuario"');
    contiene($html, 'name="contrasena"');
    no_contiene($html, 'Cerrar sesión');
}

function test_login_correcto_muestra_la_sesion_iniciada(): void
{
    [$estado, , $cabeceras] = login();
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No redirige al Catálogo');

    [, $html] = get('/catalogo.php');
    contiene($html, 'Cerrar sesión');
    no_contiene($html, 'name="contrasena"');
    contiene($html, 'Fruta de la pasión'); // sigue viendo lo mismo que un Cliente
}

function test_contrasena_o_usuario_incorrectos_dan_el_mismo_mensaje(): void
{
    login('admin', 'mala');
    [, $html_contrasena] = get('/catalogo.php');

    login('otro', CLAVE_TEST);
    [, $html_usuario] = get('/catalogo.php');

    contiene($html_contrasena, 'Usuario o contraseña incorrectos');
    contiene($html_contrasena, 'name="contrasena"');
    no_contiene($html_contrasena, 'Cerrar sesión');
    comprobar($html_contrasena === $html_usuario, 'Las dos páginas de error no son idénticas');
}

function test_el_mensaje_de_error_sale_una_sola_vez(): void
{
    login('admin', 'mala');
    get('/catalogo.php');

    [, $html] = get('/catalogo.php');
    no_contiene($html, 'Usuario o contraseña incorrectos');
}

function test_login_cambia_el_id_de_sesion(): void
{
    global $COOKIES;
    get('/catalogo.php');
    $antes = $COOKIES['PHPSESSID'] ?? '';

    login();
    comprobar($antes !== '' && $COOKIES['PHPSESSID'] !== $antes, 'El id de sesión no ha cambiado');
}

function test_cookie_de_sesion_acaba_al_cerrar_el_navegador(): void
{
    [, , $cabeceras] = login();
    $cookie = cabecera_http($cabeceras, 'Set-Cookie');
    contiene($cookie, 'PHPSESSID=');
    no_contiene(strtolower($cookie), 'expires');
    no_contiene(strtolower($cookie), 'max-age');
}

function test_inicio_de_cliente_no_tiene_enlaces_de_administrador(): void
{
    [, $html] = get('/index.php');
    no_contiene($html, 'href="inventario.php"');
    no_contiene($html, 'Cerrar sesión');
}

function test_inicio_de_administrador_tiene_inventario_y_cerrar_sesion(): void
{
    login();
    [, $html] = get('/index.php');
    contiene($html, 'href="catalogo.php"');
    contiene($html, 'href="inventario.php"');
    contiene($html, 'Cerrar sesión');
}

function test_cerrar_sesion_vuelve_a_la_vista_de_cliente(): void
{
    login();
    [$estado, , $cabeceras] = post('/logout.php', ['volver' => 'index.php']);
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'index.php', 'No vuelve a la página de inicio');

    [, $html] = get('/index.php');
    no_contiene($html, 'Cerrar sesión');
    no_contiene($html, 'href="inventario.php"');

    [, $html] = get('/catalogo.php');
    contiene($html, 'name="contrasena"');
}

function test_cerrar_sesion_no_redirige_a_otra_web(): void
{
    login();
    [, , $cabeceras] = post('/logout.php', ['volver' => 'https://ejemplo.com']);
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'Redirige fuera de la web');
}