<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include(__DIR__ . '../../../config/conexion.php');

// Configuración Supabase
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw');
define('SUPABASE_STORAGE_BUCKET', 'documentos');

// Función para subir archivo
function subirArchivoASupabase($fileTmpPath, $fileName) {
    echo "<pre>📤 Subiendo: $fileName</pre>";
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
    $err = curl_error($curl);
    curl_close($curl);

    echo "<pre>PUT HTTP code: $http_code</pre>";
    echo "<pre>PUT Response: " . htmlspecialchars($response) . "</pre>";

    if ($err) {
        echo "<pre>❌ cURL Error: $err</pre>";
        return false;
    }
    return ($http_code >= 200 && $http_code < 300);
}

// Función para eliminar archivo
function eliminarArchivoSupabase($fileName) {
    echo "<pre>🗑 Eliminando: $fileName</pre>";
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
    $err = curl_error($curl);
    curl_close($curl);

    echo "<pre>DELETE HTTP code: $http_code</pre>";
    echo "<pre>DELETE Response: " . htmlspecialchars($response) . "</pre>";

    if ($err) {
        echo "<pre>❌ Error al eliminar archivo: $err</pre>";
        return false;
    }
    return ($http_code >= 200 && $http_code < 300);
}

// Construir URL pública
function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
}

// Nombre de archivo fijo
function nombreArchivoFijo($id, $tipo, $extension) {
    return "{$id}_{$tipo}.{$extension}";
}

// Datos formulario
$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";
$id_usuarios = $_SESSION['id_usuario'] ?? "";

if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Función para procesar cada archivo
    function procesarArchivo($campo, $tipo, &$url_guardada, $row, $last_id, $allowed_types) {
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
            if (!subirArchivoASupabase($_FILES[$campo]["tmp_name"], $nombre_nuevo)) {
                die("Error al subir $tipo.");
            }
            $url_guardada = obtenerUrlPublica($nombre_nuevo) . "?t=" . time();
        } else {
            $url_guardada = $row[$campo];
        }
    }

    // Procesar cada documento
    procesarArchivo("licencia_de_conducir", "licencia", $licencia_de_conducir_url, $row, $last_id, $allowed_types);
    procesarArchivo("tarjeta_de_propiedad", "tarjeta", $tarjeta_de_propiedad_url, $row, $last_id, $allowed_types);
    procesarArchivo("soat", "soat", $soat_url, $row, $last_id, $allowed_types);
    procesarArchivo("tecno_mecanica", "tecno", $tecno_mecanica_url, $row, $last_id, $allowed_types);

    // Guardar en BD
    $sql_update = "UPDATE documentos SET placa=?, marca=?, modelo=?, color=?, licencia_de_conducir=?, tarjeta_de_propiedad=?, soat=?, tecno_mecanica=? WHERE id_documentos=?";
    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("ssssssssi", $placa, $marca, $modelo, $color, $licencia_de_conducir_url, $tarjeta_de_propiedad_url, $soat_url, $tecno_mecanica_url, $last_id);

    if ($stmt->execute()) {
        echo "<pre>✅ Documentos actualizados correctamente.</pre>";
    } else {
        echo "<pre>❌ Error al actualizar documentos: {$stmt->error}</pre>";
    }
}
?>
