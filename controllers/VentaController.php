<?php
/**
 * Controller: VentaController — Celeste Boutique
 * Maneja ventas, devoluciones y datos para recibos.
 * Accesible por cajero y administrador.
 */
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Venta.php';

// ── Protección ────────────────────────────────────────────────
if (!isset($_SESSION['usuario'])) {
    header('Location: ../views/usuarios/login.php');
    exit;
}

$rol = $_SESSION['usuario']['rol'];
if (!in_array($rol, ['cajero', 'administrador'])) {
    header('Location: ../views/usuarios/login.php');
    exit;
}

$db    = (new Database())->conectar();
$model = new Venta($db);
$accion = $_GET['accion'] ?? '';

// ── Dispatcher ────────────────────────────────────────────────
switch ($accion) {

    // ── Registrar venta ──────────────────────────────────────
    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirigir(); }

        $cliente = trim($_POST['cliente_nombre'] ?? 'Cliente general');
        $items   = $_POST['items'] ?? [];   // array de {id_producto, cantidad}

        if (empty($items)) {
            alerta('warning', 'Sin productos', 'Agrega al menos un producto a la venta.');
            redirigir();
        }

        $resultado = $model->crearVenta(
            (int) $_SESSION['usuario']['id_usuario'],
            $cliente,
            $items
        );

        if (is_int($resultado)) {
            alerta('success', 'Venta registrada', "Venta #{$resultado} completada correctamente.");
            // Redirigir al recibo
            header("Location: ../views/dashboard/cajero.php?tab=recibo&id={$resultado}");
            exit;
        } else {
            alerta('error', 'Error', $resultado);
            redirigir();
        }
        break;

    // ── Devolución ───────────────────────────────────────────
    case 'devolucion':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirigir(); }

        $idVenta = (int) ($_POST['id_venta'] ?? 0);
        $motivo  = trim($_POST['motivo'] ?? '');

        if (!$idVenta) {
            alerta('warning', 'Datos inválidos', 'Número de venta requerido.');
            redirigir('devoluciones');
        }

        $resultado = $model->registrarDevolucion(
            $idVenta,
            (int) $_SESSION['usuario']['id_usuario'],
            $motivo
        );

        if ($resultado === true) {
            alerta('success', 'Devolución registrada', "La venta #{$idVenta} fue anulada y el stock repuesto.");
        } else {
            alerta('error', 'Error', $resultado);
        }
        redirigir('devoluciones');
        break;

    default:
        redirigir();
}

// ── Helpers ───────────────────────────────────────────────────
function alerta(string $icon, string $title, string $text): void
{
    $_SESSION['alert'] = compact('icon', 'title', 'text');
}

function redirigir(string $tab = 'ventas'): never
{
    header("Location: ../views/dashboard/cajero.php?tab={$tab}");
    exit;
}
?>
