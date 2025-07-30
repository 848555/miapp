<?php
include(__DIR__ . '/../config/conexion.php');
session_start();

$token = $_GET['token'] ?? '';
$mensaje = '';
$mostrarFormulario = false;

if ($token) {
    // Verificar si el token existe y no ha expirado
    $stmt = $conexion->prepare("SELECT * FROM recuperacion_password WHERE token = ? AND expiracion > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $datos = $resultado->fetch_assoc();
        $telefono = $datos['telefono'];
        $mostrarFormulario = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nueva_contrasena'])) {
            $nueva_contrasena = trim($_POST['nueva_contrasena']);

            // Actualizar contraseña del usuario sin encriptar
            $stmt_update = $conexion->prepare("UPDATE usuarios SET password = ? WHERE telefono = ?");
            $stmt_update->bind_param("ss", $nueva_contrasena, $telefono);
            $stmt_update->execute();

            // Eliminar el token
            $stmt_delete = $conexion->prepare("DELETE FROM recuperacion_password WHERE token = ?");
            $stmt_delete->bind_param("s", $token);
            $stmt_delete->execute();

            $mensaje = "Contraseña actualizada correctamente. Ahora puedes iniciar sesión.";
            $mostrarFormulario = false;
        }
    } else {
        $mensaje = "El enlace de recuperación es inválido o ha expirado.";
    }
} else {
    $mensaje = "Token no proporcionado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="/app/assets/css/recordar_contraseñastyle.css">
</head>
<body>
<div class="form-recuperar">
    <h2>Restablecer Contraseña</h2>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?= $mensaje ?></p>
    <?php endif; ?>

    <?php if ($mostrarFormulario): ?>
    <form method="POST">
        <input type="password" name="nueva_contrasena" placeholder="Nueva contraseña" required>
        <button type="submit">Actualizar Contraseña</button>
    </form>
    <?php endif; ?>
</div>
   <script>
        // Esperar 5 segundos (5000 ms) y ocultar el mensaje
        setTimeout(function() {
            var mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.display = 'none';
            }
        }, 5000);
    </script
</body>
</html>
