<?php
session_start();
include(__DIR__ . '../../config/conexion.php');

$sql_departamentos = "SELECT id_departamentos, departamentos FROM departamentos";
$resultado_departamentos = $conexion->query($sql_departamentos);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <main>
        <div class="contenedor__todo">
            <div class="contenedor__register">
                <form action="/include/registro_usuario.php" method="POST" class="formulario__register">
                    <h2>Regístrarse</h2>
                    <input type="text" placeholder="Nombres" name="nombres" id="nombres">
                    <input type="text" placeholder="Apellidos" name="apellidos" id="apellidos">
                    <input type="text" placeholder="Documento de Identidad" name="dni" id="dni">
                    <input type="date" placeholder="Fecha de Nacimiento" name="fecha" id="fecha">
                    <input type="text" placeholder="Telefono" name="telefono" id="telefono"> <br><br>
                    
                    <div class="mb-3">
                        <label for="departamento" class="form-label">Departamento</label>
                        <select name="departamento" id="departamento" class="form-select" onchange="getCiudades()">
                            <option value="">Selecciona un departamento</option>
                            <?php
                            while ($departamento = $resultado_departamentos->fetch_assoc()) {
                                echo '<option value="' . $departamento['id_departamentos'] . '">' . $departamento['departamentos'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ciudad" class="form-label">Ciudad</label>
                        <select name="ciudad" id="ciudad" class="form-select">
                            <option value="">Selecciona una ciudad</option>
                        </select>
                    </div>

                    <input type="text" placeholder="Direccion de residencia" name="direccion" id="direccion">
                    <input type="text" placeholder="Usuario" name="usuario" id="usuario">
                    
                    <div class="password-container">
                        <input type="password" placeholder="Contraseña" name="contraseña" id="contraseña" class="password-input">
                        <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('contraseña')"></i>
                    </div>
                    
                   

                    <div class="button-container">
                    <button type="submit" class="btn btn-principal" name="btnregistrar" value="ok">Regístrarme</button>
                    <a class="recordar" href="/index.php">Regresar</a>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script>
        function getCiudades() {
            var departamentoId = document.getElementById("departamento").value;
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "/admin/include/obtener_ciudades.php?departamento=" + departamentoId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var ciudades = JSON.parse(xhr.responseText);
                    var ciudadSelect = document.getElementById("ciudad");
                    ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
                    ciudades.forEach(function(ciudad) {
                        var option = document.createElement("option");
                        option.value = ciudad.id_ciudades;
                        option.textContent = ciudad.ciudades;
                        ciudadSelect.appendChild(option);
                    });
                }
            };
            xhr.send();
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
</body>

</html>
