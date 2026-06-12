<?php
require_once '../../includes/config.php';

// Verificar autenticacion
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

// Validar CSRF token
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfTokenValue($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalido']);
    exit;
}

$db = getConnection();
$accion = $_POST['accion'] ?? '';

// Crear directorio de uploads si no existe
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

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
        echo json_encode(['success' => false, 'error' => 'El nombre, subcategoria y precio son obligatorios']);
        exit;
    }

    // Procesar imagen si se subio
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadSecureImage($_FILES['imagen']);
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            exit;
        }
        $imagen = $result['filename'];
    }

    $stmt = $db->prepare("INSERT INTO productos (subcategoria_id, nombre, descripcion, precio, imagen, orden, destacado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$subcategoria_id, $nombre, $descripcion, $precio, $imagen, $orden, $destacado])) {
        $newId = $db->lastInsertId();

        // Obtener el producto recien creado con info completa
        $stmtGet = $db->prepare("
            SELECT p.*, s.nombre as subcategoria_nombre, c.nombre as categoria_nombre
            FROM productos p
            JOIN subcategorias s ON p.subcategoria_id = s.id
            JOIN categorias c ON s.categoria_id = c.id
            WHERE p.id = ?
        ");
        $stmtGet->execute([$newId]);
        $producto = $stmtGet->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'producto' => $producto
        ]);
    } else {
        if ($imagen) deleteImage($imagen);
        echo json_encode(['success' => false, 'error' => 'Error al crear el producto']);
    }
    exit;
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
        echo json_encode(['success' => false, 'error' => 'Datos invalidos']);
        exit;
    }

    // Obtener imagen actual
    $stmtImg = $db->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmtImg->execute([$id]);
    $imagenActual = $stmtImg->fetchColumn();
    $imagen = $imagenActual;

    // Procesar nueva imagen si se subio
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadSecureImage($_FILES['imagen']);
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            exit;
        }
        // Eliminar imagen anterior
        if ($imagenActual) deleteImage($imagenActual);
        $imagen = $result['filename'];
    }

    $stmt = $db->prepare("UPDATE productos SET subcategoria_id = ?, nombre = ?, descripcion = ?, precio = ?, imagen = ?, activo = ?, destacado = ? WHERE id = ?");
    if ($stmt->execute([$subcategoria_id, $nombre, $descripcion, $precio, $imagen, $activo, $destacado, $id])) {
        // Obtener el producto actualizado con info completa
        $stmtGet = $db->prepare("
            SELECT p.*, s.nombre as subcategoria_nombre, c.nombre as categoria_nombre
            FROM productos p
            JOIN subcategorias s ON p.subcategoria_id = s.id
            JOIN categorias c ON s.categoria_id = c.id
            WHERE p.id = ?
        ");
        $stmtGet->execute([$id]);
        $producto = $stmtGet->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar el producto']);
    }
    exit;
}

if ($accion === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID invalido']);
        exit;
    }

    // Obtener imagen para eliminar
    $stmtImg = $db->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmtImg->execute([$id]);
    $imagen = $stmtImg->fetchColumn();

    $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
    if ($stmt->execute([$id])) {
        if ($imagen) deleteImage($imagen);
        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado exitosamente',
            'id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar el producto']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Accion no valida']);
