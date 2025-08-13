<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw');
define('SUPABASE_STORAGE_BUCKET', 'documentos');

// ====== Funciones para Supabase ======
function extraerPathInterno($urlPublica) {
    if (!$urlPublica) return '';
    $prefix = SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/";
    return str_replace($prefix, '', strtok($urlPublica, '?'));
}

function eliminarArchivoSupabase($urlPublica) {
    $pathInterno = extraerPathInterno($urlPublica);
    if (!$pathInterno) return true;
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $pathInterno;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_KEY
        ],
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
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ($http_code >= 200 && $http_code < 300);
}

function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName . "?v=" . time();
}

function nombreArchivoUnico($id, $tipo, $extension) {
    return "{$id}_{$tipo}_" . uniqid() . ".{$extension}";
}

function actualizarSupabase($id_usuarios, $data) {
    $url = SUPABASE_URL . "/rest/v1/documentos?id_usuarios=eq." . intval($id_usuarios);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "apikey: " . SUPABASE_KEY,
            "Authorization: Bearer " . SUPABASE_KEY,
            "Content-Type: application/json",
            "Prefer: return=representation"
        ],
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code >= 200 && $http_code < 300);
}

// ====== Datos ======
$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";
$id_usuarios = $_SESSION['id_usuario'] ?? "";

if (empty($id_usuarios)) die('Error: el id_usuarios no está definido.');

// ====== Obtener registro existente en MySQL ======
$sql_check = "SELECT * FROM documentos WHERE id_usuarios = " . intval($id_usuarios) . " LIMIT 1";
$result = $conexion->query($sql_check);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
            VALUES ('" . $conexion->real_escape_string($placa) . "', '" . $conexion->real_escape_string($marca) . "', '" . $conexion->real_escape_string($modelo) . "', '" . $conexion->real_escape_string($color) . "', " . intval($id_usuarios) . ")";
    $conexion->query($sql);
    $result = $conexion->query($sql_check);
    $row = $result->fetch_assoc();
}

$allowed_types = ['jpg','jpeg','png'];
$img_paths = [
    'licencia_de_conducir' => $_POST['licencia_actual'] ?? $row['licencia_de_conducir'],
    'tarjeta_de_propiedad' => $_POST['tarjeta_actual'] ?? $row['tarjeta_de_propiedad'],
    'soat' => $_POST['soat_actual'] ?? $row['soat'],
    'tecno_mecanica' => $_POST['tecno_actual'] ?? $row['tecno_mecanica']
];

// ====== Subida de archivos ======
foreach ($img_paths as $campo => &$url_guardada) {
    if (isset($_FILES[$campo]) && $_FILES[$campo]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES[$campo]["tmp_name"]) === false) die("Archivo $campo inválido.");

        if (!empty($url_guardada)) eliminarArchivoSupabase($url_guardada);

        $nombre_nuevo = nombreArchivoUnico($id_usuarios, $campo, $ext);
        if (!subirArchivoASupabase($_FILES[$campo]["tmp_name"], $nombre_nuevo)) die("Error al subir $campo.");

        $url_guardada = obtenerUrlPublica($nombre_nuevo);
    }
}
unset($url_guardada);

// ====== Actualizar MySQL ======
$sql_update = "UPDATE documentos SET 
    licencia_de_conducir='" . $conexion->real_escape_string($img_paths['licencia_de_conducir']) . "', 
    tarjeta_de_propiedad='" . $conexion->real_escape_string($img_paths['tarjeta_de_propiedad']) . "', 
    soat='" . $conexion->real_escape_string($img_paths['soat']) . "', 
    tecno_mecanica='" . $conexion->real_escape_string($img_paths['tecno_mecanica']) . "',
    placa='" . $conexion->real_escape_string($placa) . "', 
    marca='" . $conexion->real_escape_string($marca) . "', 
    modelo='" . $conexion->real_escape_string($modelo) . "', 
    color='" . $conexion->real_escape_string($color) . "'
    WHERE id_usuarios = " . intval($id_usuarios);
$conexion->query($sql_update);

// ====== Actualizar Supabase ======
$supabase_data = [
    "placa" => $placa,
    "marca" => $marca,
    "modelo" => $modelo,
    "color" => $color,
    "licencia_de_conducir" => $img_paths['licencia_de_conducir'],
    "tarjeta_de_propiedad" => $img_paths['tarjeta_de_propiedad'],
    "soat" => $img_paths['soat'],
    "tecno_mecanica" => $img_paths['tecno_mecanica']
];

if (actualizarSupabase($id_usuarios, $supabase_data)) {
    $_SESSION['mensaje'] = "Documentos actualizados correctamente en MySQL y Supabase.";
    header("Location: ../pages/sermototaxista.php");
    exit();
} else {
    echo "Error al actualizar Supabase.";
}
?>
