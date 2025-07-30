<?php
session_start();
include(__DIR__ . '/validar_permiso_directo.php');
// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header("Location: ../../../../index.php");
    exit();
}
// ✅ Obtener ID del admin desde la sesión
$id_admin = $_SESSION['id_usuario'] ?? 0;

// ✅ Validar permiso: módulo 1 = Gestión de Usuarios, acción 3 = eliminar
if (!tienePermiso($id_admin, 1, 3)) {
    echo "<script>alert('No tienes permiso para eliminar usuarios'); window.location='../pages/principal.php';</script>";
    exit();
}
// Verificar si se ha proporcionado un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Error: ID de usuario no válido.";
    header("Location: ../../../pages/principal.php"); // Redirigir a la página de inicio
    exit();
}

// Obtener el ID del usuario a eliminar
$id_usuario = (int)$_GET['id'];

// Confirmar eliminación
if (isset($_GET['confirm'])) {
    include(__DIR__ . '../../../config/conexion.php');

    // Utilizar transacciones para asegurar la consistencia
    $conexion->begin_transaction();

   try {
    // 1. Eliminar retenciones ligadas a las solicitudes del usuario
    $conexion->query("DELETE FROM retenciones WHERE id_solicitud IN (SELECT id_solicitud FROM solicitudes WHERE id_usuarios = $id_usuario)");

    // 2. Eliminar retenciones directas por id_usuarios (si aplica)
    $conexion->query("DELETE FROM retenciones WHERE id_usuarios = $id_usuario");

    // 3. Eliminar otras dependencias
    $conexion->query("DELETE FROM calificaciones_usuarios WHERE id_usuario = $id_usuario");
    $conexion->query("DELETE FROM calificaciones_mototaxistas WHERE id_usuario = $id_usuario");
    $conexion->query("DELETE FROM documentos WHERE id_usuarios = $id_usuario");
    $conexion->query("DELETE FROM mensajes_temporales WHERE id_usuario = $id_usuario");

    // 4. Eliminar las solicitudes
    $conexion->query("DELETE FROM solicitudes WHERE id_usuarios = $id_usuario");

    // 5. Eliminar el usuario
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuarios = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Usuario eliminado correctamente.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar el usuario.";
    }

        $stmt->close();
        $conexion->commit();
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['error_message'] = "Error al eliminar el usuario: " . $e->getMessage();
    } finally {
        $conexion->close();
    }

    // Redirigir a la página de inicio con el mensaje correspondiente
    header("Location: ../pages/principal.php");
    exit();
}
?>



