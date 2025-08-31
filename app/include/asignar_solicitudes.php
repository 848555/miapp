<?php  
include(__DIR__ . '../../../config/conexion.php');  

// Obtener la primera solicitud pendiente
$solicitud = $conexion->query("SELECT * FROM solicitudes WHERE estado = 'pendiente' ORDER BY id_solicitud ASC LIMIT 1");  

if ($solicitud->num_rows > 0) {  
    $sol = $solicitud->fetch_assoc();  

    // Buscar mototaxista libre con menor prioridad
    $mtx = $conexion->query("SELECT * FROM mototaxistas_en_linea WHERE en_linea = 1 AND en_servicio = 0 ORDER BY prioridad ASC LIMIT 1");  

    if ($mtx->num_rows > 0) {  
        $mototaxista = $mtx->fetch_assoc();  

        // ⚡️ Actualizar solicitud: ya no está pendiente, ahora está ofrecida
        $stmt = $conexion->prepare("UPDATE solicitudes SET estado = 'ofrecida', id_usuarios = ? WHERE id_solicitud = ?");
        $stmt->bind_param("ii", $mototaxista['id_usuario'], $sol['id_solicitud']);
        $stmt->execute();
        $stmt->close();

        echo json_encode([  
            'asignada'   => true,  
            'id_usuario' => $mototaxista['id_usuario'],  
            'solicitud'  => $sol  
        ]);  

        mysqli_close($conexion);  
        exit;  
    }  
}  

// Si no hay solicitud o no hay mototaxista disponible
echo json_encode(['asignada' => false]);  
mysqli_close($conexion);
