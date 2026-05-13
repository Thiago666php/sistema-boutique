<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioControllers {

    public function registrar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $nombres   = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');

        // Combinar nombres y apellidos en un solo campo
        $nombre_completo = $nombres . ' ' . $apellidos;

        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($confirmar_password)) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Campos incompletos',
                'text'  => 'Debe completar todos los campos'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo inválido',
                'text'  => 'Ingrese un correo válido'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if ($password !== $confirmar_password) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Las contraseñas no coinciden'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'Contraseña inválida',
                'text'  => 'La contraseña debe tener al menos 6 caracteres'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $database = new Database();
        $db = $database->conectar();
        $usuario = new Usuario($db);

        if ($usuario->existeCorreo($email)) {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Correo existente',
                'text'  => 'Este correo ya está registrado'
            ];
            header("Location: ../views/usuarios/registre.php");
            exit;
        }

        $datos = [
            'nombre'   => $nombre_completo,
            'correo'   => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id_rol'   => 2, // rol por defecto, ajusta según tu tabla de roles
            'telefono' => $telefono
        ];

        $resultado = $usuario->registrar($datos);

        if ($resultado === true) {
            $_SESSION['alert'] = [
                'icon'     => 'success',
                'title'    => 'Registro exitoso',
                'text'     => 'Tu cuenta fue creada correctamente',
                'redirect' => 'login.php'
            ];
        } else {
            $_SESSION['alert'] = [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => $resultado
            ];
        }

        header("Location: ../views/usuarios/registre.php");
        exit;
    }
}

$controller = new UsuarioControllers();
$controller->registrar();
?>