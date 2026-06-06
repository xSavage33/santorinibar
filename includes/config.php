<?php
// =============================================
// CONFIGURACION DE BASE DE DATOS
// =============================================

// Cargar variables de entorno si existe .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Configuracion de base de datos desde variables de entorno
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'santorini');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Conexion PDO
function getConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Error de conexion DB: " . $e->getMessage());
            die("Error de conexion a la base de datos. Contacte al administrador.");
        }
    }
    return $pdo;
}

// Configuracion general
define('SITE_NAME', 'Santorini Restobar');
define('UPLOAD_PATH', __DIR__ . '/../uploads/productos/');
define('UPLOAD_URL', 'uploads/productos/');

// Imagen placeholder para productos sin foto (SVG en base64)
define('NO_IMAGE_PLACEHOLDER', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgMjAwIiBmaWxsPSJub25lIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iYmdHcmFkIiB4MT0iMCUiIHkxPSIwJSIgeDI9IjEwMCUiIHkyPSIxMDAlIj4KICAgICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFhMWEyZTtzdG9wLW9wYWNpdHk6MSIgLz4KICAgICAgPHN0b3Agb2Zmc2V0PSIxMDAlIiBzdHlsZT0ic3RvcC1jb2xvcjojMTYyMTNlO3N0b3Atb3BhY2l0eToxIiAvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iaWNvbkdyYWQiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPgogICAgICA8c3RvcCBvZmZzZXQ9IjAlIiBzdHlsZT0ic3RvcC1jb2xvcjojZDRhZjM3O3N0b3Atb3BhY2l0eToxIiAvPgogICAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNiODk2MGM7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICA8L2xpbmVhckdyYWRpZW50PgogIDwvZGVmcz4KICA8cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0idXJsKCNiZ0dyYWQpIi8+CiAgPHJlY3QgeD0iOCIgeT0iOCIgd2lkdGg9IjE4NCIgaGVpZ2h0PSIxODQiIHJ4PSI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNkNGFmMzciIHN0cm9rZS13aWR0aD0iMSIgb3BhY2l0eT0iMC4zIi8+CiAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTAwLDEwMCkiIGZpbGw9InVybCgjaWNvbkdyYWQpIj4KICAgIDxwYXRoIGQ9Ik0tMTIgNDUgTC0xMiAxMCBRLTEyIC01IC04IC0xNSBMLTggLTM1IFEtOCAtNDAgLTYgLTQwIEw2IC00MCBROCAtNDAgOCAtMzUgTDggLTE1IFExMiAtNSAxMiAxMCBMMTIgNDUgUTEyIDUwIDggNTAgTC04IDUwIFEtMTIgNTAgLTEyIDQ1IFoiIG9wYWNpdHk9IjAuOSIvPgogICAgPHJlY3QgeD0iLTYiIHk9Ii00MiIgd2lkdGg9IjEyIiBoZWlnaHQ9IjQiIHJ4PSIxIiBvcGFjaXR5PSIwLjciLz4KICAgIDxyZWN0IHg9Ii04IiB5PSI1IiB3aWR0aD0iMTYiIGhlaWdodD0iMjAiIHJ4PSIyIiBmaWxsPSIjMWExYTJlIiBvcGFjaXR5PSIwLjUiLz4KICA8L2c+CiAgPHBhdGggZD0iTTE1IDE1IEwzNSAxNSBNMTUgMTUgTDE1IDM1IiBzdHJva2U9IiNkNGFmMzciIHN0cm9rZS13aWR0aD0iMS41IiBvcGFjaXR5PSIwLjUiLz4KICA8cGF0aCBkPSJNMTg1IDE1IEwxNjUgMTUgTTE4NSAxNSBMMTg1IDM1IiBzdHJva2U9IiNkNGFmMzciIHN0cm9rZS13aWR0aD0iMS41IiBvcGFjaXR5PSIwLjUiLz4KICA8cGF0aCBkPSJNMTUgMTg1IEwzNSAxODUgTTE1IDE4NSBMMTU1IDE2NSIgc3Ryb2tlPSIjZDRhZjM3IiBzdHJva2Utd2lkdGg9IjEuNSIgb3BhY2l0eT0iMC41Ii8+CiAgPHBhdGggZD0iTTE4NSAxODUgTDE2NSAxODUgTTE4NSAxODUgTDE4NSAxNjUiIHN0cm9rZT0iI2Q0YWYzNyIgc3Ryb2tlLXdpZHRoPSIxLjUiIG9wYWNpdHk9IjAuNSIvPgogIDx0ZXh0IHg9IjEwMCIgeT0iMTc1IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjZDRhZjM3IiBmb250LWZhbWlseT0iR2VvcmdpYSwgc2VyaWYiIGZvbnQtc2l6ZT0iMTEiIG9wYWNpdHk9IjAuNiI+U2luIGltYWdlbjwvdGV4dD4KPC9zdmc+');

// Configuracion de sesion segura ANTES de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    // Configurar cookies de sesion seguras
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Funcion para verificar autenticacion
function isAuthenticated() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Funcion para requerir autenticacion
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Funcion para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Funcion para formatear precio
function formatPrice($price) {
    return '$' . number_format($price, 0, ',', '.');
}

// =============================================
// PROTECCION CSRF
// =============================================
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function getCsrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

function validateCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF invalido. Recarga la pagina e intenta de nuevo.');
        }
    }
}

