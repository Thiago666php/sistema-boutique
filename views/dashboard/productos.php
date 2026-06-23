<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../usuarios/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Categoria.php';
require_once __DIR__ . '/../../models/Inventario.php';

$db = (new Database())->conectar();

// Asegurar tablas
$db->exec("CREATE TABLE IF NOT EXISTS `categorias_productos` (
    `id_categoria` INT          PRIMARY KEY AUTO_INCREMENT,
    `nombre`       VARCHAR(100) NOT NULL,
    `descripcion`  VARCHAR(255) DEFAULT NULL,
    `cantidad`     INT          NOT NULL DEFAULT 0,
    `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

try { $db->exec("ALTER TABLE `categorias_productos` ADD COLUMN `cantidad` INT NOT NULL DEFAULT 0 AFTER `descripcion`;"); } catch (Exception $e) {}

$db->exec("CREATE TABLE IF NOT EXISTS `productos` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

try { $db->exec("ALTER TABLE `productos` ADD COLUMN `imagen` VARCHAR(255) DEFAULT NULL AFTER `stock`;"); } catch (Exception $e) {}

$categoriaModel = new Categoria($db);
$categorias     = $categoriaModel->obtenerTodas();
$invModel       = new Inventario($db);
$productos      = $invModel->obtenerProductos();

$titulo = 'Gestión de Productos';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --navy:#1a2d47; --sky:#8FB7C7; }
    .btn-navy       { background:var(--navy); color:#fff; }
    .btn-navy:hover { background:#14233a; }
    .thead-custom   { background:var(--sky); color:var(--navy); }
    .modal-header   { background:var(--sky); }
    .btn-edit { background:#e8f0fe; color:#1a2d47; } .btn-edit:hover { background:#d0e0fc; }
    .btn-del  { background:#fde8e8; color:#c0392b; } .btn-del:hover  { background:#fac8c8; }
    .input-field       { border:1.5px solid #c8d8df; border-radius:10px; }
    .input-field:focus { border-color:var(--sky); outline:none; box-shadow:0 0 0 3px rgba(143,183,199,.2); }

    /* Categoría inline */
    .cat-select-wrap { display:flex; gap:8px; align-items:center; }
    .cat-select-wrap select { flex:1; }
    .btn-new-cat {
        background:#e8f0fe; color:#1a2d47; border:none;
        padding:9px 12px; border-radius:10px; cursor:pointer;
        font-size:13px; font-weight:600; white-space:nowrap;
        display:flex; align-items:center; gap:5px;
        transition:background .15s;
    }
    .btn-new-cat:hover { background:#d0e0fc; }

    /* Inline form nueva categoría */
    .new-cat-inline {
        background:#f0f6f9; border:1.5px solid #c8d8df;
        border-radius:10px; padding:14px; margin-top:8px;
        display:none; flex-direction:column; gap:10px;
    }
    .new-cat-inline.visible { display:flex; }
    .new-cat-inline label { font-size:11px; font-weight:700; color:var(--navy); }
</style>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () => Swal.fire({
    icon: '<?= $_SESSION['alert']['icon'] ?>',
    title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
    text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#1a2d47'
}));
</script>
<?php unset($_SESSION['alert']); endif; ?>

<div class="p-8 space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-4xl font-bold" style="color:var(--navy);">Gestión de Productos</h2>
        <button onclick="openModal('modalCrearProducto')"
                class="btn-navy px-5 py-2.5 rounded-xl font-semibold text-sm border-0 cursor-pointer transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo Producto
        </button>
    </div>

    <!-- Buscador -->
    <input type="text" id="buscarProd" placeholder="Buscar por nombre o categoría..."
           oninput="filtrarProductos()"
           class="input-field px-4 py-2.5 text-sm" style="max-width:320px;">

    <!-- Tabla -->
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.07)">
        <div style="overflow-x:auto;">
        <table class="w-full border-collapse text-sm" id="tablaProductos">
            <thead>
                <tr class="thead-custom">
                    <th class="px-4 py-3.5 text-left font-bold">#</th>
                    <th class="px-4 py-3.5 text-left font-bold">Imagen</th>
                    <th class="px-4 py-3.5 text-left font-bold">Producto</th>
                    <th class="px-4 py-3.5 text-left font-bold">Categoría</th>
                    <th class="px-4 py-3.5 text-left font-bold">Precio</th>
                    <th class="px-4 py-3.5 text-left font-bold">Stock</th>
                    <th class="px-4 py-3.5 text-left font-bold">Estado</th>
                    <th class="px-4 py-3.5 text-center font-bold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="8" class="py-14 text-center text-gray-400">
                            <i class="fas fa-box" style="font-size:40px;opacity:.15;display:block;margin-bottom:10px;"></i>
                            No hay productos registrados. Crea el primero con el botón de arriba.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($productos as $i => $p): ?>
                <tr class="border-b border-gray-100 hover:bg-slate-50 transition-colors"
                    data-search="<?= strtolower(htmlspecialchars($p['nombre'].' '.$p['categoria'])) ?>">
                    <td class="px-4 py-3 text-gray-400"><?= $i + 1 ?></td>
                    <td class="px-4 py-3">
                        <?php if (!empty($p['imagen'])): ?>
                            <img src="../../img/productos/<?= htmlspecialchars($p['imagen']) ?>"
                                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                                 style="width:48px;height:48px;object-fit:cover;border-radius:10px;border:1px solid #e0e8ec;cursor:pointer;"
                                 onclick="verImagen('../../img/productos/<?= htmlspecialchars($p['imagen']) ?>','<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>')">
                        <?php else: ?>
                            <div style="width:48px;height:48px;border-radius:10px;background:#f0f4f6;display:flex;align-items:center;justify-content:center;border:1px solid #e0e8ec;">
                                <i class="fas fa-image" style="color:#c8d8df;font-size:18px;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-semibold" style="color:var(--navy)">
                        <?= htmlspecialchars($p['nombre']) ?>
                        <?php if ($p['descripcion']): ?>
                            <div style="font-size:11px;color:#7a8fa6;font-weight:400;">
                                <?= htmlspecialchars(mb_strimwidth($p['descripcion'], 0, 45, '…')) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($p['categoria']) ?></td>
                    <td class="px-4 py-3 font-semibold" style="color:var(--navy)">
                        $ <?= number_format($p['precio'], 2, ',', '.') ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $sc = $p['stock'] <= 0
                            ? 'bg-red-50 text-red-700 border border-red-200'
                            : ($p['stock'] <= 5
                                ? 'bg-orange-50 text-orange-700 border border-orange-200'
                                : 'bg-green-50 text-green-700 border border-green-200');
                        ?>
                        <span class="<?= $sc ?> text-xs font-bold px-3 py-1 rounded-full">
                            <?= $p['stock'] ?> uds.
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($p['activo']): ?>
                            <span class="bg-green-50 text-green-700 border border-green-200 text-xs font-bold px-3 py-1 rounded-full">Activo</span>
                        <?php else: ?>
                            <span class="bg-red-50 text-red-700 border border-red-200 text-xs font-bold px-3 py-1 rounded-full">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button onclick='openEditProducto(<?= json_encode($p) ?>)'
                                title="Editar"
                                class="btn-edit px-3 py-1.5 rounded-lg border-0 cursor-pointer text-sm transition-colors">
                            <i class="fas fa-pen"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════
     MODAL: CREAR PRODUCTO
══════════════════════════════════════════════ -->
<div id="modalCrearProducto"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden"
         style="box-shadow:0 8px 32px rgba(0,0,0,.15);max-height:92vh;overflow-y:auto;">

        <div class="modal-header flex justify-between items-center px-7 py-5 sticky top-0 z-10">
            <h3 class="text-xl font-bold" style="color:var(--navy)">
                <i class="fas fa-plus mr-2"></i>Nuevo Producto
            </h3>
            <button onclick="closeModal('modalCrearProducto')"
                    class="bg-transparent border-0 text-2xl cursor-pointer leading-none"
                    style="color:var(--navy)">✕</button>
        </div>

        <form action="../../controllers/InventarioController.php?accion=crear_producto"
              method="POST" enctype="multipart/form-data"
              class="px-7 py-6 flex flex-col gap-4">

            <!-- Nombre -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Nombre *</label>
                <input type="text" name="nombre" required placeholder="Nombre del producto"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <!-- Categoría + botón nueva categoría -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Categoría *</label>
                <div class="cat-select-wrap">
                    <select name="id_categoria" id="cp_cat" required
                            class="input-field px-4 py-2.5 text-sm bg-white">
                        <option value="">Selecciona una categoría...</option>
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-new-cat"
                            onclick="toggleNuevaCat('crear')" title="Crear nueva categoría">
                        <i class="fas fa-plus"></i> Nueva
                    </button>
                </div>

                <!-- Formulario inline nueva categoría -->
                <div id="nueva-cat-crear" class="new-cat-inline">
                    <p style="font-size:12px;font-weight:700;color:var(--navy);margin:0;">
                        <i class="fas fa-tag" style="color:var(--sky);"></i> Nueva categoría
                    </p>
                    <div>
                        <label>Nombre de la categoría *</label>
                        <input type="text" id="cp_nueva_cat_nombre"
                               class="input-field w-full px-3 py-2 text-sm mt-1"
                               placeholder="Ej: Ropa de mujer">
                    </div>
                    <div>
                        <label>Descripción <span style="font-weight:400;color:#aaa;">(opcional)</span></label>
                        <input type="text" id="cp_nueva_cat_desc"
                               class="input-field w-full px-3 py-2 text-sm mt-1"
                               placeholder="Descripción breve...">
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" onclick="toggleNuevaCat('crear')"
                                style="padding:6px 14px;border-radius:8px;border:1px solid #ccc;background:#fff;font-size:12px;cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="button" onclick="crearCategoriaAjax('crear')"
                                style="padding:6px 14px;border-radius:8px;background:var(--navy);color:#fff;border:none;font-size:12px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-check"></i> Guardar categoría
                        </button>
                    </div>
                </div>
            </div>

            <!-- Precio y Stock -->
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold" style="color:var(--navy)">Precio *</label>
                    <input type="number" name="precio" required min="0" step="0.01" placeholder="0.00"
                           class="input-field w-full px-4 py-2.5 text-sm">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold" style="color:var(--navy)">Stock inicial</label>
                    <input type="number" name="stock" min="0" value="0"
                           class="input-field w-full px-4 py-2.5 text-sm">
                </div>
            </div>

            <!-- Descripción -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Descripción</label>
                <textarea name="descripcion" rows="2" placeholder="Descripción opcional..."
                          class="input-field w-full px-4 py-2.5 text-sm resize-none"></textarea>
            </div>

            <!-- Imagen -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">
                    Imagen <span class="font-normal text-gray-400">(JPG, PNG, WEBP · máx. 2 MB)</span>
                </label>
                <div onclick="document.getElementById('cp_file').click()"
                     style="border:2px dashed #c8d8df;border-radius:10px;padding:20px;text-align:center;cursor:pointer;background:#fafcfd;transition:border-color .15s;"
                     onmouseover="this.style.borderColor='#8FB7C7'" onmouseout="this.style.borderColor='#c8d8df'">
                    <i class="fas fa-cloud-upload-alt" style="font-size:26px;color:var(--sky);display:block;margin-bottom:6px;"></i>
                    <span id="cp_label" style="font-size:12px;color:#7a8fa6;">Haz clic o arrastra una imagen aquí</span>
                    <img id="cp_preview" src="" alt=""
                         style="display:none;max-height:110px;margin:10px auto 0;border-radius:8px;object-fit:cover;">
                </div>
                <input type="file" id="cp_file" name="imagen" accept="image/*" style="display:none;"
                       onchange="previewImagen(this,'cp_preview','cp_label')">
            </div>

            <div class="flex justify-end gap-3 mt-1">
                <button type="button" onclick="closeModal('modalCrearProducto')"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-500 text-sm cursor-pointer hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-navy px-5 py-2.5 rounded-xl text-sm font-bold border-0 cursor-pointer transition-colors">
                    Guardar producto
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════════
     MODAL: EDITAR PRODUCTO
══════════════════════════════════════════════ -->
<div id="modalEditarProducto"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden"
         style="box-shadow:0 8px 32px rgba(0,0,0,.15);max-height:92vh;overflow-y:auto;">

        <div class="modal-header flex justify-between items-center px-7 py-5 sticky top-0 z-10">
            <h3 class="text-xl font-bold" style="color:var(--navy)">
                <i class="fas fa-pen mr-2"></i>Editar Producto
            </h3>
            <button onclick="closeModal('modalEditarProducto')"
                    class="bg-transparent border-0 text-2xl cursor-pointer leading-none"
                    style="color:var(--navy)">✕</button>
        </div>

        <form action="../../controllers/InventarioController.php?accion=editar_producto"
              method="POST" enctype="multipart/form-data"
              class="px-7 py-6 flex flex-col gap-4">

            <input type="hidden" name="id_producto"   id="ep_id">
            <input type="hidden" name="imagen_actual" id="ep_imagen_actual">

            <!-- Nombre -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Nombre *</label>
                <input type="text" name="nombre" id="ep_nombre" required
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <!-- Categoría + botón nueva -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Categoría *</label>
                <div class="cat-select-wrap">
                    <select name="id_categoria" id="ep_cat" required
                            class="input-field px-4 py-2.5 text-sm bg-white">
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-new-cat"
                            onclick="toggleNuevaCat('editar')" title="Crear nueva categoría">
                        <i class="fas fa-plus"></i> Nueva
                    </button>
                </div>

                <!-- Formulario inline nueva categoría -->
                <div id="nueva-cat-editar" class="new-cat-inline">
                    <p style="font-size:12px;font-weight:700;color:var(--navy);margin:0;">
                        <i class="fas fa-tag" style="color:var(--sky);"></i> Nueva categoría
                    </p>
                    <div>
                        <label>Nombre de la categoría *</label>
                        <input type="text" id="ep_nueva_cat_nombre"
                               class="input-field w-full px-3 py-2 text-sm mt-1"
                               placeholder="Ej: Ropa de mujer">
                    </div>
                    <div>
                        <label>Descripción <span style="font-weight:400;color:#aaa;">(opcional)</span></label>
                        <input type="text" id="ep_nueva_cat_desc"
                               class="input-field w-full px-3 py-2 text-sm mt-1"
                               placeholder="Descripción breve...">
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" onclick="toggleNuevaCat('editar')"
                                style="padding:6px 14px;border-radius:8px;border:1px solid #ccc;background:#fff;font-size:12px;cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="button" onclick="crearCategoriaAjax('editar')"
                                style="padding:6px 14px;border-radius:8px;background:var(--navy);color:#fff;border:none;font-size:12px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-check"></i> Guardar categoría
                        </button>
                    </div>
                </div>
            </div>

            <!-- Precio -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Precio *</label>
                <input type="number" name="precio" id="ep_precio" required min="0" step="0.01"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <!-- Descripción -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">Descripción</label>
                <textarea name="descripcion" id="ep_desc" rows="2"
                          class="input-field w-full px-4 py-2.5 text-sm resize-none"></textarea>
            </div>

            <!-- Imagen -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:var(--navy)">
                    Imagen <span class="font-normal text-gray-400">(dejar vacío para conservar la actual)</span>
                </label>
                <div onclick="document.getElementById('ep_file').click()"
                     style="border:2px dashed #c8d8df;border-radius:10px;padding:20px;text-align:center;cursor:pointer;background:#fafcfd;transition:border-color .15s;"
                     onmouseover="this.style.borderColor='#8FB7C7'" onmouseout="this.style.borderColor='#c8d8df'">
                    <i class="fas fa-cloud-upload-alt" style="font-size:26px;color:var(--sky);display:block;margin-bottom:6px;"></i>
                    <span id="ep_drop_label" style="font-size:12px;color:#7a8fa6;">Haz clic o arrastra una imagen aquí</span>
                    <img id="ep_preview" src="" alt=""
                         style="display:none;max-height:110px;margin:10px auto 0;border-radius:8px;object-fit:cover;">
                </div>
                <input type="file" id="ep_file" name="imagen" accept="image/*" style="display:none;"
                       onchange="previewImagen(this,'ep_preview','ep_drop_label')">
            </div>

            <div class="flex justify-end gap-3 mt-1">
                <button type="button" onclick="closeModal('modalEditarProducto')"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-500 text-sm cursor-pointer hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-navy px-5 py-2.5 rounded-xl text-sm font-bold border-0 cursor-pointer transition-colors">
                    Actualizar producto
                </button>
            </div>
        </form>
    </div>
</div>


<script>
/* ── Modales ────────────────────────────────────────────────── */
const openModal  = id => document.getElementById(id).classList.remove('hidden');
const closeModal = id => document.getElementById(id).classList.add('hidden');

window.addEventListener('click', e => {
    ['modalCrearProducto','modalEditarProducto'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
});

/* ── Abrir/cerrar formulario inline de nueva categoría ──────── */
function toggleNuevaCat(modal) {
    const el = document.getElementById('nueva-cat-' + modal);
    el.classList.toggle('visible');
    if (el.classList.contains('visible')) {
        el.querySelector('input').focus();
    }
}

/* ── Crear categoría vía AJAX sin salir del modal ───────────── */
function crearCategoriaAjax(modal) {
    const prefix  = modal === 'crear' ? 'cp' : 'ep';
    const nombre  = document.getElementById(prefix + '_nueva_cat_nombre').value.trim();
    const desc    = document.getElementById(prefix + '_nueva_cat_desc').value.trim();
    const select  = document.getElementById(prefix + '_cat');

    if (!nombre) {
        Swal.fire({ icon:'warning', title:'Nombre requerido',
                    text:'Escribe el nombre de la categoría.', confirmButtonColor:'#1a2d47' });
        return;
    }

    const fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('descripcion', desc);
    fd.append('ajax', '1');

    fetch('../../controllers/CategoriaController.php?accion=crear', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                Swal.fire({ icon:'error', title:'Error', text:data.error, confirmButtonColor:'#1a2d47' });
                return;
            }
            // Agregar la nueva categoría a AMBOS selects
            ['cp_cat','ep_cat'].forEach(selId => {
                const s = document.getElementById(selId);
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.nombre;
                s.appendChild(opt);
            });
            // Seleccionar en el select del modal activo
            select.value = data.id;

            // Limpiar y cerrar el formulario inline
            document.getElementById(prefix + '_nueva_cat_nombre').value = '';
            document.getElementById(prefix + '_nueva_cat_desc').value   = '';
            toggleNuevaCat(modal);

            Swal.fire({ icon:'success', title:'Categoría creada',
                        text:`«${data.nombre}» ya está disponible.`,
                        timer:1800, showConfirmButton:false });
        })
        .catch(() => Swal.fire({ icon:'error', title:'Error', text:'No se pudo crear la categoría.', confirmButtonColor:'#1a2d47' }));
}

/* ── Abrir modal editar con datos del producto ──────────────── */
function openEditProducto(p) {
    document.getElementById('ep_id').value            = p.id_producto;
    document.getElementById('ep_nombre').value        = p.nombre;
    document.getElementById('ep_precio').value        = p.precio;
    document.getElementById('ep_desc').value          = p.descripcion ?? '';
    document.getElementById('ep_cat').value           = p.id_categoria;
    document.getElementById('ep_imagen_actual').value = p.imagen ?? '';

    const preview = document.getElementById('ep_preview');
    const label   = document.getElementById('ep_drop_label');
    if (p.imagen) {
        preview.src = '../../img/productos/' + p.imagen;
        preview.style.display = 'block';
        label.textContent = 'Imagen actual (sube una nueva para reemplazarla)';
    } else {
        preview.src = '';
        preview.style.display = 'none';
        label.textContent = 'Haz clic o arrastra una imagen aquí';
    }
    document.getElementById('ep_file').value = '';
    // Cerrar inline cat si estaba abierto
    document.getElementById('nueva-cat-editar').classList.remove('visible');
    openModal('modalEditarProducto');
}

/* ── Preview imagen ─────────────────────────────────────────── */
function previewImagen(input, previewId, labelId) {
    const file    = input.files[0];
    const preview = document.getElementById(previewId);
    const label   = document.getElementById(labelId);
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
        label.textContent = file.name;
    };
    reader.readAsDataURL(file);
}

/* ── Filtrar tabla ──────────────────────────────────────────── */
function filtrarProductos() {
    const q = document.getElementById('buscarProd').value.toLowerCase();
    document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
        tr.style.display = (tr.dataset.search || '').includes(q) ? '' : 'none';
    });
}

/* ── Ver imagen ampliada ────────────────────────────────────── */
function verImagen(src, nombre) {
    Swal.fire({ title:nombre, imageUrl:src, imageAlt:nombre,
                imageWidth:320, confirmButtonColor:'#1a2d47', confirmButtonText:'Cerrar' });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
