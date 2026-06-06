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

// Placeholder para licores (copa de vino elegante)
define('PLACEHOLDER_LICORES', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1a1a2e"/>
      <stop offset="100%" stop-color="#16213e"/>
    </linearGradient>
    <linearGradient id="gold" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#f4d03f"/>
      <stop offset="50%" stop-color="#d4af37"/>
      <stop offset="100%" stop-color="#b8960c"/>
    </linearGradient>
  </defs>
  <rect width="200" height="200" fill="url(#bg)"/>
  <g transform="translate(100,100)" stroke="url(#gold)" fill="none" stroke-width="2" stroke-linecap="round">
    <ellipse cx="0" cy="-35" rx="28" ry="20" opacity="0.9"/>
    <path d="M0 -15 L0 30" opacity="0.9"/>
    <ellipse cx="0" cy="35" rx="18" ry="6" opacity="0.9"/>
    <path d="M-20 -45 Q-10 -60 0 -55 Q10 -60 20 -45" opacity="0.5"/>
  </g>
</svg>'));

// Placeholder para comidas (plato con cubiertos)
define('PLACEHOLDER_COMIDAS', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <defs>
    <linearGradient id="bg2" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1a1a2e"/>
      <stop offset="100%" stop-color="#16213e"/>
    </linearGradient>
    <linearGradient id="gold2" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#f4d03f"/>
      <stop offset="50%" stop-color="#d4af37"/>
      <stop offset="100%" stop-color="#b8960c"/>
    </linearGradient>
  </defs>
  <rect width="200" height="200" fill="url(#bg2)"/>
  <g transform="translate(100,100)" stroke="url(#gold2)" fill="none" stroke-width="2" stroke-linecap="round">
    <circle cx="0" cy="0" r="35" opacity="0.9"/>
    <circle cx="0" cy="0" r="25" opacity="0.5"/>
    <path d="M-55 -30 L-55 35 M-55 -30 Q-55 -40 -50 -40 Q-45 -40 -45 -30 L-45 -5 M-55 -5 L-45 -5" opacity="0.8"/>
    <path d="M55 -40 L55 35 M50 -40 L50 -20 L55 -15 L60 -20 L60 -40" opacity="0.8"/>
  </g>
</svg>'));

// Placeholder por defecto (usa licores)
define('NO_IMAGE_PLACEHOLDER', PLACEHOLDER_LICORES);

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
