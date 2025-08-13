<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

// Configuración Supabase
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_ANON_KEY', 'TU_API_KEY');
define('SUPABASE_STORAGE_BUCKET', 'documentos');

function subirArchivoASupabase($fileTmpPath, $fileName) {
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => file_get_contents($fileTmpPath),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_ANON_KEY,
            "Content-Type: application/octet-stream",
            "x-upsert: true"
        ],
    ]);
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return ($http_code >= 200 && $http_code < 300);
}

function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
}

function nombreArchivoUnico($id, $tipo, $extension) {
    return "{$id}_{$tipo}_" . date("Ymd_His") . ".{$extension}";
}

$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";
$id_usuarios = $_SESSION['id_usuario'] ?? "";

if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

$sql_check = "SELECT * FROM documentos WHERE id_usuarios = $id_usuarios LIMIT 1";
$result = $conexion->query($sql_check);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_id = $row['id_documentos'];
} else {
    $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
            VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios')";
    if ($conexion->query($sql) === TRUE) {
        $last_id = $conexion->insert_id;
        $row = ['licencia_de_conducir' => "", 'tarjeta_de_propiedad' => "", 'soat' => "", 'tecno_mecanica' => ""];
    } else {
        die("Error: " . $conexion->error);
    }
}

$allowed_types = ['jpg', 'jpeg', 'png'];
$img_paths = [
    'licencia_de_conducir' => $row['licencia_de_conducir'],
    'tarjeta_de_propiedad' => $row['tarjeta_de_propiedad'],
    'soat' => $row['soat'],
    'tecno_mecanica' => $row['tecno_mecanica']
];

foreach ($img_paths as $campo => &$url_guardada) {
    if (isset($_FILES[$campo]) && $_FILES[$campo]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES[$campo]["tmp_name"]) === false) {
            die("Archivo $campo inválido.");
        }
        $nombre_nuevo = nombreArchivoUnico($last_id, $campo, $ext);
        if (!subirArchivoASupabase($_FILES[$campo]["tmp_name"], $nombre_nuevo)) {
            die("Error al subir $campo.");
        }
        $url_guardada = obtenerUrlPublica($nombre_nuevo);
    }
}

$sql_update = "UPDATE documentos SET 
    licencia_de_conducir='{$img_paths['licencia_de_conducir']}', 
    tarjeta_de_propiedad='{$img_paths['tarjeta_de_propiedad']}', 
    soat='{$img_paths['soat']}', 
    tecno_mecanica='{$img_paths['tecno_mecanica']}',
    placa='$placa', 
    marca='$marca', 
    modelo='$modelo', 
    color='$color'
    WHERE id_documentos=$last_id";

if ($conexion->query($sql_update)) {
    $_SESSION['mensaje'] = "Documentos subidos correctamente.";
    header("Location: ../pages/sermototaxista.php");
    exit();
} else {
    echo "Error al actualizar la base de datos: " . $conexion->error;
}
?>
