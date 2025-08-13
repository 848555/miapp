<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

// Configuración Supabase
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw'); // Service role key
define('SUPABASE_STORAGE_BUCKET', 'documentos');

function extraerPathInterno($urlPublica) {
    if (!$urlPublica) return '';
    $prefix = SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/";
    return str_replace($prefix, '', strtok($urlPublica, '?')); // quita query params
}
echo '<pre>';
print_r($_FILES);
echo '</pre>';
exit();

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

    if ($http_code >= 200 && $http_code < 300) {
        return true;
    } else {
        echo "Error en Supabase upload ($http_code): $response";
        return false;
    }
}


function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName . "?v=" . time();
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
    die('Error: el id_usuarios no está definido.');
}

// ====== Obtener registro existente ======
$sql_check = "SELECT * FROM documentos WHERE id_usuarios = " . intval($id_usuarios) . " LIMIT 1";
$result = $conexion->query($sql_check);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    // Insertar nuevo registro si no existe
    $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
            VALUES ('" . $conexion->real_escape_string($placa) . "', '" . $conexion->real_escape_string($marca) . "', '" . $conexion->real_escape_string($modelo) . "', '" . $conexion->real_escape_string($color) . "', " . intval($id_usuarios) . ")";
    if (!$conexion->query($sql)) {
        die("Error: " . $conexion->error);
    }
    // Obtener el nuevo registro
    $result = $conexion->query($sql_check);
    $row = $result->fetch_assoc();
}

$allowed_types = ['jpg', 'jpeg', 'png'];

// Tomamos las rutas guardadas actuales, o de los inputs ocultos si vienen
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
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES[$campo]["tmp_name"]) === false) {
            die("Archivo $campo inválido.");
        }

        // Eliminar archivo anterior en Supabase
        if (!empty($url_guardada)) {
            eliminarArchivoSupabase($url_guardada);
        }

        $nombre_nuevo = nombreArchivoUnico($id_usuarios, $campo, $ext);
        if (!subirArchivoASupabase($_FILES[$campo]["tmp_name"], $nombre_nuevo)) {
            die("Error al subir $campo.");
        }

        $url_guardada = obtenerUrlPublica($nombre_nuevo);
    }
}
unset($url_guardada); // Romper referencia

// ====== Actualizar BD usando id_usuarios ======
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

if ($conexion->query($sql_update)) {
    $_SESSION['mensaje'] = "Documentos actualizados correctamente.";
    header("Location: ../pages/sermototaxista.php");
    exit();
} else {
    echo "Error al actualizar la base de datos: " . $conexion->error;
}
?>
