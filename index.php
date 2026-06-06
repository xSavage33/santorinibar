<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Santorini Restobar | Licores & Comidas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- Background -->
    <div class="bg-overlay"></div>

    <!-- Main Container -->
    <div class="main-container">

        <!-- Header Nav -->
        <header class="top-header">
            <nav class="nav-menu">
                <a href="#" class="nav-link active">Inicio</a>
                <a href="menu-licores.php" class="nav-link">Licores</a>
                <a href="menu-comidas.php" class="nav-link">Comidas</a>
            </nav>
            <div class="logo-center">
                <img src="assets/img/header.png" alt="Santorini" onerror="this.style.display='none'">
                <!-- <span>SANTORINI</span> -->
            </div>
            <nav class="nav-menu">
                <a href="https://maps.app.goo.gl/tSKgcWovmNAI6Hm5J" class="nav-link" target="_blank">Ubicacion</a>
                <a href="https://wa.me/573159492999" class="nav-link" target="_blank">Contacto</a>
            </nav>
        </header>

        <!-- Mobile Header -->
        <header class="mobile-header">
            <div class="mobile-logo">
                <img src="assets/img/header.png" alt="Santorini" onerror="this.style.display='none'">
                <!-- <span>SANTORINI</span> -->
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <p class="hero-subtitle">BIENVENIDOS A</p>
                <h1 class="hero-title">SANTORINI</h1>
                <h2 class="hero-title-sub">RESTOBAR</h2>
                <p class="hero-description">Donde el sabor se encuentra con la elegancia. Disfruta de nuestra selecta carta de licores premium y deliciosas comidas rapidas.</p>

                <div class="hero-tags">
                    <span class="tag"><i class="fas fa-wine-glass-alt"></i> Premium</span>
                    <span class="tag"><i class="fas fa-utensils"></i> Fastfood</span>
                    <span class="tag"><i class="fas fa-star"></i> +50 Productos</span>
                </div>
            </div>
        </section>

        <!-- Selection Cards -->
        <section class="selection-section">
            <div class="selection-grid">

                <!-- Licores Card -->
                <a href="menu-licores.php" class="selection-card licores">
                    <div class="card-bg"></div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-wine-glass-alt"></i>
                        </div>
                        <h3 class="card-title">LICORES</h3>
                        <p class="card-desc">Whisky, Ron, Tequila y más</p>
                        <span class="card-btn">
                            Ver Carta <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Comidas Card -->
                <a href="menu-comidas.php" class="selection-card comidas">
                    <div class="card-bg"></div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-hamburger"></i>
                        </div>
                        <h3 class="card-title">COMIDAS</h3>
                        <p class="card-desc">Hamburguesas, Perros, Suizos</p>
                        <span class="card-btn">
                            Ver Menu <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>

            </div>
        </section>

        <!-- Footer -->
        <footer class="bottom-footer">
            <div class="footer-contact">
                <button type="button" class="contact-btn mesera" onclick="openMeseraModal()">
                    <i class="fas fa-bell-concierge"></i>
                    <span>Llamar mesera</span>
                </button>
                <a href="https://share.google/CrWbFGxKq2CS1IgIT" class="contact-btn location" target="_blank">
                    <i class="fas fa-location-dot"></i>
                    <span>Ubicacion</span>
                </a>
            </div>
            <p class="footer-address">
                <i class="fas fa-map-marker-alt"></i> Cra 24 #27B-18 &nbsp;|&nbsp;
                <i class="fas fa-phone"></i> +57 315 949 2999
            </p>
        </footer>

    </div>

    <!-- Modal Llamar Mesera -->
    <div class="modal-overlay" id="modalMesera">
        <div class="modal-container">
            <button class="modal-close" onclick="closeMeseraModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-icon">
                <i class="fas fa-bell-concierge"></i>
            </div>
            <h3 class="modal-title">Ingresa el numero de la mesa en la que te encuentras</h3>
            <p class="modal-subtitle">Puedes llamar una mesera personalmente, solo usa esta opcion cuando las meseras esten muy ocupadas</p>
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
                <div class="success-ring"></div>
            </div>
            <h3 class="modal-title success">¡Solicitud enviada!</h3>
            <p class="modal-subtitle">En breves una mesera te atendera en tu mesa</p>
            <button type="button" class="modal-btn success" onclick="closeConfirmacionModal()">
                Entendido
            </button>
        </div>
    </div>

    <script src="assets/js/landing.js"></script>
</body>
</html>
