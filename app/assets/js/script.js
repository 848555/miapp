document.addEventListener('DOMContentLoaded', function () {
    // === Menú lateral ===
    const menuIcon = document.getElementById('menuIcon');
    const closeIcon = document.getElementById('closeIcon');
    const barraLateral = document.getElementById('barraLateral');

    if (menuIcon && closeIcon && barraLateral) {
        menuIcon.addEventListener('click', function () {
            barraLateral.style.display = 'block';
            menuIcon.style.display = 'none';
            closeIcon.style.display = 'block';
        });

        closeIcon.addEventListener('click', function () {
            barraLateral.style.display = 'none';
            menuIcon.style.display = 'block';
            closeIcon.style.display = 'none';
        });
    }

=== Modo oscuro ===
document.addEventListener('DOMContentLoaded', () => {
    const modoOscuroSwitch = document.querySelector('.modo-oscuro .switch');
    const baseSwitch = document.querySelector('.modo-oscuro .base');
    const circuloSwitch = document.querySelector('.modo-oscuro .circulo');
    const body = document.body;

    if (!modoOscuroSwitch || !baseSwitch || !circuloSwitch) return; // evita errores si faltan

    // Aplicar estado guardado
    if (localStorage.getItem('modoOscuro') === 'enabled') {
        body.classList.add('modo-oscuro');
        baseSwitch.classList.add('activo');
        circuloSwitch.classList.add('activo');
    }

    // Manejar click
    modoOscuroSwitch.addEventListener('click', function () {
        const activo = body.classList.toggle('modo-oscuro');
        baseSwitch.classList.toggle('activo', activo);
        circuloSwitch.classList.toggle('activo', activo);
        localStorage.setItem('modoOscuro', activo ? 'enabled' : 'disabled');
    });
});




    // === Accesibilidad ===
    const accessibilityIcon = document.getElementById('accessibility-icon');
    const accessibilityPanel = document.getElementById('accessibility-panel');
    const decreaseText = document.getElementById('decreaseText');
    const increaseText = document.getElementById('increaseText');

    if (accessibilityIcon && accessibilityPanel) {
        // Mostrar/Ocultar panel de accesibilidad
        accessibilityIcon.addEventListener('click', function () {
            if (accessibilityPanel.style.display === 'none' || accessibilityPanel.style.display === '') {
                accessibilityPanel.style.display = 'block';
            } else {
                accessibilityPanel.style.display = 'none';
            }
        });

        // Aplicar configuración guardada
        if (localStorage.getItem('highContrast') === 'enabled') {
            document.body.classList.add('high-contrast');
        }

        if (localStorage.getItem('largeText') === 'enabled') {
            document.body.classList.add('large-text');
        }

        // Aumentar tamaño de texto
        if (decreaseText) {
            decreaseText.addEventListener('click', function () {
                document.body.classList.add('large-text');
                localStorage.setItem('largeText', 'enabled');
            });
        }

        // Disminuir tamaño de texto
        if (increaseText) {
            increaseText.addEventListener('click', function () {
                document.body.classList.remove('large-text');
                localStorage.setItem('largeText', 'disabled');
            });
        }

        // Cerrar panel si se hace clic fuera
        document.addEventListener('click', function (event) {
            const isClickInside = accessibilityPanel.contains(event.target) || accessibilityIcon.contains(event.target);
            if (!isClickInside) {
                accessibilityPanel.style.display = 'none';
            }
        });
    }
});
