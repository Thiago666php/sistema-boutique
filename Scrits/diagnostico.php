<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();

echo "<pre style='font-family:monospace;font-size:13px;padding:20px;'>";

// Todas las tablas
echo "=== TABLAS EN LA BD ===\n";
foreach ($db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN) as $t) {
    echo "  - $t\n";
}

// Columnas de ventas si existe
echo "\n=== COLUMNAS DE ventas ===\n";
try {
    foreach ($db->query("DESCRIBE ventas")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  {$c['Field']} | {$c['Type']} | {$c['Null']} | {$c['Key']}\n";
    }
} catch(Exception $e) { echo "  (no existe)\n"; }

// FK que apuntan a ventas
echo "\n=== FK QUE REFERENCIAN ventas ===\n";
$stmt = $db->query("
    SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
      AND REFERENCED_TABLE_NAME = 'ventas'
");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  Tabla: {$r['TABLE_NAME']} | FK: {$r['CONSTRAINT_NAME']} | Col: {$r['COLUMN_NAME']}\n";
}

echo "\n=== COLUMNAS DE venta_detalle ===\n";
try {
    foreach ($db->query("DESCRIBE venta_detalle")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  {$c['Field']} | {$c['Type']}\n";
    }
} catch(Exception $e) { echo "  (no existe)\n"; }

echo "\n=== COLUMNAS DE devoluciones ===\n";
try {
    foreach ($db->query("DESCRIBE devoluciones")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  {$c['Field']} | {$c['Type']}\n";
    }
} catch(Exception $e) { echo "  (no existe)\n"; }

echo "</pre>";
?>
