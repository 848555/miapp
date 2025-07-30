<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: /index.php?vista=login");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Consultar calificaciones que el mototaxista ha dado al usuario (almacenadas en calificaciones_usuarios)
$query_clientes = "SELECT c.calificacion, c.comentario, c.fecha, u.Usuario AS cliente
                   FROM calificaciones_usuarios c
                   JOIN usuarios u ON c.id_usuario = u.id_usuarios
                   WHERE c.id_solicitud IN (SELECT id_solicitud FROM solicitudes WHERE id_usuarios = ?)";
$stmt_clientes = $conexion->prepare($query_clientes);
$stmt_clientes->bind_param("i", $id_usuario);
$stmt_clientes->execute();
$result_clientes = $stmt_clientes->get_result();

// Consultar calificaciones que el usuario ha recibido como cliente (almacenadas en calificaciones_mototaxistas)
$query_mototaxistas = "SELECT c.calificacion, c.comentario, c.fecha, u.Usuario AS mototaxista
                       FROM calificaciones_mototaxistas c
                       JOIN usuarios u ON c.id_usuario = u.id_usuarios
                       WHERE c.id_solicitud IN (SELECT id_solicitud FROM solicitudes WHERE id_usuarios = ?)";
$stmt_mototaxistas = $conexion->prepare($query_mototaxistas);
$stmt_mototaxistas->bind_param("i", $id_usuario);
$stmt_mototaxistas->execute();
$result_mototaxistas = $stmt_mototaxistas->get_result();

// Consultar el último mototaxista que prestó servicio al usuario
$query_ultimo_servicio = "SELECT u.Usuario AS mototaxista, r.id_solicitud, r.id_usuarios AS id_mototaxista
                          FROM retenciones r
                          JOIN usuarios u ON r.id_usuarios = u.id_usuarios
                          WHERE r.id_solicitud IN (SELECT id_solicitud FROM solicitudes WHERE id_usuarios = ?) 
                          ORDER BY r.fecha DESC LIMIT 1";


$stmt_ultimo_servicio = $conexion->prepare($query_ultimo_servicio);
$stmt_ultimo_servicio->bind_param("i", $id_usuario);
$stmt_ultimo_servicio->execute();
$result_ultimo_servicio = $stmt_ultimo_servicio->get_result();
$mototaxista = $result_ultimo_servicio->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/app/assets/css/calificaciones.css">
    <title>Mis Calificaciones</title>
</head>

<body>
<?php
                    if (isset($_SESSION['mensaje'])) {
                        echo '<p class="mensaje">' . $_SESSION['mensaje'] . '</p>';
                        unset($_SESSION['mensaje']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<p class="error" style="color: red;">' . $_SESSION['error'] . '</p>';
                        unset($_SESSION['error']);
                    }
                    ?>
                    
    <div class="container">
   <div class="contenido">
        <h1>Mis Calificaciones</h1>

        <h2>Calificaciones Recibidas</h2>
        <?php if ($result_clientes->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Puntuación</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_clientes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($row['calificacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['comentario']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes calificaciones recibidas como cliente.</p>
        <?php endif; ?>

        <h2>Calificaciones Dadas</h2>
        <?php if ($result_mototaxistas->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Puntuación</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_mototaxistas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['mototaxista']); ?></td>
                            <td><?php echo htmlspecialchars($row['calificacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['comentario']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes calificaciones dadas como mototaxista.</p>
        <?php endif; ?>

        <h2>Calificar Mototaxista</h2>
        <?php if ($mototaxista): ?>
            <?php if ($mototaxista['id_mototaxista'] != $id_usuario): ?>
                <form method="POST" action="/app/include/calificar_mototaxista.php">
                    <input type="hidden" name="id_solicitud" value="<?php echo $mototaxista['id_solicitud']; ?>">

                    <label for="calificacion">Mototaxista: <?php echo htmlspecialchars($mototaxista['mototaxista']); ?></label>
                    <br>

                    <label for="calificacion">Calificación (1-5):</label>
                    <input type="number" id="calificacion" name="calificacion" min="1" max="5" required>

                    <label for="comentario">Comentario:</label>
                    <textarea id="comentario" name="comentario" required></textarea>

                    <button type="submit">Enviar Calificación</button>
                </form>
            <?php else: ?>
                <p>No puedes calificarte a ti mismo.</p>

            <?php endif; ?>
        <?php else: ?>
            <p>No hay mototaxista disponible para calificar.</p>
        <?php endif; ?>
        <a href="/app/pages/inicio.php" class="btn">Regresar</a>
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
    <script src="/app/assets/js/script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="/app/assets/js/funcionalidad.js"></script>
</body>

</html>

<?php
$stmt_clientes->close();
$stmt_mototaxistas->close();
$stmt_ultimo_servicio->close();
$conexion->close();
?>