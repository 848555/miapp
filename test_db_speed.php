<?php
$host = "b4c1tgfsfzndtxfygnwk-mysql.services.clever-cloud.com";
$puerto = 3306;

$inicio = microtime(true);
$conexion = @fsockopen($host, $puerto, $errno, $errstr, 5); // timeout de 5 segundos
$fin = microtime(true);

if ($conexion) {
    fclose($conexion);
    $latencia_ms = round(($fin - $inicio) * 1000, 2);
    echo "⏱ Latencia TCP hacia MySQL: {$latencia_ms} ms\n";
} else {
    echo "❌ No se pudo conectar: $errstr ($errno)\n";
}
?>

