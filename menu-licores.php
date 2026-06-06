<?php
require_once 'includes/config.php';

$db = getConnection();

// Obtener categorías activas del menú de licores
$stmtCat = $db->prepare("SELECT * FROM categorias WHERE activo = 1 AND tipo_menu = 'licores' ORDER BY orden");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll();

// Obtener subcategorías activas
$stmtSub = $db->prepare("SELECT s.*, c.nombre as categoria_nombre
    FROM subcategorias s
    JOIN categorias c ON s.categoria_id = c.id
    WHERE s.activo = 1 AND c.tipo_menu = 'licores' AND c.activo = 1
    ORDER BY c.orden, s.orden");
$stmtSub->execute();
$subcategorias = $stmtSub->fetchAll();

// Obtener productos activos
$stmtProd = $db->prepare("SELECT p.*, s.nombre as subcategoria_nombre, s.id as subcategoria_id
    FROM productos p
    JOIN subcategorias s ON p.subcategoria_id = s.id
    JOIN categorias c ON s.categoria_id = c.id
    WHERE p.activo = 1 AND c.tipo_menu = 'licores' AND c.activo = 1
    ORDER BY c.orden, s.orden, p.orden");
$stmtProd->execute();
$productos = $stmtProd->fetchAll();

// Organizar productos por subcategoría
$productosPorSubcategoria = [];
foreach ($productos as $producto) {
    $subId = $producto['subcategoria_id'];
    if (!isset($productosPorSubcategoria[$subId])) {
        $productosPorSubcategoria[$subId] = [];
    }
    $productosPorSubcategoria[$subId][] = $producto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licores | Santorini Restobar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/menu.css">
</head>
<body data-theme="licores">
    <!-- Background Effects -->
    <div class="bg-effects">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
    </div>

    <!-- Header -->
    <header class="menu-header">
        <div class="header-container">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
            <div class="header-brand">
                <img src="assets/img/header.png" alt="Santorini" class="header-logo" onerror="this.style.display='none'">
                <div class="header-text">
                    <!-- <h1 class="header-title">Santorini</h1> -->
                    <!-- <span class="header-subtitle">RESTOBAR</span> -->
                </div>
            </div>
            <a href="menu-comidas.php" class="switch-menu">
                <i class="fas fa-utensils"></i>
                <span>Comidas</span>
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="menu-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-wine-glass-alt"></i>
            </div>
            <h2 class="hero-title">Nuestra Carta de Licores</h2>
            <p class="hero-description">Selección premium de whisky, ron, tequila, vodka y más</p>
        </div>
        <div class="hero-decoration">
            <div class="deco-line left"></div>
            <div class="deco-diamond"></div>
            <div class="deco-line right"></div>
        </div>
    </section>

    <!-- Category Navigation -->
    <nav class="category-nav">
        <div class="nav-container">
            <div class="nav-scroll">
                <?php foreach ($subcategorias as $index => $sub): ?>
                <a href="#sub-<?= $sub['id'] ?>" class="nav-item <?= $index === 0 ? 'active' : '' ?>">
                    <span class="nav-text"><?= htmlspecialchars($sub['nombre']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <!-- Menu Content -->
    <main class="menu-content">
        <div class="content-container">
            <?php foreach ($subcategorias as $sub): ?>
            <section class="subcategory-section" id="sub-<?= $sub['id'] ?>">
                <div class="section-header">
                    <div class="section-decoration left"></div>
                    <h3 class="section-title"><?= htmlspecialchars($sub['nombre']) ?></h3>
                    <div class="section-decoration right"></div>
                </div>
                <?php if (!empty($sub['descripcion'])): ?>
                <p class="section-description"><?= htmlspecialchars($sub['descripcion']) ?></p>
                <?php endif; ?>

                <div class="products-grid">
                    <?php if (isset($productosPorSubcategoria[$sub['id']])): ?>
                    <?php foreach ($productosPorSubcategoria[$sub['id']] as $producto): ?>
                    <article class="product-card <?= $producto['destacado'] ? 'featured' : '' ?>">
                        <?php if ($producto['destacado']): ?>
                        <div class="featured-badge">
                            <i class="fas fa-star"></i>
                            <span>Destacado</span>
                        </div>
                        <?php endif; ?>

                        <div class="product-image">
                            <img src="<?= !empty($producto['imagen']) ? 'uploads/productos/' . htmlspecialchars($producto['imagen']) : PLACEHOLDER_LICORES ?>"
                                 alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='<?= PLACEHOLDER_LICORES ?>'">
                            <div class="image-overlay"></div>
                        </div>

                        <div class="product-info">
                            <h4 class="product-name"><?= htmlspecialchars($producto['nombre']) ?></h4>
                            <?php if (!empty($producto['descripcion'])): ?>
                            <p class="product-description"><?= htmlspecialchars($producto['descripcion']) ?></p>
                            <?php endif; ?>
                            <div class="product-price">
                                <span class="price-value"><?= formatPrice($producto['precio']) ?></span>
                            </div>
                        </div>

                        <div class="product-glow"></div>
                    </article>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p class="no-products">No hay productos disponibles en esta categoria.</p>
                    <?php endif; ?>
                </div>
            </section>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="menu-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <h3>Santorini Restobar</h3>
                <p>Donde el sabor se encuentra con la elegancia</p>
            </div>
            <div class="footer-contact">
                <button type="button" class="contact-btn mesera" onclick="openMeseraModal()">
                    <i class="fas fa-bell-concierge"></i>
                    <span>Llamar mesera</span>
                </button>
                <a href="https://maps.app.goo.gl/tSKgcWovmNAI6Hm5J" class="contact-btn location" target="_blank">
                    <i class="fas fa-location-dot"></i>
                    <span>Como llegar</span>
                </a>
            </div>
            <div class="footer-info">
                <p><i class="fas fa-map-marker-alt"></i> Cra 24 #27B-18</p>
                <p><i class="fas fa-phone"></i> +57 315 949 2999</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top -->
    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Modal Llamar Mesera -->
    <div class="modal-overlay" id="modalMesera">
        <div class="modal-container">
            <button class="modal-close" onclick="closeMeseraModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-icon">
                <i class="fas fa-bell-concierge"></i>
            </div>
            <h3 class="modal-title">Ingresa el numero de la mesa</h3>
            <p class="modal-subtitle">Solo usa esta opcion cuando las meseras esten muy ocupadas</p>
            <div class="modal-input-group">
                <input type="text" id="mesaInput" class="modal-input" placeholder="Ej: 5" maxlength="2" inputmode="numeric" pattern="[0-9]*">
                <span class="input-label">Mesa #</span>
            </div>
            <button type="button" class="modal-btn" onclick="enviarSolicitud()">
                <i class="fab fa-whatsapp"></i>
                Enviar solicitud
            </button>
        </div>
    </div>

    <!-- Modal Confirmacion -->
    <div class="modal-overlay" id="modalConfirmacion">
        <div class="modal-container confirmacion">
            <div class="success-animation">
                <div class="success-circle">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h3 class="modal-title success">¡Solicitud enviada!</h3>
            <p class="modal-subtitle">En breves una mesera te atendera</p>
            <button type="button" class="modal-btn success" onclick="closeConfirmacionModal()">
                Entendido
            </button>
        </div>
    </div>

    <script src="assets/js/menu.js"></script>
    <script src="assets/js/landing.js"></script>
</body>
</html>
