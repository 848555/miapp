<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_KEY', 'TU_SERVICE_ROLE_KEY'); // Mantén tu key aquí
define('SUPABASE_STORAGE_BUCKET', 'documentos');

// ====== Funciones ======
function eliminarArchivoSupabase($nombreArchivo) {
    if (!$nombreArchivo) return true;
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $nombreArchivo;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => ["Authorization: Bearer " . SUPABASE_KEY],
    ]);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($status >= 200 && $status < 300);
}

function subirArchivoASupabase($fileTmpPath, $fileName) {
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => file_get_contents($fileTmpPath),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_KEY,
            "Content-Type: application/octet-stream"
        ],
    ]);
    curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ($http_code >= 200 && $http_code < 300);
}

function nombreArchivoUnico($id, $tipo, $extension) {
    return "{$id}_{$tipo}_" . uniqid() . ".{$extension}";
}

// ====== Datos ======
$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";
$id_usuarios = $_SESSION['id_usuario'] ?? "";

if (empty($id_usuarios)) {
    header("Location: ../pages/inicio.php");
    exit();
}

// ====== Obtener registro existente ======
$sql_check = "SELECT * FROM documentos WHERE id_usuarios = " . intval($id_usuarios) . " LIMIT 1";
$result = $conexion->query($sql_check);
$row = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;

// Crear registro si no existe
if (!$row) {
    $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
            VALUES ('" . $conexion->real_escape_string($placa) . "', '" . $conexion->real_escape_string($marca) . "', '" . $conexion->real_escape_string($modelo) . "', '" . $conexion->real_escape_string($color) . "', " . intval($id_usuarios) . ")";
    $conexion->query($sql);
    $result = $conexion->query($sql_check);
    $row = $result->fetch_assoc();
}

// ====== Archivos ======
$allowed_types = ['jpg','jpeg','png'];
$img_paths = [
    'licencia_img' => $row['licencia_de_conducir'],
    'tarjeta_img' => $row['tarjeta_de_propiedad'],
    'soat_img' => $row['soat'],
    'tecno_img' => $row['tecno_mecanica']
];

foreach ($img_paths as $campo => &$nombreArchivo) {
    if (isset($_FILES[$campo]) && $_FILES[$campo]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES[$campo]["tmp_name"]) === false) {
            continue; // Ignorar archivo inválido
        }

        // Eliminar archivo antiguo
        if (!empty($nombreArchivo)) {
            eliminarArchivoSupabase($nombreArchivo);
        }

        // Subir nuevo archivo
        $nombreNuevo = nombreArchivoUnico($id_usuarios, $campo, $ext);
        if (subirArchivoASupabase($_FILES[$campo]["tmp_name"], $nombreNuevo)) {
            $nombreArchivo = $nombreNuevo; // Guardamos solo el nombre en MySQL
        }
    }
}
unset($nombreArchivo);

// ====== Actualizar MySQL ======
$sql_update = "UPDATE documentos SET 
    licencia_de_conducir='" . $conexion->real_escape_string($img_paths['licencia_img']) . "', 
    tarjeta_de_propiedad='" . $conexion->real_escape_string($img_paths['tarjeta_img']) . "', 
    soat='" . $conexion->real_escape_string($img_paths['soat_img']) . "', 
    tecno_mecanica='" . $conexion->real_escape_string($img_paths['tecno_img']) . "',
    placa='" . $conexion->real_escape_string($placa) . "', 
    marca='" . $conexion->real_escape_string($marca) . "', 
    modelo='" . $conexion->real_escape_string($modelo) . "', 
    color='" . $conexion->real_escape_string($color) . "' 
    WHERE id_usuarios = " . intval($id_usuarios);
$conexion->query($sql_update);

// ====== Redirigir ======
header("Location: ../pages/inicio.php");
exit();
?>
