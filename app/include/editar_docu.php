<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Iniciar sesión si no está iniciada

include(__DIR__ . '../../../config/conexion.php');

// Ruta donde se guardan las imágenes
$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/app/assets/imagen/";

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
    // Primero, verificamos si ya existe un registro de documentos para este usuario
    $sql_check = "SELECT * FROM documentos WHERE id_usuarios = $id_usuarios LIMIT 1";
    $result = $conexion->query($sql_check);
    
    if ($result && $result->num_rows > 0) {
        // Si ya existe, obtener el registro y su id_documentos
        $row = $result->fetch_assoc();
        $last_id = $row['id_documentos'];
    } else {
        // Si no existe, insertar un nuevo registro con los datos de texto
        $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) 
                VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios')";
        if ($conexion->query($sql) === TRUE) {
            $last_id = $conexion->insert_id;
            // Como es un registro nuevo, no hay archivos previos
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
    
    // Definir tipos de archivos permitidos
    $allowed_types = ['jpg', 'jpeg', 'png'];

    // --- Procesar archivo de Licencia ---
    if (isset($_FILES["licencia_de_conducir"]) && $_FILES["licencia_de_conducir"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["licencia_de_conducir"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            $_SESSION['error_archivo'] = "El archivo de licencia no es un tipo permitido.";
            exit();
        }
        if (getimagesize($_FILES["licencia_de_conducir"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "El archivo de licencia no es una imagen válida.";
            exit();
        }
        $licencia_de_conducir_name = $last_id . "_licencia." . $ext;
        $licencia_de_conducir_path = $target_dir . $licencia_de_conducir_name;
        if (!move_uploaded_file($_FILES["licencia_de_conducir"]["tmp_name"], $licencia_de_conducir_path)) {
            die("Error al subir la imagen de la licencia.");
        }
    } else {
        // Si no se sube un nuevo archivo, se conserva el valor actual
        $licencia_de_conducir_name = $row['licencia_de_conducir'];
    }
    
    // --- Procesar archivo de Tarjeta de Propiedad ---
    if (isset($_FILES["tarjeta_de_propiedad"]) && $_FILES["tarjeta_de_propiedad"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tarjeta_de_propiedad"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            $_SESSION['error_archivo'] = "El archivo de tarjeta de propiedad no es un tipo permitido.";
            exit();
        }
        if (getimagesize($_FILES["tarjeta_de_propiedad"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "El archivo de tarjeta de propiedad no es una imagen válida.";
            exit();
        }
        $tarjeta_de_propiedad_name = $last_id . "_tarjeta." . $ext;
        $tarjeta_de_propiedad_path = $target_dir . $tarjeta_de_propiedad_name;
        if (!move_uploaded_file($_FILES["tarjeta_de_propiedad"]["tmp_name"], $tarjeta_de_propiedad_path)) {
            die("Error al subir la imagen de la tarjeta de propiedad.");
        }
    } else {
        $tarjeta_de_propiedad_name = $row['tarjeta_de_propiedad'];
    }
    
    // --- Procesar archivo de Soat ---
    if (isset($_FILES["soat"]) && $_FILES["soat"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["soat"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            $_SESSION['error_archivo'] = "El archivo de SOAT no es un tipo permitido.";
            exit();
        }
        if (getimagesize($_FILES["soat"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "El archivo de SOAT no es una imagen válida.";
            exit();
        }
        $soat_name = $last_id . "_soat." . $ext;
        $soat_path = $target_dir . $soat_name;
        if (!move_uploaded_file($_FILES["soat"]["tmp_name"], $soat_path)) {
            die("Error al subir la imagen del SOAT.");
        }
    } else {
        $soat_name = $row['soat'];
    }
    
    // --- Procesar archivo de Tecnomecánica ---
    if (isset($_FILES["tecno_mecanica"]) && $_FILES["tecno_mecanica"]["error"] === 0) {
        $ext = strtolower(pathinfo($_FILES["tecno_mecanica"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            $_SESSION['error_archivo'] = "El archivo de tecnomecánica no es un tipo permitido.";
            exit();
        }
        if (getimagesize($_FILES["tecno_mecanica"]["tmp_name"]) === false) {
            $_SESSION['error_archivo'] = "El archivo de tecnomecánica no es una imagen válida.";
            exit();
        }
        $tecno_mecanica_name = $last_id . "_tecno." . $ext;
        $tecno_mecanica_path = $target_dir . $tecno_mecanica_name;
        if (!move_uploaded_file($_FILES["tecno_mecanica"]["tmp_name"], $tecno_mecanica_path)) {
            die("Error al subir la imagen de la tecnomecánica.");
        }
    } else {
        $tecno_mecanica_name = $row['tecno_mecanica'];
    }
    
    // Actualizar la base de datos con los nuevos datos y rutas (o conservar los existentes)
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
        $_SESSION['success_message'] = "<p style='color: green;'>Documentos actualizados correctamente, ya puedes aceptar un servicio.</p>";
        header("Location: ../pages/sermototaxista.php");
        exit();
    } else {
        echo "Error: " . $sql_update . "<br>" . $conexion->error;
        exit();
    }
}
?>
