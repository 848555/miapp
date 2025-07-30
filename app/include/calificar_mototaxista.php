<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: /index.php?vista=login");
    exit;
}

// Verificar si se recibió la calificación y el comentario a través del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $id_usuario = $_SESSION['id_usuario'];  // El usuario que está realizando la calificación
    $id_solicitud = $_POST['id_solicitud'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'];
    $fecha = date('Y-m-d H:i:s');  // Obtener la fecha y hora actuales

    // Validar que los campos requeridos no estén vacíos
    if (empty($calificacion) || empty($comentario) || empty($id_solicitud)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("Location:  ../pages/mis_calificaciones.php");
        exit;
    }

    // Insertar la calificación en la tabla calificaciones_mototaxistas
    $query = "INSERT INTO calificaciones_mototaxistas (id_usuario, id_solicitud, calificacion, comentario, fecha)
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("iiiss", $id_usuario, $id_solicitud, $calificacion, $comentario, $fecha);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Calificación registrada correctamente.";
    } else {
        $_SESSION['error'] = "Error al registrar la calificación.";
    }

    $stmt->close();
    $conexion->close();

    // Redirigir a la página de calificaciones
    header("Location: ../pages/mis_calificaciones.php");
    exit;
} else {
    // Si el acceso al archivo no es a través de POST, redirigir al formulario de calificaciones
    header("Location: ../pages/mis_calificaciones.php");
    exit;
}
?>
