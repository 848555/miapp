<?php
session_start();

include(__DIR__ . '../../../config/conexion.php');
include(__DIR__ . '/validar_permiso_directo.php');

// Validar permiso: módulo 4 = Ganancias de la App, acción 3 = eliminar
$id_admin = $_SESSION['id_usuario'] ?? 0;

if (!tienePermiso($id_admin, 4, 3)) {
    echo "<script>
        alert('No tienes permiso para eliminar retenciones.');
        window.location = '../pages/ver_retenciones.php';
    </script>";
    exit();
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el registro
    $sql = $conexion->prepare("DELETE FROM retenciones WHERE id = ?");
    $sql->bind_param("i", $id);

    // Ejecutar la consulta y verificar si fue exitosa
    if ($sql->execute()) {
        $_SESSION['mensaje'] = "Retención eliminada correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la retención.";
    }

    // Cerrar la consulta
    $sql->close();
} else {
    $_SESSION['mensaje'] = "ID de retención no proporcionado.";
}

// Redirigir a la página de retenciones
header("Location: ../pages/ver_retenciones.php");
exit();
?>
