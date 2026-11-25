<?php
// Acción del Administrador: cambia el Estado de un Pedido y vuelve a Pedidos (PRG).
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/pedidos.php';

exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('pedidos.php');
}

$id = is_string($_POST['id'] ?? null) ? $_POST['id'] : '';
$estado = is_string($_POST['estado'] ?? null) ? $_POST['estado'] : '';

poner_aviso(ctype_digit($id) ? cambiar_estado((int) $id, $estado) : 'Ese Pedido no existe.');
redirigir('pedidos.php');