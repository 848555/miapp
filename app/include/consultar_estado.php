<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . '../../../config/conexion.php');

$id_usuario = $_SESSION['id_usuario'];

$check = $conexion->query("SELECT en_linea FROM mototaxistas_en_linea WHERE id_usuario = $id_usuario");
if ($check->num_rows > 0) {
    $status = $check->fetch_assoc();
    echo json_encode(['en_linea' => (bool)$status['en_linea']]);
} else {
    echo json_encode(['en_linea' => false]);
}
