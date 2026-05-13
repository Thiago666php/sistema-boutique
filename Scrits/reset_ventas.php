<?php
/**
 * Script de instalación — Módulo Cajero
 * Elimina y recrea las tablas ventas, venta_detalle y devoluciones
 * con la estructura correcta para Celeste Boutique.
 *
 * Ejecutar UNA SOLA VEZ desde el navegador:
 *   http://localhost:8080/sistema-boutique/Scrits/reset_ventas.php
 */
require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();
$log = [];

function run(PDO $db, string $sql, string $desc, array &$log): void {
    try {
        $db->exec($sql);
        $log[] = "✅ $desc";
    } catch (Throwable $e) {
        $log[] = "⚠️  $desc — " . $e->getMessage();
    }
}

// 1. Desactivar FK checks para poder eliminar sin orden
run($db, "SET FOREIGN_KEY_CHECKS = 0", "Desactivar FK checks", $log);

// 2. Eliminar tablas viejas
run($db, "DROP TABLE IF EXISTS `devoluciones`",  "Eliminar devoluciones",  $log);
run($db, "DROP TABLE IF EXISTS `venta_detalle`", "Eliminar venta_detalle", $log);
run($db, "DROP TABLE IF EXISTS `ventas`",        "Eliminar ventas",        $log);

// 3. Recrear con estructura correcta
run($db, "
CREATE TABLE `ventas` (
    `id_venta`       INT           PRIMARY KEY AUTO_INCREMENT,
    `id_usuario`     INT           NOT NULL,
    `cliente_nombre` VARCHAR(150)  DEFAULT 'Cliente general',
    `total`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado`         VARCHAR(20)   NOT NULL DEFAULT 'completada',
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
", "Crear ventas", $log);

run($db, "
CREATE TABLE `venta_detalle` (
    `id_detalle`  INT           PRIMARY KEY AUTO_INCREMENT,
    `id_venta`    INT           NOT NULL,
    `id_producto` INT           NOT NULL,
    `cantidad`    INT           NOT NULL DEFAULT 1,
    `precio_unit` DECIMAL(10,2) NOT NULL,
    `subtotal`    DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
", "Crear venta_detalle", $log);

run($db, "
CREATE TABLE `devoluciones` (
    `id_devolucion` INT           PRIMARY KEY AUTO_INCREMENT,
    `id_venta`      INT           NOT NULL,
    `id_usuario`    INT           NOT NULL,
    `motivo`        VARCHAR(255)  DEFAULT NULL,
    `monto`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
", "Crear devoluciones", $log);

// 4. Agregar FK
run($db, "ALTER TABLE `venta_detalle` ADD CONSTRAINT `fk_det_venta`    FOREIGN KEY (`id_venta`)    REFERENCES `ventas`(`id_venta`)    ON DELETE CASCADE",  "FK venta_detalle → ventas",   $log);
run($db, "ALTER TABLE `venta_detalle` ADD CONSTRAINT `fk_det_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`) ON DELETE RESTRICT", "FK venta_detalle → productos", $log);
run($db, "ALTER TABLE `devoluciones`  ADD CONSTRAINT `fk_dev_venta`    FOREIGN KEY (`id_venta`)    REFERENCES `ventas`(`id_venta`)    ON DELETE RESTRICT", "FK devoluciones → ventas",    $log);

// 5. Reactivar FK checks
run($db, "SET FOREIGN_KEY_CHECKS = 1", "Reactivar FK checks", $log);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reset Ventas — Celeste Boutique</title>
    <style>
        body { font-family: sans-serif; background: #f0f4f6; display:flex; justify-content:center; padding:40px; }
        .box { background:#fff; border-radius:16px; padding:32px; max-width:600px; width:100%; box-shadow:0 4px 20px rgba(0,0,0,.1); }
        h2   { color:#1a2d47; margin:0 0 20px; }
        li   { padding:6px 0; border-bottom:1px solid #eef2f5; font-size:14px; }
        .ok  { color:#1a7a4a; }
        .btn { display:inline-block; margin-top:24px; padding:10px 24px; background:#1a2d47; color:#fff; border-radius:10px; text-decoration:none; font-weight:700; font-size:14px; }
    </style>
</head>
<body>
<div class="box">
    <h2>🛠 Instalación — Módulo Cajero</h2>
    <ul>
        <?php foreach ($log as $l): ?>
            <li class="<?= str_starts_with($l,'✅')?'ok':'' ?>"><?= htmlspecialchars($l) ?></li>
        <?php endforeach; ?>
    </ul>
    <a class="btn" href="../views/dashboard/cajero.php">Ir al Dashboard del Cajero →</a>
</div>
</body>
</html>
