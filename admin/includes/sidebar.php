<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Menu Toggle Button -->
<button class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/img/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
        <div class="sidebar-brand">Licorera Terraza</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="categorias.php" class="nav-link <?= $currentPage === 'categorias' ? 'active' : '' ?>">
                <i class="fas fa-folder"></i>
                Categorias
            </a>
        </div>
        <div class="nav-item">
            <a href="subcategorias.php" class="nav-link <?= $currentPage === 'subcategorias' ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i>
                Subcategorias
            </a>
        </div>
        <div class="nav-item">
            <a href="productos.php" class="nav-link <?= $currentPage === 'productos' ? 'active' : '' ?>">
                <i class="fas fa-wine-bottle"></i>
                Productos
            </a>
        </div>

        <div class="nav-divider"></div>

        <div class="nav-item">
            <a href="../" target="_blank" class="nav-link">
                <i class="fas fa-external-link-alt"></i>
                Ver Menu
            </a>
        </div>
        <div class="nav-item">
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesion
            </a>
        </div>
    </nav>
</aside>
