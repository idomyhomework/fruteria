<?php
// Pedidos: solo el Administrador. Del más nuevo al más antiguo.
require_once __DIR__ . '/includes/vista.php';
require_once __DIR__ . '/includes/pedidos.php';

exigir_administrador();

$pedidos = listar_pedidos();

cabecera('Pedidos');
migas('Pedidos');
?>
<h1>Pedidos</h1>

<?php if (!$pedidos): ?>
        <p>Todavía no hay Pedidos.</p>
<?php endif; ?>

<?php foreach ($pedidos as $pedido): ?>
        <?php
        $total = 0;
        foreach ($pedido['lineas'] as $l) {
            $total += $l['kilos'] * $l['precio'];
        }
        ?>
        <section class="pedido">
            <h2>Pedido nº <?= (int) $pedido['id'] ?></h2>
            <p>
                <?= date('d/m/Y H:i', strtotime($pedido['creado'])) ?> ·
                <?= e($pedido['nombre']) ?> · <?= e($pedido['telefono']) ?> ·
                Estado: <strong><?= ESTADOS[$pedido['estado']] ?></strong>
            </p>

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

            <?php if (CAMBIOS[$pedido['estado']]): ?>
                    <!-- Si se elige un Estado final, la opción lleva la pregunta para confirm(). -->
                    <form method="post" action="pedido_estado.php" class="cambiar-estado"
                        onsubmit="const p = this.estado.selectedOptions[0].dataset.pregunta; return !p || confirm(p)">
                        <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                        <select name="estado">
                            <?php foreach (CAMBIOS[$pedido['estado']] as $estado): ?>
                                    <option value="<?= $estado ?>"
                                        <?php if (!CAMBIOS[$estado]): ?>
                                                data-pregunta="¿Marcar el Pedido nº <?= (int) $pedido['id'] ?> como <?= ESTADOS[$estado] ?>?"
                                        <?php endif; ?>><?= ESTADOS[$estado] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Cambiar</button>
                    </form>
            <?php endif; ?>
        </section>
<?php endforeach; ?>
<?php
pie();