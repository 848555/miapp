<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

// Configuración Supabase
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...');
define('SUPABASE_STORAGE_BUCKET', 'documentos');

// ---- Funciones ---- //
function subirArchivoASupabase($fileTmpPath, $fileName) {
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName . "?upsert=true";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => file_get_contents($fileTmpPath),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_ANON_KEY,
            "Content-Type: application/octet-stream"
        ],
    ]);
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return ($http_code >= 200 && $http_code < 300);
}

function eliminarArchivoSupabase($fileName) {
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_ANON_KEY
        ],
    ]);
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return ($http_code >= 200 && $http_code < 300);
}

function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName . "?t=" . time();
}

function nombreArchivoFijo($id, $tipo, $extension) {
    return "{$id}_{$tipo}.{$extension}";
}

// ---- Procesar datos ---- //
$id_usuarios = $_SESSION['id_usuario'] ?? "";
if (empty($id_usuarios)) {
    die("Error: usuario no identificado.");
}

$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";

// Verificar si el registro existe
$sql_check = "SELECT * FROM documentos WHERE id_usuarios = $id_usuarios LIMIT 1";
$result = $conexion->query($sql_check);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_id = $row['id_documentos'];
} else {
    $conexion->query("INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
                      VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios')");
    $last_id = $conexion->insert_id;
    $row = ['licencia_de_conducir' => '', 'tarjeta_de_propiedad' => '', 'soat' => '', 'tecno_mecanica' => ''];
}

$allowed_types = ['jpg', 'jpeg', 'png'];
$img_paths = [
    'licencia_de_conducir' => $row['licencia_de_conducir'],
    'tarjeta_de_propiedad' => $row['tarjeta_de_propiedad'],
    'soat'                 => $row['soat'],
    'tecno_mecanica'       => $row['tecno_mecanica']
];

function procesarDocumento($campo, $tipo, &$img_paths, $row, $last_id, $allowed_types) {
    if (isset($_FILES[$campo]) && $_FILES[$campo]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES[$campo]["tmp_name"]) === false) {
            die("Archivo $tipo inválido.");
        }
        // Eliminar anterior si existe
        if (!empty($row[$campo])) {
            $nombre_anterior = basename(parse_url($row[$campo], PHP_URL_PATH));
            eliminarArchivoSupabase($nombre_anterior);
        }
        // Subir nuevo
        $nombre_nuevo = nombreArchivoFijo($last_id, $tipo, $ext);
        if (subirArchivoASupabase($_FILES[$campo]["tmp_name"], $nombre_nuevo)) {
            $img_paths[$campo] = obtenerUrlPublica($nombre_nuevo);
        } else {
            die("Error al subir $tipo.");
        }
    }
}

// Procesar cada archivo
procesarDocumento('licencia_de_conducir', 'licencia', $img_paths, $row, $last_id, $allowed_types);
procesarDocumento('tarjeta_de_propiedad', 'tarjeta', $img_paths, $row, $last_id, $allowed_types);
procesarDocumento('soat', 'soat', $img_paths, $row, $last_id, $allowed_types);
procesarDocumento('tecno_mecanica', 'tecno', $img_paths, $row, $last_id, $allowed_types);

// Guardar cambios en BD
$sql_update = "UPDATE documentos SET 
    placa='$placa', 
    marca='$marca', 
    modelo='$modelo', 
    color='$color', 
    licencia_de_conducir='{$img_paths['licencia_de_conducir']}', 
    tarjeta_de_propiedad='{$img_paths['tarjeta_de_propiedad']}', 
    soat='{$img_paths['soat']}', 
    tecno_mecanica='{$img_paths['tecno_mecanica']}' 
    WHERE id_documentos=$last_id";

if ($conexion->query($sql_update)) {
    $_SESSION['mensaje'] = "Documentos subidos correctamente.";
    header("Location: ../pages/sermototaxista.php");
    exit();
} else {
    echo "Error al actualizar la base de datos: " . $conexion->error;
}
?>
