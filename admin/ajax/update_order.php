<?php
/**
 * Endpoint AJAX para actualizar el orden de elementos via drag & drop
 */
require_once '../../includes/config.php';
requireAuth();

header('Content-Type: application/json');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
    exit;
}

// Obtener datos del body
$input = json_decode(file_get_contents('php://input'), true);

// Verificar CSRF - buscar en headers y en el body JSON
$csrfToken = '';

// Intentar obtener de headers (varios formatos)
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? $headers['X-Csrf-Token'] ?? $headers['x-csrf-token'] ?? '';
}

// Si no está en headers, buscar en el body JSON
if (empty($csrfToken) && isset($input['csrf_token'])) {
    $csrfToken = $input['csrf_token'];
}

// Fallback: buscar en $_SERVER
if (empty($csrfToken)) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
}

if (empty($csrfToken) || !validateCsrfTokenValue($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalido']);
    exit;
}
$tabla = $input['tabla'] ?? '';
$items = $input['items'] ?? [];

// Validar tabla permitida
$tablasPermitidas = ['categorias', 'subcategorias', 'productos'];
if (!in_array($tabla, $tablasPermitidas)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tabla no valida']);
    exit;
}

// Validar items
if (empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Items no validos']);
    exit;
}

try {
    $db = getConnection();
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE {$tabla} SET orden = ? WHERE id = ?");

    foreach ($items as $index => $id) {
        $id = intval($id);
        if ($id > 0) {
            $stmt->execute([$index, $id]);
        }
    }

    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Orden actualizado']);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al actualizar orden']);
}
