<?php
require_once __DIR__ . '/includes/carrito.php';

exigir_cliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('carrito.php');
}

$id = is_string($_POST['id'] ?? null) ? $_POST['id'] : '';
$kilos = is_string($_POST['kilos'] ?? null) ? trim($_POST['kilos']) : '';

if (!ctype_digit($id) || !isset(carrito()[(int) $id])) {
    poner_aviso('Esa Línea ya no está en el Carrito.');
} else {
    poner_aviso(guardar_linea((int) $id, $kilos, false));
}

redirigir('carrito.php');