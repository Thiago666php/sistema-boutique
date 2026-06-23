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

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = subirImagen($_FILES['imagen']);
            if ($imagen === false) {
                alerta('error', 'Error', 'Formato de imagen no válido. Use JPG, PNG, WEBP o GIF (máx. 2 MB).');
                redir('inventario');
            }
        }

        $res = $model->crearProducto([
            'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'precio'       => (float)($_POST['precio'] ?? 0),
            'stock'        => (int)($_POST['stock'] ?? 0),
            'imagen'       => $imagen,
        ]);
        alerta($res === true ? 'success' : 'error', $res === true ? 'Producto creado' : 'Error', $res === true ? 'Producto registrado correctamente.' : $res);
        redir('inventario');

    case 'editar_producto':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redir(); }
        $id = (int)($_POST['id_producto'] ?? 0);

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = subirImagen($_FILES['imagen']);
            if ($imagen === false) {
                alerta('error', 'Error', 'Formato de imagen no válido. Use JPG, PNG, WEBP o GIF (máx. 2 MB).');
                redir('inventario');
            }
            // Eliminar imagen anterior si existe
            $imagenAnterior = trim($_POST['imagen_actual'] ?? '');
            if ($imagenAnterior) {
                $rutaAnterior = __DIR__ . '/../img/productos/' . basename($imagenAnterior);
                if (file_exists($rutaAnterior)) @unlink($rutaAnterior);
            }
        }

        $res = $model->actualizarProducto($id, [
            'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'precio'       => (float)($_POST['precio'] ?? 0),
            'imagen'       => $imagen,
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
    // Si viene del panel de admin (productos.php), redirigir allí
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (str_contains($referer, 'productos.php')) {
        header("Location: ../views/dashboard/productos.php?tab=productos");
        exit;
    }
    header("Location: ../views/dashboard/bodeguero.php?tab={$tab}");
    exit;
}

/**
 * Sube una imagen de producto al directorio img/productos/.
 * Retorna el nombre del archivo guardado, o false si hay error.
 */
function subirImagen(array $file): string|false
{
    $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxBytes   = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK)   return false;
    if ($file['size'] > $maxBytes)           return false;
    if (!in_array($file['type'], $permitidos)) return false;

    // Verificar que sea imagen real con getimagesize
    $info = @getimagesize($file['tmp_name']);
    if (!$info) return false;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre   = uniqid('prod_', true) . '.' . strtolower($ext);
    $destino  = __DIR__ . '/../img/productos/' . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $destino)) return false;

    return $nombre;
}
?>
