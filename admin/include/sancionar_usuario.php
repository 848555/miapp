<?php
// Iniciar sesión
session_start();


// Conectar a la base de datos
include(__DIR__ . '../../../config/conexion.php');

// Verificar si se ha enviado el ID del usuario
if (isset($_GET['id'])) {
    $id_usuario = intval($_GET['id']);

    // Actualizar el estado del usuario a "sancionado"
    $query = "UPDATE usuarios SET Estado = 'sancionado' WHERE id_usuarios = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        // Verificar si se actualizó correctamente
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Usuario sancionado exitosamente.";
        } else {
            $_SESSION['error_message'] = "No se pudo sancionar al usuario. Verifica que el ID sea correcto.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error en la consulta a la base de datos.";
    }
} else {
    $_SESSION['error_message'] = "ID de usuario no proporcionado.";
}

// Redirigir a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
