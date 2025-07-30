<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    $_SESSION['error_message'] = "Debes iniciar sesión para acceder a esta página.";
    header("Location: ../../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Incluir archivo de conexión a la base de datos
include(__DIR__ . '../../../config/conexion.php');

// Verificar si se ha proporcionado la contraseña
if (!isset($_POST['password'])) {
    $_SESSION['error_message'] = "Contraseña no proporcionada.";
    header("Location: ../../../pages/inicio.php");
    exit();
}

$password = $_POST['password'];

// Consultar la contraseña del usuario
$stmt = $conexion->prepare("SELECT Password FROM usuarios WHERE id_usuarios = ?");
if (!$stmt) {
    $_SESSION['error_message'] = "Error en la consulta: " . $conexion->error;
    header("Location: ../../../pages/inicio.php");
    exit();
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($stored_password);
$stmt->fetch();
$stmt->close();

if (!$stored_password) {
    $_SESSION['error_message'] = "No se encontró el usuario.";
    header("Location: ../../../pages/inicio.php");
    exit();
}

// Verificar si la contraseña proporcionada coincide con la almacenada en la base de datos
if ($password === $stored_password) {
    // Eliminar documentos asociados al usuario
    $stmt_delete_docs = $conexion->prepare("DELETE FROM documentos WHERE id_usuarios = ?");
    if (!$stmt_delete_docs) {
        $_SESSION['error_message'] = "Error en la preparación de la consulta DELETE documentos: " . $conexion->error;
        header("Location: ../../../pages/inicio.php");
        exit();
    }

    $stmt_delete_docs->bind_param("i", $id_usuario);
    $stmt_delete_docs->execute();
    $stmt_delete_docs->close();

    // Eliminar solicitudes asociadas al usuario
    $stmt_delete_solicitudes = $conexion->prepare("DELETE FROM solicitudes WHERE id_usuarios = ?");
    if (!$stmt_delete_solicitudes) {
        $_SESSION['error_message'] = "Error en la preparación de la consulta DELETE solicitudes: " . $conexion->error;
        header("Location: ../../../pages/inicio.php");
        exit();
    }

    $stmt_delete_solicitudes->bind_param("i", $id_usuario);
    $stmt_delete_solicitudes->execute();
    $stmt_delete_solicitudes->close();

    // Luego eliminar al usuario
    $stmt_delete_user = $conexion->prepare("DELETE FROM usuarios WHERE id_usuarios = ?");
    if (!$stmt_delete_user) {
        $_SESSION['error_message'] = "Error en la preparación de la consulta DELETE usuario: " . $conexion->error;
        header("Location: ../../../pages/inicio.php");
        exit();
    }

    $stmt_delete_user->bind_param("i", $id_usuario);
    $stmt_delete_user->execute();

    if ($stmt_delete_user->affected_rows > 0) {
        $_SESSION['success_message'] = "Tu cuenta ha sido eliminada correctamente.";
    } else {
        $_SESSION['error_message'] = "Error al intentar eliminar la cuenta.";
    }

    $stmt_delete_user->close();
    $conexion->close();

    header("Location: ../../../../index.php"); // Redirigir a la página de inicio de sesión
    exit();
} else {
    $_SESSION['error_message'] = "La contraseña ingresada es incorrecta. Inténtalo nuevamente.";
    header("Location: ../../../pages/inicio.php"); // Redirigir de vuelta al inicio
    exit();
}
?>
