<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

include(__DIR__ . '../../../config/conexion.php');

$id_usuario = $_SESSION['id_usuario'];

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $telefono = $_POST['telefono'];
    $departamento = $_POST['departamento'];
    $ciudad = $_POST['ciudad'];
    $direccion = $_POST['direccion'];
    $usuario = $_POST['usuario'];
    $nueva_contraseña = $_POST['nueva_contraseña'];

    if (!empty($nueva_contraseña)) {
        $nueva_contraseña = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
        $query = "UPDATE usuarios SET telefono=?, Departamento=?, Ciudad=?, Direccion=?, Usuario=?, Password=? WHERE id_usuarios=?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssssssi", $telefono, $departamento, $ciudad, $direccion, $usuario, $nueva_contraseña, $id_usuario);
    } else {
        $query = "UPDATE usuarios SET telefono=?, Departamento=?, Ciudad=?, Direccion=?, Usuario=? WHERE id_usuarios=?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssssi", $telefono, $departamento, $ciudad, $direccion, $usuario, $id_usuario);
    }

    if ($stmt->execute()) {
        $_SESSION['success_mensaje'] = "¡Datos actualizados con éxito!";
    } else {
        $_SESSION['error_message'] = "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
    header("Location: perfil.php");
    exit();
}

// Obtener datos del usuario + nombres
$query = "SELECT u.*, d.departamentos AS nombre_departamento, c.ciudades AS nombre_ciudad
          FROM usuarios u
          LEFT JOIN departamentos d ON u.Departamento = d.id_departamentos
          LEFT JOIN ciudades c ON u.Ciudad = c.id_ciudades
          WHERE u.id_usuarios = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$perfil_usuario = $result->fetch_assoc();
$stmt->close();

// Obtener todos los departamentos
$query_departamentos = "SELECT * FROM departamentos";
$resultado_departamentos = $conexion->query($query_departamentos);

// Obtener ciudades del departamento actual
$ciudades_usuario = [];
if (!empty($perfil_usuario['Departamento'])) {
    $stmt_ciudades = $conexion->prepare("SELECT * FROM ciudades WHERE id_departamentos = ?");
    $stmt_ciudades->bind_param("i", $perfil_usuario['Departamento']);
    $stmt_ciudades->execute();
    $resultado_ciudades = $stmt_ciudades->get_result();
    while ($ciudad = $resultado_ciudades->fetch_assoc()) {
        $ciudades_usuario[] = $ciudad;
    }
    $stmt_ciudades->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="/app/assets/css/perfil_style.css">
</head>
<body>
    <div class="container">
     <div class="contenido">
        <a href="/app/pages/inicio.php?uid=<?= $_SESSION['id_usuario'] ?>&php=<?= uniqid(); ?>">
        </a>
        <h2>Perfil <?= $_SESSION['usuario'] ?></h2>

        <?php if (isset($_SESSION['success_mensaje'])): ?>
            <div class="alert-message alert-message-success">
                <?= $_SESSION['success_mensaje']; unset($_SESSION['success_mensaje']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert-message alert-message-error">
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($perfil_usuario): ?>
            <form action="perfil.php" method="POST">
                <p><strong>Nombres:</strong> <?= $perfil_usuario['Nombres'] ?></p>
                <p><strong>Apellidos:</strong> <?= $perfil_usuario['Apellidos'] ?></p>
                <p><strong>DNI:</strong> <?= $perfil_usuario['DNI'] ?></p>
                <p><strong>Fecha de Nacimiento:</strong> <?= $perfil_usuario['fecha_de_nacimiento'] ?></p>
                <p><strong>Teléfono:</strong>
                    <input type="text" name="telefono" value="<?= $perfil_usuario['telefono'] ?>" required>
                </p>

                <div class="mb-3">
                    <label for="departamento" class="form-label">Departamento</label>
                    <select name="departamento" id="departamento" class="form-select" onchange="getCiudades()" required>
                        <option value="">Selecciona un departamento</option>
                        <?php while ($dep = $resultado_departamentos->fetch_assoc()): ?>
                            <option value="<?= $dep['id_departamentos'] ?>" <?= $dep['id_departamentos'] == $perfil_usuario['Departamento'] ? 'selected' : '' ?>>
                                <?= $dep['departamentos'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <select name="ciudad" id="ciudad" class="form-select" required>
                        <option value="">Selecciona una ciudad</option>
                        <?php foreach ($ciudades_usuario as $ciu): ?>
                            <option value="<?= $ciu['id_ciudades'] ?>" <?= $ciu['id_ciudades'] == $perfil_usuario['Ciudad'] ? 'selected' : '' ?>>
                                <?= $ciu['ciudades'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <p><strong>Dirección:</strong>
                    <input type="text" name="direccion" value="<?= $perfil_usuario['Direccion'] ?>" required>
                </p>

                <p><strong>Usuario:</strong>
                    <input type="text" name="usuario" value="<?= $perfil_usuario['Usuario'] ?>" required>
                </p>

                <p><strong>Contraseña:</strong>
                    <input type="password" name="nueva_contraseña" placeholder="Nueva contraseña (opcional)">
                </p>

                <p><strong>Estado:</strong> <?= $perfil_usuario['Estado'] ?></p>
                
        <a href="/app/pages/inicio.php" class="btn">Regresar</a>

                <button type="submit" class="btn">Guardar Cambios</button>
            </form>
        <?php else: ?>
            <p>No se encontraron datos del perfil.</p>
        <?php endif; ?>
    </div>
 </div>
    <script>
    function getCiudades() {
        var departamentoId = document.getElementById("departamento").value;
        var ciudadSelect = document.getElementById("ciudad");
        ciudadSelect.innerHTML = '<option value="">Cargando ciudades...</option>';

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "/admin/include/obtener_ciudades.php?departamento=" + departamentoId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var ciudades = JSON.parse(xhr.responseText);
                ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
                ciudades.forEach(function (ciudad) {
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

    <script>
    setTimeout(() => {
        document.querySelector('.alert-message-success')?.style.setProperty("display", "none");
        document.querySelector('.alert-message-error')?.style.setProperty("display", "none");
    }, 5000);
    </script>
        <script src="/app/assets/js/script.js"></script>

</body>
</html>
