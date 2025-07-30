<?php
// Iniciar la sesión
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Verificar sesión y rol
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 2) {
    header("Location: ../../../../index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/app/assets/css/paginainiciostyle.css"> <!--enlace a estilos css-->
    <title>Página de Inicio</title>
</head>

<body>
    <?php
    // Verificar si hay un mensaje de error al eliminar la cuenta
    if (isset($_SESSION['error_message'])) {
        echo '<div id="error-message" class="error-message">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']); // Limpiar el mensaje de error después de mostrarlo
    }
    ?>

    <div class="menu" id="menu">
    <ion-icon name="menu-outline" id="menuIcon"></ion-icon>
    <ion-icon name="close-outline" id="closeIcon" style="display: none;"></ion-icon>
</div>

<div class="barra-lateral" id="barraLateral">
    <div class="nombre-pagina">
        <ion-icon id="cloud" name="cloud-outline"></ion-icon>
        <span>Menu</span>
    </div>

    <nav class="navegacion">
        <ul>
            <li>
                <a href="/app/pages/perfil.php">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    <span>Perfil</span>
                </a>
            </li>
            
            <li>
                <a href="/app/pages/mis_calificaciones.php">
                    <ion-icon name="star-outline"></ion-icon>
                    <span>Mis Calificaciones</span>
                </a>
            </li>

            <li>
                <a href="/app/pages/documentos_mototaxistas_usu.php">
                    <ion-icon name="newspaper-outline"></ion-icon>
                    <span>Mis documentos</span>
                </a>
            </li>
      
            
            <li>
                <a href="/app/pages/ayuda.php">
                    <ion-icon name="help-circle-outline"></ion-icon>
                    <span>Centro de ayuda</span>
                </a>
            </li>
        </ul>
        
   <li>
    <a href="/app/pages/pagar_servicio_mototaxista.php" id="pagarServicioMototaxista">
        <ion-icon name="cash-outline"></ion-icon>
        <span>Pagar al mototaxista</span>
    </a>
</li>
<li>
    <a href="/app/pages/pagar_retencion_app.php" id="pagarUsoApp">
        <ion-icon name="card-outline"></ion-icon>
        <span>Pagar uso de la app</span>
    </a>
</li>


        
        <li>
            <a href="/index.php?vista=logout">
                <ion-icon name="power-outline"></ion-icon>
                <span>Salir</span>
            </a>
        </li>
        
        <div class="modo-oscuro">
            <div class="info">
                <ion-icon name="moon-outline"></ion-icon>
                <span>Modo oscuro</span>
            </div>
            <div class="switch">
                <div class="base">
                    <div class="circulo"></div>
                </div>
            </div>
        </div>
    </nav>
</div>



<!-- Modales ocultos -->
<div id="eliminarCuentaModal" class="modal" style="display: none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <p>¿Estás seguro que deseas eliminar tu cuenta?</p>
    <form method="POST" action="/app/include/eliminar_cuenta.php">
      <ul class="modal-ul">
        <li>
          <span class="mensa">Ingresa tu contraseña para eliminar</span>
          <input class="password" type="password" name="password" id="passwordInput" required>
        </li>
        <li><button type="submit" id="confirmarEliminarBtn">Confirmar</button></li>
      </ul>
    </form>
  </div>
</div>

<div id="politicasModal" class="modal" style="display: none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Políticas y privacidad</h2>
    <p>Aquí van las políticas y la información de privacidad.</p>
  </div>
</div>





    <div class="contenedor">

        <div class="info">
            <h2>Bienvenido <?php echo $_SESSION['usuario']; ?></h2>
            <?php if (isset($_SESSION['mensaje'])) {
                echo '<p class="mensaje">' . $_SESSION['mensaje'] . '</p>';
                unset($_SESSION['mensaje']);
            } ?>
<!-- Contenedor donde se insertarán los mensajes AJAX -->
<div id="mensaje-ajax"></div>


            <hr>
            <p>¿QUÉ QUIERES HACER HOY?</p>
        </div>

        <div class="logo"></div>
        <div class="bottom_part"></div>
        <div class="salir"></div>

        <div class="cliente">
            <a href="/app/pages/solicitud.php" id="ser-cliente">Quiero Ser Cliente</a>
            <a onclick="mostrarAlerta(event)" id="ser-mototaxista">Quiero Ser Mototaxista</a>
        </div>
    </div>

    <!-- Ícono de accesibilidad para abrir el panel -->
    <div id="accessibility-icon">
    <ion-icon name="accessibility-outline"></ion-icon>
    </div>

    <!-- Controles de accesibilidad, ocultos inicialmente -->
    <div id="accessibility-panel" class="accessibility-controls" style="display: none;">
        <button id="increaseText">Aumentar letra</button>
        <button id="decreaseText">Disminuir letra</button>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
  var userId = <?= json_encode($_SESSION['id_usuario'] ?? null) ?>;
</script>

    <script src="/app/assets/js/funcionalidad.js"></script>
    <script src="/app/assets/js/script.js"></script>
    
<script>
    // Esperar 5 segundos y luego ocultar los mensajes
    setTimeout(function() {
    var mensaje = document.querySelector('.mensaje');        
    if (mensaje) {
        mensaje.style.display = 'none';
    }

    var error = document.querySelector('.error-message'); // ✅ define error
    if (error) {
        error.style.display = 'none';
    }
}, 5000);
 // 5000 milisegundos = 5 segundos
</script>

<script>
setInterval(function () {
    fetch('/app/include/ver_mensajes_ajax.php')
        .then(response => response.text())
        .then(data => {
            if (data.trim()) {
                const contenedor = document.getElementById('mensaje-ajax');
                contenedor.innerHTML = data;

                // Ocultar después de 5 segundos
                setTimeout(() => {
                    contenedor.innerHTML = '';
                }, 5000);
            }
        });
}, 5000);
</script>

</body>

</html>
