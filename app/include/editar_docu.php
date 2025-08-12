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

// Función para subir archivo a Supabase con upsert
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
            'Content-Type: application/octet-stream',
            'x-upsert: true'  // importante para actualizar si ya existe
        ],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new Exception("Error subiendo archivo a Supabase ($status): $response");
    }

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

    // Archivos recibidos
    $files = [
        "licencia_de_conducir" => $_FILES["licencia_de_conducir"] ?? null,
        "tarjeta_de_propiedad" => $_FILES["tarjeta_de_propiedad"] ?? null,
        "soat" => $_FILES["soat"] ?? null,
        "tecno_mecanica" => $_FILES["tecno_mecanica"] ?? null,
    ];

    $img_paths = [];

    foreach ($files as $key => $file) {
        if ($file && $file["error"] === 0) {
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_types)) {
                $_SESSION['error_message'] = "El archivo $key no es un tipo permitido.";
                // Puedes decidir si continuar o abortar aquí
                break;
            }
            if (getimagesize($file["tmp_name"]) === false) {
                $_SESSION['error_message'] = "El archivo $key no es una imagen válida.";
                break;
            }
            $final_name = $last_id . "_" . $key . "." . $ext;

            try {
                $ruta = subirASupabase($file["tmp_name"], $final_name);
                $img_paths[$key] = $final_name;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Error al subir $key: " . $e->getMessage();
                break;
            }
        } else {
            // Mantener nombre actual en BD si no hay nuevo archivo
            $img_paths[$key] = $row[$key] ?? "";
        }
    }

    // Actualizar base de datos solo si no hay error
    if (!isset($_SESSION['error_message'])) {
        $sql_update = "UPDATE documentos SET 
                        placa = '$placa', 
                        marca = '$marca', 
                        modelo = '$modelo', 
                        color = '$color', 
                        licencia_de_conducir = '{$img_paths['licencia_de_conducir']}', 
                        tarjeta_de_propiedad = '{$img_paths['tarjeta_de_propiedad']}', 
                        soat = '{$img_paths['soat']}', 
                        tecno_mecanica = '{$img_paths['tecno_mecanica']}' 
                       WHERE id_documentos = $last_id";
        
        if ($conexion->query($sql_update) === TRUE) {
            $_SESSION['success_message'] = "Documentos actualizados correctamente.";
        } else {
            $_SESSION['error_message'] = "Error al actualizar en BD: " . $conexion->error;
        }
    }
}
?>
