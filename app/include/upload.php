<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

// 🚨 CONFIGURACIÓN SUPABASE
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co'); 
define('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw'); // No uses el anon key, debe ser el service_role

// Obtener el id_usuarios de la sesión
$id_usuarios = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : "";
if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

// Verificar si el usuario ya tiene documentos registrados
$sql_check = "SELECT id_documentos FROM documentos WHERE id_usuarios = '$id_usuarios'";
$result_check = $conexion->query($sql_check);

if ($result_check->num_rows > 0) {
    $_SESSION['warning_message'] = "Ya has subido tus documentos previamente.";
    header("Location: ../pages/sermototaxista.php");
    exit();
}

$placa = $_POST["placa"] ?? "";
$marca = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color = $_POST["color"] ?? "";

if (!isset($_POST["submit"])) {
    echo "No se ha enviado el formulario";
    exit();
}

$files = [
    'licencia_de_conducir' => $_FILES["licencia_de_conducir"],
    'tarjeta_de_propiedad' => $_FILES["tarjeta_de_propiedad"],
    'soat' => $_FILES["soat"],
    'tecno_mecanica' => $_FILES["tecno_mecanica"]
];

$allowed_types = ['jpg', 'jpeg', 'png'];
foreach ($files as $key => $file) {
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $_SESSION['error_archivo'] = "Error al subir el archivo " . $file["name"];
        header('Location: ../pages/registro_de_documentos.php');
        exit();
    }

    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        $_SESSION['error_archivo'] = "Archivo no permitido: " . $file["name"];
        header('Location: ../pages/registro_de_documentos.php');
        exit();
    }
}

// Insertar datos sin imágenes aún
$sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios')";
if (!$conexion->query($sql)) {
    die("Error al insertar documentos: " . $conexion->error);
}
$last_id = $conexion->insert_id;

// Subir archivos a Supabase
function subirASupabase($archivo_tmp, $nombre_final) {
    $bucket = 'documentos';
    $url = SUPABASE_URL . "/storage/v1/object/$bucket/$nombre_final";
    $data = file_get_contents($archivo_tmp);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . SUPABASE_KEY,
            'Content-Type: application/octet-stream',
            'x-upsert: true'
        ],
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 200 && $status < 300) {
        return "$bucket/$nombre_final";
    } else {
        return false;
    }
}

$img_paths = [];

foreach ($files as $key => $file) {
    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $final_name = $last_id . "_" . $key . "." . $ext;

    $ruta = subirASupabase($file["tmp_name"], $final_name);
    if ($ruta === false) {
        $_SESSION['error'] = "Error al subir $key a Supabase.";
        header('Location: ../pages/registro_de_documentos.php');
        exit();
    }
    $img_paths[$key] = $final_name;
}

// Actualizar base de datos con rutas de las imágenes
$sql_update = "UPDATE documentos SET 
    licencia_de_conducir='{$img_paths['licencia_de_conducir']}', 
    tarjeta_de_propiedad='{$img_paths['tarjeta_de_propiedad']}', 
    soat='{$img_paths['soat']}', 
    tecno_mecanica='{$img_paths['tecno_mecanica']}' 
    WHERE id_documentos=$last_id";

if ($conexion->query($sql_update)) {
    $_SESSION['success_message'] = "Documentos subidos correctamente.";
    header("Location: ../pages/sermototaxista.php");
    exit();
} else {
    echo "Error al actualizar la base de datos: " . $conexion->error;
}
?>
