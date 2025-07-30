<?php
session_start();
include(__DIR__ . '/validar_permiso_directo.php');

$id_admin = $_SESSION['id_usuario'] ?? 0;

// Validar permiso: módulo 1 (Gestión de Usuarios), acción 2 (editar)
if (!tienePermiso($id_admin, 1, 2)) {
    echo "<script>alert('No tienes permiso para editar usuarios'); window.location='/admin/pages/principal.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include(__DIR__ . '/../../config/conexion.php'); 

    // Validar y limpiar los datos del formulario
    $id_usuario = intval($_POST['id_usuario']); // ID del usuario a actualizar
    $nuevo_estado = $_POST['estadoUsuario']; // Nuevo estado a asignar

    // Consulta preparada para actualizar el estado del usuario
    $query_update = "UPDATE usuarios SET Estado = ? WHERE id_usuarios = ?";
    $stmt_update = $conexion->prepare($query_update);
    $stmt_update->bind_param("si", $nuevo_estado, $id_usuario);

    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = 'Estado de usuario actualizado correctamente a ' . $nuevo_estado . '.';
    } else {
        $_SESSION['error_message'] = 'Error al actualizar el estado de usuario: ' . $stmt_update->error;
    }

    $stmt_update->close();
    $conexion->close();

    // Redirigir a la página deseada
    header("Location: ../pages/principal.php");
    exit();
} else {
    $_SESSION['error_message'] = 'Acceso denegado.';
    header("Location: ../pages/principal.php");
    exit();
}
?>
