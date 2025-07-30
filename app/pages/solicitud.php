<?php 
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Verificar si el id_usuario está definido en la sesión
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error_message'] = "No se ha iniciado sesión";
    header("Location: ../../../../index.php");
    exit();
}

// Obtener el id_usuario de la sesión
$id_usuario = $_SESSION['id_usuario']; 

// Realizar la consulta para obtener las solicitudes pendientes del usuario
$query = "SELECT * FROM solicitudes WHERE id_usuarios = ? AND estado = 'pendiente'";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$solicitudes_pendientes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Mototaxi</title>
    <link rel="stylesheet" href="/app/assets/css/solicitar.css"> <!-- Enlace al archivo CSS para estilos -->
    
</head>
<body>
    
<h2>Solicitudes Pendientes</h2>

<?php if (count($solicitudes_pendientes) > 0): ?>
    <?php foreach ($solicitudes_pendientes as $solicitud): ?>
        <div class="contenedor">
            <p><strong>Origen:</strong> <?php echo htmlspecialchars($solicitud['origen']); ?></p>
            <p><strong>Destino:</strong> <?php echo htmlspecialchars($solicitud['destino']); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($solicitud['estado']); ?></p>
            <form action="/app/include/cancelar_solicitud.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres cancelar esta solicitud?');">
                <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id_solicitud']; ?>">
                <button type="submit" class="btn btn-danger">Cancelar Solicitud</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="contenedor">
        <p>No tienes solicitudes pendientes.</p>
    </div>
<?php endif; ?>


    <h2>¿A dónde quieres ir?</h2>

    <!-- Mensajes de error y éxito -->
    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div id="error-message" class="error-message message">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['success_message'])) {
        echo '<div id="success-message" class="success-message message">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <form class="contenedor" action="/app/include/insertar.php" method="POST">
        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id_usuario']; ?>">

        <!-- Campos de solicitud -->
        <label for="origen">Dirección de origen:</label>
        <input type="text" name="origen" id="origen" required>

        <label for="destino">Dirección de destino:</label>
        <input type="text" name="destino" id="destino" required>

        <label for="personas">Número de personas:</label>
        <input type="number" name="personas" id="personas" min="1" value="1" required>

        <label for="cantidad">Cantidad de Motos:</label>
        <input type="number" name="cantidad" id="cantidad" min="1" value="1" required>

        <label for="pago">Método de pago:</label>
        <select name="pago" id="pago" required>
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Nequi</option>
        </select>
        <a href="/app/pages/inicio.php">Regresar</a>
        <button>Solicitar Mototaxi</button>
    </form>

    <script>
        // Ocultar los mensajes de error y éxito después de 5 segundos
        setTimeout(function() {
            var error_message = document.getElementById('error-message');
            var success_message = document.getElementById('success-message');

            if (error_message) {
                error_message.style.display = 'none';
            }
            if (success_message) {
                success_message.style.display = 'none';
            }
        }, 5000); // 5000 milisegundos = 5 segundos
    </script>
    <script src="/app/assets/js/script.js"></script>

</body>
</html>
