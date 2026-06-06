<?php
require_once '../includes/config.php';
requireAuth();
validateCsrfToken();

$db = getConnection();
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $tipo_menu = validateTipoMenu($_POST['tipo_menu'] ?? 'licores');
        $orden = intval($_POST['orden'] ?? 0);

        if (empty($nombre)) {
            $mensaje = 'El nombre es obligatorio';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, tipo_menu, orden) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $descripcion, $tipo_menu, $orden])) {
                $mensaje = 'Categoria creada exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al crear la categoria';
                $tipo_mensaje = 'danger';
            }
        }
    }

    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $tipo_menu = validateTipoMenu($_POST['tipo_menu'] ?? 'licores');
        $orden = intval($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $id <= 0) {
            $mensaje = 'Datos invalidos';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("UPDATE categorias SET nombre = ?, descripcion = ?, tipo_menu = ?, orden = ?, activo = ? WHERE id = ?");
            if ($stmt->execute([$nombre, $descripcion, $tipo_menu, $orden, $activo, $id])) {
                $mensaje = 'Categoria actualizada exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar la categoria';
                $tipo_mensaje = 'danger';
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
            if ($stmt->execute([$id])) {
                $mensaje = 'Categoria eliminada exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar la categoria';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

// Obtener categorias
$categorias = $db->query("SELECT c.*,
    (SELECT COUNT(*) FROM subcategorias WHERE categoria_id = c.id) as total_subcategorias,
    (SELECT COUNT(*) FROM productos p JOIN subcategorias s ON p.subcategoria_id = s.id WHERE s.categoria_id = c.id) as total_productos
    FROM categorias c ORDER BY c.orden, c.nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-layout">
        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <h1 class="page-title">Categorias</h1>
                    <p class="page-subtitle">Gestiona las categorias del menu</p>
                </div>
                <button class="btn btn-primary" onclick="openModal('crear')">
                    <i class="fas fa-plus"></i>
                    Nueva Categoria
                </button>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($categorias)): ?>
                    <p class="text-center text-muted">No hay categorias registradas</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Nombre</th>
                                    <th>Menu</th>
                                    <th>Descripcion</th>
                                    <th>Subcategorias</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td><?= $cat['orden'] ?></td>
                                    <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                                    <td>
                                        <?php if (($cat['tipo_menu'] ?? 'licores') === 'licores'): ?>
                                        <span class="badge badge-gold"><i class="fas fa-wine-glass-alt"></i> Licores</span>
                                        <?php else: ?>
                                        <span class="badge badge-green"><i class="fas fa-utensils"></i> Comidas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($cat['descripcion'] ?: '-') ?></td>
                                    <td><?= $cat['total_subcategorias'] ?></td>
                                    <td><?= $cat['total_productos'] ?></td>
                                    <td>
                                        <?php if ($cat['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-secondary btn-icon"
                                                    onclick="openModal('editar', <?= htmlspecialchars(json_encode($cat)) ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-icon"
                                                    onclick="confirmarEliminar(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['nombre'])) ?>')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Crear/Editar -->
    <div class="modal-overlay" id="modalCategoria">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitulo">Nueva Categoria</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <?= getCsrfInput() ?>
                <div class="modal-body">
                    <input type="hidden" name="accion" id="formAccion" value="crear">
                    <input type="hidden" name="id" id="formId" value="">

                    <div class="form-group">
                        <label class="form-label" for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="descripcion">Descripcion</label>
                        <textarea id="descripcion" name="descripcion" class="form-textarea" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo_menu">Mostrar en</label>
                        <select id="tipo_menu" name="tipo_menu" class="form-input">
                            <option value="licores">Menu de Licores</option>
                            <option value="comidas">Menu de Comidas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="orden">Orden</label>
                        <input type="number" id="orden" name="orden" class="form-input" value="0" min="0">
                    </div>

                    <div class="form-group" id="grupoActivo" style="display: none;">
                        <label class="form-label">
                            <input type="checkbox" name="activo" id="activo" checked>
                            Categoria activa
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Confirmar Eliminacion</h3>
                <button class="modal-close" onclick="closeModalEliminar()">&times;</button>
            </div>
            <form method="POST" action="">
                <?= getCsrfInput() ?>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" id="eliminarId" value="">
                    <p>¿Estas seguro de eliminar la categoria <strong id="eliminarNombre"></strong>?</p>
                    <p class="text-muted" style="font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle text-gold"></i>
                        Esta accion eliminara tambien todas las subcategorias y productos asociados.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalEliminar()">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(tipo, data = null) {
        const modal = document.getElementById('modalCategoria');
        const titulo = document.getElementById('modalTitulo');
        const accion = document.getElementById('formAccion');
        const grupoActivo = document.getElementById('grupoActivo');

        if (tipo === 'crear') {
            titulo.textContent = 'Nueva Categoria';
            accion.value = 'crear';
            document.getElementById('formId').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('tipo_menu').value = 'licores';
            document.getElementById('orden').value = '0';
            grupoActivo.style.display = 'none';
        } else {
            titulo.textContent = 'Editar Categoria';
            accion.value = 'editar';
            document.getElementById('formId').value = data.id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('tipo_menu').value = data.tipo_menu || 'licores';
            document.getElementById('orden').value = data.orden;
            document.getElementById('activo').checked = data.activo == 1;
            grupoActivo.style.display = 'block';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('modalCategoria').classList.remove('active');
    }

    function confirmarEliminar(id, nombre) {
        document.getElementById('eliminarId').value = id;
        document.getElementById('eliminarNombre').textContent = nombre;
        document.getElementById('modalEliminar').classList.add('active');
    }

    function closeModalEliminar() {
        document.getElementById('modalEliminar').classList.remove('active');
    }

    // Cerrar modal al hacer clic fuera
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
    </script>
</body>
</html>
