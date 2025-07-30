
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control del Mototaxista</title>
    <link rel="stylesheet" href="/php/css/panel.css"> <!-- Estilos CSS -->
</head>
<body>
    <h2>Registro de Ingresos Semanales</h2>

    <!-- Mensajes de éxito o error -->
    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['success_message'])) {
        echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <!-- Formulario para registrar ingresos -->
    <form action="/php/procesar/registrar_ingresos.php" method="POST">
        <label for="ingresos_semana">Ingresos de la Semana:</label>
        <input type="number" name="ingresos_semana" id="ingresos_semana" required>
        <button type="submit">Registrar Ingresos</button>
    </form>

    <!-- Mostrar detalles de ingresos y tarifas a pagar -->
    <h3>Detalles Actuales:</h3>
    <p><strong>Ingresos Semanales:</strong> <?php echo $ingresos_semana; ?> pesos</p>
    <p><strong>Tarifa a Pagar:</strong> <?php echo $tarifa_a_pagar; ?> pesos</p>

</body>
</html>
