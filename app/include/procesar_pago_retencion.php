<?php
session_start();
$id_retencion = $_POST['id_retencion'];
$fecha = date('Y-m-d H:i:s');

$conn = new mysqli("localhost", "root", "", "mototaxi");

// Actualizar la retención como pagada (simulado)
$sql = "UPDATE retenciones SET pagado = 1, fecha = '$fecha' WHERE id = $id_retencion";

if ($conexion->query($sql) === TRUE) {
    $_SESSION['mensaje'] = "✅ Retención pagada exitosamente (simulado).";
} else {
    $_SESSION['mensaje'] = "❌ Error al pagar la retención: " . $conn->error;
}

$conn->close();
header("Location: /app/pages/pagar_retencion_app.php");
exit();
?>
