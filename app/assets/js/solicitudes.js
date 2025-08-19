
document.addEventListener('DOMContentLoaded', () => {
    fetchSolicitudes();
    setInterval(fetchSolicitudes, 5 * 60 * 1000);

    const terminarServicioModal = document.getElementById('terminarServicioModal');
    terminarServicioModal.addEventListener('click', (e) => {
        if (e.target === terminarServicioModal) closeTerminarServicioModal();
    });

    document.getElementById('calificarClienteForm').addEventListener('submit', (e) => {
        e.preventDefault();

        const idSolicitud = document.getElementById('id_solicitud').value;
        const idUsuarios = document.getElementById('id_usuarios').value;
        const rating = document.getElementById('rating').value;
        const comentarios = document.getElementById('comentarios').value;

        fetch('/app/include/calificar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_solicitud: idSolicitud, id_usuarios: idUsuarios, rating, comentarios })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.success ? 'Calificación enviada correctamente' : 'Error al enviar la calificación');
            if (data.success) {
                closeCalificarModal();
                fetchSolicitudes();
            }
        })
        .catch(console.error);
    });

    // Mostrar mensajes
    ['success-message', 'error-message'].forEach(id => {
        const msg = document.getElementById(id);
        if (msg) {
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 5000);
        }
    });

   // Escuchar asignaciones
let ultimaSolicitudMostrada = null; // 👈 variable global

document.addEventListener('DOMContentLoaded', () => {
    fetchSolicitudes();
    setInterval(fetchSolicitudes, 5 * 60 * 1000);

    const terminarServicioModal = document.getElementById('terminarServicioModal');
    terminarServicioModal.addEventListener('click', (e) => {
        if (e.target === terminarServicioModal) closeTerminarServicioModal();
    });

    document.getElementById('calificarClienteForm').addEventListener('submit', (e) => {
        e.preventDefault();

        const idSolicitud = document.getElementById('id_solicitud').value;
        const idUsuarios = document.getElementById('id_usuarios').value;
        const rating = document.getElementById('rating').value;
        const comentarios = document.getElementById('comentarios').value;

        fetch('/app/include/calificar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_solicitud: idSolicitud, id_usuarios: idUsuarios, rating, comentarios })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.success ? 'Calificación enviada correctamente' : 'Error al enviar la calificación');
            if (data.success) {
                closeCalificarModal();
                fetchSolicitudes();
            }
        })
        .catch(console.error);
    });
});

// ================== FUNCIÓN FETCH ==================
function fetchSolicitudes() {
    fetch('/app/include/obtener_solicitudes.php')
        .then(res => res.json())
        .then(data => {
            if (data.solicitud) {
                const idSolicitud = data.solicitud.id_solicitud;

                // Revisar si ya fue rechazada en localStorage
                let rechazadas = JSON.parse(localStorage.getItem('rechazadas') || '[]');
                if (!rechazadas.includes(idSolicitud) && idSolicitud !== ultimaSolicitudMostrada) {
                    mostrarSolicitud(data.solicitud);
                    ultimaSolicitudMostrada = idSolicitud;
                }
            }
        })
        .catch(console.error);
}

// ================== RECHAZAR SOLICITUD ==================
function rechazarSolicitud(idSolicitud) {
    let rechazadas = JSON.parse(localStorage.getItem('rechazadas') || '[]');
    if (!rechazadas.includes(idSolicitud)) {
        rechazadas.push(idSolicitud);
        localStorage.setItem('rechazadas', JSON.stringify(rechazadas));
    }

    ultimaSolicitudMostrada = null; // 🔄 reset para que llegue otra
    fetchSolicitudes(); // pedir la siguiente
}

    // Mostrar mensajes
    ['success-message', 'error-message'].forEach(id => {
        const msg = document.getElementById(id);
        if (msg) {
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 5000);
        }
    });

    // Escuchar asignaciones
    setInterval(escucharAsignaciones, 10000);
});

function fetchSolicitudes(page = 1) {
    fetch(`/app/include/obtener_solicitudes.php?page=${page}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('solicitudes-container');
            const pagination = document.getElementById('pagination');
            container.innerHTML = '';
            pagination.innerHTML = '';

            if (data.solicitudes.length > 0) {
                data.solicitudes.forEach(row => {
                    const div = document.createElement('div');
                    div.classList.add('solicitud');
                    div.innerHTML = `
                        <h3>${row.Nombres}</h3>
                        <h3>${row.Apellidos}</h3>
                        <p><strong>Origen:</strong> ${row.origen}</p>
                        <p><strong>Destino:</strong> ${row.destino}</p>
                        <p><strong>Personas:</strong> ${row.cantidad_personas}</p>
                        <p><strong>Motos:</strong> ${row.cantidad_motos}</p>
                        <p><strong>Método:</strong> ${row.metodo_pago}</p>
                        <p><a href='/app/include/aceptar_solicitud.php?id_solicitud=${row.id_solicitud}&id_usuario=${row.id_usuarios}'>Aceptar Solicitud</a></p>
                        <p><button class="btn1" onclick="openCalificarModal(${row.id_solicitud}, ${row.id_usuarios})">Calificar al Cliente</button></p>
                        <p><button class="btn2" onclick="terminarServicio(${row.id_solicitud})">Terminar Servicio</button></p>
                    `;
                    container.appendChild(div);
                });

                for (let i = 1; i <= data.total_pages; i++) {
                    const pageLink = document.createElement('a');
                    pageLink.classList.add('page-link');
                    if (i === page) pageLink.classList.add('active');
                    pageLink.href = `javascript:fetchSolicitudes(${i})`;
                    pageLink.textContent = i;
                    pagination.appendChild(pageLink);
                }
            } else {
                container.innerHTML = '<p>No se encontraron registros.</p>';
            }
        })
        .catch(console.error);
}

function terminarServicio(idSolicitud) {
    document.getElementById('id_solicitud_terminar').value = idSolicitud;
    document.getElementById('terminarServicioModal').style.display = 'block';
}

function closeTerminarServicioModal() {
    document.getElementById('terminarServicioModal').style.display = 'none';
}

function openCalificarModal(idSolicitud, idUsuarios) {
    document.getElementById('id_solicitud').value = idSolicitud;
    document.getElementById('id_usuarios').value = idUsuarios;
    document.getElementById('calificarClienteModal').style.display = 'block';
}

function closeCalificarModal() {
    document.getElementById('calificarClienteModal').style.display = 'none';
}

function escucharAsignaciones() {
    fetch('/app/include/asignar_solicitudes.php')
        .then(res => res.json())
        .then(data => {
            if (data.asignada && data.id_usuario == userId) {
                // ✅ solo mostrar confirm si la solicitud es nueva
                if (ultimaSolicitudMostrada !== data.solicitud.id_solicitud) {
                    ultimaSolicitudMostrada = data.solicitud.id_solicitud;
                    if (confirm(`Tienes una solicitud de ${data.solicitud.origen} a ${data.solicitud.destino}. ¿Aceptar?`)) {
                        window.location.href = `/app/include/aceptar_solicitud.php?id_solicitud=${data.solicitud.id_solicitud}&id_usuario=${data.solicitud.id_usuarios}`;
                    }
                }
            }
        })
        .catch(console.error);
}

