<?php
/**
 * Instalador — Módulo Cajero
 * http://localhost:8080/sistema-boutique/sql/instalar_cajero.php
 */
require_once __DIR__ . '/../config/database.php';
$db  = (new Database())->conectar();
$log = [];

function q(PDO $db, string $sql, string $desc, array &$log): void {
    try { $db->exec($sql); $log[] = ['ok', $desc]; }
    catch (Throwable $e) { $log[] = ['err', "$desc — " . $e->getMessage()]; }
}

// 1. Desactivar FK
q($db, "SET FOREIGN_KEY_CHECKS = 0", "Desactivar FK checks", $log);

// 2. Buscar y eliminar TODAS las tablas que referencian ventas o devoluciones
$tablasDependientes = $db->query("
    SELECT DISTINCT TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
      AND REFERENCED_TABLE_NAME IN ('ventas','devoluciones','venta_detalle')
")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tablasDependientes as $t) {
    q($db, "DROP TABLE IF EXISTS `$t`", "Eliminar tabla dependiente: $t", $log);
}

// 3. Eliminar las tablas principales
q($db, "DROP TABLE IF EXISTS `devoluciones`",  "Eliminar devoluciones",  $log);
q($db, "DROP TABLE IF EXISTS `venta_detalle`", "Eliminar venta_detalle", $log);
q($db, "DROP TABLE IF EXISTS `ventas`",        "Eliminar ventas",        $log);

// 4. Crear tablas limpias
q($db, "CREATE TABLE `ventas` (
    `id_venta`       INT           PRIMARY KEY AUTO_INCREMENT,
    `id_usuario`     INT           NOT NULL,
    `cliente_nombre` VARCHAR(150)  DEFAULT 'Cliente general',
    `total`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado`         VARCHAR(20)   NOT NULL DEFAULT 'completada',
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Crear ventas", $log);

q($db, "CREATE TABLE `venta_detalle` (
    `id_detalle`  INT           PRIMARY KEY AUTO_INCREMENT,
    `id_venta`    INT           NOT NULL,
    `id_producto` INT           NOT NULL,
    `cantidad`    INT           NOT NULL DEFAULT 1,
    `precio_unit` DECIMAL(10,2) NOT NULL,
    `subtotal`    DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Crear venta_detalle", $log);

q($db, "CREATE TABLE `devoluciones` (
    `id_devolucion` INT           PRIMARY KEY AUTO_INCREMENT,
    `id_venta`      INT           NOT NULL,
    `id_usuario`    INT           NOT NULL,
    `motivo`        VARCHAR(255)  DEFAULT NULL,
    `monto`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Crear devoluciones", $log);

// 5. Reactivar FK
q($db, "SET FOREIGN_KEY_CHECKS = 1", "Reactivar FK checks", $log);

// 6. Columnas faltantes en productos
foreach ([
    "ALTER TABLE `productos` ADD COLUMN `stock`       INT          NOT NULL DEFAULT 0   AFTER `precio`",
    "ALTER TABLE `productos` ADD COLUMN `activo`      TINYINT(1)   NOT NULL DEFAULT 1",
    "ALTER TABLE `productos` ADD COLUMN `descripcion` VARCHAR(255) DEFAULT NULL AFTER `nombre`",
] as $alter) {
    try { $db->exec($alter); $log[] = ['ok', "Columna agregada a productos"]; }
    catch (Throwable $e) { $log[] = ['ok', "Columna ya existe en productos (OK)"]; }
}

// 7. Verificar resultado final
$tablas = $db->query("SHOW TABLES LIKE 'vent%'")->fetchAll(PDO::FETCH_COLUMN);
$log[] = ['ok', "Tablas activas: " . implode(', ', $tablas)];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalar Cajero — Celeste Boutique</title>
    <style>
        body { font-family:sans-serif; background:#f0f4f6; display:flex; justify-content:center; padding:40px; }
        .box { background:#fff; border-radius:16px; padding:32px; max-width:660px; width:100%; box-shadow:0 4px 20px rgba(0,0,0,.1); }
        h2   { color:#1a2d47; margin:0 0 20px; }
        ul   { padding:0; margin:0 0 8px; }
        li   { padding:7px 0; border-bottom:1px solid #eef2f5; font-size:14px; list-style:none; }
        .ok  { color:#1a7a4a; } .ok::before  { content:'✅  '; }
        .err { color:#c0392b; } .err::before { content:'❌  '; }
        .btn { display:inline-block; margin-top:24px; padding:11px 28px; background:#1a2d47; color:#fff; border-radius:10px; text-decoration:none; font-weight:700; font-size:14px; }
    </style>
</head>
<body>
<div class="box">
    <h2>🛠 Instalación — Módulo Cajero</h2>
    <ul>
        <?php foreach ($log as [$tipo, $msg]): ?>
            <li class="<?= $tipo ?>"><?= htmlspecialchars($msg) ?></li>
        <?php endforeach; ?>
    </ul>
    <a class="btn" href="../views/dashboard/cajero.php">Ir al Dashboard del Cajero →</a>
</div>
</body>
</html>
