<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Buscar mensajes no leídos
$sql = "SELECT id, mensaje, fecha FROM mensajes_temporales WHERE id_usuario = ? AND leido = FALSE";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}
$stmt->close();

// Marcar como leídos
foreach ($mensajes as $m) {
    $stmtUpdate = $conexion->prepare("UPDATE mensajes_temporales SET leido = TRUE WHERE id = ?");
    $stmtUpdate->bind_param("i", $m['id']);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

// Devolver HTML con clase .mensaje como el login
if (!empty($mensajes)) {
    foreach ($mensajes as $mensaje) {
        echo '<p class="mensaje">' . htmlspecialchars($mensaje['mensaje']) . '<br><em>';
    }
}
?>
