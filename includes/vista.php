<?php
require_once __DIR__ . '/carrito.php';
require_once __DIR__ . '/sesion.php';
// Escapa texto para mostrarlo en HTML (evita inyección de HTML/JS).
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// 9.9 -> "9,90"
function numero_es(string|float $numero): string
{
    return number_format((float) $numero, 2, ',', '.');
}

// Solo mostramos imágenes con URL http(s).
function es_url_imagen_valida(string $url): bool
{
    return preg_match('#^https?://#i', $url) === 1;
}

function cabecera(string $titulo): void
{
    ?>
    <!doctype html>
    <html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($titulo) ?> · Frutería Online</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <header class="cabecera">
            <a href="index.php" class="marca">Frutería Online</a>
            <?php if (es_administrador()): ?>
                <form method="post" action="logout.php" class="salir">
                    <input type="hidden" name="volver" value="<?= e(basename($_SERVER['SCRIPT_NAME'])) ?>">
                    <button type="submit">Cerrar sesión</button>
                </form>
            <?php else :?>
                 <a href="carrito.php" class="enlace-carrito">Carrito (<?= count(carrito()) ?>)</a>
            <?php endif; ?>
        </header>
        <main>
            <?php if ($aviso = tomar_aviso()): ?>
                <p class="aviso"><?= e($aviso) ?></p>
            <?php endif; ?>
            <?php
}

// Ruta del Administrador: "Principal > Página".
function migas(string $pagina): void
{
    ?>
    <nav class="migas" aria-label="Ruta">
        <a href="index.php">Principal</a> &gt; <span aria-current="page"><?= e($pagina) ?></span>
    </nav>
    <?php
}

function pie(): void
{
    ?>
        </main>
    </body>

    </html>
    <?php
}