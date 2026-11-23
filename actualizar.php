<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/productos.php';

exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('catalogo.php');
}

$id = is_string($_POST['id'] ?? null) ? $_POST['id'] : '';
$cantidad = is_string($_POST['cantidad'] ?? null) ? trim($_POST['cantidad']) : '';
$q = trim(is_string($_POST['q'] ?? null) ? $_POST['q'] : '');

// Hasta 8 cifras enteras (cabe en DECIMAL(10,2)) y hasta 2 decimales, con coma o punto.
if (!preg_match('/^\d{1,8}([.,]\d{1,2})?$/', $cantidad)) {
    poner_aviso('Cantidad no válida: escribe un número de 0 en adelante, con 2 decimales como máximo (por ejemplo 2,5).');
} elseif (!ctype_digit($id) || !cambiar_cantidad((int) $id, str_replace(',', '.', $cantidad))) {
    poner_aviso('Ese Producto ya no existe.');
} else {
    poner_aviso('Cantidad actualizada.');
}

redirigir('catalogo.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));