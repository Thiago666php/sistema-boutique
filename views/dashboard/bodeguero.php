<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["rol"] !== "bodeguero") {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Inventario.php";

$db    = (new Database())->conectar();
$model = new Inventario($db);

$tab         = $_GET["tab"] ?? "inventario";
$productos   = $model->obtenerProductos();
$categorias  = $model->obtenerCategorias();
$proveedores = $model->obtenerProveedores();
$stockBajo   = $model->productosStockBajo();
$movimientos = $model->obtenerMovimientos();
$repProv     = $model->reportePorProveedor();

$titulo = "Módulo Bodeguero";
require_once __DIR__ . "/../layouts/header.php";
require_once __DIR__ . "/../layouts/sidebar.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--navy:#1a2d47;--sky:#8FB7C7;--light:#D6E0E4;}
.bod-wrap{padding:24px 32px;}
.bod-tabs{display:flex;gap:4px;background:#e8f0f3;border-radius:14px;padding:5px;margin-bottom:24px;width:fit-content;flex-wrap:wrap;}
.bod-tab{padding:9px 18px;border-radius:10px;border:none;background:transparent;color:#5a7080;font-size:13px;font-weight:600;cursor:pointer;transition:all .18s;display:flex;align-items:center;gap:7px;}
.bod-tab:hover{background:rgba(255,255,255,.6);color:var(--navy);}
.bod-tab.active{background:#fff;color:var(--navy);box-shadow:0 2px 8px rgba(0,0,0,.1);}
.bod-pane{display:none;}.bod-pane.active{display:block;}
.panel{background:#fff;border-radius:16px;padding:22px 24px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:18px;}
.panel-title{font-size:15px;font-weight:700;color:var(--navy);margin:0 0 16px;display:flex;align-items:center;gap:8px;}
.panel-title i{color:var(--sky);}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
@media(max-width:860px){.grid2{grid-template-columns:1fr;}}
.bod-tbl{width:100%;border-collapse:collapse;font-size:13px;}
.bod-tbl thead tr{background:var(--sky);color:var(--navy);}
.bod-tbl th{padding:10px 14px;text-align:left;font-weight:700;}
.bod-tbl td{padding:9px 14px;border-bottom:1px solid #eef2f5;color:#3a4a5c;}
.bod-tbl tbody tr:hover{background:#f7fafc;}
.bod-tbl tbody tr:last-child td{border-bottom:none;}
.badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-block;}
.badge-ok{background:#e6f9f0;color:#1a7a4a;border:1px solid #a3d9b8;}
.badge-warn{background:#fef9e7;color:#b7950b;border:1px solid #f9e79f;}
.badge-err{background:#fde8e8;color:#c0392b;border:1px solid #f5a8a8;}
.badge-blue{background:#e8f0fe;color:#1a2d47;border:1px solid #c5d8fc;}
.inp{border:1.5px solid #c8d8df;border-radius:10px;padding:9px 14px;font-size:13px;width:100%;outline:none;transition:border-color .15s;box-sizing:border-box;}
.inp:focus{border-color:var(--sky);box-shadow:0 0 0 3px rgba(143,183,199,.2);}
.btn-navy{background:var(--navy);color:#fff;border:none;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:7px;}
.btn-navy:hover{background:#253E63;}
.btn-sky{background:var(--sky);color:var(--navy);border:none;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:7px;}
.btn-sky:hover{background:#7aaabb;color:#fff;}
.btn-edit{background:#e8f0fe;color:#1a2d47;border:none;padding:6px 12px;border-radius:8px;cursor:pointer;font-size:12px;}
.btn-edit:hover{background:#d0e0fc;}
.form-row{display:flex;flex-direction:column;gap:4px;margin-bottom:12px;}
.form-row label{font-size:12px;font-weight:700;color:var(--navy);}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.alert-banner{background:#fde8e8;border:1px solid #f5a8a8;border-radius:10px;padding:12px 16px;font-size:13px;color:#c0392b;display:flex;align-items:center;gap:8px;margin-bottom:16px;}
@media print{.bod-tabs,.sidebar,header,footer,.no-print{display:none!important;}.bod-pane{display:block!important;}body{background:#fff!important;}}
</style>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded',()=>Swal.fire({
    icon:'<?= $_SESSION['alert']['icon'] ?>',
    title:'<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
    text:'<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
    confirmButtonColor:'#1a2d47'
}));
</script>
<?php unset($_SESSION['alert']); endif; ?>

<div class="bod-wrap">

<!-- Tabs -->
<div class="bod-tabs">
    <button class="bod-tab <?= $tab==='inventario'?'active':'' ?>" onclick="switchTab('inventario')"><i class="fas fa-warehouse"></i> Inventario</button>
    <button class="bod-tab <?= $tab==='entradas'  ?'active':'' ?>" onclick="switchTab('entradas')"><i class="fas fa-arrow-down"></i> Entradas</button>
    <button class="bod-tab <?= $tab==='ajustes'   ?'active':'' ?>" onclick="switchTab('ajustes')"><i class="fas fa-sliders-h"></i> Ajustes</button>
    <button class="bod-tab <?= $tab==='movimientos'?'active':'' ?>" onclick="switchTab('movimientos')"><i class="fas fa-history"></i> Movimientos</button>
    <button class="bod-tab <?= $tab==='reportes'  ?'active':'' ?>" onclick="switchTab('reportes')"><i class="fas fa-chart-bar"></i> Reportes</button>
</div>

<!-- ══ TAB 1: INVENTARIO ══ -->
<div id="pane-inventario" class="bod-pane <?= $tab==='inventario'?'active':'' ?>">

    <?php if (!empty($stockBajo)): ?>
    <div class="alert-banner">
        <i class="fas fa-exclamation-triangle"></i>
        <strong><?= count($stockBajo) ?> producto(s)</strong> con stock crítico (≤ 5 unidades). Revisa la pestaña Reportes.
    </div>
    <?php endif; ?>

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
        <h2 style="font-size:22px;font-weight:800;color:var(--navy);margin:0;">Inventario de Productos</h2>
        <button class="btn-navy no-print" onclick="openModal('modalCrearProd')"><i class="fas fa-plus"></i> Nuevo Producto</button>
    </div>

    <input type="text" id="buscarInv" class="inp no-print" placeholder="Buscar producto o categoría..." oninput="filtrarTabla('tablaInv','buscarInv')" style="max-width:320px;margin-bottom:14px;">

    <div class="panel" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
    <table class="bod-tbl" id="tablaInv">
        <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th class="no-print">Acción</th></tr></thead>
        <tbody>
        <?php if (empty($productos)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaa;"><i class="fas fa-box-open" style="font-size:32px;opacity:.2;display:block;margin-bottom:8px;"></i>Sin productos registrados.</td></tr>
        <?php endif; ?>
        <?php foreach ($productos as $i => $p): ?>
        <tr data-search="<?= strtolower(htmlspecialchars($p['nombre'].' '.$p['categoria'])) ?>">
            <td style="color:#aaa"><?= $i+1 ?></td>
            <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= htmlspecialchars($p['categoria']) ?></td>
            <td>$ <?= number_format($p['precio'],2,',','.') ?></td>
            <td>
                <span class="badge <?= $p['stock']<=0?'badge-err':($p['stock']<=5?'badge-warn':'badge-ok') ?>">
                    <?= $p['stock'] ?> uds.
                </span>
            </td>
            <td><span class="badge <?= $p['activo']?'badge-ok':'badge-err' ?>"><?= $p['activo']?'Activo':'Inactivo' ?></span></td>
            <td class="no-print">
                <button class="btn-edit" onclick='openEditProd(<?= json_encode($p) ?>)'><i class="fas fa-pen"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    </div>
</div>

<!-- ══ TAB 2: ENTRADAS ══ -->
<div id="pane-entradas" class="bod-pane <?= $tab==='entradas'?'active':'' ?>">
    <div class="grid2">
        <div class="panel">
            <p class="panel-title"><i class="fas fa-arrow-down"></i> Registrar Entrada al Almacén</p>
            <form method="POST" action="../../controllers/InventarioController.php?accion=entrada">
                <div class="form-row"><label>Producto *</label>
                    <select name="id_producto" class="inp" required>
                        <option value="">Selecciona un producto...</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>"><?= htmlspecialchars($p['nombre']) ?> (stock: <?= $p['stock'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-row"><label>Cantidad *</label><input type="number" name="cantidad" class="inp" min="1" required placeholder="Ej: 20"></div>
                    <div class="form-row"><label>Proveedor</label>
                        <select name="id_proveedor" class="inp">
                            <option value="">Sin proveedor</option>
                            <?php foreach ($proveedores as $pv): ?>
                            <option value="<?= $pv['id_proveedor'] ?>"><?= htmlspecialchars($pv['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row"><label>Motivo / Observación</label><input type="text" name="motivo" class="inp" placeholder="Ej: Compra a proveedor, reposición..."></div>
                <button type="submit" class="btn-navy" style="width:100%;justify-content:center;margin-top:4px;"><i class="fas fa-check"></i> Registrar Entrada</button>
            </form>
        </div>
        <div class="panel">
            <p class="panel-title"><i class="fas fa-list"></i> Últimas entradas</p>
            <?php $entradas = $model->obtenerMovimientos('entrada'); ?>
            <?php if (empty($entradas)): ?>
                <p style="text-align:center;color:#aaa;padding:30px;">Sin entradas registradas.</p>
            <?php else: ?>
            <div style="overflow-x:auto;"><table class="bod-tbl">
                <thead><tr><th>Producto</th><th>Cant.</th><th>Proveedor</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($entradas,0,15) as $m): ?>
                <tr>
                    <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($m['producto']) ?></td>
                    <td><span class="badge badge-ok">+<?= $m['cantidad'] ?></span></td>
                    <td><?= $m['proveedor'] ? htmlspecialchars($m['proveedor']) : '<span style="color:#ccc">—</span>' ?></td>
                    <td style="font-size:12px;color:#7a8fa6"><?= $m['fecha'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ TAB 3: AJUSTES ══ -->
<div id="pane-ajustes" class="bod-pane <?= $tab==='ajustes'?'active':'' ?>">
    <div class="grid2">
        <div class="panel">
            <p class="panel-title"><i class="fas fa-sliders-h"></i> Registrar Ajuste / Pérdida / Merma</p>
            <form method="POST" action="../../controllers/InventarioController.php?accion=ajuste">
                <div class="form-row"><label>Producto *</label>
                    <select name="id_producto" class="inp" required>
                        <option value="">Selecciona un producto...</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>"><?= htmlspecialchars($p['nombre']) ?> (stock: <?= $p['stock'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-row"><label>Tipo de ajuste *</label>
                        <select name="tipo_ajuste" class="inp" required>
                            <option value="perdida">Pérdida</option>
                            <option value="merma">Merma</option>
                            <option value="ajuste_negativo">Ajuste negativo</option>
                            <option value="ajuste_positivo">Ajuste positivo</option>
                        </select>
                    </div>
                    <div class="form-row"><label>Cantidad *</label><input type="number" name="cantidad" class="inp" min="1" required placeholder="Ej: 3"></div>
                </div>
                <div class="form-row"><label>Motivo *</label><textarea name="motivo" class="inp" rows="3" required placeholder="Describe el motivo del ajuste..." style="resize:none;"></textarea></div>
                <button type="submit" class="btn-navy" style="width:100%;justify-content:center;margin-top:4px;"><i class="fas fa-check"></i> Registrar Ajuste</button>
            </form>
            <div style="margin-top:14px;padding:12px;background:#fef9e7;border-radius:10px;border:1px solid #f9e79f;">
                <p style="font-size:12px;color:#7d6608;margin:0;"><i class="fas fa-info-circle"></i> Los ajustes negativos (pérdidas, mermas) reducen el stock. Los positivos lo incrementan.</p>
            </div>
        </div>
        <div class="panel">
            <p class="panel-title"><i class="fas fa-list"></i> Últimos ajustes</p>
            <?php $ajustes = array_filter($movimientos, fn($m) => $m['tipo'] !== 'entrada'); ?>
            <?php if (empty($ajustes)): ?>
                <p style="text-align:center;color:#aaa;padding:30px;">Sin ajustes registrados.</p>
            <?php else: ?>
            <div style="overflow-x:auto;"><table class="bod-tbl">
                <thead><tr><th>Producto</th><th>Tipo</th><th>Cant.</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach (array_slice(array_values($ajustes),0,15) as $m): ?>
                <?php $esNeg = in_array($m['tipo'],['perdida','merma','ajuste_negativo']); ?>
                <tr>
                    <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($m['producto']) ?></td>
                    <td><span class="badge <?= $esNeg?'badge-err':'badge-ok' ?>"><?= ucfirst(str_replace('_',' ',$m['tipo'])) ?></span></td>
                    <td><span class="badge <?= $esNeg?'badge-err':'badge-ok' ?>"><?= $esNeg?'-':'+' ?><?= $m['cantidad'] ?></span></td>
                    <td style="font-size:12px;color:#7a8fa6"><?= $m['fecha'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ TAB 4: MOVIMIENTOS ══ -->
<div id="pane-movimientos" class="bod-pane <?= $tab==='movimientos'?'active':'' ?>">
    <div class="panel">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <p class="panel-title" style="margin:0;"><i class="fas fa-history"></i> Historial de Movimientos</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;" class="no-print">
                <select id="filtroTipo" class="inp" style="width:180px;" onchange="filtrarMovimientos()">
                    <option value="">Todos los tipos</option>
                    <option value="entrada">Entradas</option>
                    <option value="perdida">Pérdidas</option>
                    <option value="merma">Mermas</option>
                    <option value="ajuste_positivo">Ajuste positivo</option>
                    <option value="ajuste_negativo">Ajuste negativo</option>
                </select>
                <button class="btn-navy" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            </div>
        </div>
        <?php if (empty($movimientos)): ?>
            <p style="text-align:center;color:#aaa;padding:40px;">Sin movimientos registrados.</p>
        <?php else: ?>
        <div style="overflow-x:auto;"><table class="bod-tbl" id="tablaMovimientos">
            <thead><tr><th>#</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($movimientos as $i => $m): ?>
            <?php $esNeg = in_array($m['tipo'],['perdida','merma','ajuste_negativo']); ?>
            <tr data-tipo="<?= $m['tipo'] ?>">
                <td style="color:#aaa"><?= $i+1 ?></td>
                <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($m['producto']) ?></td>
                <td><span class="badge <?= $m['tipo']==='entrada'?'badge-blue':($esNeg?'badge-err':'badge-ok') ?>"><?= ucfirst(str_replace('_',' ',$m['tipo'])) ?></span></td>
                <td><span class="badge <?= $esNeg?'badge-err':'badge-ok' ?>"><?= $esNeg?'-':'+' ?><?= $m['cantidad'] ?></span></td>
                <td style="color:#5a7080;font-size:12px;"><?= htmlspecialchars($m['motivo'] ?? '—') ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars($m['usuario']) ?></td>
                <td style="font-size:12px;color:#7a8fa6;"><?= $m['fecha'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ TAB 5: REPORTES ══ -->
<div id="pane-reportes" class="bod-pane <?= $tab==='reportes'?'active':'' ?>">
    <div style="display:flex;justify-content:flex-end;margin-bottom:14px;" class="no-print">
        <button class="btn-navy" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>

    <!-- Stock crítico -->
    <div class="panel">
        <p class="panel-title"><i class="fas fa-exclamation-triangle"></i> Alertas de Stock Crítico (≤ 5 unidades)</p>
        <?php if (empty($stockBajo)): ?>
            <p style="text-align:center;color:#27ae60;padding:20px;"><i class="fas fa-check-circle"></i> Todos los productos tienen stock suficiente.</p>
        <?php else: ?>
        <div style="overflow-x:auto;"><table class="bod-tbl">
            <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th>Stock actual</th><th>Alerta</th></tr></thead>
            <tbody>
            <?php foreach ($stockBajo as $i => $p): ?>
            <tr>
                <td style="color:#aaa"><?= $i+1 ?></td>
                <td style="font-weight:600;color:#c0392b"><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['categoria']) ?></td>
                <td><span class="badge <?= $p['stock']<=0?'badge-err':'badge-warn' ?>"><?= $p['stock'] ?> uds.</span></td>
                <td><?= $p['stock']<=0 ? '<span class="badge badge-err">Sin stock</span>' : '<span class="badge badge-warn">Stock bajo</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>

    <!-- Reporte por proveedor -->
    <div class="panel">
        <p class="panel-title"><i class="fas fa-truck"></i> Reporte de Entradas por Proveedor</p>
        <?php if (empty($repProv)): ?>
            <p style="text-align:center;color:#aaa;padding:20px;">Sin entradas registradas por proveedor.</p>
        <?php else: ?>
        <div style="overflow-x:auto;"><table class="bod-tbl">
            <thead><tr><th>#</th><th>Proveedor</th><th>Productos distintos</th><th>Total unidades</th><th>Última entrada</th></tr></thead>
            <tbody>
            <?php foreach ($repProv as $i => $r): ?>
            <tr>
                <td style="color:#aaa"><?= $i+1 ?></td>
                <td style="font-weight:600;color:var(--navy)"><?= htmlspecialchars($r['proveedor']) ?></td>
                <td style="text-align:center;"><?= $r['productos_distintos'] ?></td>
                <td><span class="badge badge-ok"><?= number_format($r['total_unidades']) ?></span></td>
                <td style="font-size:12px;color:#7a8fa6;"><?= $r['ultima_entrada'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>

</div><!-- /bod-wrap -->

<!-- ══ MODAL: Crear Producto ══ -->
<div id="modalCrearProd" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
<div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden" style="box-shadow:0 8px 32px rgba(0,0,0,.15)">
    <div style="background:#8FB7C7;display:flex;justify-content:space-between;align-items:center;padding:18px 24px;">
        <h3 style="font-size:17px;font-weight:700;color:#1a2d47;margin:0;"><i class="fas fa-plus"></i> Nuevo Producto</h3>
        <button onclick="closeModal('modalCrearProd')" style="background:none;border:none;font-size:22px;cursor:pointer;color:#1a2d47;">✕</button>
    </div>
    <form method="POST" action="../../controllers/InventarioController.php?accion=crear_producto" style="padding:22px;display:flex;flex-direction:column;gap:12px;">
        <div class="form-grid">
            <div class="form-row"><label>Nombre *</label><input type="text" name="nombre" class="inp" required placeholder="Nombre del producto"></div>
            <div class="form-row"><label>Categoría *</label>
                <select name="id_categoria" class="inp" required>
                    <option value="">Selecciona...</option>
                    <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-row"><label>Precio *</label><input type="number" name="precio" class="inp" step="0.01" min="0" required placeholder="0.00"></div>
            <div class="form-row"><label>Stock inicial</label><input type="number" name="stock" class="inp" min="0" value="0" placeholder="0"></div>
        </div>
        <div class="form-row"><label>Descripción</label><textarea name="descripcion" class="inp" rows="2" style="resize:none;" placeholder="Descripción opcional..."></textarea></div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
            <button type="button" onclick="closeModal('modalCrearProd')" style="padding:9px 20px;border-radius:10px;border:1px solid #ccc;background:#fff;cursor:pointer;font-size:13px;">Cancelar</button>
            <button type="submit" class="btn-navy">Guardar</button>
        </div>
    </form>
</div>
</div>

<!-- ══ MODAL: Editar Producto ══ -->
<div id="modalEditarProd" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
<div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden" style="box-shadow:0 8px 32px rgba(0,0,0,.15)">
    <div style="background:#8FB7C7;display:flex;justify-content:space-between;align-items:center;padding:18px 24px;">
        <h3 style="font-size:17px;font-weight:700;color:#1a2d47;margin:0;"><i class="fas fa-pen"></i> Editar Producto</h3>
        <button onclick="closeModal('modalEditarProd')" style="background:none;border:none;font-size:22px;cursor:pointer;color:#1a2d47;">✕</button>
    </div>
    <form method="POST" action="../../controllers/InventarioController.php?accion=editar_producto" style="padding:22px;display:flex;flex-direction:column;gap:12px;">
        <input type="hidden" name="id_producto" id="edit_prod_id">
        <div class="form-grid">
            <div class="form-row"><label>Nombre *</label><input type="text" name="nombre" id="edit_prod_nombre" class="inp" required></div>
            <div class="form-row"><label>Categoría *</label>
                <select name="id_categoria" id="edit_prod_cat" class="inp" required>
                    <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row"><label>Precio *</label><input type="number" name="precio" id="edit_prod_precio" class="inp" step="0.01" min="0" required></div>
        <div class="form-row"><label>Descripción</label><textarea name="descripcion" id="edit_prod_desc" class="inp" rows="2" style="resize:none;"></textarea></div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:4px;">
            <button type="button" onclick="closeModal('modalEditarProd')" style="padding:9px 20px;border-radius:10px;border:1px solid #ccc;background:#fff;cursor:pointer;font-size:13px;">Cancelar</button>
            <button type="submit" class="btn-navy">Actualizar</button>
        </div>
    </form>
</div>
</div>

<script>
function switchTab(name) {
    document.querySelectorAll('.bod-tab').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.bod-pane').forEach(p=>p.classList.remove('active'));
    document.getElementById('pane-'+name).classList.add('active');
    document.querySelector(`.bod-tab[onclick="switchTab('${name}')"]`).classList.add('active');
    history.replaceState(null,'',`?tab=${name}`);
}
const openModal  = id => document.getElementById(id).classList.remove('hidden');
const closeModal = id => document.getElementById(id).classList.add('hidden');
window.addEventListener('click', e => {
    ['modalCrearProd','modalEditarProd'].forEach(id=>{
        if(e.target===document.getElementById(id)) closeModal(id);
    });
});
function openEditProd(p) {
    document.getElementById('edit_prod_id').value     = p.id_producto;
    document.getElementById('edit_prod_nombre').value = p.nombre;
    document.getElementById('edit_prod_precio').value = p.precio;
    document.getElementById('edit_prod_desc').value   = p.descripcion ?? '';
    document.getElementById('edit_prod_cat').value    = p.id_categoria;
    openModal('modalEditarProd');
}
function filtrarTabla(tablaId, inputId) {
    const q = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#'+tablaId+' tbody tr').forEach(tr=>{
        tr.style.display = (tr.dataset.search||'').includes(q) ? '' : 'none';
    });
}
function filtrarMovimientos() {
    const tipo = document.getElementById('filtroTipo').value;
    document.querySelectorAll('#tablaMovimientos tbody tr').forEach(tr=>{
        tr.style.display = (!tipo || tr.dataset.tipo===tipo) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
