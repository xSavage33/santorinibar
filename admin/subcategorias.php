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
        $orden = intval($_POST['orden'] ?? 0);

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
        $orden = intval($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $id <= 0 || $categoria_id <= 0) {
            $mensaje = 'Datos invalidos';
            $tipo_mensaje = 'danger';
        } else {
            $stmt = $db->prepare("UPDATE subcategorias SET categoria_id = ?, nombre = ?, descripcion = ?, orden = ?, activo = ? WHERE id = ?");
            if ($stmt->execute([$categoria_id, $nombre, $descripcion, $orden, $activo, $id])) {
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
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-layout">
        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <h1 class="page-title">Subcategorias</h1>
                    <p class="page-subtitle">Gestiona las subcategorias del menu</p>
                </div>
                <button class="btn btn-primary" onclick="openModal('crear')" <?= empty($categorias) ? 'disabled' : '' ?>>
                    <i class="fas fa-plus"></i>
                    Nueva Subcategoria
                </button>
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
                                    <th>Orden</th>
                                    <th>Nombre</th>
                                    <th>Categoria</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subcategorias as $sub): ?>
                                <tr>
                                    <td><?= $sub['orden'] ?></td>
                                    <td><strong><?= htmlspecialchars($sub['nombre']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-warning"><?= htmlspecialchars($sub['categoria_nombre']) ?></span>
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

                    <div class="form-group">
                        <label class="form-label" for="orden">Orden</label>
                        <input type="number" id="orden" name="orden" class="form-input" value="0" min="0">
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
                        <i class="fas fa-exclamation-triangle text-gold"></i>
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

    <script>
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
            document.getElementById('orden').value = '0';
            grupoActivo.style.display = 'none';
        } else {
            titulo.textContent = 'Editar Subcategoria';
            accion.value = 'editar';
            document.getElementById('formId').value = data.id;
            document.getElementById('categoria_id').value = data.categoria_id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('orden').value = data.orden;
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
