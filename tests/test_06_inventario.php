<?php
// Ticket 06: Inventario, añadir un Producto.

// Un formulario correcto; $cambios sustituye los campos que queramos probar.
function producto_valido(array $cambios = []): array
{
    return array_merge([
        'nombre' => 'Pera',
        'tipo' => 'fruta',
        'precio' => '2.50',
        'cantidad' => '10',
        'imagen_url' => 'https://example.com/pera.jpg',
    ], $cambios);
}

function guardar(array $cambios = []): array
{
    return post('/inventario.php', producto_valido($cambios));
}

function total_productos(): int
{
    return (int) bd_test()->query('SELECT COUNT(*) FROM producto')->fetchColumn();
}

// La fila del Producto en la BD (o false si no existe).
function producto_en_bd(string $nombre): array|false
{
    $c = bd_test()->prepare('SELECT nombre, tipo, precio, cantidad, imagen_url FROM producto WHERE nombre = ?');
    $c->execute([$nombre]);
    return $c->fetch(PDO::FETCH_ASSOC);
}

function test_inventario_sin_login_redirige_al_catalogo(): void
{
    [$estado, , $cabeceras] = get('/inventario.php');
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'catalogo.php', 'No redirige al Catálogo');

    [, $html] = get('/catalogo.php');
    contiene($html, 'Inicia sesión como Administrador');
}

function test_guardar_sin_login_no_guarda_nada(): void
{
    [$estado] = guardar();
    comprobar($estado === 303, "Estado $estado");
    comprobar(total_productos() === 1, 'Se ha guardado sin login');
}

function test_administrador_ve_el_formulario(): void
{
    login();
    [$estado, $html] = get('/inventario.php');
    comprobar($estado === 200, "Estado $estado");
    foreach (['nombre', 'tipo', 'precio', 'cantidad', 'imagen_url'] as $campo) {
        contiene($html, "name=\"$campo\"");
    }
    contiene($html, '<option value="fruta"');
    contiene($html, '<option value="verdura"');
    contiene($html, 'Guardar');
}

function test_guardar_un_producto_valido_aparece_en_el_catalogo(): void
{
    login();
    [$estado, , $cabeceras] = guardar();
    comprobar($estado === 303, "Estado $estado");
    comprobar(cabecera_http($cabeceras, 'Location') === 'inventario.php', 'No vuelve al Inventario');

    [, $html] = get('/inventario.php');
    contiene($html, 'Producto guardado');
    contiene($html, 'href="catalogo.php"');

    [, $html] = get('/catalogo.php');
    contiene(fila($html, 'Pera'), '2,50 €');
}

function test_nombre_se_guarda_sin_espacios_alrededor(): void
{
    login();
    guardar(['nombre' => '  Pera  ']);
    $p = producto_en_bd('Pera');
    comprobar($p !== false && $p['nombre'] === 'Pera', 'El nombre no se ha recortado');
}

function test_acepta_decimales_con_coma(): void
{
    login();
    guardar(['precio' => '2,5', 'cantidad' => '10,25']);
    $p = producto_en_bd('Pera');
    comprobar($p !== false, 'No se ha guardado');
    comprobar($p['precio'] === '2.50', "Precio {$p['precio']}");
    comprobar($p['cantidad'] === '10.25', "Cantidad {$p['cantidad']}");
}

function test_acepta_los_limites(): void
{
    login();
    // 50 caracteres (no bytes), Cantidad 0 y Precio máximo de DECIMAL(8,2).
    $nombre = str_repeat('ñ', 50);
    [$estado] = guardar(['nombre' => $nombre, 'cantidad' => '0', 'precio' => '999999,99', 'tipo' => 'verdura']);
    comprobar($estado === 303, "Estado $estado");
    $p = producto_en_bd($nombre);
    comprobar($p !== false && $p['tipo'] === 'verdura', 'No se ha guardado');
}

function test_nombre_repetido_se_rechaza(): void
{
    login();
    // Ya existe "Fruta de la pasión": distinto en mayúsculas, acento y espacios.
    [$estado, $html] = guardar(['nombre' => '  FRUTA DE LA PASION ']);
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'Ya existe un Producto con ese nombre');
    comprobar(total_productos() === 1, 'Se ha guardado un repetido');
}

function test_cada_regla_se_rechaza_con_su_mensaje(): void
{
    $casos = [
        ['nombre', '', 'Escribe el nombre'],
        ['nombre', '   ', 'Escribe el nombre'],
        ['nombre', str_repeat('a', 51), 'como máximo 50 caracteres'],
        ['tipo', 'carne', 'Elige fruta o verdura'],
        ['precio', '', 'Precio no válido'],
        ['precio', 'abc', 'Precio no válido'],
        ['precio', '0', 'Precio no válido'],
        ['precio', '0,00', 'Precio no válido'],
        ['precio', '-1', 'Precio no válido'],
        ['precio', '2,555', 'Precio no válido'],
        ['precio', '1000000', 'Precio no válido'],
        ['cantidad', '', 'Cantidad no válida'],
        ['cantidad', 'abc', 'Cantidad no válida'],
        ['cantidad', '-1', 'Cantidad no válida'],
        ['cantidad', '1,234', 'Cantidad no válida'],
        ['imagen_url', '', 'debe empezar por http:// o https://'],
        ['imagen_url', 'example.com/pera.jpg', 'debe empezar por http:// o https://'],
        ['imagen_url', 'ftp://example.com/pera.jpg', 'debe empezar por http:// o https://'],
        ['imagen_url', 'javascript:alert(1)', 'debe empezar por http:// o https://'],
    ];

    login();
    foreach ($casos as [$campo, $valor, $mensaje]) {
        [$estado, $html] = guardar([$campo => $valor]);
        comprobar($estado === 200, "$campo='$valor': estado $estado");
        comprobar(str_contains($html, $mensaje), "$campo='$valor': falta el mensaje \"$mensaje\"");
        comprobar(total_productos() === 1, "$campo='$valor': se ha guardado");
    }
}

function test_con_error_se_mantienen_los_valores_y_solo_sale_su_mensaje(): void
{
    login();
    [, $html] = guardar([
        'nombre' => 'Pera <b>',
        'tipo' => 'verdura',
        'precio' => 'abc',
        'cantidad' => '3,5',
    ]);

    contiene($html, 'Precio no válido');
    no_contiene($html, 'Cantidad no válida');
    no_contiene($html, 'Escribe el nombre');
    no_contiene($html, 'debe empezar por');

    contiene($html, 'value="Pera &lt;b&gt;"'); // escapado
    contiene($html, 'value="abc"');
    contiene($html, 'value="3,5"');
    contiene($html, 'value="https://example.com/pera.jpg"');
    contiene($html, '<option value="verdura" selected>');
}

function test_inventario_no_acepta_campos_que_no_son_texto(): void
{
    login();
    // nombre[]=x llega a PHP como array: no debe romper la página.
    [$estado, $html] = post('/inventario.php', producto_valido(['nombre' => ['x']]));
    comprobar($estado === 200, "Estado $estado");
    contiene($html, 'Escribe el nombre');
}