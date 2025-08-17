<?php
// monitor_conexiones.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . '/config/conexion.php');

$GLOBALS['consulta_registro'] = [];

// Función para registrar consultas
function registrarConsulta() {
    global $conexion, $consulta_registro;

    // Obtenemos la pila de llamadas
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $archivo = isset($backtrace[1]['file']) ? $backtrace[1]['file'] : 'desconocido';
    $linea  = isset($backtrace[1]['line']) ? $backtrace[1]['line'] : '?';

    if (!isset($consulta_registro[$archivo])) {
        $consulta_registro[$archivo] = [
            'consultas' => 0,
            'conexion_abierta' => $conexion->ping() ? 1 : 0,
            'ultima_linea' => $linea
        ];
    }

    $consulta_registro[$archivo]['consultas']++;
    $consulta_registro[$archivo]['conexion_abierta'] = $conexion->ping() ? 1 : 0;
    $consulta_registro[$archivo]['ultima_linea'] = $linea;
}

// Función para mostrar dónde quedó abierta la conexión
function mostrarResumenConexiones() {
    global $consulta_registro;
    echo "<pre>Resumen de consultas y conexiones abiertas:\n";
    foreach ($consulta_registro as $archivo => $info) {
        echo "Archivo: $archivo\n";
        echo "Última línea de consulta: {$info['ultima_linea']}\n";
        echo "Consultas registradas: {$info['consultas']}\n";
        echo "Conexión abierta: " . ($info['conexion_abierta'] ? "Sí" : "No") . "\n\n";
    }
    echo "</pre>";
}

// ----------- EJEMPLO DE USO -----------
// Llamar registrarConsulta() cada vez que se haga una consulta
registrarConsulta(); // Ejemplo
registrarConsulta(); // Ejemplo

// Mostrar dónde quedó abierta
mostrarResumenConexiones();
