<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Celeste Boutique</title>
    <link rel="shortcut icon" type="image/png" href="../../img/icono2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #8FB7C7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: sans-serif;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.2rem;
        }

        .logo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.5rem;
        }

        .logo-area img { height: 80px; }

        .fields-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            width: 100%;
        }

        .field-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #1a2d47;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            border: 1.5px solid #c8d8df;
            border-radius: 10px;
            background: #E2E7E9;
            padding: 0 1rem;
            transition: border-color 0.2s;
        }

        .input-wrapper:focus-within { border-color: #1a2d47; }

        .input-wrapper input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 0.8rem 0.5rem;
            font-size: 15px;
            color: #1a1a1a;
        }

        .input-wrapper input::placeholder { color: #9aacb4; }

        .toggle-pass {
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7f8a;
            padding: 0;
            line-height: 1;
        }

        .row-remember {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            font-size: 13px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #4a5568;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #1a2d47;
        }

        .forgot-link {
            color: #1a2d47;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            background: #1a2d47;
            color: #fff;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn-login:hover  { opacity: 0.88; transform: translateY(-1px); }
        .btn-login:active { transform: scale(0.98); }

        .footer-text { font-size: 13px; color: #4a5568; }

        .footer-text a {
            color: #1a2d47;
            font-weight: 700;
            text-decoration: none;
        }

        .footer-text a:hover { text-decoration: underline; }

        .login-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
    </style>
</head>
<body>

    <div class="card">

        <div class="logo-area">
            <img src="../../img/logo1.png" alt="Celeste Boutique">
        </div>

        <form action="../../controllers/AuthController.php" method="POST" class="login-form">

            <div class="fields-wrapper">

                <div class="field-group">
                    <label class="field-label">Correo electrónico</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            name="email"
                            placeholder="correo@ejemplo.com"
                            required
                        >
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Contraseña</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            id="passInput"
                            required
                        >
                        <button class="toggle-pass" onclick="togglePass()" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

            <div class="row-remember">
                <label class="remember-label">
                    <input type="checkbox" name="recordar">
                    Recordar mi sesión
                </label>
                <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-login">Iniciar sesión</button>

        </form>

        <p class="footer-text">¿No tienes cuenta? <a href="registre.php">Regístrate aquí</a></p>

    </div>

    <?php if (isset($_SESSION['alert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
            title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
            text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
            confirmButtonText: 'Aceptar'
        });
    </script>
    <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <script>
        function togglePass() {
            const input = document.getElementById('passInput');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>