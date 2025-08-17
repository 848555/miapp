<?php
session_start();
include(__DIR__ . '../../../config/conexion.php'); // 👈 Usamos tu conexión centralizada

$id_retencion = $_POST['id_retencion'];
$fecha = date('Y-m-d H:i:s');

// Actualizar la retención como pagada (simulado)
$sql = "UPDATE retenciones SET pagado = 1, fecha = '$fecha' WHERE id = $id_retencion";

if ($conexion->query($sql) === TRUE) {
    $_SESSION['mensaje'] = "✅ Retención pagada exitosamente (simulado).";
} else {
    $_SESSION['mensaje'] = "❌ Error al pagar la retención: " . $conexion->error;
}

$conexion->close();
header("Location: /app/pages/pagar_retencion_app.php");
exit();
?>
