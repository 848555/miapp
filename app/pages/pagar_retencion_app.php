<?php
include(__DIR__ . '../../../config/conexion.php');
session_start();
$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT * FROM retenciones WHERE id_usuarios = $id_usuario AND pagado = 0";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pagar uso de la aplicación</title>
        <link rel="stylesheet" href="/app/assets/css/retenciones.css">

</head>
<body class="bg-light">


<div class="container mt-5">
    <h2 class="mb-4 text-center">Pagar uso de la aplicación</h2>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <form action="/app/procesar_pago_retencion.php" method="POST">
            <div class="tarjetas-solicitudes">
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <div class="tarjeta-solicitud">
                        <p><strong>Solicitud:</strong> #<?= $row['id_solicitud'] ?></p>
                        <p><strong>Monto:</strong> $<?= number_format($row['monto'], 0, ',', '.') ?></p>
                        <p><strong>Retención:</strong> $<?= number_format($row['retencion'], 0, ',', '.') ?></p>
                        <p><strong>Fecha:</strong> <?= $row['fecha'] ?></p>
                        <div class="radio-container">
                            <label>
                                <input type="radio" name="id_retencion" value="<?= $row['id'] ?>" required>
                                Seleccionar esta retención
                            </label>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="botones-container mt-4">
                <button type="submit" class="btn btn-success">Simular pago con Nequi</button>
             
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info text-center">
            No tienes retenciones pendientes por pagar.
        </div>
    <?php endif; ?>
 <br></br>
    <br></br>
  <!-- Botón regresar dentro del container -->
    <div class="mt-4">
           <a href="/app/pages/inicio.php" class="btn btn-success">Regresar</a>
    </div>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-warning mt-4 text-center">
            <?= $_SESSION['mensaje']; ?>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
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
        <script src="/app/assets/js/funcionalidad.js"></script>
    <script src="/app/assets/js/script.js"></script>

</body>
</html>

<?php
// ✅ Ahora sí, después de usar los datos
$resultado->free();
$conexion->close();
?>
