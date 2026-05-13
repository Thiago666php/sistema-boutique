<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../usuarios/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Categoria.php';

$db = (new Database())->conectar();

// Crea las tablas si no existen
$db->exec("CREATE TABLE IF NOT EXISTS `categorias_productos` (
    `id_categoria` INT          PRIMARY KEY AUTO_INCREMENT,
    `nombre`       VARCHAR(100) NOT NULL,
    `descripcion`  VARCHAR(255) DEFAULT NULL,
    `cantidad`     INT          NOT NULL DEFAULT 0,
    `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Agrega columna cantidad si no existe (por si la tabla ya estaba creada)
try {
    $db->exec("ALTER TABLE `categorias_productos` ADD COLUMN `cantidad` INT NOT NULL DEFAULT 0 AFTER `descripcion`;");
} catch (Exception $e) { /* ya existe, ignorar */ }

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

$categoriaModel = new Categoria($db);
$categorias     = $categoriaModel->obtenerTodas();

$titulo = 'Gestión de Productos';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .btn-navy       { background:#1a2d47; color:#fff; }
    .btn-navy:hover { background:#14233a; }
    .thead-custom   { background:#8FB7C7; color:#1a2d47; }
    .modal-header   { background:#8FB7C7; }
    .btn-edit { background:#e8f0fe; color:#1a2d47; } .btn-edit:hover { background:#d0e0fc; }
    .btn-del  { background:#fde8e8; color:#c0392b; } .btn-del:hover  { background:#fac8c8; }
    .input-field       { border:1.5px solid #c8d8df; border-radius:10px; }
    .input-field:focus { border-color:#8FB7C7; outline:none; box-shadow:0 0 0 3px rgba(143,183,199,.2); }
    .tab-btn        { border-bottom:3px solid transparent; color:#64748b; }
    .tab-btn.active { border-bottom-color:#1a2d47; color:#1a2d47; font-weight:700; }
    .tab-panel      { display:none; }
    .tab-panel.active { display:block; }
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

    <div class="flex items-center justify-between">
        <h2 class="text-4xl font-bold" style="color:#1a2d47;">Gestión de Productos</h2>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 flex gap-6">
        <button class="tab-btn active pb-3 px-1 text-sm transition-colors" onclick="switchTab('categorias', this)">
            <i class="fas fa-tags mr-2"></i>Categorías
        </button>
        <button class="tab-btn pb-3 px-1 text-sm transition-colors" onclick="switchTab('productos', this)">
            <i class="fas fa-box mr-2"></i>Productos
        </button>
    </div>

    <!-- TAB CATEGORÍAS -->
    <div id="tab-categorias" class="tab-panel active space-y-4">

        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Administra las categorías para clasificar tus productos.</p>
            <button onclick="openModal('modalCrearCategoria')"
                    class="btn-navy px-5 py-2.5 rounded-xl font-semibold text-sm border-0 cursor-pointer transition-colors">
                <i class="fas fa-plus mr-1"></i> Nueva Categoría
            </button>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.07)">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="thead-custom">
                        <th class="px-5 py-3.5 text-left font-bold">#</th>
                        <th class="px-5 py-3.5 text-left font-bold">Nombre</th>
                        <th class="px-5 py-3.5 text-left font-bold">Descripción</th>
                        <th class="px-5 py-3.5 text-left font-bold">Cantidad</th>
                        <th class="px-5 py-3.5 text-center font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-400">
                                <i class="fas fa-tags text-3xl mb-2 block opacity-30"></i>
                                No hay categorías registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($categorias as $i => $cat): ?>
                    <tr class="border-b border-gray-100 hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 text-gray-400"><?= $i + 1 ?></td>
                        <td class="px-5 py-3 font-semibold" style="color:#1a2d47">
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </td>
                        <td class="px-5 py-3 text-gray-500">
                            <?= $cat['descripcion'] ? htmlspecialchars($cat['descripcion']) : '<span class="text-gray-300 italic">Sin descripción</span>' ?>
                        </td>
                        <td class="px-5 py-3">
                            <?php $cant = (int)($cat['cantidad'] ?? 0);
                            $color = $cant === 0 ? 'bg-red-50 text-red-700 border border-red-200'
                                   : ($cant <= 5  ? 'bg-orange-50 text-orange-700 border border-orange-200'
                                                  : 'bg-green-50 text-green-700 border border-green-200'); ?>
                            <span class="<?= $color ?> text-xs font-bold px-3 py-1 rounded-full">
                                <?= $cant ?> uds.
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center space-x-1">
                            <button onclick='openEditCategoria(<?= json_encode($cat) ?>)'
                                    title="Editar"
                                    class="btn-edit px-3 py-1.5 rounded-lg border-0 cursor-pointer text-sm transition-colors">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button onclick="confirmarEliminarCategoria(<?= $cat['id_categoria'] ?>, '<?= htmlspecialchars($cat['nombre'], ENT_QUOTES) ?>')"
                                    title="Eliminar"
                                    class="btn-del px-3 py-1.5 rounded-lg border-0 cursor-pointer text-sm transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB PRODUCTOS -->
    <div id="tab-productos" class="tab-panel space-y-4">
        <div class="flex items-center justify-center py-20 text-gray-400 flex-col gap-3">
            <i class="fas fa-box text-5xl opacity-20"></i>
            <p class="text-sm">Módulo de productos disponible próximamente.</p>
        </div>
    </div>

</div>


<!-- MODAL: Crear Categoría -->
<div id="modalCrearCategoria"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden"
         style="box-shadow:0 8px 32px rgba(0,0,0,.15)">

        <div class="modal-header flex justify-between items-center px-7 py-5">
            <h3 class="text-xl font-bold" style="color:#1a2d47">
                <i class="fas fa-tags mr-2"></i>Nueva Categoría
            </h3>
            <button onclick="closeModal('modalCrearCategoria')"
                    class="bg-transparent border-0 text-2xl cursor-pointer leading-none"
                    style="color:#1a2d47">✕</button>
        </div>

        <form action="../../controllers/CategoriaController.php?accion=crear"
              method="POST" class="px-7 py-6 flex flex-col gap-4">

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Nombre *</label>
                <input type="text" name="nombre" required placeholder="Ej: Ropa de mujer"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">
                    Descripción <span class="font-normal text-gray-400">(opcional)</span>
                </label>
                <textarea name="descripcion" rows="2" placeholder="Describe brevemente esta categoría..."
                          class="input-field w-full px-4 py-2.5 text-sm resize-none"></textarea>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Cantidad de productos *</label>
                <input type="number" name="cantidad" required min="0" step="1" value="0"
                       placeholder="0"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <div class="flex justify-end gap-3 mt-1">
                <button type="button" onclick="closeModal('modalCrearCategoria')"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-500 text-sm cursor-pointer hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-navy px-5 py-2.5 rounded-xl text-sm font-bold border-0 cursor-pointer transition-colors">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL: Editar Categoría -->
<div id="modalEditarCategoria"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden"
         style="box-shadow:0 8px 32px rgba(0,0,0,.15)">

        <div class="modal-header flex justify-between items-center px-7 py-5">
            <h3 class="text-xl font-bold" style="color:#1a2d47">
                <i class="fas fa-pen mr-2"></i>Editar Categoría
            </h3>
            <button onclick="closeModal('modalEditarCategoria')"
                    class="bg-transparent border-0 text-2xl cursor-pointer leading-none"
                    style="color:#1a2d47">✕</button>
        </div>

        <form action="../../controllers/CategoriaController.php?accion=editar"
              method="POST" class="px-7 py-6 flex flex-col gap-4">

            <input type="hidden" name="id_categoria" id="edit_cat_id">

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Nombre *</label>
                <input type="text" name="nombre" id="edit_cat_nombre" required
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">
                    Descripción <span class="font-normal text-gray-400">(opcional)</span>
                </label>
                <textarea name="descripcion" id="edit_cat_descripcion" rows="2"
                          class="input-field w-full px-4 py-2.5 text-sm resize-none"></textarea>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Cantidad de productos *</label>
                <input type="number" name="cantidad" id="edit_cat_cantidad" required min="0" step="1"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <div class="flex justify-end gap-3 mt-1">
                <button type="button" onclick="closeModal('modalEditarCategoria')"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-500 text-sm cursor-pointer hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-navy px-5 py-2.5 rounded-xl text-sm font-bold border-0 cursor-pointer transition-colors">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>


<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

const openModal  = id => document.getElementById(id).classList.remove('hidden');
const closeModal = id => document.getElementById(id).classList.add('hidden');

window.addEventListener('click', e => {
    ['modalCrearCategoria', 'modalEditarCategoria'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
});

function openEditCategoria(cat) {
    document.getElementById('edit_cat_id').value          = cat.id_categoria;
    document.getElementById('edit_cat_nombre').value      = cat.nombre;
    document.getElementById('edit_cat_descripcion').value = cat.descripcion ?? '';
    document.getElementById('edit_cat_cantidad').value    = cat.cantidad ?? 0;
    openModal('modalEditarCategoria');
}

function confirmarEliminarCategoria(id, nombre) {
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar categoría?',
        text: `Se eliminará «${nombre}». Esta acción no se puede deshacer.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#1a2d47'
    }).then(r => {
        if (r.isConfirmed)
            window.location.href = '../../controllers/CategoriaController.php?accion=eliminar&id=' + id;
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
