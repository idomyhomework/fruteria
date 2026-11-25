<?php
require_once __DIR__ . '/includes/vista.php';

cabecera('Inicio');
?>
<h1>Frutería Online</h1>
<p>Fruta y verdura fresca, cada día.</p>

<nav class="menu">
    <a href="catalogo.php">Ver el catálogo</a>
    <?php if (es_administrador()): ?>
        <a href="inventario.php">Inventario</a>
        <a href="pedidos.php">Pedidos</a>
    <?php endif; ?>
</nav>
<?php
pie();

