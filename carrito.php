<?php
require_once __DIR__ . '/includes/carrito.php';
require_once __DIR__ . '/includes/pedidos.php';

exigir_cliente();

// Antes de pintar la cabecera, para que "Carrito (n)" ya no cuente las Líneas quitadas.
$lineas = [];
$quitadas = [];
$total = 0;
foreach (carrito() as $id => $linea) {
    $producto = buscar_producto($id);
    if ($producto === null) {
        quitar_linea($id);
        $quitadas[] = $linea['nombre'];
        continue;
    }
    $subtotal = $linea['kilos'] * (float) $producto['precio'];
    $total += $subtotal;
    $lineas[] = [
        'id' => $id,
        'nombre' => $producto['nombre'],
        'kilos' => $linea['kilos'],
        'precio' => $producto['precio'],
        'subtotal' => $subtotal,
    ];
}
// Hacer pedido. Si algo falla se vuelve a pintar esta página (sin redirigir)
// con los mensajes y lo que escribió el Cliente.
$valores = ['nombre' => '', 'telefono' => ''];
$errores = [];        // por campo: 'nombre', 'telefono'
$errores_linea = [];  // por id de Producto

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $_) {
        $valores[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
    }

    // Con el Carrito vacío o con Líneas recién quitadas no se pide nada:
    // el Cliente ve antes el Carrito tal como ha quedado.
    if ($lineas && !$quitadas) {
        [$nombre, $telefono, $errores] = validar_cliente($valores['nombre'], $valores['telefono']);
        if (!$errores) {
            $resultado = hacer_pedido($nombre, $telefono, carrito());
            if (is_int($resultado)) {
                $_SESSION['carrito'] = [];
                $_SESSION['pedido'] = $resultado;
                redirigir('pedido.php');
            }
            $errores_linea = $resultado;
        }
    }
}
cabecera('Carrito');
?>
<h1>Carrito</h1>

<?php foreach ($quitadas as $nombre): ?>
    <p class="aviso"><?= e($nombre) ?> ya no está a la venta y se ha quitado del Carrito.</p>
<?php endforeach; ?>

<?php if (count($lineas) === 0): ?>
    <p>Tu Carrito está vacío.</p>
    <nav class="menu">
        <a href="catalogo.php">Ver el catálogo</a>
    </nav>
<?php else: ?>
    <table class="catalogo carrito">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Kilos</th>
                <th>Precio (€/kg)</th>
                <th>Subtotal</th>
                <th>Quitar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lineas as $l): ?>
                <tr>
                    <td><?= e($l['nombre']) ?></td>
                    <td>
                        <form method="post" action="carrito_cambiar.php" class="cambiar-cantidad">
                            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                            <label>
                                Kilos
                                <input type="text" name="kilos" inputmode="decimal" size="6" required
                                    value="<?= number_format($l['kilos'], 2, ',', '') ?>">
                            </label>
                            <button type="submit">Cambiar</button>
                            <?php if (isset($errores_linea[$l['id']])): ?>
                                <p class="error">
                                    <?= e($errores_linea[$l['id']]) ?>
                                </p>
                            <?php endif; ?>
                        </form>
                    </td>
                    <td><?= numero_es($l['precio']) ?> €</td>
                    <td><?= numero_es($l['subtotal']) ?> €</td>
                    <td>
                        <form method="post" action="carrito_quitar.php" class="eliminar">
                            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                            <button type="submit">Quitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="total">Total: <strong><?= numero_es($total) ?> €</strong></p>
    <form method="post" action="carrito.php" class="inventario hacer-pedido" novalidate>
        <h2>Hacer pedido</h2>
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" maxlength="50" required value="<?= e($valores['nombre']) ?>">
        <?php if (isset($errores['nombre'])): ?>
            <p class="error"><?= e($errores['nombre']) ?></p>
        <?php endif; ?>
        <label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" required value="<?= e($valores['telefono']) ?>">
        <?php if (isset($errores['telefono'])): ?>
            <p class="error"><?= e($errores['telefono']) ?></p>
        <?php endif; ?>
        <p>Se paga al recoger en la tienda.</p>
        <button type="submit">Hacer pedido</button>
    </form>

<?php endif; ?>
<?php
pie();