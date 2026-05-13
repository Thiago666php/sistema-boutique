<?php
/**
 * INSTALADOR DE TABLA PROVEEDORES
 * ─────────────────────────────────────────────────────────
 * Abre este archivo UNA SOLA VEZ en el navegador:
 *   http://localhost/sistema-boutique/sistema-boutique/sql/instalar_proveedores.php
 *
 * Después de ejecutarlo puedes eliminarlo o dejarlo,
 * ya que usa IF NOT EXISTS y no rompe nada si se corre de nuevo.
 * ─────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

$pasos = [];

// ── 1. Crear tabla ────────────────────────────────────────
try {
    $db->exec("
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
    ");
    $pasos[] = ['ok', 'Tabla <strong>proveedores</strong> creada (o ya existía).'];
} catch (Exception $e) {
    $pasos[] = ['err', 'Error al crear tabla: ' . $e->getMessage()];
}

// ── 2. Insertar datos de ejemplo (solo si la tabla está vacía) ──
try {
    $count = $db->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();

    if ($count == 0) {
        $db->exec("
            INSERT INTO `proveedores` (`nombre`, `ruc`, `telefono`, `correo`, `direccion`, `activo`) VALUES
            ('Textiles Moda S.A.',      '20100012345', '+51 999 111 222', 'ventas@textilesmoda.com',    'Av. Industrial 123, Lima',     1),
            ('Distribuidora Elegance',  '20200023456', '+51 988 222 333', 'contacto@elegance.com',      'Jr. Comercio 456, Miraflores', 1),
            ('Importaciones Chic',      '20300034567', '+51 977 333 444', 'info@importacioneschic.com', 'Calle Moda 789, San Isidro',   1),
            ('Confecciones del Norte',  '20400045678', '+51 966 444 555', 'norte@confecciones.com',     'Av. Libertad 321, Trujillo',   1),
            ('Fashion Supply Co.',      '20500056789', '+51 955 555 666', 'supply@fashionco.com',       'Calle Boutique 654, Surco',    1);
        ");
        $pasos[] = ['ok', 'Se insertaron <strong>5 proveedores de ejemplo</strong>.'];
    } else {
        $pasos[] = ['info', "La tabla ya tiene <strong>{$count} proveedor(es)</strong>. No se insertaron datos de ejemplo."];
    }
} catch (Exception $e) {
    $pasos[] = ['err', 'Error al insertar datos: ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalador — Proveedores</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-8 space-y-6">

        <div class="flex items-center gap-3">
            <span class="text-3xl">🛠️</span>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Instalador de Proveedores</h1>
                <p class="text-sm text-slate-500">Base de datos: <strong>bdboutique</strong></p>
            </div>
        </div>

        <ul class="space-y-3">
            <?php foreach ($pasos as [$tipo, $msg]): ?>
            <?php
                $color = match($tipo) {
                    'ok'   => 'bg-green-50 border-green-200 text-green-800',
                    'err'  => 'bg-red-50 border-red-200 text-red-800',
                    default=> 'bg-blue-50 border-blue-200 text-blue-800',
                };
                $icon = match($tipo) {
                    'ok'   => '✅',
                    'err'  => '❌',
                    default=> 'ℹ️',
                };
            ?>
            <li class="flex items-start gap-3 px-4 py-3 rounded-xl border <?= $color ?>">
                <span class="text-lg leading-none mt-0.5"><?= $icon ?></span>
                <span class="text-sm"><?= $msg ?></span>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="pt-2 flex gap-3">
            <a href="../views/dashboard/proveedores.php"
               class="flex-1 text-center bg-slate-800 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-slate-700 transition-colors">
                Ir a Proveedores →
            </a>
            <a href="../views/usuarios/login.php"
               class="flex-1 text-center border border-slate-300 text-slate-600 text-sm font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                Ir al Login
            </a>
        </div>

    </div>
</body>
</html>
