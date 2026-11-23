<?php
// Configuración de la aplicación.
// Las variables de entorno permiten que los tests usen otra base de datos.
return [
    'db' => [
        'host' => getenv('FRUTERIA_DB_HOST') ?: 'localhost',
        'nombre' => getenv('FRUTERIA_DB_NAME') ?: 'fruteria',
        'usuario' => getenv('FRUTERIA_DB_USER') ?: 'fruteria',
        'password' => getenv('FRUTERIA_DB_PASS') ?: 'JustHangingAround',
    ],
    'admin' => [
        'usuario' => getenv('FRUTERIA_ADMIN_USER') ?: 'admin',
        'hash' => getenv('FRUTERIA_ADMIN_HASH') ?: '$2y$10$MTVH.MEuYUzSEvo7StoRXe0yAPMRwv.18fhiWwISDlqTSIA74UuMC', #admin12345
    ],
];