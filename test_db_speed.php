<?php
$start_global = microtime(true);

$start_conexion = microtime(true);
   include(__DIR__ . '../../config/conexion.php');
$end_conexion = microtime(true);

$start_query = microtime(true);
$result = $conexion->query("SELECT id_usuarios FROM usuarios LIMIT 1");
$end_query = microtime(true);

$end_global = microtime(true);

echo "<pre>";
echo "⏱ Tiempo conexión: " . round(($end_conexion - $start_conexion) * 1000, 2) . " ms\n";
echo "⏱ Tiempo consulta: " . round(($end_query - $start_query) * 1000, 2) . " ms\n";
echo "⏱ Tiempo total: " . round(($end_global - $start_global) * 1000, 2) . " ms\n";
echo "</pre>";

$conexion->close();
?>
