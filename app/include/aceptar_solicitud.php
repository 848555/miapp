<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

header('Content-Type: application/json; charset=utf-8');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Verificar si se ha proporcionado el ID de usuario y de solicitud
if (!isset($_POST['id_usuario']) || !isset($_POST['id_solicitud'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos: id_usuario o id_solicitud']);
    exit();
}

$id_usuario = intval($_POST['id_usuario']);
$id_solicitud = intval($_POST['id_solicitud']);

if ($id_usuario <= 0 || $id_solicitud <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario o solicitud no válido']);
    exit();
}

// Obtener la solicitud pendiente
$query = "SELECT * FROM solicitudes WHERE id_solicitud = ? AND estado = 'pendiente'";
$stmt = $conexion->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar consulta: ' . $conexion->error]);
    exit();
}

$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada o ya aceptada']);
    $stmt->close();
    $conexion->close();
    exit();
}

// Verificar si el mototaxista ya tiene una solicitud en curso
$sql_check_servicio = "SELECT COUNT(*) AS en_servicio FROM solicitudes WHERE id_usuarios = ? AND estado IN ('aceptada', 'en progreso')";
$stmt_check = $conexion->prepare($sql_check_servicio);
$stmt_check->bind_param("i", $id_usuario);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$en_servicio = $result_check->fetch_assoc()['en_servicio'] ?? 0;
$stmt_check->close();

if ($en_servicio > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya tienes una solicitud en curso. Termínala antes de aceptar otra.']);
    $stmt->close();
    $conexion->close();
    exit();
}

// Actualizar el estado de la solicitud a 'aceptada'
$sql_update = "UPDATE solicitudes SET estado='aceptada', id_usuarios=? WHERE id_solicitud=?";
$stmt_update = $conexion->prepare($sql_update);

if (!$stmt_update) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar actualización: ' . $conexion->error]);
    $stmt->close();
    $conexion->close();
    exit();
}

$stmt_update->bind_param("ii", $id_usuario, $id_solicitud);

if ($stmt_update->execute()) {
    // Insertar mensaje temporal
    $mensaje = "Tu solicitud ha sido aceptada.";
    $leido = 0;
    $sql_insert_mensaje = "INSERT INTO mensajes_temporales (id_usuario, id_solicitud, mensaje, fecha, leido) VALUES (?, ?, ?, NOW(), ?)";
    $stmt_insert = $conexion->prepare($sql_insert_mensaje);

    if ($stmt_insert) {
        $stmt_insert->bind_param("iisi", $id_usuario, $id_solicitud, $mensaje, $leido);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    echo json_encode(['success' => true, 'message' => 'Solicitud aceptada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la solicitud: ' . $conexion->error]);
}

$stmt_update->close();
$stmt->close();
$conexion->close();
?>
