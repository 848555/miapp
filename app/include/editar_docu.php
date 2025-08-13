<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co'); 
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw'); // Service role key
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

function nombreArchivoUnico($id, $campo, $extension) {
    return "{$id}_{$campo}_" . uniqid() . ".{$extension}";
}

// ====== Datos recibidos ======
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
$sql_check = "SELECT * FROM documentos WHERE id_usuarios = ?";
$stmt = $conexion->prepare($sql_check);
$stmt->bind_param("i", $id_usuarios);
$stmt->execute();
$result = $stmt->get_result();
$row = ($result->num_rows > 0) ? $result->fetch_assoc() : null;
$stmt->close();

// ====== Crear registro si no existe ======
if (!$row) {
    $sql_insert = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    $stmt->bind_param("ssssi", $placa, $marca, $modelo, $color, $id_usuarios);
    $stmt->execute();
    $stmt->close();

    // Obtener el registro recién creado
    $stmt = $conexion->prepare($sql_check);
    $stmt->bind_param("i", $id_usuarios);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// ====== Manejo de archivos ======
$allowed_types = ['jpg','jpeg','png'];

$img_paths = [
    'licencia_de_conducir' => $row['licencia_de_conducir'],
    'tarjeta_de_propiedad'  => $row['tarjeta_de_propiedad'],
    'soat'                  => $row['soat'],
    'tecno_mecanica'        => $row['tecno_mecanica']
];

$input_names = [
    'licencia_de_conducir' => 'licencia_img',
    'tarjeta_de_propiedad' => 'tarjeta_img',
    'soat'                  => 'soat_img',
    'tecno_mecanica'        => 'tecno_img'
];

foreach ($img_paths as $campo => &$nombreArchivo) {
    $input = $input_names[$campo];

    if (isset($_FILES[$input]) && $_FILES[$input]['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES[$input]["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_types) || getimagesize($_FILES[$input]["tmp_name"]) === false) {
            continue; // Ignorar archivo inválido
        }

        // Eliminar archivo anterior
        if (!empty($nombreArchivo)) {
            eliminarArchivoSupabase($nombreArchivo);
        }

        // Subir nuevo archivo
        $nombreNuevo = nombreArchivoUnico($id_usuarios, $campo, $ext);
        if (subirArchivoASupabase($_FILES[$input]["tmp_name"], $nombreNuevo)) {
            $nombreArchivo = $nombreNuevo;
        }
    } elseif (isset($_POST[$campo . '_actual'])) {
        // Mantener archivo actual si no se subió uno nuevo
        $nombreArchivo = $_POST[$campo . '_actual'];
    }
}
unset($nombreArchivo);

// ====== Actualizar MySQL ======
$sql_update = "UPDATE documentos SET 
    licencia_de_conducir = ?, 
    tarjeta_de_propiedad = ?, 
    soat = ?, 
    tecno_mecanica = ?, 
    placa = ?, 
    marca = ?, 
    modelo = ?, 
    color = ?
    WHERE id_usuarios = ?";

$stmt = $conexion->prepare($sql_update);
$stmt->bind_param(
    "ssssssssi",
    $img_paths['licencia_de_conducir'],
    $img_paths['tarjeta_de_propiedad'],
    $img_paths['soat'],
    $img_paths['tecno_mecanica'],
    $placa,
    $marca,
    $modelo,
    $color,
    $id_usuarios
);
$stmt->execute();
$stmt->close();

// ====== Redirigir ======
header("Location: ../pages/inicio.php");
exit();
?>

