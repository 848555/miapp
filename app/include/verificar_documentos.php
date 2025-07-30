<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

$userId = $_SESSION['id_usuario']; // El ID del usuario debe estar en la sesión



if ($conexion->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para verificar si el usuario ya tiene documentos subidos
$sql = "SELECT COUNT(*) as documentosCount FROM documentos WHERE id_usuarios = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($documentosCount);
$stmt->fetch();
$stmt->close();
$conexion->close();

// Responder según si tiene o no documentos subidos
if ($documentosCount > 0) {
    echo "Ya has subido tus documentos"; // Respuesta si ya subió los documentos
} else {
    echo "No has subido tus documentos"; // Respuesta si no ha subido documentos
}
?>
