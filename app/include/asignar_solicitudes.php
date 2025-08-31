<?php
header('Content-Type: application/json; charset=utf-8');
include(__DIR__ . '../../../config/conexion.php');

// Buscar la primera solicitud pendiente
$solicitud = $conexion->query("
    SELECT * FROM solicitudes 
    WHERE estado = 'pendiente' 
    ORDER BY id_solicitud ASC 
    LIMIT 1
");

if ($solicitud->num_rows === 0) {
    echo json_encode(['asignada' => false, 'mensaje' => 'No hay solicitudes pendientes']);
    exit;
}

$sol = $solicitud->fetch_assoc();

// Verificar si ya está siendo ofrecida
$check = $conexion->query("
    SELECT * FROM solicitudes_ofrecidas 
    WHERE id_solicitud = {$sol['id_solicitud']}
    ORDER BY fecha_asignacion DESC LIMIT 1
");

if ($check->num_rows > 0) {
    echo json_encode(['asignada' => false, 'mensaje' => 'Una Solicitud está siendo ofrecida']);
    exit;
}

// Buscar mototaxista disponible con menor prioridad
$mtx = $conexion->query("
    SELECT * FROM mototaxistas_en_linea
    WHERE en_linea = 1 AND en_servicio = 0
    ORDER BY prioridad ASC 
    LIMIT 1
");

if ($mtx->num_rows === 0) {
    echo json_encode(['asignada' => false, 'mensaje' => 'No te has conectado no seras priorizado para servicios']);
    exit;
}

$mototaxista = $mtx->fetch_assoc();

// Insertar en solicitudes_ofrecidas
$stmt = $conexion->prepare("
    INSERT INTO solicitudes_ofrecidas (id_solicitud, id_usuario) 
    VALUES (?, ?)
");
$stmt->bind_param("ii", $sol['id_solicitud'], $mototaxista['id_usuario']);
$stmt->execute();
$stmt->close();

// Bloquear mototaxista (en servicio = 1 mientras decide)
$conexion->query("
    UPDATE mototaxistas_en_linea 
    SET en_servicio = 1 
    WHERE id_usuario = {$mototaxista['id_usuario']}
");

echo json_encode([
    'asignada' => true,
    'solicitud' => $sol,
    'mototaxista' => $mototaxista
]);

$conexion->close();
