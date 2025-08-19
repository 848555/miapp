<?php 
$host = "b4c1tgfsfzndtxfygnwk-mysql.services.clever-cloud.com";
$usuario = "umgtjph08lecwoj0";
$contrasena = "xwQsSDDkSMi7jAS7a02f";
$base_datos = "b4c1tgfsfzndtxfygnwk";
$puerto = 3306;

$conexion = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
// Verifica que la conexión esté viva
if (!$conexion->ping()) {
    $conexion = new mysqli('p:'.$host, $usuario, $contrasena, $base_datos, $puerto);
}
$conexion->set_charset("utf8");
?>
