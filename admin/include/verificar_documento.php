<?php

session_start();

include(__DIR__ . '../../../config/conexion.php');
include(__DIR__ . '/validar_permiso_directo.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_admin = $_SESSION['id_usuario'] ?? 0;
    $id_documentos = $_POST['id_documentos'];
    $estado_documento = $_POST['estado_documento']; // 'validado' o 'rechazado'

    // Validar permisos por tipo de acción
    if ($estado_documento === 'validado' && !tienePermiso($id_admin, 2, 5)) {
        echo "<script>
            alert('No tienes permiso para validar documentos.');
            window.location.href = '/admin/pages/mototaxistas.php';
        </script>";
        exit();
    }

    if ($estado_documento === 'rechazado' && !tienePermiso($id_admin, 2, 6)) {
        echo "<script>
            alert('No tienes permiso para rechazar documentos.');
            window.location.href = '/admin/pages/mototaxistas.php';
        </script>";
        exit();
    }

    // Obtener el id del usuario que subió el documento
    $sql_documento = "SELECT id_usuarios FROM documentos WHERE id_documentos = ?";
    $stmt_documento = $conexion->prepare($sql_documento);
    $stmt_documento->bind_param("i", $id_documentos);
    $stmt_documento->execute();
    $stmt_documento->bind_result($id_usuario);
    $stmt_documento->fetch();
    $stmt_documento->close();

    // Determinar el nuevo estado del documento y el mensaje
    if ($estado_documento == 'validado') {
        $nuevo_estado = 'validado';
        $mensaje = "Tus documentos han sido validados correctamente.";
    } elseif ($estado_documento == 'rechazado') {
        $nuevo_estado = 'rechazado';
        $mensaje = "Tus documentos han sido rechazados por no ser vigentes o correctos.";
    } else {
        $_SESSION['error_message'] = 'Estado de documento no válido.';
        header("Location: ../pages/mototaxistas.php");
        exit();
    }

    // Actualizar el estado de verificación del documento
    $sql_update = "UPDATE documentos SET documento_verificado = ? WHERE id_documentos = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("si", $nuevo_estado, $id_documentos);

    if ($stmt_update->execute()) {
        // Insertar un mensaje en mensajes_temporales
        $sql_insert_mensaje = "INSERT INTO mensajes_temporales (id_usuario, id_solicitud, mensaje, leido) VALUES (?, NULL, ?, FALSE)";
        $stmt_insert = $conexion->prepare($sql_insert_mensaje);
        $stmt_insert->bind_param("is", $id_usuario, $mensaje);
        $stmt_insert->execute();
        $stmt_insert->close();

        $_SESSION['success_message'] = 'Documento ' . $nuevo_estado . ' correctamente y mensaje enviado.';
    } else {
        $_SESSION['error_message'] = 'Error al procesar el documento.';
    }

    $stmt_update->close();
    $conexion->close();

    // Redirigir a la página de documentos
    header("Location: ../pages/mototaxistas.php");
    exit();
}
?>
