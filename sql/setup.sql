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