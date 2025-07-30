<?php
session_start();

include(__DIR__ . '../../../config/conexion.php');

// Verificar si se recibió el parámetro departamento
if (isset($_GET['departamento'])) {
    $departamentoId = $_GET['departamento'];

    // Consulta para obtener ciudades del departamento seleccionado
    $sql_ciudades = "SELECT id_ciudades, ciudades FROM ciudades WHERE id_departamentos = $departamentoId";
    $resultado_ciudades = $conexion->query($sql_ciudades);

    // Preparar un array para almacenar las ciudades
    $ciudades = array();
    while ($ciudad = $resultado_ciudades->fetch_assoc()) {
        $ciudades[] = $ciudad;
    }

    // Devolver las ciudades en formato JSON
    echo json_encode($ciudades);
} else {
    // Si no se recibió el parámetro correcto, devolver un error o mensaje adecuado
    echo json_encode(array('error' => 'Parámetro departamento no recibido.'));
}
?>
