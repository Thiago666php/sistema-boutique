<?php
/**
 * Controller: ReporteController — Celeste Boutique
 * Endpoint JSON protegido. Solo rol administrador.
 */
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Reporte.php';

// ── Protección ────────────────────────────────────────────────
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// ── Conexión ──────────────────────────────────────────────────
try {
    $db = (new Database())->conectar();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$model  = new Reporte($db);
$accion = $_GET['accion'] ?? '';

// ── Dispatcher ────────────────────────────────────────────────
try {
    switch ($accion) {

        case 'resumen':
            echo json_encode([
                'usuarios'         => $model->totalUsuarios(),
                'productos'        => $model->totalProductos(),
                'proveedores'      => $model->totalProveedores(),
                'categorias'       => $model->totalCategorias(),
                'valor_inventario' => $model->valorInventario(),
                'stock_bajo'       => $model->productosStockBajo(),
            ]);
            break;

        case 'productos_por_categoria':
            echo json_encode($model->productosPorCategoria());
            break;

        case 'usuarios_por_rol':
            echo json_encode($model->usuariosPorRol());
            break;

        case 'usuarios_por_mes':
            echo json_encode($model->usuariosPorMes());
            break;

        case 'top_productos_stock':
            echo json_encode($model->topProductosStock());
            break;

        case 'stock_critico':
            echo json_encode($model->productosStockCritico());
            break;

        case 'proveedores_activos':
            echo json_encode($model->proveedoresActivos());
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no reconocida: ' . htmlspecialchars($accion)]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
