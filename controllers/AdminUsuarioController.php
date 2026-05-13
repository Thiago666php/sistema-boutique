<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header("Location: ../views/usuarios/login.php");
    exit;
}

class AdminUsuarioController {
    private $usuarioModel;

    public function __construct() {
        $database = new Database();
        $db = $database->conectar();
        $this->usuarioModel = new Usuario($db);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $nombres   = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $rol       = trim($_POST['rol'] ?? '');

        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($rol)) {
            $this->setAlert('warning', 'Campos incompletos', 'Debe completar todos los campos obligatorios');
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        if ($this->usuarioModel->existeCorreo($email)) {
            $this->setAlert('error', 'Correo existente', 'Este correo ya está registrado en el sistema');
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $roles = [
            'administrador' => 1,
            'cajero'        => 2,
            'bodeguero'     => 3
        ];

        $datos = [
            'nombre'   => $nombres . ' ' . $apellidos,
            'correo'   => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id_rol'   => $roles[$rol] ?? 2,
            'telefono' => $_POST['telefono'] ?? ''
        ];

        $resultado = $this->usuarioModel->registrar($datos);

        if ($resultado === true) {
            $this->setAlert('success', 'Éxito', 'Usuario creado correctamente');
        } else {
            $this->setAlert('error', 'Error', $resultado);
        }

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? null;
        if (!$id_usuario) {
            $this->setAlert('error', 'Error', 'ID de usuario no proporcionado');
            header("Location: ../views/dashboard/admin.php");
            exit;
        }

        $roles = [
            'administrador' => 1,
            'cajero'        => 2,
            'bodeguero'     => 3
        ];

        $nombres   = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $rol       = trim($_POST['rol'] ?? '');

        
        $datos = [
            'nombre' => $nombres . ' ' . $apellidos,
            'id_rol' => $roles[$rol] ?? 2
        ];

        if (!empty($_POST['password'])) {
            $datos['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $resultado = $this->usuarioModel->actualizar($id_usuario, $datos);

        if ($resultado === true) {
            $this->setAlert('success', 'Éxito', 'Usuario actualizado correctamente');
        } else {
            $this->setAlert('error', 'Error', $resultado);
        }

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    public function toggleEstado() {
        $id_usuario = $_GET['id'] ?? null;
        $estado     = $_GET['estado'] ?? null;

        if ($id_usuario !== null && $estado !== null) {
            $nuevo_estado = $estado == 1 ? 0 : 1;
            $resultado = $this->usuarioModel->cambiarEstado($id_usuario, $nuevo_estado);

            if ($resultado === true) {
                $status_text = $nuevo_estado == 1 ? 'activado' : 'desactivado';
                $this->setAlert('success', 'Éxito', "Usuario $status_text correctamente");
            } else {
                $this->setAlert('error', 'Error', $resultado);
            }
        } else {
            $this->setAlert('error', 'Error', 'Parámetros no válidos');
        }

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    public function eliminar() {
        $id_usuario = $_GET['id'] ?? null;

        if ($id_usuario) {
            $resultado = $this->usuarioModel->eliminar($id_usuario);
            if ($resultado === true) {
                $this->setAlert('success', 'Éxito', 'Usuario eliminado correctamente');
            } else {
                $this->setAlert('error', 'Error', $resultado);
            }
        } else {
            $this->setAlert('error', 'Error', 'ID de usuario no proporcionado');
        }

        header("Location: ../views/dashboard/admin.php");
        exit;
    }

    private function setAlert($icon, $title, $text) {
        $_SESSION['alert'] = ['icon' => $icon, 'title' => $title, 'text' => $text];
    }
}

$controller = new AdminUsuarioController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'crear':        $controller->crear();        break;
    case 'editar':       $controller->editar();       break;
    case 'toggleEstado': $controller->toggleEstado(); break;
    case 'eliminar':     $controller->eliminar();     break;
    default:
        header("Location: ../views/dashboard/admin.php");
        exit;
}
?>