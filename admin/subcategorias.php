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
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');

        // Obtener el orden maximo actual + 1 para esta categoria
        $maxOrden = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM subcategorias WHERE categoria_id = ?");
        $maxOrden->execute([$categoria_id]);
        $orden = $maxOrden->fetchColumn();

        if (empty($nombre) || $categoria_id <= 0) {
            $mensaje = 'El nombre y la categoria son obligatorios';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("INSERT INTO subcategorias (categoria_id, nombre, descripcion, orden) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$categoria_id, $nombre, $descripcion, $orden])) {
                $mensaje = 'Subcategoria creada exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al crear la subcategoria';
                $tipo_mensaje = 'danger';
            }
        }
    }

    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $id <= 0 || $categoria_id <= 0) {
            $mensaje = 'Datos invalidos';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("UPDATE subcategorias SET categoria_id = ?, nombre = ?, descripcion = ?, activo = ? WHERE id = ?");
            if ($stmt->execute([$categoria_id, $nombre, $descripcion, $activo, $id])) {
                $mensaje = 'Subcategoria actualizada exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar la subcategoria';
                $tipo_mensaje = 'danger';
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM subcategorias WHERE id = ?");
            if ($stmt->execute([$id])) {
                $mensaje = 'Subcategoria eliminada exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar la subcategoria';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

// Obtener categorias para el select
$categorias = $db->query("SELECT * FROM categorias ORDER BY orden, nombre")->fetchAll();

// Obtener subcategorias con info de categoria
$subcategorias = $db->query("
    SELECT s.*, c.nombre as categoria_nombre,
    (SELECT COUNT(*) FROM productos WHERE subcategoria_id = s.id) as total_productos
    FROM subcategorias s
    JOIN categorias c ON s.categoria_id = c.id
    ORDER BY c.orden, c.nombre, s.orden, s.nombre
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subcategorias - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .drag-handle {
            cursor: grab;
            padding: 8px;
            color: var(--slate-400);
            transition: color 0.15s;
        }
        .drag-handle:hover {
            color: var(--slate-600);
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .sortable-ghost {
            opacity: 0.4;
            background: var(--primary-50) !important;
        }
        .sortable-chosen {
            background: var(--slate-50);
        }
        .sortable-drag {
            background: white !important;
            box-shadow: var(--shadow-lg);
        }
        .order-updated {
            animation: highlight 1s ease;
        }
        @keyframes highlight {
            0%, 100% { background: transparent; }
            50% { background: var(--success-light); }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="admin-content">
                <div class="admin-header">
                    <div class="page-title-group">
                        <h1 class="page-title">Subcategorias</h1>
                        <p class="page-subtitle">Arrastra las filas para reorganizar el orden</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" onclick="openModal('crear')" <?= empty($categorias) ? 'disabled' : '' ?>>
                            <i class="fas fa-plus"></i>
                            Nueva Subcategoria
                        </button>
                    </div>
                </div>

            <?php if (empty($categorias)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Debes crear al menos una categoria antes de agregar subcategorias.
                <a href="categorias.php" style="color: inherit; margin-left: 10px;">Ir a Categorias</a>
            </div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($subcategorias)): ?>
                    <p class="text-center text-muted">No hay subcategorias registradas</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Nombre</th>
                                    <th>Categoria</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-subcategorias">
                                <?php foreach ($subcategorias as $sub): ?>
                                <tr data-id="<?= $sub['id'] ?>">
                                    <td>
                                        <span class="drag-handle" title="Arrastra para reordenar">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($sub['nombre']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($sub['categoria_nombre']) ?></span>
                                    </td>
                                    <td><?= $sub['total_productos'] ?></td>
                                    <td>
                                        <?php if ($sub['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-secondary btn-icon"
                                                    onclick="openModal('editar', <?= htmlspecialchars(json_encode($sub)) ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-icon"
                                                    onclick="confirmarEliminar(<?= $sub['id'] ?>, '<?= htmlspecialchars(addslashes($sub['nombre'])) ?>')"
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
            </div>
        </main>
    </div>

    <!-- Modal Crear/Editar -->
    <div class="modal-overlay" id="modalSubcategoria">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitulo">Nueva Subcategoria</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <?= getCsrfInput() ?>
                <div class="modal-body">
                    <input type="hidden" name="accion" id="formAccion" value="crear">
                    <input type="hidden" name="id" id="formId" value="">

                    <div class="form-group">
                        <label class="form-label" for="categoria_id">Categoria *</label>
                        <select id="categoria_id" name="categoria_id" class="form-select" required>
                            <option value="">Selecciona una categoria</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="descripcion">Descripcion</label>
                        <textarea id="descripcion" name="descripcion" class="form-textarea" rows="3"></textarea>
                    </div>

                    <div class="form-group" id="grupoActivo" style="display: none;">
                        <label class="form-label">
                            <input type="checkbox" name="activo" id="activo" checked>
                            Subcategoria activa
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
                    <p>¿Estas seguro de eliminar la subcategoria <strong id="eliminarNombre"></strong>?</p>
                    <p class="text-muted" style="font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Esta accion eliminara tambien todos los productos asociados.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalEliminar()">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    const csrfToken = '<?= getCsrfToken() ?>';

    // Inicializar Sortable
    const sortableEl = document.getElementById('sortable-subcategorias');
    if (sortableEl) {
        new Sortable(sortableEl, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                saveOrder();
            }
        });
    }

    function saveOrder() {
        const rows = document.querySelectorAll('#sortable-subcategorias tr[data-id]');
        const items = Array.from(rows).map(row => row.dataset.id);

        fetch('ajax/update_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                tabla: 'subcategorias',
                items: items
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('#sortable-subcategorias tr').forEach(row => {
                    row.classList.add('order-updated');
                    setTimeout(() => row.classList.remove('order-updated'), 1000);
                });
            } else {
                alert('Error al guardar el orden: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el orden');
        });
    }

    function openModal(tipo, data = null) {
        const modal = document.getElementById('modalSubcategoria');
        const titulo = document.getElementById('modalTitulo');
        const accion = document.getElementById('formAccion');
        const grupoActivo = document.getElementById('grupoActivo');

        if (tipo === 'crear') {
            titulo.textContent = 'Nueva Subcategoria';
            accion.value = 'crear';
            document.getElementById('formId').value = '';
            document.getElementById('categoria_id').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('descripcion').value = '';
            grupoActivo.style.display = 'none';
        } else {
            titulo.textContent = 'Editar Subcategoria';
            accion.value = 'editar';
            document.getElementById('formId').value = data.id;
            document.getElementById('categoria_id').value = data.categoria_id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('activo').checked = data.activo == 1;
            grupoActivo.style.display = 'block';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('modalSubcategoria').classList.remove('active');
    }

    function confirmarEliminar(id, nombre) {
        document.getElementById('eliminarId').value = id;
        document.getElementById('eliminarNombre').textContent = nombre;
        document.getElementById('modalEliminar').classList.add('active');
    }

    function closeModalEliminar() {
        document.getElementById('modalEliminar').classList.remove('active');
    }

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
