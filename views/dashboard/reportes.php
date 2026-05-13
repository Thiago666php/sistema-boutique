<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../usuarios/login.php');
    exit;
}

$titulo = 'Reportes';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<style>
:root {
    --navy:  #1a2d47;
    --blue:  #253E63;
    --sky:   #8FB7C7;
    --light: #D6E0E4;
}

.rep-wrap { padding: 28px 32px; }

.rep-topbar {
    display: flex; align-items: center;
    justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
    margin-bottom: 28px;
}
.rep-topbar h2 { font-size:26px; font-weight:800; color:var(--navy); margin:0; }
.rep-topbar p  { font-size:13px; color:#7a8fa6; margin:4px 0 0; }

/* KPI */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(195px, 1fr));
    gap: 16px; margin-bottom: 28px;
}
.kpi-card {
    background:#fff; border-radius:16px; padding:20px 18px;
    display:flex; align-items:center; gap:14px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
    border-left:5px solid var(--sky);
    transition:transform .18s, box-shadow .18s;
}
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 6px 18px rgba(0,0,0,.1); }
.kpi-card.kpi-danger  { border-left-color:#e74c3c; }
.kpi-card.kpi-success { border-left-color:#27ae60; }
.kpi-card.kpi-gold    { border-left-color:#d4ac0d; }
.kpi-card.kpi-purple  { border-left-color:#8e44ad; }

.kpi-icon {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:19px; flex-shrink:0;
}
.kpi-icon.c-sky    { background:#e8f4f8; color:var(--sky); }
.kpi-icon.c-navy   { background:#e8ecf2; color:var(--navy); }
.kpi-icon.c-green  { background:#e6f9f0; color:#27ae60; }
.kpi-icon.c-red    { background:#fde8e8; color:#e74c3c; }
.kpi-icon.c-gold   { background:#fef9e7; color:#d4ac0d; }
.kpi-icon.c-purple { background:#f3e8fd; color:#8e44ad; }

.kpi-val { font-size:24px; font-weight:800; color:var(--navy); line-height:1; }
.kpi-lbl { font-size:12px; color:#7a8fa6; margin-top:4px; font-weight:500; }

/* Paneles */
.panel {
    background:#fff; border-radius:16px; padding:22px 24px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
}
.panel-title {
    font-size:14px; font-weight:700; color:var(--navy);
    margin:0 0 16px; display:flex; align-items:center; gap:8px;
}
.panel-title i { color:var(--sky); }

.charts-row {
    display:grid; grid-template-columns:1fr 1fr;
    gap:18px; margin-bottom:18px;
}
@media(max-width:860px){ .charts-row{ grid-template-columns:1fr; } }

/* Tabs */
.tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
.tab-btn {
    padding:7px 16px; border-radius:10px;
    border:2px solid var(--light); background:#fff;
    color:var(--navy); font-size:13px; font-weight:600;
    cursor:pointer; transition:all .15s;
}
.tab-btn:hover  { background:var(--light); }
.tab-btn.active { background:var(--navy); color:#fff; border-color:var(--navy); }
.tab-pane { display:none; }
.tab-pane.active { display:block; }

/* Tabla */
.rep-tbl { width:100%; border-collapse:collapse; font-size:13px; }
.rep-tbl thead tr { background:var(--sky); color:var(--navy); }
.rep-tbl th { padding:10px 14px; text-align:left; font-weight:700; }
.rep-tbl td { padding:9px 14px; border-bottom:1px solid #eef2f5; color:#3a4a5c; }
.rep-tbl tbody tr:hover { background:#f7fafc; }
.rep-tbl tbody tr:last-child td { border-bottom:none; }

.badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-block; }
.badge-ok  { background:#e6f9f0; color:#1a7a4a; border:1px solid #a3d9b8; }
.badge-low { background:#fde8e8; color:#c0392b; border:1px solid #f5a8a8; }

/* Spinner */
.spinner { display:flex; align-items:center; justify-content:center; padding:40px; color:var(--sky); font-size:26px; }

/* Error inline */
.rep-error {
    display:flex; align-items:center; gap:10px;
    background:#fde8e8; color:#c0392b;
    border:1px solid #f5a8a8; border-radius:10px;
    padding:14px 18px; font-size:13px; font-weight:600;
}

/* Botón */
.btn-navy {
    background:var(--navy); color:#fff; border:none;
    padding:9px 20px; border-radius:10px;
    font-size:13px; font-weight:600; cursor:pointer;
    transition:background .15s;
    display:inline-flex; align-items:center; gap:7px;
}
.btn-navy:hover { background:var(--blue); }

@media print {
    .sidebar, header, .rep-topbar .btn-navy,
    .tabs, footer { display:none !important; }
    body { background:#fff !important; }
    .panel, .kpi-card { box-shadow:none !important; border:1px solid #ddd; }
    .tab-pane { display:block !important; }
}
</style>

<div class="rep-wrap">

    <!-- Encabezado -->
    <div class="rep-topbar">
        <div>
            <h2><i class="fas fa-chart-pie" style="color:var(--sky);margin-right:8px;"></i>Reportes y Estadísticas</h2>
            <p>Resumen general del sistema · actualizado en tiempo real</p>
        </div>
        <button class="btn-navy" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <!-- KPI -->
    <div class="kpi-grid" id="kpiGrid">
        <div class="spinner"><i class="fas fa-spinner fa-spin"></i></div>
    </div>

    <!-- Gráficos fila 1 -->
    <div class="charts-row">
        <div class="panel">
            <p class="panel-title"><i class="fas fa-tags"></i> Productos por Categoría</p>
            <div style="position:relative;height:255px;">
                <canvas id="chartCat"></canvas>
            </div>
        </div>
        <div class="panel">
            <p class="panel-title"><i class="fas fa-users"></i> Usuarios por Rol</p>
            <div style="position:relative;height:255px;">
                <canvas id="chartRol"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico línea -->
    <div class="panel" style="margin-bottom:18px;">
        <p class="panel-title"><i class="fas fa-calendar-alt"></i> Nuevos Usuarios — Últimos 12 meses</p>
        <div style="position:relative;height:210px;">
            <canvas id="chartMes"></canvas>
        </div>
    </div>

    <!-- Tablas con tabs -->
    <div class="panel">
        <p class="panel-title"><i class="fas fa-table"></i> Detalle de Datos</p>
        <div class="tabs">
            <button class="tab-btn active" data-tab="pStock"><i class="fas fa-box"></i> Top Stock</button>
            <button class="tab-btn" data-tab="pCritico"><i class="fas fa-exclamation-triangle"></i> Stock Crítico</button>
            <button class="tab-btn" data-tab="pProv"><i class="fas fa-truck"></i> Proveedores</button>
        </div>
        <div id="pStock"   class="tab-pane active"><div class="spinner"><i class="fas fa-spinner fa-spin"></i></div></div>
        <div id="pCritico" class="tab-pane"><div class="spinner"><i class="fas fa-spinner fa-spin"></i></div></div>
        <div id="pProv"    class="tab-pane"><div class="spinner"><i class="fas fa-spinner fa-spin"></i></div></div>
    </div>

</div>

<script>
const API     = '../../controllers/ReporteController.php';
const PALETTE = ['#253E63','#8FB7C7','#27ae60','#d4ac0d','#8e44ad','#e74c3c','#2980b9','#16a085','#d35400','#7f8c8d'];

/* ── Fetch con manejo de error ──────────────────────────────── */
async function get(accion) {
    const r = await fetch(`${API}?accion=${accion}`);
    const text = await r.text();
    let data;
    try { data = JSON.parse(text); } catch(e) {
        throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 200));
    }
    if (!r.ok || data.error) throw new Error(data.error || `HTTP ${r.status}`);
    return data;
}

/* ── Helpers ────────────────────────────────────────────────── */
const fmt      = n => Number(n).toLocaleString('es-CO');
const fmtMoney = n => '$ ' + Number(n).toLocaleString('es-CO', { minimumFractionDigits: 2 });
const esc      = s => s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';
const dash     = v => v ? esc(v) : '<span style="color:#ccc">—</span>';
const errBox   = msg => `<div class="rep-error"><i class="fas fa-exclamation-circle"></i>${esc(msg)}</div>`;

/* ── KPI ────────────────────────────────────────────────────── */
async function loadKPI() {
    const el = document.getElementById('kpiGrid');
    try {
        const d = await get('resumen');
        const items = [
            { icon:'fa-users',                cls:'c-navy',   card:'',           val:fmt(d.usuarios),              lbl:'Usuarios registrados'  },
            { icon:'fa-box',                  cls:'c-sky',    card:'',           val:fmt(d.productos),             lbl:'Productos activos'     },
            { icon:'fa-truck',                cls:'c-green',  card:'kpi-success',val:fmt(d.proveedores),           lbl:'Proveedores activos'   },
            { icon:'fa-tags',                 cls:'c-purple', card:'kpi-purple', val:fmt(d.categorias),            lbl:'Categorías activas'    },
            { icon:'fa-dollar-sign',          cls:'c-gold',   card:'kpi-gold',   val:fmtMoney(d.valor_inventario), lbl:'Valor del inventario'  },
            { icon:'fa-exclamation-triangle', cls:'c-red',    card:'kpi-danger', val:fmt(d.stock_bajo),            lbl:'Stock crítico (≤5)'    },
        ];
        el.innerHTML = items.map(c => `
            <div class="kpi-card ${c.card}">
                <div class="kpi-icon ${c.cls}"><i class="fas ${c.icon}"></i></div>
                <div><div class="kpi-val">${c.val}</div><div class="kpi-lbl">${c.lbl}</div></div>
            </div>`).join('');
    } catch(e) { el.innerHTML = errBox(e.message); }
}

/* ── Gráfico doughnut: categorías ───────────────────────────── */
async function loadChartCat() {
    try {
        const data = await get('productos_por_categoria');
        if (!data.length) { document.getElementById('chartCat').parentElement.innerHTML = '<p style="text-align:center;color:#aaa;padding:40px">Sin datos</p>'; return; }
        new Chart(document.getElementById('chartCat'), {
            type: 'doughnut',
            data: { labels: data.map(r=>r.categoria), datasets:[{ data:data.map(r=>r.total), backgroundColor:PALETTE, borderWidth:2, borderColor:'#fff' }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'right', labels:{ font:{size:12}, padding:12 } } } }
        });
    } catch(e) { document.getElementById('chartCat').parentElement.innerHTML = errBox(e.message); }
}

/* ── Gráfico barras: roles ──────────────────────────────────── */
async function loadChartRol() {
    try {
        const data = await get('usuarios_por_rol');
        if (!data.length) { document.getElementById('chartRol').parentElement.innerHTML = '<p style="text-align:center;color:#aaa;padding:40px">Sin datos</p>'; return; }
        new Chart(document.getElementById('chartRol'), {
            type: 'bar',
            data: { labels:data.map(r=>r.rol), datasets:[{ label:'Usuarios', data:data.map(r=>r.total), backgroundColor:PALETTE, borderRadius:8, borderSkipped:false }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#eef2f5'}}, x:{grid:{display:false}} } }
        });
    } catch(e) { document.getElementById('chartRol').parentElement.innerHTML = errBox(e.message); }
}

/* ── Gráfico línea: usuarios/mes ────────────────────────────── */
async function loadChartMes() {
    try {
        const data = await get('usuarios_por_mes');
        if (!data.length) { document.getElementById('chartMes').parentElement.innerHTML = '<p style="text-align:center;color:#aaa;padding:40px">Sin datos en los últimos 12 meses</p>'; return; }
        new Chart(document.getElementById('chartMes'), {
            type: 'line',
            data: { labels:data.map(r=>r.mes), datasets:[{ label:'Nuevos usuarios', data:data.map(r=>r.total), borderColor:'#253E63', backgroundColor:'rgba(37,62,99,.12)', fill:true, tension:0.4, pointBackgroundColor:'#8FB7C7', pointRadius:5 }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#eef2f5'}}, x:{grid:{display:false}} } }
        });
    } catch(e) { document.getElementById('chartMes').parentElement.innerHTML = errBox(e.message); }
}

/* ── Tabla: top stock ───────────────────────────────────────── */
async function loadTablaStock() {
    const el = document.getElementById('pStock');
    try {
        const data = await get('top_productos_stock');
        el.innerHTML = !data.length
            ? '<p style="text-align:center;color:#aaa;padding:30px">Sin datos disponibles.</p>'
            : `<div style="overflow-x:auto"><table class="rep-tbl">
                <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Valor Total</th></tr></thead>
                <tbody>${data.map((r,i)=>`
                <tr>
                    <td style="color:#aaa">${i+1}</td>
                    <td style="font-weight:600;color:#1a2d47">${esc(r.nombre)}</td>
                    <td>${esc(r.categoria)}</td>
                    <td>${fmtMoney(r.precio)}</td>
                    <td><span class="badge ${r.stock>5?'badge-ok':'badge-low'}">${fmt(r.stock)}</span></td>
                    <td style="font-weight:600">${fmtMoney(r.valor_total)}</td>
                </tr>`).join('')}</tbody>
               </table></div>`;
    } catch(e) { el.innerHTML = errBox(e.message); }
}

/* ── Tabla: stock crítico ───────────────────────────────────── */
async function loadTablaCritico() {
    const el = document.getElementById('pCritico');
    try {
        const data = await get('stock_critico');
        el.innerHTML = !data.length
            ? '<p style="text-align:center;color:#27ae60;padding:30px"><i class="fas fa-check-circle"></i> Sin productos en stock crítico.</p>'
            : `<div style="overflow-x:auto"><table class="rep-tbl">
                <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr></thead>
                <tbody>${data.map((r,i)=>`
                <tr>
                    <td style="color:#aaa">${i+1}</td>
                    <td style="font-weight:600;color:#c0392b">${esc(r.nombre)}</td>
                    <td>${esc(r.categoria)}</td>
                    <td>${fmtMoney(r.precio)}</td>
                    <td><span class="badge badge-low">${fmt(r.stock)}</span></td>
                </tr>`).join('')}</tbody>
               </table></div>`;
    } catch(e) { el.innerHTML = errBox(e.message); }
}

/* ── Tabla: proveedores ─────────────────────────────────────── */
async function loadTablaProveedores() {
    const el = document.getElementById('pProv');
    try {
        const data = await get('proveedores_activos');
        el.innerHTML = !data.length
            ? '<p style="text-align:center;color:#aaa;padding:30px">Sin proveedores activos.</p>'
            : `<div style="overflow-x:auto"><table class="rep-tbl">
                <thead><tr><th>#</th><th>Nombre</th><th>RUC</th><th>Teléfono</th><th>Correo</th><th>Registro</th></tr></thead>
                <tbody>${data.map((r,i)=>`
                <tr>
                    <td style="color:#aaa">${i+1}</td>
                    <td style="font-weight:600;color:#1a2d47">${esc(r.nombre)}</td>
                    <td>${dash(r.ruc)}</td>
                    <td>${dash(r.telefono)}</td>
                    <td>${dash(r.correo)}</td>
                    <td>${esc(r.fecha_registro)}</td>
                </tr>`).join('')}</tbody>
               </table></div>`;
    } catch(e) { el.innerHTML = errBox(e.message); }
}

/* ── Tabs ───────────────────────────────────────────────────── */
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

/* ── Init ───────────────────────────────────────────────────── */
Promise.allSettled([
    loadKPI(),
    loadChartCat(),
    loadChartRol(),
    loadChartMes(),
    loadTablaStock(),
    loadTablaCritico(),
    loadTablaProveedores(),
]);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
