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

// Función para subir archivo a Supabase Storage
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
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("cURL Error: " . $err);
        return false;
    }

    return ($http_code >= 200 && $http_code < 300);
}

// Función para eliminar archivo en Supabase
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
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("Error al eliminar archivo: " . $err);
        return false;
    }

    return ($http_code >= 200 && $http_code < 300);
}

// Construir URL pública
function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
}

// Nombre de archivo fijo
function nombreArchivoFijo($id, $tipo, $ext) {
    return "{$id}_{$tipo}." . $ext;
}

// Recoger datos del formulario
$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";

$id_usuarios = $_SESSION['id_usuario'] ?? "";
if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si ya existe registro
    $sql_check = "SELECT * FROM documentos WHERE id_usuarios = $id_usuarios LIMIT 1";
    $result = $conexion->query($sql_check);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['id_documentos'];
    } else {
        // Crear nuevo registro
        $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
                VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios')";
        if ($conexion->query($sql) === TRUE) {
            $last_id = $conexion->insert_id;
            $row = [
                'licencia_de_conducir' => "",
                'tarjeta_de_propiedad' => "",
                'soat' => "",
                'tecno_mecanica' => ""
            ];
        } else {
            echo "Error: " . $conexion->error;
            exit();
        }
    }

    $allowed_types = ['jpg', 'jpeg', 'png'];

    // === LICENCIA ===
    if (isset($_FILES["licencia_de_conducir"]) && $_FILES["licencia_de_conducir"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["licencia_de_conducir"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["licencia_de_conducir"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo licencia inválido.";
            exit();
        }
        $nombre_base = nombreArchivoFijo($last_id, "licencia", $ext);

        if (!empty($row['licencia_de_conducir'])) {
            $nombre_antiguo = basename(parse_url($row['licencia_de_conducir'], PHP_URL_PATH));
            eliminarArchivoSupabase($nombre_antiguo);
        }

        if (!subirArchivoASupabase($_FILES["licencia_de_conducir"]["tmp_name"], $nombre_base)) {
            die("Error al subir la licencia.");
        }
        $licencia_de_conducir_url = obtenerUrlPublica($nombre_base) . "?t=" . time();
    } else {
        $licencia_de_conducir_url = $row['licencia_de_conducir'];
    }

    // === TARJETA ===
    if (isset($_FILES["tarjeta_de_propiedad"]) && $_FILES["tarjeta_de_propiedad"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tarjeta_de_propiedad"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["tarjeta_de_propiedad"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo tarjeta inválido.";
            exit();
        }
        $nombre_base = nombreArchivoFijo($last_id, "tarjeta", $ext);

        if (!empty($row['tarjeta_de_propiedad'])) {
            $nombre_antiguo = basename(parse_url($row['tarjeta_de_propiedad'], PHP_URL_PATH));
            eliminarArchivoSupabase($nombre_antiguo);
        }

        if (!subirArchivoASupabase($_FILES["tarjeta_de_propiedad"]["tmp_name"], $nombre_base)) {
            die("Error al subir la tarjeta.");
        }
        $tarjeta_de_propiedad_url = obtenerUrlPublica($nombre_base) . "?t=" . time();
    } else {
        $tarjeta_de_propiedad_url = $row['tarjeta_de_propiedad'];
    }

    // === SOAT ===
    if (isset($_FILES["soat"]) && $_FILES["soat"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["soat"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["soat"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo SOAT inválido.";
            exit();
        }
        $nombre_base = nombreArchivoFijo($last_id, "soat", $ext);

        if (!empty($row['soat'])) {
            $nombre_antiguo = basename(parse_url($row['soat'], PHP_URL_PATH));
            eliminarArchivoSupabase($nombre_antiguo);
        }

        if (!subirArchivoASupabase($_FILES["soat"]["tmp_name"], $nombre_base)) {
            die("Error al subir el SOAT.");
        }
        $soat_url = obtenerUrlPublica($nombre_base) . "?t=" . time();
    } else {
        $soat_url = $row['soat'];
    }

    // === TECNOMECÁNICA ===
    if (isset($_FILES["tecno_mecanica"]) && $_FILES["tecno_mecanica"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tecno_mecanica"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["tecno_mecanica"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo tecnomecánica inválido.";
            exit();
        }
        $nombre_base = nombreArchivoFijo($last_id, "tecno", $ext);

        if (!empty($row['tecno_mecanica'])) {
            $nombre_antiguo = basename(parse_url($row['tecno_mecanica'], PHP_URL_PATH));
            eliminarArchivoSupabase($nombre_antiguo);
        }

        if (!subirArchivoASupabase($_FILES["tecno_mecanica"]["tmp_name"], $nombre_base)) {
            die("Error al subir la tecnomecánica.");
        }
        $tecno_mecanica_url = obtenerUrlPublica($nombre_base) . "?t=" . time();
    } else {
        $tecno_mecanica_url = $row['tecno_mecanica'];
    }

    // === Actualizar DB ===
    $sql_update = "UPDATE documentos SET 
                    placa = ?, 
                    marca = ?, 
                    modelo = ?, 
                    color = ?, 
                    licencia_de_conducir = ?, 
                    tarjeta_de_propiedad = ?, 
                    soat = ?, 
                    tecno_mecanica = ?
                   WHERE id_documentos = ?";

    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("ssssssssi", 
        $placa, 
        $marca, 
        $modelo, 
        $color, 
        $licencia_de_conducir_url, 
        $tarjeta_de_propiedad_url, 
        $soat_url, 
        $tecno_mecanica_url, 
        $last_id
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "<p style='color: green;'>Documentos actualizados correctamente.</p>";
        header("Location: ../pages/sermototaxista.php");
        exit();
    } else {
        echo "Error al actualizar documentos: " . $stmt->error;
        exit();
    }
}
?>
