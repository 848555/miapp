<?php
// monitor_conexiones.php
include(__DIR__ . '/config/conexion.php'); // Incluye tu conexión

$GLOBALS['consulta_registro'] = [];

// Sobrescribimos mysqli_query temporalmente
function mysqli_query_monitoreada($sql) {
    global $conexion, $consulta_registro;

    // Obtenemos el archivo que llamó la consulta
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $archivo = isset($backtrace[1]['file']) ? $backtrace[1]['file'] : 'desconocido';

    if (!isset($consulta_registro[$archivo])) {
        $consulta_registro[$archivo] = [
            'consultas' => 0,
            'conexion_abierta' => $conexion->ping() ? 1 : 0
        ];
    }

    $consulta_registro[$archivo]['consultas']++;
    $consulta_registro[$archivo]['conexion_abierta'] = $conexion->ping() ? 1 : 0;

    // Ejecutamos la consulta real
    return mysqli_query($conexion, $sql);
}

// Función para mostrar el resumen
function mostrarResumen() {
    global $consulta_registro;
    echo "<pre>Resumen de consultas y conexiones abiertas:\n";
    foreach ($consulta_registro as $archivo => $info) {
        if ($info['consultas'] > 10 || $info['conexion_abierta'] > 0) {
            echo "Archivo: $archivo\n";
            echo "Consultas realizadas: {$info['consultas']}\n";
            echo "Conexión abierta: " . ($info['conexion_abierta'] ? "Sí" : "No") . "\n\n";
        }
    }
    echo "</pre>";
}

// ----------- EJEMPLO DE USO -----------
// Reemplaza temporalmente tus consultas para monitoreo:
$resultado = mysqli_query_monitoreada("SELECT * FROM usuarios");
$resultado2 = mysqli_query_monitoreada("SELECT * FROM solicitudes");

// Al final mostramos el resumen
mostrarResumen();