function validateCsrfTokenValue($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function getCsrfToken() {
    return generateCsrfToken();
}

// =============================================
// PROTECCION DE FUERZA BRUTA
// =============================================
function checkLoginAttempts($ip) {
    $maxAttempts = 5;
    $lockoutTime = 900; // 15 minutos

    $attempts = $_SESSION['login_attempts'][$ip] ?? ['count' => 0, 'time' => 0];

    if ($attempts['count'] >= $maxAttempts) {
        if (time() - $attempts['time'] < $lockoutTime) {
            $remaining = ceil(($lockoutTime - (time() - $attempts['time'])) / 60);
            return "Demasiados intentos fallidos. Intenta en {$remaining} minutos.";
        } else {
            $_SESSION['login_attempts'][$ip] = ['count' => 0, 'time' => 0];
        }
    }
    return true;
}

function recordLoginAttempt($ip, $success) {
    if ($success) {
        unset($_SESSION['login_attempts'][$ip]);
    } else {
        $attempts = $_SESSION['login_attempts'][$ip] ?? ['count' => 0, 'time' => 0];
        $_SESSION['login_attempts'][$ip] = [
            'count' => $attempts['count'] + 1,
            'time' => time()
        ];
    }
}

// =============================================
// VALIDACION DE ARCHIVOS SEGURA
// =============================================
function validateImageFile($file) {
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Error al subir el archivo'];
    }

    if ($file['size'] > $maxSize) {
        return ['error' => 'El archivo es muy grande. Maximo 5MB'];
    }

    // Verificar extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return ['error' => 'Extension no permitida. Usa JPG, PNG, GIF o WEBP'];
    }

    // Verificar MIME type real usando fileinfo (magic bytes)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (!in_array($realMime, $allowedMimes)) {
        return ['error' => 'Tipo de archivo no permitido'];
    }

    // Verificar que sea una imagen valida
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['error' => 'El archivo no es una imagen valida'];
    }

    // Generar nombre unico seguro
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;

    return ['success' => true, 'filename' => $filename, 'extension' => $extension];
}

function uploadSecureImage($file) {
    $validation = validateImageFile($file);
    if (isset($validation['error'])) {
        return $validation;
    }

    // Crear directorio si no existe
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }

    $destination = UPLOAD_PATH . $validation['filename'];

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $validation['filename']];
    }

    return ['error' => 'Error al guardar el archivo'];
}

function deleteImage($filename) {
    if (empty($filename)) return;
    $filepath = UPLOAD_PATH . basename($filename); // basename previene path traversal
    if (file_exists($filepath) && is_file($filepath)) {
        unlink($filepath);
    }
}

// Funcion para validar tipo_menu
function validateTipoMenu($tipo) {
    $allowed = ['licores', 'comidas'];
    return in_array($tipo, $allowed) ? $tipo : 'licores';
}
?>
