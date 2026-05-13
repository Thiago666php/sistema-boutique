<?php
/**
 * Controller: InventarioController — Celeste Boutique
 */
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Inventario.php';

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['bodeguero','administrador'])) {
    header('Location: ../views/usuarios/login.php');
    exit;
}

$db    = (new Database())->conectar();
$model = new Inventario($db);
$uid   = (int) $_SESSION['usuario']['id_usuario'];
$accion = $_GET['accion'] ?? '';

switch ($accion) {

    case 'crear_producto':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redir(); }
        $res = $model->crearProducto([
            'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'precio'       => (float)($_POST['precio'] ?? 0),
            'stock'        => (int)($_POST['stock'] ?? 0),
        ]);
        alerta($res === true ? 'success' : 'error', $res === true ? 'Producto creado' : 'Error', $res === true ? 'Producto registrado correctamente.' : $res);
        redir('inventario');

    case 'editar_producto':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redir(); }
        $id  = (int)($_POST['id_producto'] ?? 0);
        $res = $model->actualizarProducto($id, [
            'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'precio'       => (float)($_POST['precio'] ?? 0),
        ]);
        alerta($res === true ? 'success' : 'error', $res === true ? 'Actualizado' : 'Error', $res === true ? 'Producto actualizado.' : $res);
        redir('inventario');

    case 'entrada':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redir(); }
        $res = $model->registrarEntrada(
            (int)($_POST['id_producto'] ?? 0),
            (int)($_POST['cantidad']    ?? 0),
            $uid,
            trim($_POST['motivo'] ?? 'Entrada de mercancía'),
            ($_POST['id_proveedor'] ?? '') !== '' ? (int)$_POST['id_proveedor'] : null
        );
        alerta($res === true ? 'success' : 'error', $res === true ? 'Entrada registrada' : 'Error', $res === true ? 'Stock actualizado correctamente.' : $res);
        redir('entradas');

    case 'ajuste':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redir(); }
        $res = $model->registrarAjuste(
            (int)($_POST['id_producto'] ?? 0),
            (int)($_POST['cantidad']    ?? 0),
            trim($_POST['tipo_ajuste']  ?? 'ajuste_negativo'),
            $uid,
            trim($_POST['motivo'] ?? '')
        );
        alerta($res === true ? 'success' : 'error', $res === true ? 'Ajuste registrado' : 'Error', $res === true ? 'Inventario ajustado correctamente.' : $res);
        redir('ajustes');

    default:
        redir();
}

function alerta(string $icon, string $title, string $text): void
{
    $_SESSION['alert'] = compact('icon','title','text');
}

function redir(string $tab = 'inventario'): never
{
    header("Location: ../views/dashboard/bodeguero.php?tab={$tab}");
    exit;
}
?>
