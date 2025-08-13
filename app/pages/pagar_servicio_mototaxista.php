<?php
include(__DIR__ . '../../../config/conexion.php');
session_start();
$id_usuario = $_SESSION['id_usuario'] ?? null;


if (!$id_usuario) {
    die("Usuario no autenticado.");
}

// CONSULTA CORREGIDA:
$stmt = $conexion->prepare("SELECT * FROM solicitudes WHERE id_usuarios = ? AND (pago_completo = 0 OR pago_completo IS NULL) AND estado = 'aceptada'");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pagar al mototaxista</title>
    <link rel="stylesheet" href="/app/assets/css/retenciones.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4 text-center">Pagar al mototaxista</h2>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <form action="/app/procesar_pago_mototaxista.php" method="POST">
            <div class="tarjetas-solicitudes">
    <?php while ($row = $resultado->fetch_assoc()): ?>
        <div class="tarjeta-solicitud">
            <p><strong>Solicitud:</strong> #<?= $row['id_solicitud'] ?></p>
            <p><strong>Origen:</strong> <?= htmlspecialchars($row['origen']) ?></p>
            <p><strong>Destino:</strong> <?= htmlspecialchars($row['destino']) ?></p>
            <p><strong>Personas:</strong> <?= $row['cantidad_personas'] ?></p>
            <p><strong>Motos:</strong> <?= $row['cantidad_motos'] ?></p>
            <p><strong>Método de Pago:</strong> <?= htmlspecialchars($row['metodo_pago']) ?></p>
            <p><strong>Costo Total:</strong> $<?= number_format($row['costo_total'], 0, ',', '.') ?></p>
            <div class="radio-container">
                <label>
                    <input type="radio" name="id_solicitud" value="<?= $row['id_solicitud'] ?>" required>
                    Seleccionar esta solicitud
                </label>
            </div>
        </div>
    <?php endwhile; ?>
</div>


            <div class="botones-container mt-4">
                <button type="submit" class="btn btn-success">Pagar</button>
             
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info text-center">
            No tienes pagos pendientes al mototaxista.
        </div>
    <?php endif; ?>
  <!-- Botón regresar dentro del container -->
    <div class="mt-4">
        <a href="/app/pages/inicio.php" class="btn-regresar">Regresar</a>
    </div>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-warning mt-4 text-center">
            <?= $_SESSION['mensaje']; ?>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
</div>


<!-- Ícono de accesibilidad -->
<div id="accessibility-icon">
    <ion-icon name="accessibility-outline"></ion-icon>
</div>

<!-- Controles de accesibilidad -->
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
