<?php
    include(__DIR__ . '/../../config/conexion.php'); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function tienePermiso($id_admin, $id_modulo, $id_accion_modulo) {
    global $conexion;

    $sql = "SELECT permitido FROM permisos_detallados 
            WHERE id_admin = ? AND id_modulo = ? AND id_accion_modulo = ? AND permitido = 1 LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $id_admin, $id_modulo, $id_accion_modulo);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows > 0;
}
