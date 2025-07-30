<?php
session_start();

include(__DIR__ . '../../../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar que todos los campos están presentes
    $required_fields = ['id_administrador', 'nombres', 'apellidos', 'DNI', 'tipo_accion', 'descripcion'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error_message'] = "Todos los campos son obligatorios";
            header("Location: ../pages/principal.php");
            exit();
        }
    }

    $id_administrador = $_POST['id_administrador'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $DNI = $_POST['DNI'];
    $tipo_accion = $_POST['tipo_accion'];
    $descripcion = $_POST['descripcion'];

    $sql = $conexion->prepare("INSERT INTO acciones (id_administrador, nombres, apellidos, DNI, tipo_accion, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
    $sql->bind_param("isssss", $id_administrador, $nombres, $apellidos, $DNI, $tipo_accion, $descripcion);

    if ($sql->execute()) {
        $_SESSION['success_message'] = "Acción registrada correctamente";
    } else {
        $_SESSION['error_message'] = "Error al registrar la acción";
    }

    header("Location: ../pages/principal.php");
    exit();
} else {
    $_SESSION['error_message'] = "Método de solicitud no permitido";
    header("Location: ../pages/principal.php");
    exit();
}
