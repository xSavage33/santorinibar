// =============================================
// SANTORINI RESTOBAR - MENU JS
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initScrollTop();
});

// =============================================
// CATEGORY NAVIGATION
// =============================================

function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.subcategory-section');

    if (!navItems.length || !sections.length) return;

    // Click navigation
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);

            if (targetSection) {
                // Update active state
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');

                // Smooth scroll
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Intersection Observer for active state
    const observerOptions = {
        root: null,
        rootMargin: '-30% 0px -60% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                navItems.forEach(item => {
                    item.classList.remove('active');
                    if (item.getAttribute('href') === '#' + id) {
                        item.classList.add('active');
                        // Scroll nav item into view
                        scrollNavItemIntoView(item);
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(section => observer.observe(section));
}

function scrollNavItemIntoView(item) {
    const navScroll = document.querySelector('.nav-scroll');
    if (!navScroll) return;

    const itemRect = item.getBoundingClientRect();
    const navRect = navScroll.getBoundingClientRect();

    if (itemRect.left < navRect.left || itemRect.right > navRect.right) {
        item.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center'
        });
    }
}

// =============================================
// SCROLL TO TOP
// =============================================

function initScrollTop() {
    const scrollTopBtn = document.getElementById('scrollTop');
    if (!scrollTopBtn) return;

    // Show/hide button
    const toggleVisibility = () => {
        if (window.scrollY > 500) {
            scrollTopBtn.classList.add('visible');
        } else {
            scrollTopBtn.classList.remove('visible');
        }
    };

    window.addEventListener('scroll', throttle(toggleVisibility, 100));

    // Click to scroll
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}


// =============================================
// UTILITIES
// =============================================

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

function isTouchDevice() {
    return ('ontouchstart' in window) ||
        (navigator.maxTouchPoints > 0) ||
        (navigator.msMaxTouchPoints > 0);
}

// Add touch-device class
if (isTouchDevice()) {
    document.body.classList.add('touch-device');
}

// =============================================
// VER MAS DESCRIPCION
// =============================================

function toggleDescripcion(btn) {
    const wrapper = btn.closest('.product-description-wrapper');

    if (wrapper.classList.contains('expanded')) {
        // Colapsar
        wrapper.classList.remove('expanded');
        wrapper.classList.add('truncated');
        btn.textContent = 'Ver más...';
    } else {
        // Cerrar cualquier otra descripcion expandida
        document.querySelectorAll('.product-description-wrapper.expanded').forEach(w => {
            w.classList.remove('expanded');
            w.classList.add('truncated');
            const otherBtn = w.querySelector('.ver-mas-btn');
            if (otherBtn) otherBtn.textContent = 'Ver más...';
        });

        // Expandir esta
        wrapper.classList.remove('truncated');
        wrapper.classList.add('expanded');
        btn.textContent = 'Ver menos';
    }
}

// Cerrar al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-description-wrapper')) {
        document.querySelectorAll('.product-description-wrapper.expanded').forEach(wrapper => {
            wrapper.classList.remove('expanded');
            wrapper.classList.add('truncated');
            const btn = wrapper.querySelector('.ver-mas-btn');
            if (btn) btn.textContent = 'Ver más...';
        });
    }
});

