<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    header("Location: ../../../../index.php");
    exit;
}

$user_id = $_SESSION['id_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceptar servicio</title>
    <link rel="stylesheet" href="/app/assets/css/sermototaxista.css">
<!-- CORRECTO -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</head>

<body>
    <div class="contenedor">
       
             <h1>Solicitudes Por Aceptar</h1><br>
       <div class="estado-en-linea">
    <button id="toggleOnlineBtn" class="boton-estado" type="button">  
    <ion-icon id="estadoIcono" name="power-outline" size="large"></ion-icon>  
</button>  

</div>
<!-- Elemento de audio (puedes cambiar el archivo de sonido si deseas) -->
<audio id="conexionSonido" src="/app/assets/sounds/conect.wav" preload="auto"></audio>

        <div id="asignacion-mensaje"></div>
        <a href="/app/pages/inicio.php" class="btn1">Regresar</a>
        <form id="verSolicitudes" action="/app/include/aceptar_solicitud.php" method="post">
        
            <?php
            if (isset($_SESSION['success_message'])) {
                echo "<div id='success-message' class='alert-message alert-message-success'>{$_SESSION['success_message']}</div>";
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['success_mensaje'])) {
                echo "<div id='success-mensaje' class='alert-mensaje alert-mensaje-success'>{$_SESSION['success_mensaje']}</div>";
                unset($_SESSION['success_mensaje']);
            }
            if (isset($_SESSION['error_message'])) {
                echo "<div id='error-message' class='alert-message alert-message-error'>{$_SESSION['error_message']}</div>";
                unset($_SESSION['error_message']);
            }
            if (isset($_SESSION['warning_message'])) {
                echo "<p style='color: blue;'>{$_SESSION['warning_message']}</p>";
                unset($_SESSION['warning_message']);
            }
            ?>
        </form>
    </div>

    <div class="table-container" id="solicitudes-container"></div>
    <div class="pagination" id="pagination"></div>

    <!-- Modales -->
    <div id="terminarServicioModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Terminar Servicio</h2>
            <form id="terminarServicioForm" action="/app/include/terminar_servicio.php" method="POST">
                <input type="hidden" id="id_solicitud_terminar" name="id_solicitud_terminar">
                <label for="pago_completo">¿Le pagaron el servicio?</label>
                <select id="pago_completo" name="pago_completo">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>

                <label for="cliente_ausente">¿El cliente estuvo ausente?</label>
                <select id="cliente_ausente" name="cliente_ausente">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>

                <button type="submit" class="btn1">Confirmar</button>
                <button type="button" onclick="closeTerminarServicioModal()" class="btn1">Cancelar</button>
            </form>
        </div>
    </div>

    <div id="calificarClienteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Calificar Cliente</h2>
            <form id="calificarClienteForm">
                <input type="hidden" id="id_solicitud" name="id_solicitud">
                <input type="hidden" id="id_usuarios" name="id_usuarios">

                <label for="rating">Calificación:</label>
                <select id="rating" name="rating">
                    <option value="5">Excelente</option>
                    <option value="4">Muy Bueno</option>
                    <option value="3">Bueno</option>
                    <option value="2">Regular</option>
                    <option value="1">Malo</option>
                </select>

                <label for="comentarios">Comentarios:</label>
                <textarea id="comentarios" name="comentarios"></textarea>

                <button type="submit" class="btn1">Enviar Calificación</button>
                <button type="button" onclick="closeCalificarModal()" class="btn1">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Accesibilidad -->
    <div id="accessibility-icon"><ion-icon name="accessibility-outline"></ion-icon></div>
    <div id="accessibility-panel" class="accessibility-controls" style="display: none;">
        <button id="increaseText">Aumentar letra</button>
        <button id="decreaseText">Disminuir letra</button>
    </div>





    <script src="/app/assets/js/script.js"></script>
    <script src="/app/assets/js/funcionalidad.js"></script>
    <script>
        const userId = <?php echo $user_id; ?>;
    </script>
    <script src="/app/assets/js/solicitudes.js"></script>
 <script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('toggleOnlineBtn');
    const icono = document.getElementById('estadoIcono');
    const audio = document.getElementById('conexionSonido');

    let conectado = false; // Estado inicial en memoria

    // 🔹 Consultar estado real al cargar
    fetch('/app/include/consultar_estado.php')
        .then(res => res.json())
        .then(data => {
            conectado = data.en_linea;
            actualizarBoton();
        })
        .catch(err => console.error("Error obteniendo estado:", err));

    btn.addEventListener('click', function () {
        conectado = !conectado;
        let estado = conectado ? 1 : 0;

        fetch('/app/include/toggle_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'estado=' + estado
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarBoton();
                if (estado === 1) {
                    audio.currentTime = 0; // 🔹 Reinicia el sonido
                    audio.play().catch(err => console.warn("Audio bloqueado:", err));
                }
            } else {
                alert(data.message || 'Error al cambiar estado');
            }
        })
        .catch(err => console.error("Error en toggle:", err));
    });

    function actualizarBoton() {
        if (conectado) {
            btn.classList.add('activo');
            icono.setAttribute('name', 'power');
        } else {
            btn.classList.remove('activo');
            icono.setAttribute('name', 'power-outline');
        }
    }
});
 </script>


    
            <script>
