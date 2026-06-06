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

        // Obtener el orden maximo actual + 1
        $maxOrden = $db->query("SELECT COALESCE(MAX(orden), 0) + 1 FROM categorias")->fetchColumn();

        if (empty($nombre)) {
            $mensaje = 'El nombre es obligatorio';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, tipo_menu, orden) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $descripcion, $tipo_menu, $maxOrden])) {
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
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $id <= 0) {
            $mensaje = 'Datos invalidos';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("UPDATE categorias SET nombre = ?, descripcion = ?, tipo_menu = ?, activo = ? WHERE id = ?");
            if ($stmt->execute([$nombre, $descripcion, $tipo_menu, $activo, $id])) {
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
                        <h1 class="page-title">Categorias</h1>
                        <p class="page-subtitle">Arrastra las filas para reorganizar el orden</p>
                    </div>
                    <div class="page-actions">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-input-icon"></i>
                            <input type="text" id="searchInput" class="search-input" placeholder="Buscar categoria..." onkeyup="filterTable()">
                        </div>
                        <button class="btn btn-primary" onclick="openModal('crear')">
                            <i class="fas fa-plus"></i>
                            Nueva Categoria
                        </button>
                    </div>
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
                                    <th style="width: 50px;"></th>
                                    <th>Nombre</th>
                                    <th>Menu</th>
                                    <th>Descripcion</th>
                                    <th>Subcategorias</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-categorias">
                                <?php foreach ($categorias as $cat): ?>
                                <tr data-id="<?= $cat['id'] ?>">
                                    <td>
                                        <span class="drag-handle" title="Arrastra para reordenar">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                                    <td>
                                        <?php if (($cat['tipo_menu'] ?? 'licores') === 'licores'): ?>
                                        <span class="badge badge-warning"><i class="fas fa-wine-glass-alt"></i> Licores</span>
                                        <?php else: ?>
                                        <span class="badge badge-success"><i class="fas fa-utensils"></i> Comidas</span>
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
                        <select id="tipo_menu" name="tipo_menu" class="form-select">
                            <option value="licores">Menu de Licores</option>
                            <option value="comidas">Menu de Comidas</option>
                        </select>
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
                        <i class="fas fa-exclamation-triangle text-warning"></i>
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

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    const csrfToken = '<?= getCsrfToken() ?>';

    // Inicializar Sortable
    const sortableEl = document.getElementById('sortable-categorias');
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
        const rows = document.querySelectorAll('#sortable-categorias tr[data-id]');
        const items = Array.from(rows).map(row => row.dataset.id);

        fetch('ajax/update_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                tabla: 'categorias',
                items: items,
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Feedback visual
                document.querySelectorAll('#sortable-categorias tr').forEach(row => {
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
            grupoActivo.style.display = 'none';
        } else {
            titulo.textContent = 'Editar Categoria';
            accion.value = 'editar';
            document.getElementById('formId').value = data.id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('tipo_menu').value = data.tipo_menu || 'licores';
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

    // Filtrar tabla
    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#sortable-categorias tr[data-id]');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchValue)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        let noResults = document.getElementById('noResults');
        if (visibleCount === 0 && searchValue !== '') {
            if (!noResults) {
                noResults = document.createElement('tr');
                noResults.id = 'noResults';
                noResults.innerHTML = '<td colspan="8" class="text-center text-muted" style="padding: 30px;">No se encontraron categorias</td>';
                document.getElementById('sortable-categorias').appendChild(noResults);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }
    </script>
</body>
</html>
