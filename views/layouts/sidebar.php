<?php
$rol = $_SESSION['usuario']['rol'];
$nombreCompleto = $_SESSION['usuario']['nombre'];

$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<style>
    .sidebar {
        width: 230px;
        min-height: 100vh;
        background-color: #8FB7C7;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 0 16px;
        font-family: sans-serif;
    }

    /* Logo + nombre de usuario */
    .sidebar-logo {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        padding: 0 16px;
        width: 100%;
    }

    .sidebar-logo img {
        height: 72px;
        object-fit: contain;
    }

    .sidebar-user {
        text-align: center;
    }

    .sidebar-user-name {
        font-size: 13px;
        font-weight: 700;
        color: #1a2d47;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 190px;
    }

    .sidebar-user-role {
        font-size: 11px;
        color: #2e5068;
        text-transform: capitalize;
        letter-spacing: 0.03em;
    }

    /* Línea divisora */
    .sidebar-divider {
        width: calc(100% - 32px);
        height: 1px;
        background: rgba(255,255,255,0.45);
        margin: 4px 0 10px;
    }

    /* Etiqueta de sección */
    .sidebar-section-label {
        width: 100%;
        padding: 6px 22px 2px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2e5068;
    }

    .sidebar nav {
        width: 100%;
        padding: 0 12px;
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
    }

    .sidebar nav a {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 10px 16px;
        border-radius: 12px;
        color: #1a2d47;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.18s;
    }

    .sidebar nav a:hover {
        background: rgba(255,255,255,0.5);
    }

    .sidebar nav a.active {
        background: #fff;
        font-weight: 700;
        color: #1a2d47;
        box-shadow: 0 2px 8px rgba(0,0,0,0.09);
    }

    .sidebar nav a i {
        width: 22px;
        text-align: center;
        font-size: 15px;
        color: inherit;
        flex-shrink: 0;
    }

    /* Tarjeta de perfil en el footer */
    .sidebar-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 12px;
        background: rgba(255,255,255,0.3);
        margin-bottom: 6px;
    }

    .sidebar-profile-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #1a2d47;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-profile-avatar i {
        color: #8FB7C7;
        font-size: 16px;
    }

    .sidebar-profile-info {
        overflow: hidden;
    }

    .sidebar-profile-name {
        font-size: 13px;
        font-weight: 700;
        color: #1a2d47;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
    }

    .sidebar-profile-role {
        font-size: 11px;
        color: #2e5068;
        text-transform: capitalize;
        letter-spacing: 0.03em;
    }

    /* Logout al fondo */
    .sidebar-footer {
        width: 100%;
        padding: 10px 12px 4px;
        margin-top: auto;
    }

    .sidebar-footer a {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 10px 16px;
        border-radius: 12px;
        color: #c0392b;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.18s;
    }

    .sidebar-footer a:hover {
        background: rgba(255,255,255,0.4);
    }

    .sidebar-footer a i {
        width: 22px;
        text-align: center;
        font-size: 15px;
        color: inherit;
        flex-shrink: 0;
    }
</style>

<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <img src="../../img/icono2.png" alt="Celeste Boutique">
    </div>

    <div class="sidebar-divider"></div>

    <nav>

        <?php if ($rol === 'administrador'): ?>
            <!-- Gestión -->
            <span class="sidebar-section-label" style="margin-top:8px">Gestión</span>
            <a href="productos.php" class="<?= $paginaActual === 'productos.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            <a href="proveedores.php" class="<?= $paginaActual === 'proveedores.php' ? 'active' : '' ?>">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
            </a>

            <!-- Análisis -->
            <span class="sidebar-section-label" style="margin-top:8px">Análisis</span>
            <a href="reportes.php" class="<?= $paginaActual === 'reportes.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Reportes</span>
            </a>
        <?php endif; ?>

        <?php if ($rol === 'cajero'): ?>
            <span class="sidebar-section-label" style="margin-top:8px">Operaciones</span>
            <a href="cajero.php?tab=ventas" class="<?= ($paginaActual==='cajero.php' && ($_GET['tab']??'ventas')==='ventas') ? 'active' : '' ?>">
                <i class="fas fa-cash-register"></i>
                <span>Nueva Venta</span>
            </a>
            <a href="cajero.php?tab=devoluciones" class="<?= ($paginaActual==='cajero.php' && ($_GET['tab']??'')==='devoluciones') ? 'active' : '' ?>">
                <i class="fas fa-undo-alt"></i>
                <span>Devoluciones</span>
            </a>
            <a href="cajero.php?tab=recibo" class="<?= ($paginaActual==='cajero.php' && ($_GET['tab']??'')==='recibo') ? 'active' : '' ?>">
                <i class="fas fa-receipt"></i>
                <span>Recibos</span>
            </a>
            <a href="cajero.php?tab=historial" class="<?= ($paginaActual==='cajero.php' && ($_GET['tab']??'')==='historial') ? 'active' : '' ?>">
                <i class="fas fa-history"></i>
                <span>Historial</span>
            </a>
        <?php endif; ?>

        <?php if ($rol === 'bodeguero'): ?>
            <span class="sidebar-section-label" style="margin-top:8px">Almacén</span>
            <a href="bodeguero.php?tab=inventario" class="<?= ($paginaActual==='bodeguero.php'&&($_GET['tab']??'inventario')==='inventario')?'active':'' ?>">
                <i class="fas fa-warehouse"></i><span>Inventario</span>
            </a>
            <a href="bodeguero.php?tab=entradas" class="<?= ($paginaActual==='bodeguero.php'&&($_GET['tab']??'')==='entradas')?'active':'' ?>">
                <i class="fas fa-arrow-down"></i><span>Entradas</span>
            </a>
            <a href="bodeguero.php?tab=ajustes" class="<?= ($paginaActual==='bodeguero.php'&&($_GET['tab']??'')==='ajustes')?'active':'' ?>">
                <i class="fas fa-sliders-h"></i><span>Ajustes</span>
            </a>
            <a href="bodeguero.php?tab=movimientos" class="<?= ($paginaActual==='bodeguero.php'&&($_GET['tab']??'')==='movimientos')?'active':'' ?>">
                <i class="fas fa-history"></i><span>Movimientos</span>
            </a>
            <a href="bodeguero.php?tab=reportes" class="<?= ($paginaActual==='bodeguero.php'&&($_GET['tab']??'')==='reportes')?'active':'' ?>">
                <i class="fas fa-chart-bar"></i><span>Reportes</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Perfil + Cerrar sesión fijo al fondo -->
    <div class="sidebar-footer">
        <div class="sidebar-divider" style="margin-bottom:10px"></div>
        <!-- Tarjeta de perfil del usuario -->
        <div class="sidebar-profile">
            <div class="sidebar-profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="sidebar-profile-info">
                <span class="sidebar-profile-name"><?= htmlspecialchars($nombreCompleto) ?></span>
                <span class="sidebar-profile-role"><?= htmlspecialchars($rol) ?></span>
            </div>
        </div>
        <a href="../../controllers/AuthController.php?accion=logout">
            <i class="fas fa-right-from-bracket"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>

</aside>

<main class="flex-1">
    <header style="background:#fff; border-bottom: 2px solid #d0e6ef; padding: 18px 36px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h1 style="font-size:22px; font-weight:700; color:#1a2d47; margin:0;">
                <?= htmlspecialchars($titulo) ?>
            </h1>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <img src="../../img/logo1.png" alt="Celeste Boutique" style="height:50px;">
        </div>
    </header>

    <section class="p-8">