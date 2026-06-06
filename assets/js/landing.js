// =============================================
// SANTORINI RESTOBAR - LANDING JS
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    initMesaInput();
});

// =============================================
// MODAL MESERA
// =============================================

function openMeseraModal() {
    const modal = document.getElementById('modalMesera');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Focus en el input
    setTimeout(() => {
        document.getElementById('mesaInput').focus();
    }, 300);
}

function closeMeseraModal() {
    const modal = document.getElementById('modalMesera');
    modal.classList.remove('active');
    document.body.style.overflow = '';

    // Limpiar input
    document.getElementById('mesaInput').value = '';
}

function closeConfirmacionModal() {
    const modal = document.getElementById('modalConfirmacion');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// =============================================
// INPUT MESA - Solo numeros, max 2 digitos
// =============================================

function initMesaInput() {
    const input = document.getElementById('mesaInput');
    if (!input) return;

    input.addEventListener('input', function(e) {
        // Solo permitir numeros
        this.value = this.value.replace(/[^0-9]/g, '');

        // Maximo 2 digitos
        if (this.value.length > 2) {
            this.value = this.value.slice(0, 2);
        }
    });

    input.addEventListener('keypress', function(e) {
        // Solo permitir teclas numericas
        if (!/[0-9]/.test(e.key) && e.key !== 'Enter') {
            e.preventDefault();
        }

        // Enter para enviar
        if (e.key === 'Enter') {
            enviarSolicitud();
        }
    });
}

// =============================================
// ENVIAR SOLICITUD A WHATSAPP
// =============================================

function enviarSolicitud() {
    const input = document.getElementById('mesaInput');
    const numeroMesa = input.value.trim();

    // Validar que haya numero
    if (!numeroMesa) {
        // Efecto de error en el input
        input.style.borderColor = '#ff4444';
        input.style.animation = 'shake 0.5s ease';
        setTimeout(() => {
            input.style.borderColor = '';
            input.style.animation = '';
        }, 500);
        return;
    }

    // Construir mensaje de WhatsApp
    const mensaje = `🔔 *SOLICITUD DE MESERA*%0A%0A📍 Mesa: *${numeroMesa}*%0A%0AUn cliente solicita atencion en la mesa ${numeroMesa}.`;
    const telefono = '573159492999';
    const urlWhatsApp = `https://wa.me/${telefono}?text=${mensaje}`;

    // Abrir WhatsApp en nueva pestaña
    window.open(urlWhatsApp, '_blank');

    // Cerrar modal de mesa
    closeMeseraModal();

    // Mostrar modal de confirmacion
    setTimeout(() => {
        const modalConfirm = document.getElementById('modalConfirmacion');
        modalConfirm.classList.add('active');
        document.body.style.overflow = 'hidden';
    }, 500);
}

// =============================================
// CERRAR MODALES CON CLICK AFUERA
// =============================================

document.addEventListener('click', function(e) {
    const modalMesera = document.getElementById('modalMesera');
    const modalConfirm = document.getElementById('modalConfirmacion');

    if (e.target === modalMesera) {
        closeMeseraModal();
    }

    if (e.target === modalConfirm) {
        closeConfirmacionModal();
    }
});

// =============================================
// CERRAR MODALES CON ESC
// =============================================

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMeseraModal();
        closeConfirmacionModal();
    }
});

// =============================================
// ANIMACION SHAKE PARA ERROR
// =============================================

const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-10px); }
        40%, 80% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);
