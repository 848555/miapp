<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include(__DIR__ . '../../../config/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$estado = isset($_POST['estado']) ? intval($_POST['estado']) : 0;

// Actualizar el estado en la base de datos
$sql = "UPDATE mototaxistas_en_linea 
        SET en_linea = ?, en_servicio = 0 
        WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $estado, $id_usuario);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'estado' => $estado]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar estado']);
}

$stmt->close();
$conexion->close();
