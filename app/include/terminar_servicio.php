<?php
// terminar_servicio.php
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Comprobar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $id_solicitud = $_POST['id_solicitud_terminar'];
    $pago_completo = $_POST['pago_completo'];
    $cliente_ausente = $_POST['cliente_ausente'];

    // Verificar si los datos fueron recibidos
    if (!isset($id_solicitud, $pago_completo, $cliente_ausente)) {
        $_SESSION['error_message'] = 'Datos no recibidos correctamente.';
        header('Location: ../pages/sermototaxista.php');
        exit;
    }

    // Definir el nuevo valor de pago_completo y estado
    if ($pago_completo == 1 && $cliente_ausente == 0) {
        $nuevo_pago_completo = 1; // Pagado
        $nuevo_estado = 'completada';
        $monto_total = 4000.00;
        $retencion = 1000.00;
    } elseif ($cliente_ausente == 1) {
        $nuevo_pago_completo = 2; // Cliente ausente
        $nuevo_estado = 'cancelada';
        $monto_total = 0.00;
        $retencion = 0.00;
    } else {
        $_SESSION['error_message'] = 'Estado de pago no válido.';
        header('Location: ../pages/sermototaxista.php');
        exit;
    }

    // Obtener el ID del usuario desde la sesión
    $id_usuario = $_SESSION['id_usuario'];

    // Actualizar la solicitud en la base de datos
    $sql_update_solicitud = "UPDATE solicitudes SET estado = ?, pago_completo = ? WHERE id_solicitud = ?";
    $stmt = $conexion->prepare($sql_update_solicitud);

    if ($stmt) {
        $stmt->bind_param("sii", $nuevo_estado, $nuevo_pago_completo, $id_solicitud);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // ✅ Marcar al mototaxista como disponible nuevamente
                $conexion->query("UPDATE mototaxistas_en_linea SET en_servicio = 0 WHERE id_usuario = $id_usuario");

                // Insertar la retención si aplica
                if ($retencion > 0) {
                    $sql_insert_retencion = "INSERT INTO retenciones (id_usuarios, monto, retencion, fecha, pagado, id_solicitud) 
                                             VALUES (?, ?, ?, NOW(), 0, ?)";
                    $stmt_insert_retencion = $conexion->prepare($sql_insert_retencion);

                    if ($stmt_insert_retencion) {
                        $stmt_insert_retencion->bind_param("idii", $id_usuario, $monto_total, $retencion, $id_solicitud);

                        if ($stmt_insert_retencion->execute()) {
                            $_SESSION['success_message'] = 'Servicio terminado correctamente.';
                        } else {
                            $_SESSION['error_message'] = 'Error al insertar la retención: ' . $stmt_insert_retencion->error;
                        }
                        $stmt_insert_retencion->close();
                    } else {
                        $_SESSION['error_message'] = 'Error al preparar la consulta de retención: ' . $conexion->error;
                    }
                } else {
                    $_SESSION['success_message'] = 'Servicio terminado correctamente, cliente ausente.';
                }
            } else {
                $_SESSION['error_message'] = 'No se actualizó ningún registro.';
            }
        } else {
            $_SESSION['error_message'] = 'Error al ejecutar la consulta: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = 'Error al preparar la consulta: ' . $conexion->error;
    }

    // Redirigir a la página de éxito o error
    header('Location: ../pages/sermototaxista.php');
    exit;
} else {
    $_SESSION['error_message'] = 'Método de solicitud no válido.';
    header('Location: ../pages/sermototaxista.php');
    exit;
}
?>
