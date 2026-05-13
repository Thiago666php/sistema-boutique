<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../usuarios/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Proveedor.php';

$db = (new Database())->conectar();

// Crea la tabla si no existe
$db->exec("
    CREATE TABLE IF NOT EXISTS `proveedores` (
        `id_proveedor` INT          PRIMARY KEY AUTO_INCREMENT,
        `nombre`       VARCHAR(150) NOT NULL,
        `ruc`          VARCHAR(20)  DEFAULT NULL,
        `telefono`     VARCHAR(30)  DEFAULT NULL,
        `correo`       VARCHAR(150) DEFAULT NULL,
        `direccion`    VARCHAR(255) DEFAULT NULL,
        `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
        `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$proveedorModel = new Proveedor($db);
$proveedores    = $proveedorModel->obtenerTodos();

$titulo = 'Proveedores';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .btn-navy        { background:#1a2d47; color:#fff; }
    .btn-navy:hover  { background:#14233a; }
    .thead-custom    { background:#8FB7C7; color:#1a2d47; }
    .modal-header    { background:#8FB7C7; }

    .badge-activo    { background:#e6f9f0; color:#1a7a4a; border-color:#a3d9b8; }
    .badge-inactivo  { background:#fde8e8; color:#c0392b; border-color:#f5a8a8; }

    .btn-edit  { background:#e8f0fe; color:#1a2d47; } .btn-edit:hover  { background:#d0e0fc; }
    .btn-deact { background:#fff3e0; color:#e67e22; } .btn-deact:hover { background:#fde8c0; }
    .btn-act   { background:#e6f9f0; color:#1a7a4a; } .btn-act:hover   { background:#c8f0dc; }
    .btn-del   { background:#fde8e8; color:#c0392b; } .btn-del:hover   { background:#fac8c8; }

    .row-inactive { background:#fff5f5; opacity:.85; }

    .input-field       { border:1.5px solid #c8d8df; border-radius:10px; }
    .input-field:focus { border-color:#8FB7C7; outline:none; box-shadow:0 0 0 3px rgba(143,183,199,.2); }
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
    <div class="flex items-center justify-between">
        <h2 class="text-4xl font-bold" style="color:#1a2d47;">Proveedores</h2>
        <button onclick="openModal('modalCrear')"
                class="btn-navy px-5 py-2.5 rounded-xl font-semibold text-sm border-0 cursor-pointer transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo Proveedor
        </button>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.07)">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="thead-custom">
                    <?php foreach (['#','Nombre','RUC','Teléfono','Correo','Estado','Acciones'] as $th): ?>
                    <th class="px-5 py-3.5 text-left font-bold <?= $th === 'Acciones' ? 'text-center' : '' ?>">
                        <?= $th ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proveedores)): ?>
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400">
                            <i class="fas fa-truck text-3xl mb-2 block opacity-30"></i>
                            No hay proveedores registrados.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($proveedores as $i => $p): ?>
                <tr class="border-b border-gray-100 hover:bg-slate-50 transition-colors <?= $p['activo'] == 0 ? 'row-inactive' : '' ?>">
                    <td class="px-5 py-3 text-gray-400"><?= $i + 1 ?></td>
                    <td class="px-5 py-3 font-semibold" style="color:#1a2d47">
                        <?= htmlspecialchars($p['nombre']) ?>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        <?= $p['ruc'] ? htmlspecialchars($p['ruc']) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        <?= $p['telefono'] ? htmlspecialchars($p['telefono']) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        <?= $p['correo'] ? htmlspecialchars($p['correo']) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($p['activo'] == 1): ?>
                            <span class="badge-activo text-xs font-semibold px-3 py-1 rounded-full border">Activo</span>
                        <?php else: ?>
                            <span class="badge-inactivo text-xs font-semibold px-3 py-1 rounded-full border">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-center space-x-1">
                        <!-- Editar -->
                        <button onclick='openEditModal(<?= json_encode($p) ?>)'
                                title="Editar"
                                class="btn-edit px-3 py-1.5 rounded-lg border-0 cursor-pointer text-sm transition-colors">
                            <i class="fas fa-pen"></i>
                        </button>

                        <?php
                        $base = "../../controllers/ProveedorController.php?accion=toggleEstado&id={$p['id_proveedor']}&estado={$p['activo']}";
                        ?>
                        <?php if ($p['activo'] == 1): ?>
                            <a href="<?= $base ?>"
                               onclick="return confirm('¿Desactivar este proveedor?')"
                               title="Desactivar"
                               class="btn-deact px-3 py-1.5 rounded-lg text-sm inline-block transition-colors">
                                <i class="fas fa-ban"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?= $base ?>"
                               onclick="return confirm('¿Activar este proveedor?')"
                               title="Activar"
                               class="btn-act px-3 py-1.5 rounded-lg text-sm inline-block transition-colors">
                                <i class="fas fa-check"></i>
                            </a>
                        <?php endif; ?>

                        <!-- Eliminar -->
                        <button onclick="confirmarEliminar(<?= $p['id_proveedor'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>')"
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


<!-- ══════════════════════════════════════════════════════════
     MODAL: Crear Proveedor
══════════════════════════════════════════════════════════ -->
<?php
function modalProveedor(string $id, string $titulo, string $accion, bool $esEditar): void { ?>
<div id="<?= $id ?>"
     class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden"
         style="box-shadow:0 8px 32px rgba(0,0,0,.15)">

        <div class="modal-header flex justify-between items-center px-7 py-5">
            <h3 class="text-xl font-bold" style="color:#1a2d47">
                <i class="fas fa-truck mr-2"></i><?= $titulo ?>
            </h3>
            <button onclick="closeModal('<?= $id ?>')"
                    class="bg-transparent border-0 text-2xl cursor-pointer leading-none"
                    style="color:#1a2d47">✕</button>
        </div>

        <form action="../../controllers/ProveedorController.php?accion=<?= $accion ?>"
              method="POST" class="px-7 py-6 flex flex-col gap-4">

            <?php if ($esEditar): ?>
                <input type="hidden" name="id_proveedor" id="edit_id">
            <?php endif; ?>

            <!-- Nombre -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Nombre *</label>
                <input type="text" name="nombre"
                       <?= $esEditar ? "id='edit_nombre'" : '' ?>
                       required placeholder="Nombre del proveedor"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <!-- RUC + Teléfono -->
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold" style="color:#1a2d47">
                        RUC <span class="font-normal text-gray-400">(opcional)</span>
                    </label>
                    <input type="text" name="ruc"
                           <?= $esEditar ? "id='edit_ruc'" : '' ?>
                           placeholder="Ej: 20123456789"
                           class="input-field w-full px-4 py-2.5 text-sm">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold" style="color:#1a2d47">
                        Teléfono <span class="font-normal text-gray-400">(opcional)</span>
                    </label>
                    <input type="text" name="telefono"
                           <?= $esEditar ? "id='edit_telefono'" : '' ?>
                           placeholder="Ej: +51 999 888 777"
                           class="input-field w-full px-4 py-2.5 text-sm">
                </div>
            </div>

            <!-- Correo -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">
                    Correo <span class="font-normal text-gray-400">(opcional)</span>
                </label>
                <input type="email" name="correo"
                       <?= $esEditar ? "id='edit_correo'" : '' ?>
                       placeholder="proveedor@ejemplo.com"
                       class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <!-- Dirección -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">
                    Dirección <span class="font-normal text-gray-400">(opcional)</span>
                </label>
                <textarea name="direccion" rows="2"
                          <?= $esEditar ? "id='edit_direccion'" : '' ?>
                          placeholder="Dirección del proveedor..."
                          class="input-field w-full px-4 py-2.5 text-sm resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 mt-1">
                <button type="button" onclick="closeModal('<?= $id ?>')"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-500 text-sm cursor-pointer hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-navy px-5 py-2.5 rounded-xl text-sm font-bold border-0 cursor-pointer transition-colors">
                    <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php modalProveedor('modalCrear',  'Nuevo Proveedor',  'crear',  false); ?>
<?php modalProveedor('modalEditar', 'Editar Proveedor', 'editar', true);  ?>


<script>
const openModal  = id => document.getElementById(id).classList.remove('hidden');
const closeModal = id => document.getElementById(id).classList.add('hidden');

window.addEventListener('click', e => {
    ['modalCrear', 'modalEditar'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
});

function openEditModal(p) {
    document.getElementById('edit_id').value        = p.id_proveedor;
    document.getElementById('edit_nombre').value    = p.nombre    ?? '';
    document.getElementById('edit_ruc').value       = p.ruc       ?? '';
    document.getElementById('edit_telefono').value  = p.telefono  ?? '';
    document.getElementById('edit_correo').value    = p.correo    ?? '';
    document.getElementById('edit_direccion').value = p.direccion ?? '';
    openModal('modalEditar');
}

function confirmarEliminar(id, nombre) {
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar proveedor?',
        text: `Se eliminará a "${nombre}" de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#1a2d47'
    }).then(r => {
        if (r.isConfirmed)
            window.location.href =
                '../../controllers/ProveedorController.php?accion=eliminar&id=' + id;
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
