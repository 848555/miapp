<?php 

// Iniciar la sesión
session_start();

// Verificar si el usuario está autenticado
$validar = $_SESSION['usuario'];

// Si el usuario no está autenticado, redirigir al formulario de inicio de sesión
if ($validar == null || $validar == '') {
    header("Location: ../../../../index.php");
    die();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>registro de documentos</title>
    <link rel="stylesheet" href="/app/assets/css/(registro documentos)style.css">
</head>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var successMessages = document.querySelectorAll('.success-message');
            var errorMessages = document.querySelectorAll('.error-message');
            var errorArchivoMessages = document.querySelectorAll('.error-archivo-message');

            successMessages.forEach(function(message) {
                message.classList.add('hide-message');
            });

            errorMessages.forEach(function(message) {
                message.classList.add('hide-message');
            });

            errorArchivoMessages.forEach(function(message) {
                message.classList.add('hide-message');
            });
        }, 5000); // 5000 ms = 5 segundos
    });
</script>
<body>
    <!--formulario para registrar los documentos  -->

    <div class="contenedor"><br>
        <form class="col-3 p-3 m-auto" method="POST" action="/app/include/upload.php" enctype="multipart/form-data">
            <h3>REGISTRAR DOCUMENTOS</h3> <br>
            <div class="message-container">
        <?php 
        // Mostrar mensaje de éxito si existe
        if (isset($_SESSION['success_message'])) {
            echo "<div class='success-message'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']); // Eliminar mensaje
        }
        // Mostrar mensaje de error si existe
        if (isset($_SESSION['error'])) {
            echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']); // Eliminar mensaje de error
        }
         // Mostrar mensaje de error si existe
         if (isset($_SESSION['error_archivo'])) {
            echo "<div class='error-message'>" . $_SESSION['error_archivo'] . "</div>";
            unset($_SESSION['error_archivo']); // Eliminar mensaje de error
        }
        
        ?> </div>
            <!--se incluye la conexion a la base de datos  -->

            <?php
include(__DIR__ . '../../../config/conexion.php');
?>


            <div class="input">
                <h4>LICENCIA DE CONDUCIR</h4>
                <input type="file" id="licencia_de_conducir"   name="licencia_de_conducir" >
            </div> <br>

            <div class="input">
                <h4>TARJETA DE PROPIEDAD</h4>
                <input type="file" id="tarjeta_de_propiedad"   name="tarjeta_de_propiedad" >
            </div> <br>

            <div class="input">
                <h4>SOAT</h4>
                <input type="file" id="soat"   name="soat" >
            </div> <br>



            <div class="input">
                <h4>TECNOMECANICA</h4>
                <input type="file" id="tecno_mecanica"   name="tecno_mecanica" >
            </div> <br>

            <div class="input">
                <h4>PLACA DE LA MOTO</h4>
                <input type="text" class="form-control" name="placa" id="placa">
            </div> <br>

            <div class="input">
                <h4>MARCA</h4>
                <input type="text" class="form-control" name="marca" id="marca">
            </div> <br>

            <div class="input">
                <h4>MODELO</h4>
                <input type="text" class="form-control" name="modelo" id="modelo">
            </div> <br>


            <div class="input">
                <h4>COLOR DE LA MOTO</h4>
                <input type="text" class="form-control" name="color" id="color" ><br> <br>
            </div> <br>


          
           

            <button type="submit" name="submit" class="btn1"  href="/php/sermototaxista.php" value="ok">REGISTRAR</button>
            <a href="/app/pages/inicio.php" class="btn2">CANCELAR</a>
            </form>
    </div>
    
    <script src="/app/assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</body>

</html>