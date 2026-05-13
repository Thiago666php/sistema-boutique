-- ============================================================
-- Base de datos: bdboutique
-- Tabla: proveedores
-- Ejecutar en: Laragon > HeidiSQL o phpMyAdmin
-- ============================================================

USE `bdboutique`;

-- Crear tabla proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
    `id_proveedor` INT          NOT NULL AUTO_INCREMENT,
    `nombre`       VARCHAR(150) NOT NULL,
    `ruc`          VARCHAR(20)  DEFAULT NULL,
    `telefono`     VARCHAR(30)  DEFAULT NULL,
    `correo`       VARCHAR(150) DEFAULT NULL,
    `direccion`    VARCHAR(255) DEFAULT NULL,
    `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_proveedor`),
    UNIQUE KEY `uq_proveedor_ruc` (`ruc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Datos de ejemplo (opcional — puedes borrar este bloque)
-- ============================================================
INSERT INTO `proveedores` (`nombre`, `ruc`, `telefono`, `correo`, `direccion`, `activo`) VALUES
('Textiles Moda S.A.',      '20100012345', '+51 999 111 222', 'ventas@textilesmoda.com',   'Av. Industrial 123, Lima',        1),
('Distribuidora Elegance',  '20200023456', '+51 988 222 333', 'contacto@elegance.com',     'Jr. Comercio 456, Miraflores',    1),
('Importaciones Chic',      '20300034567', '+51 977 333 444', 'info@importacioneschic.com','Calle Moda 789, San Isidro',      1),
('Confecciones del Norte',  '20400045678', '+51 966 444 555', 'norte@confecciones.com',    'Av. Libertad 321, Trujillo',      1),
('Fashion Supply Co.',      '20500056789', '+51 955 555 666', 'supply@fashionco.com',      'Calle Boutique 654, Surco',       1);
