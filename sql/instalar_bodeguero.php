<?php
/**
 * Instalador — Módulo Bodeguero
 * http://localhost:8081/sistema-boutique/sistema-boutique/sql/instalar_bodeguero.php
 */
require_once __DIR__ . '/../config/database.php';
$db  = (new Database())->conectar();
$log = [];

function q(PDO $db, string $sql, string $desc, array &$log): void {
    try { $db->exec($sql); $log[] = ['ok', $desc]; }
    catch (\Throwable $e) { $log[] = ['warn', "$desc — " . $e->getMessage()]; }
}

// Columna id_proveedor en productos (opcional, para trazabilidad)
q($db, "ALTER TABLE `productos` ADD COLUMN `id_proveedor` INT DEFAULT NULL AFTER `id_categoria`",
  "Agregar id_proveedor a productos", $log);

// Tabla movimientos_inventario
q($db, "CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
    `id_movimiento` INT          PRIMARY KEY AUTO_INCREMENT,
    `id_producto`   INT          NOT NULL,
    `tipo`          VARCHAR(30)  NOT NULL COMMENT 'entrada|perdida|merma|ajuste_positivo|ajuste_negativo',
    `cantidad`      INT          NOT NULL DEFAULT 0,
    `motivo`        VARCHAR(255) DEFAULT NULL,
    `id_usuario`    INT          NOT NULL,
    `id_proveedor`  INT          DEFAULT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Crear movimientos_inventario", $log);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalar Bodeguero</title>
    <style>
        body{font-family:sans-serif;background:#f0f4f6;display:flex;justify-content:center;padding:40px}
        .box{background:#fff;border-radius:16px;padding:32px;max-width:660px;width:100%;box-shadow:0 4px 20px rgba(0,0,0,.1)}
        h2{color:#1a2d47;margin:0 0 20px}
        ul{padding:0;margin:0 0 8px}
        li{padding:7px 0;border-bottom:1px solid #eef2f5;font-size:14px;list-style:none}
        .ok{color:#1a7a4a}.ok::before{content:'✅  '}
        .warn{color:#b7950b}.warn::before{content:'⚠️  '}
        .btn{display:inline-block;margin-top:24px;padding:11px 28px;background:#1a2d47;color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px}
    </style>
</head>
<body>
<div class="box">
    <h2>🛠 Instalación — Módulo Bodeguero</h2>
    <ul>
        <?php foreach ($log as [$t,$m]): ?>
            <li class="<?= $t ?>"><?= htmlspecialchars($m) ?></li>
        <?php endforeach; ?>
    </ul>
    <a class="btn" href="../views/dashboard/bodeguero.php">Ir al Dashboard del Bodeguero →</a>
</div>
</body>
</html>
