<?php

include(__DIR__ . '../../../config/conexion.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_solicitud = $_POST['id_solicitud'];

    // Preparar y ejecutar la consulta para cancelar la solicitud
    $query = "UPDATE solicitudes SET estado = 'cancelada' WHERE id_solicitud = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_solicitud); // "i" indica que el parámetro es un entero
        if ($stmt->execute()) {
            // Redirigir o mostrar un mensaje de éxito
            header('Location:../pages/solicitud.php?mensaje=Solicitud cancelada con éxito');
            exit;
        } else {
            // Manejar error en la ejecución
            echo "Error al cancelar la solicitud: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Manejar error en la preparación de la consulta
        echo "Error en la preparación de la consulta: " . $conn->error;
    }
}

$conexion->close();
