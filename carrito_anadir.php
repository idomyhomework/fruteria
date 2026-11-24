<?php
require_once __DIR__ . '/includes/carrito.php';

exigir_cliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('catalogo.php');
}

$id = is_string($_POST['id'] ?? null) ? $_POST['id'] : '';
$kilos = is_string($_POST['kilos'] ?? null) ? trim($_POST['kilos']) : '';
$q = trim(is_string($_POST['q'] ?? null) ? $_POST['q'] : '');

poner_aviso(ctype_digit($id) ? guardar_linea((int) $id, $kilos, true) : 'Ese Producto ya no existe.');

redirigir('catalogo.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));