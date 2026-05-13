-- ============================================================
-- Módulo Cajero — Celeste Boutique
-- Ejecutar en: bdboutique
-- ============================================================

USE `bdboutique`;

-- Ventas (cabecera)
CREATE TABLE IF NOT EXISTS `ventas` (
    `id_venta`      INT           PRIMARY KEY AUTO_INCREMENT,
    `id_usuario`    INT           NOT NULL COMMENT 'Cajero que realizó la venta',
    `cliente_nombre`VARCHAR(150)  DEFAULT 'Cliente general',
    `total`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado`        VARCHAR(20)   NOT NULL DEFAULT 'completada' COMMENT 'completada|anulada',
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Detalle de venta (líneas)
CREATE TABLE IF NOT EXISTS `venta_detalle` (
    `id_detalle`    INT           PRIMARY KEY AUTO_INCREMENT,
    `id_venta`      INT           NOT NULL,
    `id_producto`   INT           NOT NULL,
    `cantidad`      INT           NOT NULL DEFAULT 1,
    `precio_unit`   DECIMAL(10,2) NOT NULL,
    `subtotal`      DECIMAL(10,2) NOT NULL,
    CONSTRAINT `fk_det_venta`    FOREIGN KEY (`id_venta`)    REFERENCES `ventas`    (`id_venta`)    ON DELETE CASCADE,
    CONSTRAINT `fk_det_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Devoluciones
CREATE TABLE IF NOT EXISTS `devoluciones` (
    `id_devolucion` INT           PRIMARY KEY AUTO_INCREMENT,
    `id_venta`      INT           NOT NULL,
    `id_usuario`    INT           NOT NULL COMMENT 'Cajero que procesó la devolución',
    `motivo`        VARCHAR(255)  DEFAULT NULL,
    `monto`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_dev_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
