<?php
include(__DIR__ . '../../../config/conexion.php');

// Buscar solicitudes ofrecidas con más de 30 segundos sin respuesta
$expiradas = $conexion->query("
    SELECT so.*, s.estado 
    FROM solicitudes_ofrecidas so
    INNER JOIN solicitudes s ON so.id_solicitud = s.id_solicitud
    WHERE s.estado = 'pendiente'
      AND TIMESTAMPDIFF(SECOND, so.fecha_asignacion, NOW()) > 30
");

while ($row = $expiradas->fetch_assoc()) {
    $id_solicitud = $row['id_solicitud'];
    $id_usuario   = $row['id_usuario'];

    // Liberar mototaxista
    $conexion->query("
        UPDATE mototaxistas_en_linea 
        SET en_servicio = 0 
        WHERE id_usuario = $id_usuario
    ");

    // Borrar oferta expirada
    $conexion->query("
        DELETE FROM solicitudes_ofrecidas 
        WHERE id_solicitud = $id_solicitud 
          AND id_usuario = $id_usuario
    ");
}

echo json_encode(['success' => true, 'mensaje' => 'Reintentos procesados']);
$conexion->close();
