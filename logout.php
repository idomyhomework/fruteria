<?php
require_once __DIR__ . '/includes/sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cerrar_sesion();
}

// Solo volvemos a páginas nuestras (nunca a una URL que venga de fuera).
$volver = $_POST['volver'] ?? '';
redirigir($volver === 'index.php' ? 'index.php' : 'catalogo.php');