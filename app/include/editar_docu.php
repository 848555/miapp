<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '../../../config/conexion.php');

// 🚨 CONFIGURACIÓN SUPABASE
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw');
define('SUPABASE_BUCKET', 'documentos');

// Función para subir archivo a Supabase
function subirASupabase($fileTmp, $fileName) {
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_BUCKET . "/" . $fileName;
    $fileData = file_get_contents($fileTmp);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $fileData,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . SUPABASE_KEY,
            'Content-Type: application/octet-stream'
        ],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new Exception("Error subiendo archivo a Supabase ($status): $response");
    }

    // Devolver ruta relativa que guardaremos en BD
    return $fileName;
}

// Recoger datos del formulario
$placa  = $_POST["placa"] ?? "";
$marca  = $_POST["marca"] ?? "";
$modelo = $_POST["modelo"] ?? "";
$color  = $_POST["color"] ?? "";

// Obtener id del usuario
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
        // Insertar nuevo
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
            die("Error: " . $conexion->error);
        }
    }
    
    // Tipos permitidos
    $allowed_types = ['jpg', 'jpeg', 'png'];

    // --- Licencia ---
    if (isset($_FILES["licencia_de_conducir"]) && $_FILES["licencia_de_conducir"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["licencia_de_conducir"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["licencia_de_conducir"]["tmp_name"]) === false) {
            die("Archivo de licencia inválido.");
        }
        $licencia_de_conducir_name = $last_id . "_licencia." . $ext;
        $licencia_de_conducir_name = subirASupabase($_FILES["licencia_de_conducir"]["tmp_name"], $licencia_de_conducir_name);
    } else {
        $licencia_de_conducir_name = $row['licencia_de_conducir'];
    }

    // --- Tarjeta ---
    if (isset($_FILES["tarjeta_de_propiedad"]) && $_FILES["tarjeta_de_propiedad"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tarjeta_de_propiedad"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["tarjeta_de_propiedad"]["tmp_name"]) === false) {
            die("Archivo de tarjeta inválido.");
        }
        $tarjeta_de_propiedad_name = $last_id . "_tarjeta." . $ext;
        $tarjeta_de_propiedad_name = subirASupabase($_FILES["tarjeta_de_propiedad"]["tmp_name"], $tarjeta_de_propiedad_name);
    } else {
        $tarjeta_de_propiedad_name = $row['tarjeta_de_propiedad'];
    }

    // --- Soat ---
    if (isset($_FILES["soat"]) && $_FILES["soat"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["soat"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["soat"]["tmp_name"]) === false) {
            die("Archivo de SOAT inválido.");
        }
        $soat_name = $last_id . "_soat." . $ext;
        $soat_name = subirASupabase($_FILES["soat"]["tmp_name"], $soat_name);
    } else {
        $soat_name = $row['soat'];
    }

    // --- Tecno ---
    if (isset($_FILES["tecno_mecanica"]) && $_FILES["tecno_mecanica"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tecno_mecanica"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["tecno_mecanica"]["tmp_name"]) === false) {
            die("Archivo de tecnomecánica inválido.");
        }
        $tecno_mecanica_name = $last_id . "_tecno." . $ext;
        $tecno_mecanica_name = subirASupabase($_FILES["tecno_mecanica"]["tmp_name"], $tecno_mecanica_name);
    } else {
        $tecno_mecanica_name = $row['tecno_mecanica'];
    }

    // Guardar en BD
    $sql_update = "UPDATE documentos SET 
                    placa = '$placa', 
                    marca = '$marca', 
                    modelo = '$modelo', 
                    color = '$color', 
                    licencia_de_conducir = '$licencia_de_conducir_name', 
                    tarjeta_de_propiedad = '$tarjeta_de_propiedad_name', 
                    soat = '$soat_name', 
                    tecno_mecanica = '$tecno_mecanica_name' 
                   WHERE id_documentos = $last_id";
    
    if ($conexion->query($sql_update) === TRUE) {
        $_SESSION['success_message'] = "Documentos actualizados correctamente.";
         header("Location: ../pages/sermototaxista.php");
        exit();
    } else {
        die("Error: " . $conexion->error);
    }
}
?>
