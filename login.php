<?php
require_once __DIR__ . '/includes/sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = is_string($_POST['usuario'] ?? null) ? $_POST['usuario'] : '';
    $contrasena = is_string($_POST['contrasena'] ?? null) ? $_POST['contrasena'] : '';

    if (!iniciar_sesion_administrador($usuario, $contrasena)) {
        poner_aviso('Usuario o contraseña incorrectos');
    }
}

redirigir('catalogo.php');