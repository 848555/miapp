<?php
// monitor_conexiones.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . '/config/conexion.php'); // Incluye tu conexión

// Registro global de consultas y conexiones
$GLOBALS['consulta_registro'] = [];

// Función para registrar que se hizo una consulta
function registrarConsulta() {
    global $conexion, $consulta_registro;

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
}

// Función para mostrar el resumen
function mostrarResumenConexiones() {
    global $consulta_registro;
    echo "<pre>Resumen de consultas y conexiones abiertas:\n";
    foreach ($consulta_registro as $archivo => $info) {
        echo "Archivo: $archivo\n";
        echo "Consultas registradas: {$info['consultas']}\n";
        echo "Conexión abierta: " . ($info['conexion_abierta'] ? "Sí" : "No") . "\n\n";
    }
    echo "</pre>";
}

// ----------- USO -----------
// Solo para pruebas: registra consultas simuladas
registrarConsulta(); // Llamar cada vez que se haga una consulta real
registrarConsulta(); // Llamar de nuevo para simular otra consulta

// Mostrar resumen al final
mostrarResumenConexiones();
