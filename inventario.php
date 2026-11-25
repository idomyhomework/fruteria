<?php
require_once __DIR__ . '/includes/vista.php';
require_once __DIR__ . '/includes/productos.php';

exigir_administrador();

// Lo que escribió el Administrador (vacío al abrir la página).
$valores = ['nombre' => '', 'tipo' => 'fruta', 'precio' => '', 'cantidad' => '', 'imagen_url' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $_) {
        $valores[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
    }

    if ($valores['nombre'] === '') {
        $errores['nombre'] = 'Escribe el nombre del Producto.';
    } elseif (mb_strlen($valores['nombre']) > 50) {
        $errores['nombre'] = 'El nombre puede tener como máximo 50 caracteres.';
    }

    if (!in_array($valores['tipo'], ['fruta', 'verdura'], true)) {
        $errores['tipo'] = 'Elige fruta o verdura.';
    }

    // Precio: DECIMAL(8,2) -> hasta 6 cifras enteras, 2 decimales, y mayor que 0.
    $precio = str_replace(',', '.', $valores['precio']);
    if (!preg_match('/^\d{1,6}([.,]\d{1,2})?$/', $valores['precio']) || (float) $precio <= 0) {
        $errores['precio'] = 'Precio no válido: escribe un número mayor que 0, con 2 decimales como máximo (por ejemplo 2,5).';
    }

    // Cantidad: DECIMAL(10,2) -> hasta 8 cifras enteras, como en actualizar.php.
    $cantidad = str_replace(',', '.', $valores['cantidad']);
    if (!preg_match('/^\d{1,8}([.,]\d{1,2})?$/', $valores['cantidad'])) {
        $errores['cantidad'] = 'Cantidad no válida: escribe un número de 0 en adelante, con 2 decimales como máximo (por ejemplo 2,5).';
    }

    if (!es_url_imagen_valida($valores['imagen_url']) || mb_strlen($valores['imagen_url']) > 500) {
        $errores['imagen_url'] = 'La URL de la imagen debe empezar por http:// o https:// (máximo 500 caracteres).';
    }

    if (!$errores) {
        if (crear_producto($valores['nombre'], $valores['tipo'], $precio, $cantidad, $valores['imagen_url'])) {
            poner_aviso('Producto guardado: ' . $valores['nombre'] . '.');
            redirigir('inventario.php');
        }
        $errores['nombre'] = 'Ya existe un Producto con ese nombre.';
    }
}

// Mensaje de error de un campo, justo debajo de él.
function error_de(array $errores, string $campo): void
{
    if (isset($errores[$campo])) {
        echo '<p class="error" id="error-' . $campo . '">' . e($errores[$campo]) . '</p>';
    }
}

cabecera('Inventario');
migas('Inventario');
?>
<h1>Inventario</h1>
<h4>Nuevo producto</h4>

<form method="post" action="inventario.php" class="inventario" novalidate>
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" maxlength="50" required value="<?= e($valores['nombre']) ?>">
    <?php error_de($errores, 'nombre'); ?>
    <br>
    <label for="tipo">Tipo</label>
    <select id="tipo" name="tipo">
        <?php foreach (['fruta', 'verdura'] as $tipo): ?>
            <option value="<?= $tipo ?>" <?= $valores['tipo'] === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
        <?php endforeach; ?>
    </select>
    <?php error_de($errores, 'tipo'); ?>
    <br>
    <label for="precio">Precio (€/kg)</label>
    <input type="text" id="precio" name="precio" inputmode="decimal" required value="<?= e($valores['precio']) ?>">
    <?php error_de($errores, 'precio'); ?>
    <br>
    <label for="cantidad">Cantidad (kg)</label>
    <input type="text" id="cantidad" name="cantidad" inputmode="decimal" required
        value="<?= e($valores['cantidad']) ?>">
    <?php error_de($errores, 'cantidad'); ?>
    <br>
    <label for="imagen_url">URL de la imagen</label>
    <input type="url" id="imagen_url" name="imagen_url" maxlength="500" required
        value="<?= e($valores['imagen_url']) ?>">
    <?php error_de($errores, 'imagen_url'); ?>
    <br>
    <button type="submit">Guardar</button>
</form>
<?php
pie();