document.addEventListener("DOMContentLoaded", () => {
    const contenedor = document.getElementById("asignacion-mensaje");
    const userId = <?= $_SESSION['id_usuario'] ?>;
    let checking = false;

    // Función principal: asignar nuevas solicitudes y reintentar asignaciones
    async function procesarSolicitudes() {
        if (checking) return;
        checking = true;

        try {
            // 1️⃣ Asignar nuevas solicitudes
            const resAsignar = await fetch("/app/include/asignar_solicitud.php");
            const dataAsignar = await resAsignar.json();

            // Mostrar solicitud solo si es para el mototaxista actual
            if (dataAsignar.asignada && dataAsignar.mototaxista.id_usuario == userId) {
                mostrarSolicitud(dataAsignar.solicitud);
            } else {
                contenedor.innerHTML = "<p>No hay solicitudes pendientes para ti.</p>";
            }

            // 2️⃣ Reintentar asignaciones pendientes (cancelaciones/rechazos)
            await fetch("/app/include/reintentar_asignacion.php");

            checking = false;
        } catch (err) {
            checking = false;
            console.error("Error al procesar solicitudes:", err);
        }
    }

    // Mostrar solicitud en pantalla
    function mostrarSolicitud(solicitud) {
        contenedor.innerHTML = `
            <h2>Nueva solicitud asignada:</h2>
            <p><strong>Origen:</strong> ${solicitud.origen}</p>
            <p><strong>Destino:</strong> ${solicitud.destino}</p>
            <p><strong>Personas:</strong> ${solicitud.cantidad_personas}</p>
            <p><strong>Motos:</strong> ${solicitud.cantidad_motos}</p>
            <p><strong>Método de pago:</strong> ${solicitud.metodo_pago}</p>
            <button id="btnAceptar" data-id="${solicitud.id_solicitud}">Aceptar</button>
            <button id="btnRechazar" data-id="${solicitud.id_solicitud}">Rechazar</button>
        `;

        document.getElementById("btnAceptar").addEventListener("click", () => {
            manejarSolicitud("aceptar", solicitud.id_solicitud);
        });

        document.getElementById("btnRechazar").addEventListener("click", () => {
            manejarSolicitud("rechazar", solicitud.id_solicitud);
        });
    }

    // Función aceptar/rechazar
    async function manejarSolicitud(accion, idSolicitud) {
        const url = accion === "aceptar" 
            ? "/app/include/aceptar_solicitud.php"
            : "/app/include/rechazar_solicitud.php";

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id_solicitud=${idSolicitud}&id_usuario=${userId}`
            });

            const data = await res.json();
            alert(data.message);

            // Revisar nuevas solicitudes después de aceptar/rechazar
            procesarSolicitudes();

        } catch (err) {
            console.error(`Error al ${accion} solicitud:`, err);
        }
    }

    // Ejecutar cada 5 segundos
    setInterval(procesarSolicitudes, 5000);
    procesarSolicitudes(); // Primera ejecución al cargar
})
</script>

</body>
</html>
