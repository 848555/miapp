<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Verificar si el ID de usuario está presente en la sesión
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error_message'] = "No se ha iniciado sesión.";
    header("Location: ../../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Sanitizar entradas
$origen = trim($_POST['origen']);
$destino = trim($_POST['destino']);
$cantidad_personas = intval($_POST['personas']);
$cantidad_motos = intval($_POST['cantidad']);
$metodo_pago = trim($_POST['pago']);

// Definir la tarifa y la retención
$tarifa = 4000;
$retencion = 1000;

// Calcular totales
$costo_total = $cantidad_motos * $tarifa;
$retencion_total = $cantidad_motos * $retencion;

// Validar campos requeridos
if (empty($origen) || empty($destino) || !isset($cantidad_personas) || !isset($cantidad_motos) || empty($metodo_pago)) {
    $_SESSION['error_message'] = "Faltan datos necesarios para la solicitud.";
    header("Location: ../../../../app/pages/solicitud.php");
    exit();
}

// Verificar solicitud pendiente
$query = "SELECT * FROM solicitudes WHERE id_usuarios = ? AND estado = 'pendiente'";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error_message'] = "Ya tienes una solicitud pendiente. Por favor espera.";
    $stmt->close();
    header("Location: ../../../../app/pages/solicitud.php");
    exit();
}
$stmt->close();

// Insertar nueva solicitud con pago_completo en 0
$insertQuery = "INSERT INTO solicitudes (origen, destino, cantidad_personas, cantidad_motos, metodo_pago, estado, id_usuarios, costo_total, retencion_total, pago_completo) VALUES (?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, 0)";
$stmt_insert = $conexion->prepare($insertQuery);
$stmt_insert->bind_param("ssiiisii", $origen, $destino, $cantidad_personas, $cantidad_motos, $metodo_pago, $id_usuario, $costo_total, $retencion_total);

if ($stmt_insert->execute()) {
    $_SESSION['success_message'] = "Solicitud realizada con éxito. Costo total: $costo_total.";
} else {
    $_SESSION['error_message'] = "Hubo un error al realizar la solicitud.";
}

$stmt_insert->close();
$conexion->close();

header("Location: ../../../../app/pages/solicitud.php");
exit();
?>
