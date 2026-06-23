<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cajero') {
    header('Location: ../usuarios/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Venta.php';

$db    = (new Database())->conectar();
$model = new Venta($db);

$tab       = $_GET['tab'] ?? 'ventas';
$reciboId  = (int)($_GET['id'] ?? 0);
$productos = $model->obtenerProductos();
$historial = $model->historial((int)$_SESSION['usuario']['id_usuario']);
$devoluciones = $model->obtenerDevoluciones((int)$_SESSION['usuario']['id_usuario']);

// Datos del recibo si viene de una venta recien registrada
$recibo = null;
$reciboDetalle = [];
if ($reciboId > 0) {
    $recibo = $model->obtenerVentaPorId($reciboId);
    $reciboDetalle = $model->obtenerDetalleVenta($reciboId);
}

$titulo = 'Módulo Cajero';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root { --navy:#1a2d47; --blue:#253E63; --sky:#8FB7C7; --light:#D6E0E4; }

.caj-wrap { padding:24px 32px; }

/* Tabs */
.caj-tabs {
    display:flex; gap:4px;
    background:#e8f0f3; border-radius:14px;
    padding:5px; margin-bottom:24px;
    width:fit-content;
}
.caj-tab {
    padding:9px 22px; border-radius:10px;
    border:none; background:transparent;
    color:#5a7080; font-size:13px; font-weight:600;
    cursor:pointer; transition:all .18s;
    display:flex; align-items:center; gap:7px;
}
.caj-tab:hover  { background:rgba(255,255,255,.6); color:var(--navy); }
.caj-tab.active { background:#fff; color:var(--navy); box-shadow:0 2px 8px rgba(0,0,0,.1); }

.caj-pane { display:none; }
.caj-pane.active { display:block; }

/* Panel */
.panel {
    background:#fff; border-radius:16px;
    padding:24px; box-shadow:0 2px 10px rgba(0,0,0,.07);
}
.panel-title {
    font-size:15px; font-weight:700; color:var(--navy);
    margin:0 0 18px; display:flex; align-items:center; gap:8px;
}
.panel-title i { color:var(--sky); }

/* Tabla */
.caj-tbl { width:100%; border-collapse:collapse; font-size:13px; }
.caj-tbl thead tr { background:var(--sky); color:var(--navy); }
.caj-tbl th { padding:10px 14px; text-align:left; font-weight:700; }
.caj-tbl td { padding:9px 14px; border-bottom:1px solid #eef2f5; color:#3a4a5c; }
.caj-tbl tbody tr:hover { background:#f7fafc; }
.caj-tbl tbody tr:last-child td { border-bottom:none; }

/* Badges */
.badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-block; }
.badge-ok    { background:#e6f9f0; color:#1a7a4a; border:1px solid #a3d9b8; }
.badge-warn  { background:#fef9e7; color:#b7950b; border:1px solid #f9e79f; }
.badge-err   { background:#fde8e8; color:#c0392b; border:1px solid #f5a8a8; }

/* Inputs */
.inp {
    border:1.5px solid #c8d8df; border-radius:10px;
    padding:9px 14px; font-size:13px; width:100%;
    outline:none; transition:border-color .15s;
}
.inp:focus { border-color:var(--sky); box-shadow:0 0 0 3px rgba(143,183,199,.2); }

/* Botones */
.btn-navy {
    background:var(--navy); color:#fff; border:none;
    padding:9px 20px; border-radius:10px;
    font-size:13px; font-weight:600; cursor:pointer;
    transition:background .15s;
    display:inline-flex; align-items:center; gap:7px;
}
.btn-navy:hover { background:var(--blue); }
.btn-sky {
    background:var(--sky); color:var(--navy); border:none;
    padding:9px 20px; border-radius:10px;
    font-size:13px; font-weight:600; cursor:pointer;
    transition:background .15s;
    display:inline-flex; align-items:center; gap:7px;
}
.btn-sky:hover { background:#7aaabb; color:#fff; }
.btn-red {
    background:#fde8e8; color:#c0392b; border:1px solid #f5a8a8;
    padding:7px 14px; border-radius:8px;
    font-size:12px; font-weight:600; cursor:pointer;
    transition:background .15s;
}
.btn-red:hover { background:#fac8c8; }

/* Carrito */
.cart-row {
    display:flex; align-items:center; gap:10px;
    padding:10px 0; border-bottom:1px solid #eef2f5;
}
.cart-row:last-child { border-bottom:none; }
.cart-name { flex:1; font-weight:600; color:var(--navy); font-size:13px; }
.cart-qty  { width:60px; }
.cart-sub  { width:90px; text-align:right; font-weight:700; color:var(--navy); font-size:13px; }

/* Recibo — contenedor de previsualización en pantalla */
.recibo-preview-wrap {
    display: flex;
    justify-content: center;
    padding: 10px 0 20px;
}

@media print {
    /* Técnica: ocultar toda la página con visibility y mostrar solo el ticket */
    body * { visibility: hidden !important; }
    #ticket-recibo,
    #ticket-recibo * { visibility: visible !important; }
    #ticket-recibo {
        position: fixed !important;
        top: 0 !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 340px !important;
        box-shadow: none !important;
        border-left: 1px solid #ddd !important;
        border-right: 1px solid #ddd !important;
    }
}
</style>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () => Swal.fire({
    icon:  '<?= $_SESSION['alert']['icon'] ?>',
    title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
    text:  '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
    confirmButtonColor: '#1a2d47'
}));
</script>
<?php unset($_SESSION['alert']); endif; ?>

<div class="caj-wrap">

    <!-- ══════════════════════════════════════════════════════
         TAB 1 — NUEVA VENTA
    ══════════════════════════════════════════════════════ -->
    <div id="pane-ventas" class="caj-pane <?= $tab==='ventas'?'active':'' ?>">
        <div style="display:grid;grid-template-columns:1fr 380px;gap:20px;">

            <!-- Catálogo de productos -->
            <div class="panel">
                <p class="panel-title"><i class="fas fa-box"></i> Productos disponibles</p>

                <input type="text" id="buscarProd" class="inp" placeholder="Buscar producto..."
                       oninput="filtrarProductos()" style="margin-bottom:14px;">

                <?php if (empty($productos)): ?>
                    <div style="text-align:center;padding:40px;color:#aaa;">
                        <i class="fas fa-box-open" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px;"></i>
                        No hay productos con stock disponible.
                    </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="caj-tbl" id="tablaProd">
                    <thead>
                        <tr><th>#</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Agregar</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($productos as $i => $p): ?>
                    <tr data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>">
                        <td style="color:#aaa"><?= $i+1 ?></td>
                        <td style="font-weight:600;color:#1a2d47"><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td style="font-weight:600">$ <?= number_format($p['precio'],2,',','.') ?></td>
                        <td><span class="badge <?= $p['stock']>5?'badge-ok':'badge-warn' ?>"><?= $p['stock'] ?></span></td>
                        <td>
                            <button class="btn-sky" style="padding:6px 12px;font-size:12px;"
                                onclick="agregarAlCarrito(<?= $p['id_producto'] ?>, '<?= htmlspecialchars($p['nombre'],ENT_QUOTES) ?>', <?= $p['precio'] ?>, <?= $p['stock'] ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Carrito -->
            <div class="panel" style="height:fit-content;position:sticky;top:20px;">
                <p class="panel-title"><i class="fas fa-shopping-cart"></i> Carrito de venta</p>

                <div class="inp" style="margin-bottom:14px;padding:0;">
                    <input type="text" id="clienteNombre" class="inp" placeholder="Nombre del cliente (opcional)"
                           style="border:none;box-shadow:none;">
                </div>

                <div id="carritoItems" style="min-height:60px;margin-bottom:14px;">
                    <p id="carritoVacio" style="text-align:center;color:#aaa;padding:20px;font-size:13px;">
                        <i class="fas fa-cart-plus" style="display:block;font-size:24px;margin-bottom:6px;opacity:.3;"></i>
                        Agrega productos al carrito
                    </p>
                </div>

                <hr style="border:none;border-top:2px dashed #d0e6ef;margin:10px 0;">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <span style="font-size:14px;color:#5a7080;font-weight:600;">Total</span>
                    <span id="totalCarrito" style="font-size:22px;font-weight:800;color:#1a2d47;">$ 0,00</span>
                </div>

                <form id="formVenta" method="POST"
                      action="../../controllers/VentaController.php?accion=registrar">
                    <input type="hidden" name="cliente_nombre" id="hiddenCliente">
                    <div id="hiddenItems"></div>
                    <button type="button" onclick="confirmarVenta()"
                            class="btn-navy" style="width:100%;justify-content:center;">
                        <i class="fas fa-check-circle"></i> Registrar Venta
                    </button>
                </form>

                <button onclick="limpiarCarrito()"
                        class="btn-red no-print" style="width:100%;margin-top:8px;text-align:center;">
                    <i class="fas fa-trash"></i> Limpiar carrito
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 2 — DEVOLUCIONES
    ══════════════════════════════════════════════════════ -->
    <div id="pane-devoluciones" class="caj-pane <?= $tab==='devoluciones'?'active':'' ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

            <!-- Formulario devolución -->
            <div class="panel" style="height:fit-content;">
                <p class="panel-title"><i class="fas fa-undo-alt"></i> Registrar Devolución</p>

                <form method="POST" action="../../controllers/VentaController.php?accion=devolucion"
                      onsubmit="return confirmarDevolucion(event)">

                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#1a2d47;display:block;margin-bottom:5px;">
                                N° de Venta *
                            </label>
                            <input type="number" name="id_venta" class="inp" required
                                   placeholder="Ej: 12" min="1">
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#1a2d47;display:block;margin-bottom:5px;">
                                Motivo <span style="font-weight:400;color:#aaa">(opcional)</span>
                            </label>
                            <textarea name="motivo" class="inp" rows="3"
                                      placeholder="Describe el motivo de la devolución..."
                                      style="resize:none;"></textarea>
                        </div>
                        <button type="submit" class="btn-navy" style="justify-content:center;">
                            <i class="fas fa-undo-alt"></i> Procesar Devolución
                        </button>
                    </div>
                </form>

                <div style="margin-top:16px;padding:12px;background:#fef9e7;border-radius:10px;border:1px solid #f9e79f;">
                    <p style="font-size:12px;color:#7d6608;margin:0;">
                        <i class="fas fa-info-circle"></i>
                        Al procesar una devolución, la venta queda <strong>anulada</strong> y el stock de los productos se repone automáticamente.
                    </p>
                </div>
            </div>

            <!-- Historial de devoluciones -->
            <div class="panel">
                <p class="panel-title"><i class="fas fa-list"></i> Devoluciones registradas</p>
                <?php if (empty($devoluciones)): ?>
                    <div style="text-align:center;padding:40px;color:#aaa;">
                        <i class="fas fa-check-circle" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px;color:#27ae60;"></i>
                        No hay devoluciones registradas.
                    </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="caj-tbl">
                    <thead>
                        <tr><th>#Dev</th><th>#Venta</th><th>Cliente</th><th>Monto</th><th>Fecha</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($devoluciones as $d): ?>
                    <tr>
                        <td style="color:#aaa"><?= $d['id_devolucion'] ?></td>
                        <td style="font-weight:600;color:#1a2d47">#<?= $d['id_venta'] ?></td>
                        <td><?= htmlspecialchars($d['cliente_nombre']) ?></td>
                        <td style="font-weight:700;color:#c0392b;">$ <?= number_format($d['monto'],2,',','.') ?></td>
                        <td style="color:#7a8fa6;font-size:12px;"><?= $d['fecha'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 3 — RECIBO
    ══════════════════════════════════════════════════════ -->
    <div id="pane-recibo" class="caj-pane <?= $tab==='recibo'?'active':'' ?>">
        <div class="panel" style="max-width:600px;margin:0 auto;">
            <p class="panel-title no-print"><i class="fas fa-receipt"></i> Imprimir Recibo</p>

            <!-- Buscador de recibo por número -->
            <div class="no-print" style="display:flex;gap:10px;margin-bottom:20px;">
                <input type="number" id="buscarReciboId" class="inp" placeholder="N° de venta" min="1"
                       value="<?= $reciboId ?: '' ?>" style="max-width:160px;">
                <button class="btn-sky" onclick="buscarRecibo()">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>

            <div id="reciboContenido">
            <?php if ($recibo): ?>
                <?php include __DIR__ . '/../../views/partials/recibo_template.php'; ?>
            <?php else: ?>
                <div style="text-align:center;padding:50px;color:#aaa;">
                    <i class="fas fa-receipt" style="font-size:40px;opacity:.2;display:block;margin-bottom:10px;"></i>
                    Ingresa el número de venta para ver el recibo.
                </div>
            <?php endif; ?>
            </div>

            <?php if ($recibo): ?>
            <div class="no-print" id="btnImprimirRecibo" style="display:flex;gap:10px;margin-top:20px;justify-content:center;">
                <button class="btn-navy" onclick="imprimirTicket()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
            <?php else: ?>
            <div class="no-print" id="btnImprimirRecibo" style="display:none;gap:10px;margin-top:20px;justify-content:center;">
                <button class="btn-navy" onclick="imprimirTicket()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 4 — HISTORIAL
    ══════════════════════════════════════════════════════ -->
    <div id="pane-historial" class="caj-pane <?= $tab==='historial'?'active':'' ?>">
        <div class="panel">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
                <p class="panel-title" style="margin:0;"><i class="fas fa-history"></i> Historial de Ventas</p>
                <!-- Filtros -->
                <div style="display:flex;gap:8px;flex-wrap:wrap;" class="no-print">
                    <input type="date" id="filtroDesde" class="inp" style="width:150px;" title="Desde">
                    <input type="date" id="filtroHasta" class="inp" style="width:150px;" title="Hasta">
                    <select id="filtroEstado" class="inp" style="width:140px;">
                        <option value="">Todos</option>
                        <option value="completada">Completadas</option>
                        <option value="anulada">Anuladas</option>
                    </select>
                    <button class="btn-sky" onclick="filtrarHistorial()">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button class="btn-navy" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>

            <?php if (empty($historial)): ?>
                <div style="text-align:center;padding:50px;color:#aaa;">
                    <i class="fas fa-history" style="font-size:36px;opacity:.2;display:block;margin-bottom:10px;"></i>
                    No hay ventas registradas aún.
                </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="caj-tbl" id="tablaHistorial">
                <thead>
                    <tr>
                        <th>#Venta</th><th>Cliente</th><th>Total</th>
                        <th>Estado</th><th>Fecha</th><th class="no-print">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($historial as $v): ?>
                <tr data-estado="<?= $v['estado'] ?>"
                    data-fecha="<?= date('Y-m-d', strtotime(str_replace('/','-',$v['fecha']))) ?>">
                    <td style="font-weight:700;color:#1a2d47">#<?= $v['id_venta'] ?></td>
                    <td><?= htmlspecialchars($v['cliente_nombre']) ?></td>
                    <td style="font-weight:700;">$ <?= number_format($v['total'],2,',','.') ?></td>
                    <td>
                        <span class="badge <?= $v['estado']==='completada'?'badge-ok':'badge-err' ?>">
                            <?= ucfirst($v['estado']) ?>
                        </span>
                    </td>
                    <td style="color:#7a8fa6;font-size:12px;"><?= $v['fecha'] ?></td>
                    <td class="no-print">
                        <button class="btn-sky" style="padding:5px 12px;font-size:12px;"
                                onclick="verRecibo(<?= $v['id_venta'] ?>)">
                            <i class="fas fa-receipt"></i> Recibo
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /caj-wrap -->

<script>
/* ── Tabs ─────────────────────────────────────────────────── */
function switchTab(name) {
    document.querySelectorAll('.caj-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.caj-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('pane-' + name).classList.add('active');
    document.querySelector(`.caj-tab[onclick="switchTab('${name}')"]`).classList.add('active');
    history.replaceState(null,'',`?tab=${name}`);
}

/* ── Carrito ──────────────────────────────────────────────── */
let carrito = {};   // { id_producto: { nombre, precio, cantidad, stock } }

function agregarAlCarrito(id, nombre, precio, stock) {
    if (carrito[id]) {
        if (carrito[id].cantidad >= stock) {
            Swal.fire({ icon:'warning', title:'Stock insuficiente', text:`Solo hay ${stock} unidades disponibles.`, confirmButtonColor:'#1a2d47' });
            return;
        }
        carrito[id].cantidad++;
    } else {
        carrito[id] = { nombre, precio, cantidad: 1, stock };
    }
    renderCarrito();
}

function cambiarCantidad(id, val) {
    const cant = parseInt(val);
    if (cant <= 0) { delete carrito[id]; }
    else if (cant > carrito[id].stock) {
        Swal.fire({ icon:'warning', title:'Stock insuficiente', text:`Máximo ${carrito[id].stock} unidades.`, confirmButtonColor:'#1a2d47' });
        carrito[id].cantidad = carrito[id].stock;
    } else {
        carrito[id].cantidad = cant;
    }
    renderCarrito();
}

function quitarDelCarrito(id) {
    delete carrito[id];
    renderCarrito();
}

function limpiarCarrito() {
    carrito = {};
    renderCarrito();
}

function renderCarrito() {
    const cont = document.getElementById('carritoItems');
    const vacio = document.getElementById('carritoVacio');
    const ids = Object.keys(carrito);

    if (!ids.length) {
        cont.innerHTML = `<p id="carritoVacio" style="text-align:center;color:#aaa;padding:20px;font-size:13px;">
            <i class="fas fa-cart-plus" style="display:block;font-size:24px;margin-bottom:6px;opacity:.3;"></i>
            Agrega productos al carrito</p>`;
        document.getElementById('totalCarrito').textContent = '$ 0,00';
        return;
    }

    let html = '';
    let total = 0;
    ids.forEach(id => {
        const item = carrito[id];
        const sub  = item.precio * item.cantidad;
        total += sub;
        html += `<div class="cart-row">
            <div class="cart-name">${item.nombre}</div>
            <input type="number" class="inp cart-qty" value="${item.cantidad}" min="1" max="${item.stock}"
                   onchange="cambiarCantidad(${id}, this.value)" style="width:60px;padding:5px 8px;">
            <div class="cart-sub">$ ${sub.toLocaleString('es-CO',{minimumFractionDigits:2})}</div>
            <button class="btn-red" onclick="quitarDelCarrito(${id})" style="padding:5px 8px;">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
    });

    cont.innerHTML = html;
    document.getElementById('totalCarrito').textContent =
        '$ ' + total.toLocaleString('es-CO', { minimumFractionDigits: 2 });
}

function confirmarVenta() {
    if (!Object.keys(carrito).length) {
        Swal.fire({ icon:'warning', title:'Carrito vacío', text:'Agrega al menos un producto.', confirmButtonColor:'#1a2d47' });
        return;
    }
    Swal.fire({
        icon: 'question',
        title: '¿Confirmar venta?',
        text: `Total: ${document.getElementById('totalCarrito').textContent}`,
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1a2d47',
        cancelButtonColor: '#8FB7C7'
    }).then(r => {
        if (!r.isConfirmed) return;
        // Llenar campos ocultos
        document.getElementById('hiddenCliente').value = document.getElementById('clienteNombre').value;
        const cont = document.getElementById('hiddenItems');
        cont.innerHTML = '';
        Object.keys(carrito).forEach((id, i) => {
            cont.innerHTML += `<input type="hidden" name="items[${i}][id_producto]" value="${id}">
                               <input type="hidden" name="items[${i}][cantidad]" value="${carrito[id].cantidad}">`;
        });
        document.getElementById('formVenta').submit();
    });
}

/* ── Filtrar productos ────────────────────────────────────── */
function filtrarProductos() {
    const q = document.getElementById('buscarProd').value.toLowerCase();
    document.querySelectorAll('#tablaProd tbody tr').forEach(tr => {
        tr.style.display = tr.dataset.nombre.includes(q) ? '' : 'none';
    });
}

/* ── Filtrar historial ────────────────────────────────────── */
function filtrarHistorial() {
    const desde  = document.getElementById('filtroDesde').value;
    const hasta  = document.getElementById('filtroHasta').value;
    const estado = document.getElementById('filtroEstado').value;

    document.querySelectorAll('#tablaHistorial tbody tr').forEach(tr => {
        const fila_estado = tr.dataset.estado;
        const fila_fecha  = tr.dataset.fecha;
        let visible = true;
        if (estado && fila_estado !== estado) visible = false;
        if (desde  && fila_fecha < desde)     visible = false;
        if (hasta  && fila_fecha > hasta)     visible = false;
        tr.style.display = visible ? '' : 'none';
    });
}

/* ── Confirmar devolución ─────────────────────────────────── */
function confirmarDevolucion(e) {
    e.preventDefault();
    const form = e.target;
    Swal.fire({
        icon: 'warning',
        title: '¿Procesar devolución?',
        text: 'La venta quedará anulada y el stock se repondrá.',
        showCancelButton: true,
        confirmButtonText: 'Sí, procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#1a2d47'
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}

/* ── Ver recibo desde historial ───────────────────────────── */
function verRecibo(id) {
    window.location.href = `cajero.php?tab=recibo&id=${id}`;
}

/* ── Buscar recibo (AJAX) ─────────────────────────────────── */
let reciboIdActual = <?= $reciboId ?: 0 ?>;

function buscarRecibo() {
    const id = document.getElementById('buscarReciboId').value;
    if (!id) return;
    reciboIdActual = id;
    const cont = document.getElementById('reciboContenido');
    cont.innerHTML = '<div style="text-align:center;padding:40px;color:#8FB7C7;"><i class="fas fa-spinner fa-spin" style="font-size:28px;"></i></div>';
    fetch(`../../controllers/ReciboController.php?id=${id}`)
        .then(r => r.text())
        .then(html => {
            cont.innerHTML = html;
            const btnArea = document.getElementById('btnImprimirRecibo');
            if (btnArea) btnArea.style.display = html.includes('ticket-recibo') ? 'flex' : 'none';
        })
        .catch(() => {
            cont.innerHTML = '<p style="text-align:center;color:#c0392b;padding:30px;">Error al cargar el recibo.</p>';
        });
}

/* ── Imprimir ticket en ventana limpia ───────────────────── */
function imprimirTicket() {
    const id = reciboIdActual;
    if (!id) return;

    const win = window.open('', '_blank', 'width=420,height=700,scrollbars=yes');
    win.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo #${id}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#f5f5f5; display:flex; justify-content:center; padding:20px; }
        @media print {
            body { background:#fff; padding:0; }
            .print-btn { display:none !important; }
        }
    </style>
</head>
<body>
    <div>
        <div class="print-btn" style="text-align:center;margin-bottom:16px;">
            <button onclick="window.print()" style="background:#1a2d47;color:#fff;border:none;padding:10px 28px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">
                🖨️ Imprimir
            </button>
        </div>
        <div id="ticket-container"></div>
    </div>
    <script>
        fetch('../../controllers/ReciboController.php?id=${id}')
            .then(r => r.text())
            .then(html => {
                document.getElementById('ticket-container').innerHTML = html;
            });
    <\/script>
</body>
</html>`);
    win.document.close();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
