<?php
require_once __DIR__ . '/includes/sesion.php';

// El formulario está en admin.php; aquí solo se recibe el POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('admin.php');
}

$usuario = is_string($_POST['usuario'] ?? null) ? $_POST['usuario'] : '';
$contrasena = is_string($_POST['contrasena'] ?? null) ? $_POST['contrasena'] : '';

if (iniciar_sesion_administrador($usuario, $contrasena)) {
    redirigir('index.php');
}

poner_aviso('Usuario o contraseña incorrectos');
redirigir('admin.php');