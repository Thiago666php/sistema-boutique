<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Categoria.php';

// Solo administradores
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../views/usuarios/login.php');
    exit;
}

class CategoriaController
{
    private Categoria $model;

    public function __construct()
    {
        $db = (new Database())->conectar();

        // Crea las tablas si no existen
        $db->exec("
            CREATE TABLE IF NOT EXISTS `categorias_productos` (
                `id_categoria` INT          PRIMARY KEY AUTO_INCREMENT,
                `nombre`       VARCHAR(100) NOT NULL,
                `descripcion`  VARCHAR(255) DEFAULT NULL,
                `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `productos` (
                `id_producto`  INT           PRIMARY KEY AUTO_INCREMENT,
                `id_categoria` INT           NOT NULL,
                `nombre`       VARCHAR(150)  NOT NULL,
                `descripcion`  VARCHAR(255)  DEFAULT NULL,
                `precio`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `stock`        INT           NOT NULL DEFAULT 0,
                `activo`       TINYINT(1)    NOT NULL DEFAULT 1,
                `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_producto_categoria`
                    FOREIGN KEY (`id_categoria`) REFERENCES `categorias_productos` (`id_categoria`)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->model = new Categoria($db);
    }

    // ── Crear ────────────────────────────────────────────────
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir();
        }

        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($nombre === '') {
            $this->alert('warning', 'Campo requerido', 'El nombre de la categoría es obligatorio.');
            $this->redirigir();
        }

        if ($this->model->existeNombre($nombre)) {
            $this->alert('error', 'Nombre duplicado', "Ya existe una categoría llamada «{$nombre}».");
            $this->redirigir();
        }

        $res = $this->model->crear(['nombre' => $nombre, 'descripcion' => $descripcion, 'cantidad' => (int)($_POST['cantidad'] ?? 0)]);
        $res === true
            ? $this->alert('success', 'Éxito', 'Categoría creada correctamente.')
            : $this->alert('error',   'Error', $res);

        $this->redirigir();
    }

    // ── Editar ───────────────────────────────────────────────
    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir();
        }

        $id          = (int) ($_POST['id_categoria'] ?? 0);
        $nombre      = trim($_POST['nombre']         ?? '');
        $descripcion = trim($_POST['descripcion']    ?? '');

        if (!$id || $nombre === '') {
            $this->alert('warning', 'Datos inválidos', 'ID o nombre no proporcionados.');
            $this->redirigir();
        }

        if ($this->model->existeNombre($nombre, $id)) {
            $this->alert('error', 'Nombre duplicado', "Ya existe otra categoría llamada «{$nombre}».");
            $this->redirigir();
        }

        $res = $this->model->actualizar($id, ['nombre' => $nombre, 'descripcion' => $descripcion, 'cantidad' => (int)($_POST['cantidad'] ?? 0)]);
        $res === true
            ? $this->alert('success', 'Éxito', 'Categoría actualizada correctamente.')
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
            ? $this->alert('success', 'Éxito',  'Categoría eliminada correctamente.')
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
        header('Location: ../views/dashboard/productos.php');
        exit;
    }
}

// ── Despacho ─────────────────────────────────────────────────
$ctrl   = new CategoriaController();
$accion = $_GET['accion'] ?? '';

match ($accion) {
    'crear'    => $ctrl->crear(),
    'editar'   => $ctrl->editar(),
    'eliminar' => $ctrl->eliminar(),
    default    => header('Location: ../views/dashboard/productos.php') & exit,
};
?>
