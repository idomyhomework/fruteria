<?php
// Sesión del Administrador y avisos de un solo uso.
// Al incluir este fichero se arranca la sesión, así que debe ir antes de cualquier HTML.

session_set_cookie_params([
    'lifetime' => 0,      // la cookie desaparece al cerrar el navegador
    'httponly' => true,   // JavaScript no puede leerla
    'samesite' => 'Lax',  // otra web no puede enviar POST con nuestra sesión
]);
ini_set('session.use_strict_mode', '1'); // no acepta ids de sesión inventados
session_start();

function es_administrador(): bool
{
    return ($_SESSION['administrador'] ?? false) === true;
}

// Comprueba usuario y contraseña contra config.php. Devuelve true si son correctos.
function iniciar_sesion_administrador(string $usuario, string $contrasena): bool
{
    $admin = (require __DIR__ . '/config.php')['admin'];

    // Verificamos siempre la contraseña, aunque el usuario esté mal,
    // para que ambos errores tarden lo mismo.
    $contrasena_ok = password_verify($contrasena, $admin['hash']);
    if (!$contrasena_ok || $usuario !== $admin['usuario']) {
        return false;
    }

    session_regenerate_id(true); // id nuevo: evita la fijación de sesión
    $_SESSION['administrador'] = true;
    return true;
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => 1,
        'path' => $p['path'],
        'domain' => $p['domain'],
        'secure' => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'],
    ]);
    session_destroy();
}
// Para páginas y acciones de escritura: si no es el Administrador, fuera.
function exigir_administrador(): void
{
    if (!es_administrador()) {
        redirigir('catalogo.php');
    }
}

// Aviso que se muestra una sola vez en la siguiente página.
function poner_aviso(string $texto): void
{
    $_SESSION['aviso'] = $texto;
}

function tomar_aviso(): ?string
{
    $texto = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $texto;
}

// Post/Redirect/Get: tras un POST, el navegador pide la página con GET.
function redirigir(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}