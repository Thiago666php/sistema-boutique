<?php
session_start();
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celeste Boutique | Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" type="image/png" href="../../img/icono2.png">
    <style>
        /* fondo */
        body {
            background: #8FB7C7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            font-family: sans-serif;
        }

        /* ===== COLOR 2: Tarjeta principal ===== */
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        /* Logo y nombre */
        .logo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            margin-bottom: 0.5rem;
        }

        .logo-area img {
            height: 65px;
        }

        /* ===== COLOR 3: Nombre marca normal ===== */
        .brand-name {
            font-size: 22px;
            font-weight: 400;
            color: #COLOR_MARCA_NORMAL;
            letter-spacing: 1px;
        }

        /* ===== COLOR 4: Parte acento del nombre ===== */
        .brand-name span {
            font-weight: 700;
            color: #COLOR_MARCA_ACENTO;
            font-size: 11px;
            display: block;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-align: center;
        }

        /* Grid de 2 columnas */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        /* ===== COLOR 5: Etiquetas de los campos ===== */
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: #COLOR_ETIQUETA;
        }

        /* ===== COLOR 6: Inputs — borde y fondo ===== */
        .input-field {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1.5px solid #COLOR_BORDE_INPUT;
            border-radius: 8px;
            background: #COLOR_FONDO_INPUT;
            font-size: 14px;
            color: #1a1a1a;
            outline: none;
            transition: border-color 0.2s;
        }

        /* ===== COLOR 7: Borde al enfocar ===== */
        .input-field:focus {
            border-color: #COLOR_FOCUS;
        }

        /* ===== COLOR 8: Placeholder ===== */
        .input-field::placeholder {
            color: #COLOR_PLACEHOLDER;
        }

        /* Select rol */
        .select-field {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1.5px solid #COLOR_BORDE_INPUT;
            border-radius: 8px;
            background: #COLOR_FONDO_INPUT;
            font-size: 14px;
            /* ===== COLOR 9: Texto del select ===== */
            color: #COLOR_SELECT_TEXTO;
            outline: none;
            transition: border-color 0.2s;
            appearance: none;
            cursor: pointer;
        }

        .select-field:focus {
            border-color: #COLOR_FOCUS;
        }

        /* Checkbox términos */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 13px;
            /* ===== COLOR 10: Texto términos ===== */
            color: #COLOR_TERMINOS;
        }

        .terms-row input[type="checkbox"] {
            margin-top: 2px;
            width: 15px;
            height: 15px;
            cursor: pointer;
            flex-shrink: 0;
            /* ===== COLOR 11: Checkbox acento ===== */
            accent-color: #COLOR_CHECKBOX;
        }

        /* ===== COLOR 12: Botón principal ===== */
        .btn-register {
            width: 100%;
            padding: 0.95rem;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            background: #COLOR_BOTON;
            color: #COLOR_TEXTO_BOTON;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn-register:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn-register:active { transform: scale(0.98); }

        /* ===== COLOR 13: Texto pie ===== */
        .footer-text {
            text-align: center;
            font-size: 13px;
            color: #COLOR_PIE_TEXTO;
        }

        /* ===== COLOR 14: Enlace pie ===== */
        .footer-text a {
            font-weight: 700;
            color: #COLOR_PIE_ENLACE;
            text-decoration: none;
        }

        .footer-text a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="card">

        <!-- LOGO -->
        <div class="logo-area">
            <img src="../../img/logo1.png" alt="Celeste Boutique">
        </div>

        <form action="../../controllers/UsuarioControllers.php" method="POST">

            <!-- Nombres y Apellidos -->
            <div class="grid-2">
                <div class="field-group">
                    <label class="field-label" style="color:#1a2d47;">Nombres:</label>
                    <input type="text" name="nombres" required maxlength="100"
                        class="input-field" placeholder="Juan Carlos" style="background:#E2E7E9; ">
                </div>
                <div class="field-group">
                    <label class="field-label"style="color:#1a2d47;">Apellidos:</label>
                    <input type="text" name="apellidos" required maxlength="100"
                        class="input-field" placeholder="Pérez Rodríguez" style="background:#E2E7E9; ">
                </div>
            </div>

            <!-- Correo -->
            <div class="field-group" style="margin-top: 1rem;">
                <label class="field-label" style="color:#1a2d47;">Correo Electrónico:</label>
                <input type="email" name="email" required maxlength="150"
                    class="input-field" placeholder="correo@ejemplo.com" style="background:#E2E7E9; ">
            </div>

            <!-- Teléfono -->
            <div class="field-group" style="margin-top: 1rem;">
                <label class="field-label" style="color:#1a2d47;">Teléfono:</label>
                <input type="text" name="telefono" required maxlength="30"
                    class="input-field" placeholder="3001234567" style="background:#E2E7E9; ">
            </div>

            <!-- Contraseña y Confirmar -->
            <div class="grid-2" style="margin-top: 1rem;">
                <div class="field-group">
                    <label class="field-label" style="color:#1a2d47;">Contraseña:</label>
                    <input type="password" name="password" required
                        class="input-field" placeholder="••••••••" style="background:#E2E7E9; ">
                </div>
                <div class="field-group">
                    <label class="field-label" style="color:#1a2d47;">Confirmar Contraseña:</label>
                    <input type="password" name="confirmar_password" required
                        class="input-field" placeholder="••••••••" style="background:#E2E7E9; ">
                </div>
            </div>


            <!-- Términos -->
            <div class="terms-row" style="margin-top: 1rem;">
                <input type="checkbox" id="terms" required>
                <label for="terms">
                    Acepto los términos de servicio y la política de tratamiento de datos personales.
                </label>
            </div>

            <!-- Botón -->
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn-register" style="background:#1a2d47; color:#fff;">Registrarse</button>
            </div>

            <!-- Pie -->
            <div class="footer-text" style="margin-top: 1rem;">
                ¿Ya tienes una cuenta? <a href="login.php" style="color:#1a2d47;">Inicia sesión aquí</a>
            </div>

        </form>
    </div>

    <?php if ($alert): ?>
    <script>
        Swal.fire({
            icon: '<?= htmlspecialchars($alert['icon']) ?>',
            title: '<?= htmlspecialchars($alert['title']) ?>',
            text: '<?= htmlspecialchars($alert['text']) ?>',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            <?php if (!empty($alert['redirect'])): ?>
                window.location.href = '<?= htmlspecialchars($alert['redirect']) ?>';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

</body>
</html>