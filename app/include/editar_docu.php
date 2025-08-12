<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Iniciar sesión si no está iniciada

include(__DIR__ . '../../../config/conexion.php');

// Configuración Supabase
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNjZndtaHd3amJ6aHNkdHF1c3J3Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1Mzg4ODExNiwiZXhwIjoyMDY5NDY0MTE2fQ.VL_ha2fmlgATu_ZRfknmXh_TkyDMhkWne4XojZ8qFWw'); // Usa tu key adecuada
define('SUPABASE_STORAGE_BUCKET', 'documentos'); // Cambia al bucket que uses

// Función para subir archivo a Supabase Storage vía API REST
function subirArchivoASupabase($fileTmpPath, $fileName) {
    $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;

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
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return false;
    } else {
        return true;
    }
}

// Construir URL pública para archivos (asumiendo bucket público)
function obtenerUrlPublica($fileName) {
    return SUPABASE_URL . "/storage/v1/object/public/" . SUPABASE_STORAGE_BUCKET . "/" . $fileName;
}

// Recoger datos de texto del formulario
$placa  = isset($_POST["placa"]) ? $_POST["placa"] : "";
$marca  = isset($_POST["marca"]) ? $_POST["marca"] : "";
$modelo = isset($_POST["modelo"]) ? $_POST["modelo"] : "";
$color  = isset($_POST["color"]) ? $_POST["color"] : "";

// Obtener el id_usuarios de la sesión
$id_usuarios = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : "";
if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si ya existe un registro para este usuario
    $sql_check = "SELECT * FROM documentos WHERE id_usuarios = $id_usuarios LIMIT 1";
    $result = $conexion->query($sql_check);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['id_documentos'];
    } else {
        // Insertar nuevo registro de texto
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
            echo "Error: " . $sql . "<br>" . $conexion->error;
            exit();
        }
    }

    $allowed_types = ['jpg', 'jpeg', 'png'];

    // Procesar cada archivo: si se sube, subir a Supabase, sino conservar URL previa

    // Licencia de conducir
    if (isset($_FILES["licencia_de_conducir"]) && $_FILES["licencia_de_conducir"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["licencia_de_conducir"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["licencia_de_conducir"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo licencia inválido.";
            exit();
        }
        $licencia_de_conducir_name = $last_id . "_licencia." . $ext;
        if (!subirArchivoASupabase($_FILES["licencia_de_conducir"]["tmp_name"], $licencia_de_conducir_name)) {
            die("Error al subir la licencia a Supabase Storage.");
        }
        $licencia_de_conducir_url = obtenerUrlPublica($licencia_de_conducir_name);
    } else {
        $licencia_de_conducir_url = $row['licencia_de_conducir'];
    }

    // Tarjeta de propiedad
    if (isset($_FILES["tarjeta_de_propiedad"]) && $_FILES["tarjeta_de_propiedad"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tarjeta_de_propiedad"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["tarjeta_de_propiedad"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo tarjeta inválido.";
            exit();
        }
        $tarjeta_de_propiedad_name = $last_id . "_tarjeta." . $ext;
        if (!subirArchivoASupabase($_FILES["tarjeta_de_propiedad"]["tmp_name"], $tarjeta_de_propiedad_name)) {
            die("Error al subir la tarjeta de propiedad a Supabase Storage.");
        }
        $tarjeta_de_propiedad_url = obtenerUrlPublica($tarjeta_de_propiedad_name);
    } else {
        $tarjeta_de_propiedad_url = $row['tarjeta_de_propiedad'];
    }

    // Soat
    if (isset($_FILES["soat"]) && $_FILES["soat"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["soat"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["soat"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo SOAT inválido.";
            exit();
        }
        $soat_name = $last_id . "_soat." . $ext;
        if (!subirArchivoASupabase($_FILES["soat"]["tmp_name"], $soat_name)) {
            die("Error al subir el SOAT a Supabase Storage.");
        }
        $soat_url = obtenerUrlPublica($soat_name);
    } else {
        $soat_url = $row['soat'];
    }

    // Tecnomecánica
    if (isset($_FILES["tecno_mecanica"]) && $_FILES["tecno_mecanica"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tecno_mecanica"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types) || getimagesize($_FILES["tecno_mecanica"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "Archivo tecnomecánica inválido.";
            exit();
        }
        $tecno_mecanica_name = $last_id . "_tecno." . $ext;
        if (!subirArchivoASupabase($_FILES["tecno_mecanica"]["tmp_name"], $tecno_mecanica_name)) {
            die("Error al subir la tecnomecánica a Supabase Storage.");
        }
        $tecno_mecanica_url = obtenerUrlPublica($tecno_mecanica_name);
    } else {
        $tecno_mecanica_url = $row['tecno_mecanica'];
    }

    // Actualizar base de datos con URLs públicas
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
        $_SESSION['success_message'] = "<p style='color: green;'>Documentos actualizados correctamente, ya puedes aceptar un servicio.</p>";
        header("Location: ../pages/sermototaxista.php");
        exit();
    } else {
        echo "Error al actualizar documentos: " . $stmt->error;
        exit();
    }
}
?>
