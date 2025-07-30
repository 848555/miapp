<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

// Obtener los datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar los datos recibidos
$id_solicitud = isset($data['id_solicitud']) ? intval($data['id_solicitud']) : 0;
$id_usuario = isset($data['id_usuarios']) ? intval($data['id_usuarios']) : 0;
$calificacion = isset($data['rating']) ? intval($data['rating']) : 0;
$comentarios = isset($data['comentarios']) ? $data['comentarios'] : '';

// Comprobar que los datos son válidos
if ($id_solicitud <= 0 || $id_usuario <= 0 || $calificacion < 1 || $calificacion > 5) {
    echo json_encode(['success' => false, 'message' => 'Datos no válidos.']);
    exit();
}

// Insertar la calificación en la base de datos
$sql_insert_calificacion = "INSERT INTO calificaciones_usuarios (id_usuario, id_solicitud, calificacion, comentario, fecha) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conexion->prepare($sql_insert_calificacion);

if ($stmt) {
    $stmt->bind_param("iiis", $id_usuario, $id_solicitud, $calificacion, $comentarios);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la calificación: ' . $conexion->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conexion->error]);
}

$conexion->close();
?>
