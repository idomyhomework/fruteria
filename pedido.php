<?php
// Confirmación: solo el Pedido que este Cliente acaba de hacer (guardado en su sesión).
require_once __DIR__ . '/includes/carrito.php';
require_once __DIR__ . '/includes/pedidos.php';

exigir_cliente();

$pedido = isset($_SESSION['pedido']) ? buscar_pedido($_SESSION['pedido']) : null;
if ($pedido === null) {
    redirigir('catalogo.php');
}

$total = 0;
foreach ($pedido['lineas'] as $l) {
    $total += $l['kilos'] * $l['precio'];
}

cabecera('Pedido nº ' . $pedido['id']);
?>
<h1>Pedido nº <?= (int) $pedido['id'] ?></h1>
<p>Gracias, <?= e($pedido['nombre']) ?>. Hemos recibido tu Pedido.</p>

<table class="catalogo carrito">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Kilos</th>
            <th>Precio (€/kg)</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pedido['lineas'] as $l): ?>
            <tr>
                <td><?= e($l['nombre']) ?></td>
                <td><?= numero_es($l['kilos']) ?> kg</td>
                <td><?= numero_es($l['precio']) ?> €</td>
                <td><?= numero_es($l['kilos'] * $l['precio']) ?> €</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p class="total">Total: <strong><?= numero_es($total) ?> €</strong></p>
<p><strong>Se paga al recoger</strong> en la tienda.</p>
<nav class="menu">
    <a href="catalogo.php">Volver al catálogo</a>
</nav>
<?php
pie();