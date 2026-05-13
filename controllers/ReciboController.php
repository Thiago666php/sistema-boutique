<?php
/**
 * Controller: ReciboController
 * Devuelve el HTML del recibo para carga AJAX.
 */
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Venta.php';

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['cajero','administrador'])) {
    http_response_code(403);
    echo '<p style="color:red">Acceso denegado.</p>';
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo '<p style="text-align:center;color:#aaa;padding:30px;">Ingresa un número de venta válido.</p>';
    exit;
}

$db    = (new Database())->conectar();
$model = new Venta($db);

$recibo        = $model->obtenerVentaPorId($id);
$reciboDetalle = $recibo ? $model->obtenerDetalleVenta($id) : [];

if (!$recibo) {
    echo '<p style="text-align:center;color:#c0392b;padding:30px;"><i class="fas fa-exclamation-circle"></i> Venta #'.$id.' no encontrada.</p>';
    exit;
}

include __DIR__ . '/../views/partials/recibo_template.php';
?>
