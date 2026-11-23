<?php
require_once __DIR__ . '/includes/vista.php';
require_once __DIR__ . '/includes/productos.php';

$q = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$productos = listar_productos($q);

cabecera('Catálogo');
?>
<h1>Catálogo</h1>

<form method="get" action="catalogo.php" class="buscador" role="search">
    <label for="q">Buscar Producto</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>">
    <button type="submit">Buscar</button>
    <?php if ($q !== ''): ?>
        <a href="catalogo.php">Quitar búsqueda</a>
    <?php endif; ?>
</form>

<?php if (count($productos) === 0 && $q !== ''): ?>
    <p>No se ha encontrado ningún Producto.</p>
<?php elseif (count($productos) === 0): ?>
    <p>Todavía no hay Productos.</p>
<?php else: ?>
    <table class="catalogo">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Precio (€/kg)</th>
                <th>Cantidad (kg)</th>
                <?php if (es_administrador()): ?>
                    <th>Cambiar Cantidad</th>
                    <th>Eliminar</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td>
                        <?php if (es_url_imagen_valida($p['imagen_url'])): ?>
                            <img src="<?= e($p['imagen_url']) ?>" alt="<?= e($p['nombre']) ?>" width="80" height="80"
                                loading="lazy">
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['nombre']) ?></td>
                    <td><?= e($p['tipo']) ?></td>
                    <td><?= numero_es($p['precio']) ?> €</td>
                    <td>
                        <?php if ((float) $p['cantidad'] == 0): ?>
                            <span class="agotado">Agotado</span>
                        <?php else: ?>
                            <?= numero_es($p['cantidad']) ?> kg
                        <?php endif; ?>

                    </td>
                    <?php if (es_administrador()): ?>
                        <td>
                            <form method="post" action="actualizar.php" class="cambiar-cantidad">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="q" value="<?= e($q) ?>">
                                <label>
                                    Kilos
                                    <input type="text" name="cantidad" inputmode="decimal" size="6" required
                                        value="<?= number_format((float) $p['cantidad'], 2, ',', '') ?>">
                                </label>
                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="eliminar.php" class="eliminar"
                                data-pregunta="¿Eliminar <?= e($p['nombre']) ?>?"
                                onsubmit="return confirm(this.dataset.pregunta)">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="q" value="<?= e($q) ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php if (!es_administrador()): ?>
    <section class="acceso">
        <h2>Acceso del Administrador</h2>
        <form method="post" action="login.php">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" autocomplete="username" required>
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
            <button type="submit">Entrar</button>
        </form>
    </section>
<?php endif; ?>
<?php
pie();