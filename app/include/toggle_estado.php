<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . '../../../config/conexion.php');

$id_usuario = $_SESSION['id_usuario'];

$check = $conexion->query("SELECT * FROM mototaxistas_en_linea WHERE id_usuario = $id_usuario");

if ($check->num_rows > 0) {
    $conexion->query("UPDATE mototaxistas_en_linea SET en_linea = NOT en_linea WHERE id_usuario = $id_usuario");
} else {
    $maxPrio = $conexion->query("SELECT MAX(prioridad) AS max_prio FROM mototaxistas_en_linea")->fetch_assoc()['max_prio'] ?? 0;
    $conexion->query("INSERT INTO mototaxistas_en_linea (id_usuario, prioridad) VALUES ($id_usuario, " . ($maxPrio + 1) . ")");
}

// Devolver estado actualizado
$status = $conexion->query("SELECT en_linea FROM mototaxistas_en_linea WHERE id_usuario = $id_usuario")->fetch_assoc();
echo json_encode(['en_linea' => (bool)$status['en_linea']]);
