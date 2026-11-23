<?php
// Devuelve siempre la misma conexión PDO a MariaDB.
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = (require __DIR__ . '/config.php')['db'];
        $dsn = "mysql:host={$config['host']};dbname={$config['nombre']};charset=utf8mb4";

        $pdo = new PDO($dsn, $config['usuario'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}