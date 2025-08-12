<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '../../../config/conexion.php');

define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_KEY', 'TU_SUPABASE_SERVICE_ROLE_KEY_AQUI'); // Cambia por tu key real
define('SUPABASE_BUCKET', 'documentos');

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
            'x-upsert: true'
        ],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 200 && $status < 300) {
        return "$fileName";
    } else {
        return false;
    }
}

$placa  = $conexion->real_escape_string($_POST["placa"] ?? "");
$marca  = $conexion->real_escape_string($_POST["marca"] ?? "");
$modelo = $conexion->real_escape_string($_POST["modelo"] ?? "");
$color  = $conexion->real_escape_string($_POST["color"] ?? "");

$id_usuarios = $_SESSION['id_usuario'] ?? "";
if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificar si el usuario ya tiene documentos (para update o error)
    $sql_check = "SELECT * FROM documentos WHERE id_usuarios = '$id_usuarios' LIMIT 1";
    $result = $conexion->query($sql_check);

    if ($result && $result->num_rows > 0) {
        // Registro existente - hacemos update
        $row = $result->fetch_assoc();
        $last_id = (int)$row['id_documentos'];

        // Rutas actuales para mantener si no suben archivo nuevo
        $rutas_actuales = [
            'licencia_de_conducir' => $row['licencia_de_conducir'],
            'tarjeta_de_propiedad' => $row['tarjeta_de_propiedad'],
            'soat' => $row['soat'],
            'tecno_mecanica' => $row['tecno_mecanica']
        ];

    } else {
        // No existe, insertamos nuevo registro sin rutas
        $sql_insert = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios, documento_verificado) 
                       VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios', 0)";
        if (!$conexion->query($sql_insert)) {
            die("Error al insertar documentos: " . $conexion->error);
        }
        $last_id = $conexion->insert_id;

        // Inicializamos rutas vacías para el insert nuevo
        $rutas_actuales = [
            'licencia_de_conducir' => "",
            'tarjeta_de_propiedad' => "",
            'soat' => "",
            'tecno_mecanica' => ""
        ];
    }

    $allowed_types = ['jpg', 'jpeg', 'png'];
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
                break;
            }
            if (getimagesize($file["tmp_name"]) === false) {
                $_SESSION['error_message'] = "El archivo $key no es una imagen válida.";
                break;
            }
            $final_name = $last_id . "_" . $key . "." . $ext;

            $ruta = subirASupabase($file["tmp_name"], $final_name);
            if ($ruta === false) {
                $_SESSION['error_message'] = "Error al subir $key a Supabase.";
                break;
            }
            $img_paths[$key] = $final_name;
        } else {
            // Mantener ruta anterior si no suben nuevo archivo
            $img_paths[$key] = $rutas_actuales[$key] ?? "";
        }
    }

    if (!isset($_SESSION['error_message'])) {
        // Actualizar tabla documentos con datos y rutas
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
            $_SESSION['mensaje'] = ($result->num_rows > 0) ? "Documentos actualizados correctamente." : "Documentos subidos correctamente.";
            header("Location: ../pages/inicio.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error al actualizar en BD: " . $conexion->error;
        }
    }
}
?>
