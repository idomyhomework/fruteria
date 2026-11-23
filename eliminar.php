<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/productos.php';

exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('catalogo.php');
}

$id = is_string($_POST['id'] ?? null) ? $_POST['id'] : '';
$q = trim(is_string($_POST['q'] ?? null) ? $_POST['q'] : '');

if (!ctype_digit($id) || !eliminar_producto((int) $id)) {
    poner_aviso('Ese Producto ya no existe.');
} else {
    poner_aviso('Producto eliminado.');
}

redirigir('catalogo.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));