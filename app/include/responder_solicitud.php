<?php
include(__DIR__ . '../../../config/conexion.php');

$id_solicitud = intval($_POST['id_solicitud']);
$id_usuario   = intval($_POST['id_usuario']);
$respuesta    = $_POST['respuesta']; // "aceptar" o "rechazar"

// Verificar que realmente estaba ofrecida
$check = $conexion->query("
    SELECT * FROM solicitudes_ofrecidas 
    WHERE id_solicitud = $id_solicitud 
      AND id_usuario = $id_usuario
    ORDER BY fecha_asignacion DESC LIMIT 1
");

if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La solicitud ya no está disponible']);
    exit;
}

if ($respuesta === 'aceptar') {
    // ✅ Marcar solicitud como aceptada
    $conexion->query("
        UPDATE solicitudes 
        SET estado = 'aceptada', id_usuarios = $id_usuario 
        WHERE id_solicitud = $id_solicitud
    ");
    echo json_encode(['success' => true, 'mensaje' => 'Solicitud aceptada']);
} else {
    // ❌ Rechazo: liberar mototaxista y eliminar oferta
    $conexion->query("
        UPDATE mototaxistas_en_linea 
        SET en_servicio = 0 
        WHERE id_usuario = $id_usuario
    ");
    $conexion->query("
        DELETE FROM solicitudes_ofrecidas 
        WHERE id_solicitud = $id_solicitud AND id_usuario = $id_usuario
    ");
    echo json_encode(['success' => true, 'mensaje' => 'Solicitud rechazada']);
}

$conexion->close();
