<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Proveedor.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../views/usuarios/login.php');
    exit;
}

class ProveedorController
{
    private Proveedor $model;

    public function __construct()
    {
        $db = (new Database())->conectar();

        // Crea la tabla si no existe
        $db->exec("
            CREATE TABLE IF NOT EXISTS `proveedores` (
                `id_proveedor` INT          PRIMARY KEY AUTO_INCREMENT,
                `nombre`       VARCHAR(150) NOT NULL,
                `ruc`          VARCHAR(20)  DEFAULT NULL,
                `telefono`     VARCHAR(30)  DEFAULT NULL,
                `correo`       VARCHAR(150) DEFAULT NULL,
                `direccion`    VARCHAR(255) DEFAULT NULL,
                `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->model = new Proveedor($db);
    }

    // ── Crear ────────────────────────────────────────────────
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirigir();

        $nombre = trim($_POST['nombre'] ?? '');
        $ruc    = trim($_POST['ruc']    ?? '');

        if ($nombre === '') {
            $this->alert('warning', 'Campo requerido', 'El nombre del proveedor es obligatorio.');
            $this->redirigir();
        }

        if ($ruc !== '' && $this->model->existeRuc($ruc)) {
            $this->alert('error', 'RUC duplicado', "Ya existe un proveedor con el RUC «{$ruc}».");
            $this->redirigir();
        }

        $res = $this->model->crear([
            'nombre'    => $nombre,
            'ruc'       => $ruc,
            'telefono'  => trim($_POST['telefono']  ?? ''),
            'correo'    => trim($_POST['correo']    ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
        ]);

        $res === true
            ? $this->alert('success', 'Éxito', 'Proveedor creado correctamente.')
            : $this->alert('error',   'Error', $res);

        $this->redirigir();
    }

    // ── Editar ───────────────────────────────────────────────
    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirigir();

        $id     = (int) ($_POST['id_proveedor'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $ruc    = trim($_POST['ruc']    ?? '');

        if (!$id || $nombre === '') {
            $this->alert('warning', 'Datos inválidos', 'ID o nombre no proporcionados.');
            $this->redirigir();
        }

        if ($ruc !== '' && $this->model->existeRuc($ruc, $id)) {
            $this->alert('error', 'RUC duplicado', "Ya existe otro proveedor con el RUC «{$ruc}».");
            $this->redirigir();
        }

        $res = $this->model->actualizar($id, [
            'nombre'    => $nombre,
            'ruc'       => $ruc,
            'telefono'  => trim($_POST['telefono']  ?? ''),
            'correo'    => trim($_POST['correo']    ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
        ]);

        $res === true
            ? $this->alert('success', 'Éxito', 'Proveedor actualizado correctamente.')
            : $this->alert('error',   'Error', $res);

        $this->redirigir();
    }

    // ── Toggle estado ────────────────────────────────────────
    public function toggleEstado(): void
    {
        $id     = (int) ($_GET['id']     ?? 0);
        $estado = (int) ($_GET['estado'] ?? -1);

        if (!$id || $estado === -1) {
            $this->alert('error', 'Error', 'Parámetros inválidos.');
            $this->redirigir();
        }

        $nuevo = $estado == 1 ? 0 : 1;
        $res   = $this->model->cambiarEstado($id, $nuevo);
        $txt   = $nuevo ? 'activado' : 'desactivado';

        $res === true
            ? $this->alert('success', 'Éxito', "Proveedor {$txt} correctamente.")
            : $this->alert('error',   'Error', $res);

        $this->redirigir();
    }

    // ── Eliminar ─────────────────────────────────────────────
    public function eliminar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if (!$id) {
            $this->alert('error', 'Error', 'ID no proporcionado.');
            $this->redirigir();
        }

        $res = $this->model->eliminar($id);
        $res === true
            ? $this->alert('success', 'Éxito',  'Proveedor eliminado correctamente.')
            : $this->alert('error',   'Error',   $res);

        $this->redirigir();
    }

    // ── Helpers ──────────────────────────────────────────────
    private function alert(string $icon, string $title, string $text): void
    {
        $_SESSION['alert'] = compact('icon', 'title', 'text');
    }

    private function redirigir(): never
    {
        header('Location: ../views/dashboard/proveedores.php');
        exit;
    }
}

$ctrl   = new ProveedorController();
$accion = $_GET['accion'] ?? '';

match ($accion) {
    'crear'        => $ctrl->crear(),
    'editar'       => $ctrl->editar(),
    'toggleEstado' => $ctrl->toggleEstado(),
    'eliminar'     => $ctrl->eliminar(),
    default        => header('Location: ../views/dashboard/proveedores.php') & exit,
};
?>
