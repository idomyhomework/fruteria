<?php
// Pedidos del Cliente. Cada Línea copia nombre y Precio del Producto (ADR 0001).
require_once __DIR__ . '/db.php';

// Limpia y comprueba nombre y teléfono.
// Devuelve [nombre limpio, teléfono limpio (9 cifras), errores por campo].
function validar_cliente(string $nombre, string $telefono): array
{
    $errores = [];

    $nombre = trim($nombre);
    if ($nombre === '') {
        $errores['nombre'] = 'Escribe tu nombre.';
    } elseif (mb_strlen($nombre) > 50) {
        $errores['nombre'] = 'El nombre puede tener como máximo 50 caracteres.';
    }

    // "+34 612 34 56 78" -> "612345678"
    $telefono = preg_replace('/^\+34/', '', str_replace(' ', '', $telefono));
    if (!preg_match('/^\d{9}$/', $telefono)) {
        $errores['telefono'] = 'El teléfono debe tener 9 cifras (puedes poner +34 delante).';
    }

    return [$nombre, $telefono, $errores];
}

// Guarda el Pedido del Carrito en una sola transacción: bloquea los Productos,
// comprueba cada Línea, resta los kilos e inserta el Pedido y sus Líneas.
// Devuelve el id del Pedido, o los errores por id de Producto (y entonces no cambia nada).
function hacer_pedido(string $nombre, string $telefono, array $carrito): int|array
{
    ksort($carrito); // siempre en el mismo orden, para que dos Pedidos no se bloqueen entre sí

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $bloquear = $pdo->prepare('SELECT id, nombre, precio, cantidad FROM producto WHERE id = ? FOR UPDATE');
        $productos = [];
        $errores = [];
        foreach ($carrito as $id => $linea) {
            $bloquear->execute([$id]);
            $producto = $bloquear->fetch();
            if ($producto === false) {
                $errores[$id] = "{$linea['nombre']} ya no está a la venta.";
            } elseif ($linea['kilos'] > (float) $producto['cantidad']) {
                $errores[$id] = 'Solo quedan ' . numero_es($producto['cantidad']) . " kg de {$producto['nombre']}.";
            } else {
                $productos[$id] = $producto;
            }
        }
        if ($errores) {
            $pdo->rollBack();
            return $errores;
        }

        $pdo->prepare('INSERT INTO pedido (nombre, telefono) VALUES (?, ?)')->execute([$nombre, $telefono]);
        $pedido_id = (int) $pdo->lastInsertId();

        $restar = $pdo->prepare('UPDATE producto SET cantidad = cantidad - ? WHERE id = ?');
        $insertar = $pdo->prepare('INSERT INTO linea_pedido (pedido_id, producto_id, nombre, precio, kilos)
                                   VALUES (?, ?, ?, ?, ?)');
        foreach ($carrito as $id => $linea) {
            $kilos = number_format($linea['kilos'], 2, '.', '');
            $restar->execute([$kilos, $id]);
            $insertar->execute([$pedido_id, $id, $productos[$id]['nombre'], $productos[$id]['precio'], $kilos]);
        }

        $pdo->commit();
        return $pedido_id;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}

// Un Pedido con sus Líneas, o null si no existe.
function buscar_pedido(int $id): ?array
{
    $consulta = db()->prepare('SELECT id, nombre, telefono, creado, estado FROM pedido WHERE id = ?');
    $consulta->execute([$id]);
    $pedido = $consulta->fetch();
    if ($pedido === false) {
        return null;
    }

    $lineas = db()->prepare('SELECT nombre, precio, kilos FROM linea_pedido WHERE pedido_id = ? ORDER BY id');
    $lineas->execute([$id]);
    $pedido['lineas'] = $lineas->fetchAll();
    return $pedido;
}

// Estados del Pedido: valor en la BD => texto que se muestra.
const ESTADOS = [
    'en_espera' => 'En espera',
    'preparado' => 'Preparado para entrega',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

// A qué Estados se puede pasar desde cada uno. Entregado y Cancelado son finales.
const CAMBIOS = [
    'en_espera' => ['preparado', 'entregado', 'cancelado'],
    'preparado' => ['en_espera', 'entregado', 'cancelado'],
    'entregado' => [],
    'cancelado' => [],
];

// Todos los Pedidos con sus Líneas, del más nuevo al más antiguo.
function listar_pedidos(): array
{
    $ids = db()->query('SELECT id FROM pedido ORDER BY creado DESC, id DESC')->fetchAll(PDO::FETCH_COLUMN);
    return array_map(fn($id) => buscar_pedido((int) $id), $ids);
}

// Cambia el Estado de un Pedido en una sola transacción. Al cancelar, devuelve
// los kilos de cada Línea a su Producto (si todavía existe).
// Devuelve el aviso para el Administrador.
function cambiar_estado(int $id, string $estado): string
{
    if (!isset(ESTADOS[$estado])) {
        return 'Estado no válido.';
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $bloquear = $pdo->prepare('SELECT estado FROM pedido WHERE id = ? FOR UPDATE');
        $bloquear->execute([$id]);
        $actual = $bloquear->fetchColumn();
        if ($actual === false) {
            $pdo->rollBack();
            return 'Ese Pedido no existe.';
        }
        if (!in_array($estado, CAMBIOS[$actual], true)) {
            $pdo->rollBack();
            return "El Pedido nº $id no puede pasar de " . ESTADOS[$actual] . ' a ' . ESTADOS[$estado] . '.';
        }

        $pdo->prepare('UPDATE pedido SET estado = ? WHERE id = ?')->execute([$estado, $id]);
        if ($estado === 'cancelado') {
            // El JOIN salta las Líneas de Productos eliminados (producto_id vacío).
            $pdo->prepare('UPDATE producto p JOIN linea_pedido l ON l.producto_id = p.id
                           SET p.cantidad = p.cantidad + l.kilos
                           WHERE l.pedido_id = ?')->execute([$id]);
        }

        $pdo->commit();
        return "El Pedido nº $id ahora está " . ESTADOS[$estado] . '.';
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}