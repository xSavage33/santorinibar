<?php
require_once '../includes/config.php';

// Si ya esta logueado, redirigir al dashboard
if (isAuthenticated()) {
    header('Location: index.php');
    exit;
}

$error = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar limite de intentos
    $lockCheck = checkLoginAttempts($ip);
    if ($lockCheck !== true) {
        $error = $lockCheck;
    } else {
        $usuario = sanitize($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($usuario) || empty($password)) {
            $error = 'Por favor completa todos los campos';
            recordLoginAttempt($ip, false);
        } else {
            $db = getConnection();
            $stmt = $db->prepare("SELECT * FROM administradores WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();

            // Comparar contraseña
            if ($admin && $password === $admin['password']) {
                // Regenerar session ID para prevenir session fixation
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_usuario'] = $admin['usuario'];

                recordLoginAttempt($ip, true);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Usuario o contrasena incorrectos';
                recordLoginAttempt($ip, false);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= SITE_NAME ?></title>
  	<link rel="icon" type="image/x-icon" href="../assets/img/icon.png">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <img src="../assets/img/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
            <h1 class="login-title">Administracion</h1>
            <p class="login-subtitle">Panel de Control</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="usuario">Usuario</label>
                <input type="text"
                       id="usuario"
                       name="usuario"
                       class="form-input"
                       placeholder="Ingresa tu usuario"
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                       required
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contrasena</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-input"
                       placeholder="Ingresa tu contrasena"
                       required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesion
            </button>
        </form>

        <p class="text-center text-muted mt-20" style="font-size: 0.8rem;">
            <a href="../" style="color: var(--color-gold); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Volver al menu
            </a>
        </p>
    </div>
</body>
</html>
