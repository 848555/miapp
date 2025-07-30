<?php
session_start();

include(__DIR__ . '../../../config/conexion.php');

$user_id = $_SESSION['id_usuario'];

// Verificar la conexión
if ($conexion->connect_error) {
    die("La conexión falló: " . $conexion->connect_error);
}

$limit = 5; // Número de registros por página
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Obtener las solicitudes excluyendo las completadas
$sql = "SELECT s.*, u.Nombres, u.Apellidos
        FROM solicitudes s
        INNER JOIN usuarios u ON s.id_usuarios = u.id_usuarios
        WHERE s.id_usuarios != ? AND s.estado != 'completada'
        LIMIT ?, ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iii", $user_id, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$solicitudes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
}

// Obtener el número total de registros excluyendo las completadas
$sql = "SELECT COUNT(*) as total FROM solicitudes WHERE id_usuarios != ? AND estado != 'completada'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$conexion->close(); // Cerrar la conexión después de obtener todos los datos

$response = [
    'solicitudes' => $solicitudes,
    'total_pages' => $total_pages
];

echo json_encode($response);
?>
