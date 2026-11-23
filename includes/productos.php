<?php
require_once __DIR__ . '/db.php';

// Todos los Productos, ordenados por nombre.
// Productos cuyo nombre contiene $busqueda (sin distinguir mayúsculas ni acentos,
// gracias a la collation utf8mb4_spanish_ci), ordenados por nombre.
// Con $busqueda vacía devuelve todos.
function listar_productos(string $busqueda = ''): array
{
    // % y _ son comodines en LIKE: los escapamos para buscarlos literalmente.
    $patron = '%' . addcslashes($busqueda, '%_\\') . '%';

    $sql = 'SELECT id, nombre, tipo, precio, cantidad, imagen_url
            FROM producto
            WHERE nombre LIKE ?
            ORDER BY nombre';

    $consulta = db()->prepare($sql);
    $consulta->execute([$patron]);

    return $consulta->fetchAll();
}

// Pone la Cantidad total (en kilos) de un Producto.
// Devuelve false si el Producto no existe.
function cambiar_cantidad(int $id, string $cantidad): bool
{
    $existe = db()->prepare('SELECT 1 FROM producto WHERE id = ?');
    $existe->execute([$id]);
    if ($existe->fetchColumn() === false) {
        return false;
    }

    db()->prepare('UPDATE producto SET cantidad = ? WHERE id = ?')->execute([$cantidad, $id]);
    return true;
}

// Borra un Producto. Devuelve false si no existía.
function eliminar_producto(int $id): bool
{
    $consulta = db()->prepare('DELETE FROM producto WHERE id = ?');
    $consulta->execute([$id]);
    return $consulta->rowCount() > 0;
}

// Añade un Producto. Devuelve false si ya existe uno con ese nombre
// (lo decide la restricción UNIQUE de la BD, que ignora mayúsculas y acentos).
function crear_producto(string $nombre, string $tipo, string $precio, string $cantidad, string $imagen_url): bool
{
    $sql = 'INSERT INTO producto (nombre, tipo, precio, cantidad, imagen_url) VALUES (?, ?, ?, ?, ?)';
    try {
        db()->prepare($sql)->execute([$nombre, $tipo, $precio, $cantidad, $imagen_url]);
        return true;
    } catch (PDOException $ex) {
        if (($ex->errorInfo[1] ?? null) === 1062) { // 1062 = clave única duplicada
            return false;
        }
        throw $ex;
    }
}