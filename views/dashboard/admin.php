<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

// Conecta a la base de datos y obtener todos los usuarios registrados
$db           = (new Database())->conectar();
$usuarioModel = new Usuario($db);
$usuarios     = $usuarioModel->obtenerTodos();

$roles     = [1 => 'Administrador', 2 => 'Cajero', 3 => 'Bodeguero'];

$rolClass  = [1 => 'badge-administrador', 2 => 'badge-cajero', 3 => 'badge-bodeguero'];

$rolesForm = ['administrador', 'cajero', 'bodeguero'];

$titulo = "Dashboard del Administrador";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    
    /* Botón principal  */
    .btn-navy        { background:#1a2d47; color:#fff; }
    .btn-navy:hover  { background:#14233a; }
    /* Encabezado de la tabla */
    .thead-custom    { background:#8FB7C7; color:#1a2d47; }
    /* Encabezado de los modales */
    .modal-header    { background:#8FB7C7; }
    /* roles con colores distintos por cada tipo */
    .badge-administrador { background:#bfdbfe; color:#1e3a5f; } /* Azul  */
    .badge-cajero        { background:#fed7aa; color:#92400e; } /* Naranja */
    .badge-bodeguero     { background:#bbf7d0; color:#14532d; } /* Verde */
    /* estado activo/inactivo */
    .badge-activo   { background:#e6f9f0; color:#1a7a4a; border-color:#a3d9b8; }
    .badge-inactivo { background:#fde8e8; color:#c0392b; border-color:#f5a8a8; }
    /* Botones de acción en la tabla */
    .btn-edit  { background:#e8f0fe; color:#1a2d47; } .btn-edit:hover  { background:#d0e0fc; } /* Editar  - Azul claro */
    .btn-deact { background:#fff3e0; color:#e67e22; } .btn-deact:hover { background:#fde8c0; } /* Desactivar - Naranja */
    .btn-act   { background:#e6f9f0; color:#1a7a4a; } .btn-act:hover   { background:#c8f0dc; } /* Activar - Verde */
    .btn-del   { background:#fde8e8; color:#c0392b; } .btn-del:hover   { background:#fac8c8; } /* Eliminar - Rojo */
    /* Fila de usuario inactivo (fondo rosado y opacidad reducida) */
    .row-inactive { background:#fff5f5; opacity:.8; }
    /* Campos de formulario con borde azul suave */
    .input-field  { border:1.5px solid #c8d8df; border-radius:10px; }
    .input-field:focus { border-color:#8FB7C7; outline:none; box-shadow:0 0 0 3px rgba(143,183,199,.2); }
</style>

<?php
if (isset($_SESSION['alert'])): ?>
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

    <!--  Encabezado: título + botón para abrir modal de creación -->
    <div class="flex items-center justify-between">
        <h2 class="text-5xl font-bold" style="color:#1a2d47;">Gestión de Usuarios</h2>
        <button onclick="openModal('modalCrear')" class="btn-navy px-6 py-2.5 rounded-xl font-semibold text-sm border-0 cursor-pointer transition-colors">
            + Agregar Usuario
        </button>
    </div>

    <!--TABLA DE USUARIOS -->
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 12px rgba(0,0,0,.07)">
        <table class="w-full border-collapse text-sm">

            <!-- Encabezados de columna -->
            <thead>
                <tr class="thead-custom">
                    <?php foreach (['#','Nombre','Correo','Rol','Estado','Acciones'] as $th): ?>
                    <th class="px-5 py-3.5 text-left font-bold <?= $th==='Acciones'?'text-center':'' ?>"><?= $th ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <!-- Mensaje si no hay usuarios en la base de datos -->
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="py-6 text-center text-gray-400">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($usuarios as $i => $u): ?>
                <tr class="border-b border-gray-100 <?= $u['activo']==0 ? 'row-inactive' : '' ?>">

                    <!-- Número de fila -->
                    <td class="px-5 py-3 text-gray-400"><?= $i+1 ?></td>

                    <!-- Nombre completo del usuario -->
                    <td class="px-5 py-3 font-semibold" style="color:#1a2d47"><?= htmlspecialchars($u['nombre']) ?></td>

                    <!-- Correo electrónico -->
                    <td class="px-5 py-3 text-gray-500"><?= htmlspecialchars($u['correo']) ?></td>

                    <!-- rol con color según el tipo -->
                    <td class="px-5 py-3">
                        <span class="<?= $rolClass[$u['id_rol']] ?? 'badge-administrador' ?> px-3 py-1 rounded-full text-xs font-semibold">
                            <?= $roles[$u['id_rol']] ?? 'Sin rol' ?>
                        </span>
                    </td>

                    <!-- estado: Activo (verde) o Inactivo (rojo) -->
                    <td class="px-5 py-3">
                        <?php if ($u['activo']==1): ?>
                            <span class="badge-activo text-xs font-semibold px-3 py-1 rounded-full border">Activo</span>
                        <?php else: ?>
                            <span class="badge-inactivo text-xs font-semibold px-3 py-1 rounded-full border">Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <!-- Botones de acción por usuario -->
                    <td class="px-5 py-3 text-center space-x-1">

                        <!-- Editar: abre el modal con los datos del usuario -->
                        <button onclick='openEditModal(<?= json_encode($u) ?>)' title="Editar" class="btn-edit px-3 py-1.5 rounded-lg border-0 cursor-pointer text-sm transition-colors"><i class="fas fa-pen"></i></button>

                        <?php
                        // URL base para activar/desactivar, reutilizada en ambos botones
                        $base = "../../controllers/AdminUsuarioController.php?accion=toggleEstado&id={$u['id_usuario']}";
                        ?>

                        <?php if ($u['activo']==1): ?>
                            <!-- Desactivar usuario (solo visible si está activo) -->
                            <a href="<?= $base ?>&estado=1" onclick="return confirm('¿Desactivar este usuario?')" title="Desactivar" class="btn-deact px-3 py-1.5 rounded-lg text-sm inline-block transition-colors"><i class="fas fa-ban"></i></a>
                        <?php else: ?>
                            <!--  Activar usuario (solo visible si está inactivo) -->
                            <a href="<?= $base ?>&estado=0" onclick="return confirm('¿Activar este usuario?')" title="Activar" class="btn-act px-3 py-1.5 rounded-lg text-sm inline-block transition-colors"><i class="fas fa-check"></i></a>
                        <?php endif; ?>

                        <!--  Eliminar: muestra confirmación con SweetAlert antes de proceder -->
                        <button onclick="confirmarEliminar(<?= $u['id_usuario'] ?>, '<?= htmlspecialchars($u['nombre']) ?>')" title="Eliminar" class="btn-del px-3 py-1.5 rounded-lg border-0 cursor-pointer text-sm transition-colors"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<?php
function modalUsuario($id, $titulo, $accion, $rolesForm) {
    $esEditar = $id === 'modalEditar';
?>
<div id="<?= $id ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden" style="box-shadow:0 8px 32px rgba(0,0,0,.15)">

        <!-- Encabezado del modal -->
        <div class="modal-header flex justify-between items-center px-7 py-5">
            <h3 class="text-xl font-bold" style="color:#1a2d47"><?= $titulo ?></h3>
            <!-- Botón X para cerrar el modal -->
            <button onclick="closeModal('<?= $id ?>')" class="bg-transparent border-0 text-2xl cursor-pointer leading-none" style="color:#1a2d47">✕</button>
        </div>

        <!-- Formulario: envía los datos al controlador -->
        <form action="../../controllers/AdminUsuarioController.php?accion=<?= $accion ?>" method="POST" class="px-7 py-6 flex flex-col gap-4">
            <?php if ($esEditar): ?><input type="hidden" name="id_usuario" id="edit_id_usuario"><?php endif; ?>

            <!-- Campos: Nombres y Apellidos en dos columnas -->
            <div class="grid grid-cols-2 gap-4">
                <?php foreach (['nombres'=>'Nombres','apellidos'=>'Apellidos'] as $name=>$label): ?>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold" style="color:#1a2d47"><?= $label ?> *</label>
                    <input type="text" name="<?= $name ?>" <?= $esEditar ? "id='edit_$name'" : '' ?> required class="input-field w-full px-4 py-2.5 text-sm">
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Campo correo: editable al crear, solo lectura al editar -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Correo Electrónico <?= !$esEditar ? '*' : '' ?></label>
                <?php if ($esEditar): ?>
                    <input type="email" id="edit_correo" readonly class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed outline-none">
                <?php else: ?>
                    <input type="email" name="email" required placeholder="correo@ejemplo.com" class="input-field w-full px-4 py-2.5 text-sm">
                <?php endif; ?>
            </div>

            <!-- Campo contraseña: obligatoria al crear, opcional al editar -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">
                    <?= $esEditar ? 'Nueva Contraseña <span class="font-normal text-gray-400">(opcional)</span>' : 'Contraseña *' ?>
                </label>
                <input type="password" name="password" <?= !$esEditar ? 'required' : '' ?> placeholder="••••••••" class="input-field w-full px-4 py-2.5 text-sm">
            </div>

            <!-- Seleccion de rol -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold" style="color:#1a2d47">Rol *</label>
                <select name="rol" <?= $esEditar ? "id='edit_rol'" : '' ?> required class="input-field w-full px-4 py-2.5 text-sm bg-white">
                    <?php if (!$esEditar): ?><option value="">Seleccione un rol...</option><?php endif; ?>
                    <?php foreach ($rolesForm as $r): ?>
                    <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-2">
                <button type="button" onclick="closeModal('<?= $id ?>')" class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-500 text-sm cursor-pointer hover:bg-gray-50 transition-colors">Cancelar</button>
                <button type="submit" class="btn-navy px-5 py-2.5 rounded-xl text-sm font-bold border-0 cursor-pointer transition-colors"><?= $esEditar ? 'Actualizar' : 'Guardar' ?></button>
            </div>
        </form>
    </div>
</div>
<?php } ?>
<?php modalUsuario('modalCrear', 'Agregar Usuario', 'crear', $rolesForm); ?>
<?php modalUsuario('modalEditar', 'Editar Usuario',  'editar', $rolesForm); ?>

<script>
const openModal  = id => document.getElementById(id).classList.remove('hidden');
const closeModal = id => document.getElementById(id).classList.add('hidden');
window.onclick = e => ['modalCrear','modalEditar'].forEach(id => {
    if (e.target === document.getElementById(id)) closeModal(id);
});
const rolesMap = {1:'administrador', 2:'cajero', 3:'bodeguero'};

function openEditModal(u) {
    const [nombres, ...rest] = u.nombre.trim().split(' ');
    document.getElementById('edit_id_usuario').value = u.id_usuario;
    document.getElementById('edit_nombres').value    = nombres;
    document.getElementById('edit_apellidos').value  = rest.join(' ');
    document.getElementById('edit_correo').value     = u.correo;  
    document.getElementById('edit_rol').value        = rolesMap[u.id_rol] ?? 'cajero';

    openModal('modalEditar');
}
// Muestra una confirmación antes de eliminar un usuario
function confirmarEliminar(id, nombre) {
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar usuario?',
        text: `Se eliminará a "${nombre}" de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#c0392b', 
        cancelButtonColor: '#1a2d47'   
    }).then(r => r.isConfirmed && (window.location.href = '../../controllers/AdminUsuarioController.php?accion=eliminar&id=' + id));
}
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>