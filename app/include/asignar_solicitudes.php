<?php
include(__DIR__ . '../../../config/conexion.php');

// Obtener solicitudes no asignadas
$solicitud = $conexion->query("SELECT * FROM solicitudes WHERE estado = 'pendiente' ORDER BY id_solicitud ASC LIMIT 1");

if ($solicitud->num_rows > 0) {
    $sol = $solicitud->fetch_assoc();

    // Obtener mototaxista en línea con menor prioridad y disponible
    $mtx = $conexion->query("SELECT * FROM mototaxistas_en_linea WHERE en_linea = 1 AND en_servicio = 0 ORDER BY prioridad ASC LIMIT 1");

    if ($mtx->num_rows > 0) {
        $mototaxista = $mtx->fetch_assoc();

        // Asignar solicitud provisionalmente (no cambiar el estado aún)
        echo json_encode([
            'asignada' => true,
            'id_usuario' => $mototaxista['id_usuario'],
            'solicitud' => $sol
        ]);
        exit;
    }
}

echo json_encode(['asignada' => false]);
