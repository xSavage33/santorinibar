<?php
require_once '../includes/config.php';
requireAuth();
validateCsrfToken();

$db = getConnection();
$mensaje = '';
$tipo_mensaje = '';

// Crear directorio de uploads si no existe
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $subcategoria_id = intval($_POST['subcategoria_id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $orden = intval($_POST['orden'] ?? 0);
        $destacado = isset($_POST['destacado']) ? 1 : 0;
        $imagen = null;

        if (empty($nombre) || $subcategoria_id <= 0 || $precio <= 0) {
            $mensaje = 'El nombre, subcategoria y precio son obligatorios';
            $tipo_mensaje = 'danger';
        } else {
            // Procesar imagen si se subio (usando funcion segura)
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $result = uploadSecureImage($_FILES['imagen']);
                if (isset($result['error'])) {
                    $mensaje = $result['error'];
                    $tipo_mensaje = 'danger';
                } else {
                    $imagen = $result['filename'];
                }
            }

            if (empty($mensaje)) {
                $stmt = $db->prepare("INSERT INTO productos (subcategoria_id, nombre, descripcion, precio, imagen, orden, destacado) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$subcategoria_id, $nombre, $descripcion, $precio, $imagen, $orden, $destacado])) {
                    $mensaje = 'Producto creado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al crear el producto';
                    $tipo_mensaje = 'danger';
                    if ($imagen) deleteImage($imagen);
                }
            }
        }
    }

    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $subcategoria_id = intval($_POST['subcategoria_id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $orden = intval($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        $destacado = isset($_POST['destacado']) ? 1 : 0;

        if (empty($nombre) || $id <= 0 || $subcategoria_id <= 0 || $precio <= 0) {
            $mensaje = 'Datos invalidos';
            $tipo_mensaje = 'danger';
        } else {
            // Obtener imagen actual
            $stmtImg = $db->prepare("SELECT imagen FROM productos WHERE id = ?");
            $stmtImg->execute([$id]);
            $imagenActual = $stmtImg->fetchColumn();
            $imagen = $imagenActual;

            // Procesar nueva imagen si se subio
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $result = uploadSecureImage($_FILES['imagen']);
                if (isset($result['error'])) {
                    $mensaje = $result['error'];
                    $tipo_mensaje = 'danger';
                } else {
                    // Eliminar imagen anterior
                    if ($imagenActual) deleteImage($imagenActual);
                    $imagen = $result['filename'];
                }
            }

            if (empty($mensaje)) {
                $stmt = $db->prepare("UPDATE productos SET subcategoria_id = ?, nombre = ?, descripcion = ?, precio = ?, imagen = ?, orden = ?, activo = ?, destacado = ? WHERE id = ?");
                if ($stmt->execute([$subcategoria_id, $nombre, $descripcion, $precio, $imagen, $orden, $activo, $destacado, $id])) {
                    $mensaje = 'Producto actualizado exitosamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar el producto';
                    $tipo_mensaje = 'danger';
                }
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            // Obtener imagen para eliminar
            $stmtImg = $db->prepare("SELECT imagen FROM productos WHERE id = ?");
            $stmtImg->execute([$id]);
            $imagen = $stmtImg->fetchColumn();

            $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
            if ($stmt->execute([$id])) {
                if ($imagen) deleteImage($imagen);
                $mensaje = 'Producto eliminado exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar el producto';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

// Obtener categorias y subcategorias para el select
$categorias = $db->query("SELECT * FROM categorias ORDER BY orden, nombre")->fetchAll();
$subcategorias = $db->query("
    SELECT s.*, c.nombre as categoria_nombre
    FROM subcategorias s
    JOIN categorias c ON s.categoria_id = c.id
    ORDER BY c.orden, c.nombre, s.orden, s.nombre
")->fetchAll();

// Obtener productos con info completa
$productos = $db->query("
    SELECT p.*, s.nombre as subcategoria_nombre, c.nombre as categoria_nombre
    FROM productos p
    JOIN subcategorias s ON p.subcategoria_id = s.id
    JOIN categorias c ON s.categoria_id = c.id
    ORDER BY c.orden, s.orden, p.orden, p.nombre
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="admin-content">
                <div class="admin-header">
                    <div class="page-title-group">
                        <h1 class="page-title">Productos</h1>
                        <p class="page-subtitle">Gestiona los productos del menu</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" onclick="openModal('crear')" <?= empty($subcategorias) ? 'disabled' : '' ?>>
                            <i class="fas fa-plus"></i>
                            Nuevo Producto
                        </button>
                    </div>
                </div>

            <?php if (empty($subcategorias)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Debes crear al menos una subcategoria antes de agregar productos.
                <a href="subcategorias.php" style="color: inherit; margin-left: 10px;">Ir a Subcategorias</a>
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
                    <?php if (empty($productos)): ?>
                    <p class="text-center text-muted">No hay productos registrados</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Categoria</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $prod): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($prod['imagen'])): ?>
                                        <img src="../<?= UPLOAD_URL . htmlspecialchars($prod['imagen']) ?>"
                                             alt="" class="table-image">
                                        <?php else: ?>
                                        <div class="table-image" style="background: var(--slate-100); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: var(--slate-300);"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($prod['nombre']) ?></strong>
                                        <?php if ($prod['destacado']): ?>
                                        <span class="badge badge-warning" style="margin-left: 5px;">Destacado</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 50)) ?>...</small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($prod['categoria_nombre']) ?><br>
                                        <small class="text-primary"><?= htmlspecialchars($prod['subcategoria_nombre']) ?></small>
                                    </td>
                                    <td class="text-success font-semibold"><?= formatPrice($prod['precio']) ?></td>
                                    <td>
                                        <?php if ($prod['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-secondary btn-icon"
                                                    onclick="openModal('editar', <?= htmlspecialchars(json_encode($prod)) ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-icon"
                                                    onclick="confirmarEliminar(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['nombre'])) ?>')"
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
    <div class="modal-overlay" id="modalProducto">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitulo">Nuevo Producto</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <?= getCsrfInput() ?>
                <div class="modal-body">
                    <input type="hidden" name="accion" id="formAccion" value="crear">
                    <input type="hidden" name="id" id="formId" value="">

                    <div class="form-group">
                        <label class="form-label" for="subcategoria_id">Subcategoria *</label>
                        <select id="subcategoria_id" name="subcategoria_id" class="form-select" required>
                            <option value="">Selecciona una subcategoria</option>
                            <?php
                            $currentCat = '';
                            foreach ($subcategorias as $sub):
                                if ($sub['categoria_nombre'] !== $currentCat):
                                    if ($currentCat !== '') echo '</optgroup>';
                                    $currentCat = $sub['categoria_nombre'];
                            ?>
                            <optgroup label="<?= htmlspecialchars($currentCat) ?>">
                            <?php endif; ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['nombre']) ?></option>
                            <?php endforeach; ?>
                            <?php if ($currentCat !== '') echo '</optgroup>'; ?>
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label" for="precio">Precio *</label>
                            <input type="number" id="precio" name="precio" class="form-input" required min="0" step="100">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="orden">Orden</label>
                            <input type="number" id="orden" name="orden" class="form-input" value="0" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Imagen del producto</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="imagen" id="imagen" class="file-input" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImage(this)">
                            <div class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Arrastra o haz clic para subir</span>
                            </div>
                        </div>
                        <div class="file-preview" id="imagePreview"></div>
                    </div>

                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" id="grupoActivo" style="display: none;">
                            <label class="form-label">
                                <input type="checkbox" name="activo" id="activo" checked>
                                Producto activo
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="destacado" id="destacado">
                                Producto destacado
                            </label>
                        </div>
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
                    <p>¿Estas seguro de eliminar el producto <strong id="eliminarNombre"></strong>?</p>
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
        const modal = document.getElementById('modalProducto');
        const titulo = document.getElementById('modalTitulo');
        const accion = document.getElementById('formAccion');
        const grupoActivo = document.getElementById('grupoActivo');
        const preview = document.getElementById('imagePreview');

        // Limpiar preview
        preview.innerHTML = '';
        document.getElementById('imagen').value = '';

        if (tipo === 'crear') {
            titulo.textContent = 'Nuevo Producto';
            accion.value = 'crear';
            document.getElementById('formId').value = '';
            document.getElementById('subcategoria_id').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('precio').value = '';
            document.getElementById('orden').value = '0';
            document.getElementById('destacado').checked = false;
            grupoActivo.style.display = 'none';
        } else {
            titulo.textContent = 'Editar Producto';
            accion.value = 'editar';
            document.getElementById('formId').value = data.id;
            document.getElementById('subcategoria_id').value = data.subcategoria_id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion || '';
            document.getElementById('precio').value = data.precio;
            document.getElementById('orden').value = data.orden;
            document.getElementById('activo').checked = data.activo == 1;
            document.getElementById('destacado').checked = data.destacado == 1;
            grupoActivo.style.display = 'block';

            // Mostrar imagen actual
            if (data.imagen) {
                preview.innerHTML = '<img src="../<?= UPLOAD_URL ?>' + data.imagen + '" alt="Imagen actual">';
            }
        }

        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('modalProducto').classList.remove('active');
    }

    function confirmarEliminar(id, nombre) {
        document.getElementById('eliminarId').value = id;
        document.getElementById('eliminarNombre').textContent = nombre;
        document.getElementById('modalEliminar').classList.add('active');
    }

    function closeModalEliminar() {
        document.getElementById('modalEliminar').classList.remove('active');
    }

    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            }
            reader.readAsDataURL(input.files[0]);
        }
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
