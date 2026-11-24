CREATE TABLE producto (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)   NOT NULL,
    tipo        ENUM('fruta', 'verdura') NOT NULL,
    precio      DECIMAL(8,2)  NOT NULL CHECK (precio > 0),     -- € por kilo
    cantidad    DECIMAL(10,2) NOT NULL CHECK (cantidad >= 0),  -- kilos en stock
    imagen_url  VARCHAR(500)  NOT NULL,
    UNIQUE KEY uq_producto_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO producto (nombre, tipo, precio, cantidad, imagen_url) VALUES
('Fruta de la pasión', 'fruta', 9.90, 5.00,
 'https://commons.wikimedia.org/wiki/Special:FilePath/Passion_fruit_cross_section.jpg');


 -- Ticket 09: Pedidos. Cada Línea copia nombre y Precio del Producto (ADR 0001).
CREATE TABLE pedido (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(50)  NOT NULL,
    telefono  CHAR(9)      NOT NULL,
    creado    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado    ENUM('en_espera', 'preparado', 'entregado', 'cancelado') NOT NULL DEFAULT 'en_espera'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE linea_pedido (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    pedido_id    INT UNSIGNED  NOT NULL,
    producto_id  INT UNSIGNED  NULL,                          -- vacío si se elimina el Producto
    nombre       VARCHAR(50)   NOT NULL,                      -- copiado al hacer el Pedido
    precio       DECIMAL(8,2)  NOT NULL,                      -- copiado al hacer el Pedido
    kilos        DECIMAL(10,2) NOT NULL CHECK (kilos > 0),
    FOREIGN KEY (pedido_id)   REFERENCES pedido (id)   ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;