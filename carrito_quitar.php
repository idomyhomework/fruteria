<?php
require_once __DIR__ . '/includes/carrito.php';

exigir_cliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('carrito.php');
}

$id = is_string($_POST['id'] ?? null) ? $_POST['id'] : '';

if (ctype_digit($id)) {
    quitar_linea((int) $id);
}
poner_aviso('Línea quitada del Carrito.');

redirigir('carrito.php');