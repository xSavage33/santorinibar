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
        $destacado = isset($_POST['destacado']) ? 1 : 0;
        $imagen = null;

        // Obtener el orden maximo actual + 1 para esta subcategoria
        $maxOrden = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM productos WHERE subcategoria_id = ?");
        $maxOrden->execute([$subcategoria_id]);
        $orden = $maxOrden->fetchColumn();

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
                $stmt = $db->prepare("UPDATE productos SET subcategoria_id = ?, nombre = ?, descripcion = ?, precio = ?, imagen = ?, activo = ?, destacado = ? WHERE id = ?");
                if ($stmt->execute([$subcategoria_id, $nombre, $descripcion, $precio, $imagen, $activo, $destacado, $id])) {
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
                        <h1 class="page-title">Productos</h1>
                        <p class="page-subtitle">Arrastra las filas para reorganizar el orden</p>
                    </div>
                    <div class="page-actions">
                        <select id="filterCategoria" class="form-select" onchange="filterTable()" style="width: 180px;">
                            <option value="">Todas las categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['nombre']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-input-icon"></i>
                            <input type="text" id="searchInput" class="search-input" placeholder="Buscar producto..." onkeyup="filterTable()">
                        </div>
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
                                    <th style="width: 50px;"></th>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Categoria</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-productos">
                                <?php foreach ($productos as $prod): ?>
                                <tr data-id="<?= $prod['id'] ?>">
                                    <td>
                                        <span class="drag-handle" title="Arrastra para reordenar">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <img src="<?= !empty($prod['imagen']) ? '../' . UPLOAD_URL . htmlspecialchars($prod['imagen']) : NO_IMAGE_PLACEHOLDER ?>"
                                             alt="" class="table-image"
                                             onerror="this.onerror=null; this.src='<?= NO_IMAGE_PLACEHOLDER ?>'">
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
            <form id="formProducto" enctype="multipart/form-data">
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

                    <div class="form-group">
                        <label class="form-label" for="precio">Precio *</label>
                        <input type="number" id="precio" name="precio" class="form-input" required min="0" step="100">
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
            <form id="formEliminar">
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

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    const csrfToken = '<?= getCsrfToken() ?>';
    const uploadUrl = '../<?= UPLOAD_URL ?>';
    const noImagePlaceholder = '<?= NO_IMAGE_PLACEHOLDER ?>';

    // Inicializar Sortable
    const sortableEl = document.getElementById('sortable-productos');
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
        const rows = document.querySelectorAll('#sortable-productos tr[data-id]');
        const items = Array.from(rows).map(row => row.dataset.id);

        fetch('ajax/update_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                tabla: 'productos',
                items: items,
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('#sortable-productos tr').forEach(row => {
                    row.classList.add('order-updated');
                    setTimeout(() => row.classList.remove('order-updated'), 1000);
                });
            } else {
                showAlert('Error al guardar el orden: ' + (data.error || 'Error desconocido'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al guardar el orden', 'danger');
        });
    }

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
            document.getElementById('activo').checked = data.activo == 1;
            document.getElementById('destacado').checked = data.destacado == 1;
            grupoActivo.style.display = 'block';

            // Mostrar imagen actual
            if (data.imagen) {
                preview.innerHTML = '<img src="' + uploadUrl + data.imagen + '" alt="Imagen actual">';
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

    // Mostrar alertas
    function showAlert(message, type) {
        // Remover alertas anteriores
        const oldAlerts = document.querySelectorAll('.alert-dynamic');
        oldAlerts.forEach(a => a.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dynamic`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

        const adminContent = document.querySelector('.admin-content');
        const header = adminContent.querySelector('.admin-header');
        header.insertAdjacentElement('afterend', alertDiv);

        // Auto-remover despues de 5 segundos
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // Formatear precio
    function formatPrice(price) {
        return '$' + new Intl.NumberFormat('es-CL').format(price);
    }

    // Crear fila de producto
    function createProductRow(prod) {
        const tr = document.createElement('tr');
        tr.dataset.id = prod.id;
        tr.innerHTML = `
            <td>
                <span class="drag-handle" title="Arrastra para reordenar">
                    <i class="fas fa-grip-vertical"></i>
                </span>
            </td>
            <td>
                <img src="${prod.imagen ? uploadUrl + prod.imagen : noImagePlaceholder}"
                     alt="" class="table-image"
                     onerror="this.onerror=null; this.src='${noImagePlaceholder}'">
            </td>
            <td>
                <strong>${escapeHtml(prod.nombre)}</strong>
                ${prod.destacado == 1 ? '<span class="badge badge-warning" style="margin-left: 5px;">Destacado</span>' : ''}
                <br>
                <small class="text-muted">${escapeHtml((prod.descripcion || '').substring(0, 50))}...</small>
            </td>
            <td>
                ${escapeHtml(prod.categoria_nombre)}<br>
                <small class="text-primary">${escapeHtml(prod.subcategoria_nombre)}</small>
            </td>
            <td class="text-success font-semibold">${formatPrice(prod.precio)}</td>
            <td>
                ${prod.activo == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>'}
            </td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-sm btn-secondary btn-icon"
                            onclick="openModal('editar', ${escapeHtml(JSON.stringify(prod))})"
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-icon"
                            onclick="confirmarEliminar(${prod.id}, '${escapeHtml(prod.nombre.replace(/'/g, "\\'"))}')"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        return tr;
    }

    // Escape HTML para prevenir XSS
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Manejar formulario de crear/editar
    document.getElementById('formProducto').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        fetch('ajax/productos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const accion = document.getElementById('formAccion').value;
                const tbody = document.getElementById('sortable-productos');

                if (accion === 'crear') {
                    // Agregar nueva fila
                    const newRow = createProductRow(data.producto);
                    tbody.appendChild(newRow);
                    newRow.classList.add('order-updated');
                    setTimeout(() => newRow.classList.remove('order-updated'), 1000);

                    // Remover mensaje de "no hay productos" si existe
                    const emptyMsg = document.querySelector('.text-center.text-muted');
                    if (emptyMsg && emptyMsg.textContent.includes('No hay productos')) {
                        emptyMsg.closest('.card-body').innerHTML = `
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th>Imagen</th>
                                            <th>Producto</th>
                                            <th>Categoria</th>
                                            <th>Precio</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-productos"></tbody>
                                </table>
                            </div>
                        `;
                        document.getElementById('sortable-productos').appendChild(newRow);
                    }
                } else {
                    // Actualizar fila existente
                    const existingRow = tbody.querySelector(`tr[data-id="${data.producto.id}"]`);
                    if (existingRow) {
                        const newRow = createProductRow(data.producto);
                        existingRow.replaceWith(newRow);
                        newRow.classList.add('order-updated');
                        setTimeout(() => newRow.classList.remove('order-updated'), 1000);
                    }
                }

                closeModal();
                showAlert(data.message, 'success');
            } else {
                showAlert(data.error || 'Error al guardar', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexion', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // Manejar formulario de eliminar
    document.getElementById('formEliminar').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Eliminando...';

        fetch('ajax/productos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remover fila de la tabla
                const row = document.querySelector(`#sortable-productos tr[data-id="${data.id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => row.remove(), 300);
                }

                closeModalEliminar();
                showAlert(data.message, 'success');
            } else {
                showAlert(data.error || 'Error al eliminar', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexion', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // Filtrar tabla de productos
    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const categoryFilter = document.getElementById('filterCategoria').value.toLowerCase();
        const rows = document.querySelectorAll('#sortable-productos tr[data-id]');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const categoryCell = row.querySelector('td:nth-child(4)'); // Columna de categoría
            const categoryText = categoryCell ? categoryCell.textContent.toLowerCase() : '';

            const matchesSearch = searchValue === '' || text.includes(searchValue);
            const matchesCategory = categoryFilter === '' || categoryText.includes(categoryFilter);

            if (matchesSearch && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Mostrar mensaje si no hay resultados
        let noResults = document.getElementById('noResults');
        if (visibleCount === 0) {
            if (!noResults) {
                noResults = document.createElement('tr');
                noResults.id = 'noResults';
                noResults.innerHTML = '<td colspan="7" class="text-center text-muted" style="padding: 30px;">No se encontraron productos</td>';
                document.getElementById('sortable-productos').appendChild(noResults);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }
    </script>
</body>
</html>
