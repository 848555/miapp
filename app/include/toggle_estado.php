<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . '../../../config/conexion.php');

header('Content-Type: application/json; charset=utf-8'); // 👈 asegura salida JSON

$id_usuario = intval($_SESSION['id_usuario']); // 👈 sanitizar por seguridad

$check = $conexion->query("SELECT * FROM mototaxistas_en_linea WHERE id_usuario = $id_usuario");

if ($check && $check->num_rows > 0) {
    // Alternar estado
    $conexion->query("UPDATE mototaxistas_en_linea SET en_linea = NOT en_linea WHERE id_usuario = $id_usuario");
} else {
    // Insertar con en_linea = 1 por defecto
    $maxPrioRes = $conexion->query("SELECT MAX(prioridad) AS max_prio FROM mototaxistas_en_linea");
    $maxPrio = $maxPrioRes && $maxPrioRes->num_rows > 0 ? ($maxPrioRes->fetch_assoc()['max_prio'] ?? 0) : 0;
    $conexion->query("INSERT INTO mototaxistas_en_linea (id_usuario, en_linea, prioridad) VALUES ($id_usuario, 1, " . ($maxPrio + 1) . ")");
}
if ($check) { $check->free(); } // ✅ liberar resultado
// Devolver estado actualizado
$statusRes = $conexion->query("SELECT en_linea FROM mototaxistas_en_linea WHERE id_usuario = $id_usuario");
$status = $statusRes ? $statusRes->fetch_assoc() : ['en_linea' => 0];

echo json_encode(['en_linea' => (bool)$status['en_linea']]);

$conexion->close();
