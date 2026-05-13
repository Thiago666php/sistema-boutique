-- ============================================================
-- Tabla: categorias_productos
-- ============================================================
CREATE TABLE IF NOT EXISTS `categorias_productos` (
  `id_categoria`  INT          PRIMARY KEY AUTO_INCREMENT,
  `nombre`        VARCHAR(100) NOT NULL,
  `descripcion`   VARCHAR(255) DEFAULT NULL,
  `activo`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabla: productos
-- ============================================================
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto`   INT          PRIMARY KEY AUTO_INCREMENT,
  `id_categoria`  INT          NOT NULL,
  `nombre`        VARCHAR(150) NOT NULL,
  `descripcion`   VARCHAR(255) DEFAULT NULL,
  `precio`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`         INT          NOT NULL DEFAULT 0,
  `activo`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_producto_categoria`
    FOREIGN KEY (`id_categoria`) REFERENCES `categorias_productos` (`id_categoria`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
