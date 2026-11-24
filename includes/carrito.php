<?php
// Carrito del Cliente. Vive en la sesión: id de Producto => ['nombre' => ..., 'kilos' => ...].
// Nunca se guarda en la base de datos y no reserva nada.
// Guardamos también el nombre para poder avisar si el Producto se elimina.
require_once __DIR__ . '/vista.php';
require_once __DIR__ . '/productos.php';

function carrito(): array
{
    return $_SESSION['carrito'] ?? [];
}

// Para la página y las acciones del Carrito: el Administrador no lo usa.
function exigir_cliente(): void
{
    if (es_administrador()) {
        poner_aviso('El Administrador no usa el Carrito.');
        redirigir('catalogo.php');
    }
}

// "1,5" -> 1.5. Devuelve null si no es un número mayor que 0 con 2 decimales como máximo.
function leer_kilos(string $texto): ?float
{
    if (!preg_match('/^\d{1,8}([.,]\d{1,2})?$/', $texto)) {
        return null;
    }
    $kilos = (float) str_replace(',', '.', $texto);
    return $kilos > 0 ? $kilos : null;
}

// Pone los kilos de la Línea de un Producto (o se los suma, si $sumar).
// Si algo no es válido no cambia nada. Devuelve el aviso para el Cliente.
function guardar_linea(int $id, string $texto, bool $sumar): string
{
    $kilos = leer_kilos($texto);
    if ($kilos === null) {
        return 'Kilos no válidos: escribe un número mayor que 0, con 2 decimales como máximo (por ejemplo 1,5).';
    }

    $producto = buscar_producto($id);
    if ($producto === null) {
        return 'Ese Producto ya no existe.';
    }

    if ($sumar) {
        $kilos += carrito()[$id]['kilos'] ?? 0;
    }
    $kilos = round($kilos, 2); // evita restos como 0.30000000000000004

    if ($kilos > (float) $producto['cantidad']) {
        return 'Solo quedan ' . numero_es($producto['cantidad']) . " kg de {$producto['nombre']}.";
    }

    $_SESSION['carrito'][$id] = ['nombre' => $producto['nombre'], 'kilos' => $kilos];
    return $sumar ? "Se ha añadido {$producto['nombre']} al Carrito." : 'Carrito actualizado.';
}

function quitar_linea(int $id): void
{
    unset($_SESSION['carrito'][$id]);
}