<?php
require_once '../includes/config.php';
requireAuth();

$db = getConnection();

// Obtener estadisticas
$totalCategorias = $db->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
$totalSubcategorias = $db->query("SELECT COUNT(*) FROM subcategorias")->fetchColumn();
$totalProductos = $db->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$productosActivos = $db->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();

// Obtener ultimos productos
$ultimosProductos = $db->query("
    SELECT p.*, s.nombre as subcategoria_nombre, c.nombre as categoria_nombre
    FROM productos p
    JOIN subcategorias s ON p.subcategoria_id = s.id
    JOIN categorias c ON s.categoria_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
  	<link rel="icon" type="image/x-icon" href="../assets/img/icon.png">
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
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Bienvenido, <?= htmlspecialchars($_SESSION['admin_nombre']) ?></p>
                    </div>
                    <div class="page-actions">
                        <a href="../" target="_blank" class="btn btn-secondary">
                            <i class="fas fa-external-link-alt"></i>
                            Ver Menu
                        </a>
                    </div>
                </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-value"><?= $totalCategorias ?></div>
                    <div class="stat-label">Categorias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cyan">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-value"><?= $totalSubcategorias ?></div>
                    <div class="stat-label">Subcategorias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-wine-bottle"></i>
                    </div>
                    <div class="stat-value"><?= $totalProductos ?></div>
                    <div class="stat-label">Productos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?= $productosActivos ?></div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>

            <!-- Ultimos Productos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Ultimos Productos Agregados</h2>
                    <a href="productos.php" class="btn btn-sm btn-secondary">Ver Todos</a>
                </div>
                <div class="card-body">
                    <?php if (empty($ultimosProductos)): ?>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosProductos as $prod): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($prod['imagen'])): ?>
                                        <img src="../<?= UPLOAD_URL . htmlspecialchars($prod['imagen']) ?>" 
                                             alt="" class="table-image">
                                        <?php else: ?>
                                        <img src="../assets/img/no-image.svg" alt="Sin imagen" class="table-image">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($prod['nombre']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($prod['categoria_nombre']) ?> / 
                                        <?= htmlspecialchars($prod['subcategoria_nombre']) ?>
                                    </td>
                                    <td class="text-success font-semibold"><?= formatPrice($prod['precio']) ?></td>
                                    <td>
                                        <?php if ($prod['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
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

    <script src="../assets/js/admin.js"></script>
</body>
</html>