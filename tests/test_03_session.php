<?php
// Ticket 03 (y 11): login y logout del Administrador en admin.php.

function test_admin_muestra_el_formulario_de_login(): void
{
    [$estado, $html] = get('/admin.php');
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'action="login.php"');
    contiene($html, 'name="usuario"');
    contiene($html, 'name="contrasena"');
    no_contiene($html, 'Cerrar sesión');
}

function test_el_catalogo_nunca_muestra_el_formulario_de_login(): void
{
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'name="contrasena"');

    login();
    [, $html] = get('/catalogo.php');
    no_contiene($html, 'name="contrasena"');
}

function test_admin_con_la_sesion_iniciada_redirige_al_inicio(): void
{
    login();
    [$estado, , $cabeceras] = get('/admin.php');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'index.php', 'No redirige al inicio');
}

function test_login_correcto_muestra_la_sesion_iniciada(): void
{
    [$estado, , $cabeceras] = login();
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'index.php', 'No redirige al inicio');

    [, $html] = get('/catalogo.php');
    contiene($html, 'Cerrar sesión');
    contiene($html, 'Fruta de la pasión'); // sigue viendo lo mismo que un Cliente
}

function test_login_por_get_redirige_a_admin(): void
{
    [$estado, , $cabeceras] = get('/login.php');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'admin.php', 'No redirige a admin.php');
}

function test_contrasena_o_usuario_incorrectos_dan_el_mismo_mensaje(): void
{
    [$estado, , $cabeceras] = login('admin', 'mala');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'admin.php', 'No vuelve a admin.php');
    [, $html_contrasena] = get('/admin.php');

    login('otro', CLAVE_TEST);
    [, $html_usuario] = get('/admin.php');

    contiene($html_contrasena, 'Usuario o contraseña incorrectos');
    contiene($html_contrasena, 'name="contrasena"');
    no_contiene($html_contrasena, 'Cerrar sesión');
    comprobar($html_contrasena === $html_usuario, 'Las dos páginas de error no son idénticas');
}

function test_el_mensaje_de_error_sale_una_sola_vez(): void
{
    login('admin', 'mala');
    get('/admin.php');

    [, $html] = get('/admin.php');
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

    [$estado, $html] = get('/admin.php');
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'name="contrasena"');
}

function test_cerrar_sesion_no_redirige_a_otra_web(): void
{
    login();
    [, , $cabeceras] = post('/logout.php', ['volver' => 'https://ejemplo.com']);
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'Redirige fuera de la web');
}
