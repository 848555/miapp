<?php

session_start();

include(__DIR__ . '../../../config/conexion.php');
include(__DIR__ . '/validar_permiso_directo.php'); 

// Validar permiso: módulo 3 = Solicitudes de Servicio, acción 3 = eliminar
$id_admin = $_SESSION['id_usuario'] ?? 0;

if (!tienePermiso($id_admin, 3, 3)) {
    echo "<script>
        alert('No tienes permiso para eliminar solicitudes.');
        window.location = '../pages/servicios_solicitados.php';
    </script>";
    exit();
}

if (isset($_GET['id_solicitud'])) {
    $id_solicitud = $_GET['id_solicitud'];

    // Eliminar los mensajes temporales asociados a la solicitud
    $sql_delete_mensajes = "DELETE FROM mensajes_temporales WHERE id_solicitud = ?";

    $stmt_delete_mensajes = $conexion->prepare($sql_delete_mensajes);
    if ($stmt_delete_mensajes) {
        $stmt_delete_mensajes->bind_param("i", $id_solicitud);
        if ($stmt_delete_mensajes->execute()) {
            // Después de eliminar los mensajes temporales, eliminar las retenciones asociadas a la solicitud
            $sql_delete_retenciones = "DELETE FROM retenciones WHERE id_solicitud = ?";
            $stmt_delete_retenciones = $conexion->prepare($sql_delete_retenciones);

            if ($stmt_delete_retenciones) {
                $stmt_delete_retenciones->bind_param("i", $id_solicitud);
                if ($stmt_delete_retenciones->execute()) {
                    // Después de eliminar las retenciones, proceder a eliminar la solicitud
                    $sql_delete_solicitud = "DELETE FROM solicitudes WHERE id_solicitud = ?";
                    $stmt_delete_solicitud = $conexion->prepare($sql_delete_solicitud);

                    if ($stmt_delete_solicitud) {
                        $stmt_delete_solicitud->bind_param("i", $id_solicitud);
                        if ($stmt_delete_solicitud->execute()) {
                            $_SESSION['success_message'] = '<div class="alert alert-success" role="alert">La solicitud con id ' . $id_solicitud . ' y sus datos asociados se eliminaron correctamente.</div>';
                        } else {
                            $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">Error al eliminar la solicitud: ' . $stmt_delete_solicitud->error . '</div>';
                        }

                        $stmt_delete_solicitud->close();
                    } else {
                        $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">Error en la preparación de la consulta para eliminar la solicitud: ' . $conexion->error . '</div>';
                    }
                } else {
                    $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">Error al eliminar las retenciones asociadas a la solicitud: ' . $stmt_delete_retenciones->error . '</div>';
                }

                $stmt_delete_retenciones->close();
            } else {
                $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">Error en la preparación de la consulta para eliminar las retenciones: ' . $conexion->error . '</div>';
            }
        } else {
            $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">Error al eliminar los mensajes temporales: ' . $stmt_delete_mensajes->error . '</div>';
        }

        $stmt_delete_mensajes->close();
    } else {
        $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">Error en la preparación de la consulta para eliminar los mensajes temporales: ' . $conexion->error . '</div>';
    }
} else {
    $_SESSION['error_message'] = '<div class="alert alert-danger" role="alert">No se recibió el parámetro "id_solicitud" para eliminar la solicitud.</div>';
}

// Redireccionar a la página servicios_solicitados.php con el mensaje almacenado en la sesión
header("Location: ../pages/servicios_solicitados.php");
exit();
?>
