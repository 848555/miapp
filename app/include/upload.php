<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include(__DIR__ . '../../../config/conexion.php');

// Construir la ruta completa para el directorio de destino
$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/app/assets/imagen/";

// Asegurarse de que el directorio exista
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Obtener el id_usuarios de la sesión
$id_usuarios = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : "";
if (empty($id_usuarios)) {
    die('Error: el id_usuarios no está definido.');
}

// Verificar si el usuario ya tiene documentos registrados
$sql_check = "SELECT id_documentos FROM documentos WHERE id_usuarios = '$id_usuarios'";
$result_check = $conexion->query($sql_check);

if ($result_check->num_rows > 0) {
    // Si ya existen documentos, redirigir al usuario a sermototaxista.php
    $_SESSION['warning_message'] = "Ya has subido tus documentos previamente. No es necesario volver a subirlos.";
    header("Location: ../pages/sermototaxista.php");
    exit();
}

// Datos del formulario
$placa = isset($_POST["placa"]) ? $_POST["placa"] : "";
$marca = isset($_POST["marca"]) ? $_POST["marca"] : "";
$modelo = isset($_POST["modelo"]) ? $_POST["modelo"] : "";
$color = isset($_POST["color"]) ? $_POST["color"] : "";

$uploadOk = 1;  // Bandera para verificar si las subidas fueron exitosas

// Validación y manejo de archivos
if (isset($_POST["submit"])) {
    $files = [
        'licencia_de_conducir' => $_FILES["licencia_de_conducir"],
        'tarjeta_de_propiedad' => $_FILES["tarjeta_de_propiedad"],
        'soat' => $_FILES["soat"],
        'tecno_mecanica' => $_FILES["tecno_mecanica"]
    ];

    $allowed_types = ['jpg', 'jpeg', 'png']; // Tipos permitidos

    foreach ($files as $key => $file) {
        if ($file["error"] != UPLOAD_ERR_OK) {
            $_SESSION['error_archivo'] = "Error al subir el archivo " . $file["name"] . ": " . $file["error"];
            $uploadOk = 0;
            break;
        }

        // Verificar la extensión del archivo
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error_archivo'] = "El archivo " . $file["name"] . " no es un archivo permitido.";
            $uploadOk = 0;
            break;
        }

        // Verificar si el archivo es una imagen válida
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            $_SESSION['error_archivo'] = "El archivo " . $file["name"] . " no es una imagen válida.";
            $uploadOk = 0;
            break;
        }
    }

    if ($uploadOk == 1) {
        // Inserción de datos en la tabla documentos
        $sql = "INSERT INTO documentos (placa, marca, modelo, color, id_usuarios) VALUES ('$placa', '$marca', '$modelo', '$color', '$id_usuarios')";
        if ($conexion->query($sql) === TRUE) {
            $last_id = $conexion->insert_id;

            // Nombres de archivo generados para evitar conflictos
            $licencia_de_conducir_name = $last_id . "_licencia." . pathinfo($_FILES["licencia_de_conducir"]["name"], PATHINFO_EXTENSION);
            $tarjeta_de_propiedad_name = $last_id . "_tarjeta." . pathinfo($_FILES["tarjeta_de_propiedad"]["name"], PATHINFO_EXTENSION);
            $soat_name = $last_id . "_soat." . pathinfo($_FILES["soat"]["name"], PATHINFO_EXTENSION);
            $tecno_mecanica_name = $last_id . "_tecno." . pathinfo($_FILES["tecno_mecanica"]["name"], PATHINFO_EXTENSION);

            // Rutas completas de los archivos
            $licencia_de_conducir_path = $target_dir . $licencia_de_conducir_name;
            $tarjeta_de_propiedad_path = $target_dir . $tarjeta_de_propiedad_name;
            $soat_path = $target_dir . $soat_name;
            $tecno_mecanica_path = $target_dir . $tecno_mecanica_name;

            // Verificación y movimiento de los archivos
            if (
                move_uploaded_file($_FILES["licencia_de_conducir"]["tmp_name"], $licencia_de_conducir_path) &&
                move_uploaded_file($_FILES["tarjeta_de_propiedad"]["tmp_name"], $tarjeta_de_propiedad_path) &&
                move_uploaded_file($_FILES["soat"]["tmp_name"], $soat_path) &&
                move_uploaded_file($_FILES["tecno_mecanica"]["tmp_name"], $tecno_mecanica_path)
            ) {
                // Actualización de la base de datos con los nombres de archivo
                $sql_update = "UPDATE documentos SET 
                                licencia_de_conducir='$licencia_de_conducir_name', 
                                tarjeta_de_propiedad='$tarjeta_de_propiedad_name', 
                                soat='$soat_name', 
                                tecno_mecanica='$tecno_mecanica_name' 
                               WHERE id_documentos=$last_id";

                if ($conexion->query($sql_update) === TRUE) {
                    $_SESSION['success_message'] = "<p style='color: green;'>Documentos insertados correctamente, ya puedes aceptar un servicio.</p>";
                    header("Location: ../pages/sermototaxista.php");
                    exit();
                } else {
                    echo "Error al actualizar los documentos: " . $sql_update . "<br>" . $conexion->error;
                }
            } else {
                $_SESSION['error'] = "Error al mover los archivos.";
                header('Location: ../pages/registro_de_documentos.php');
                exit();
            }
        } else {
            echo "Error al insertar los documentos: " . $sql . "<br>" . $conexion->error;
        }
    } else {
        $_SESSION['error'] = "Lo siento, tu archivo no fue subido.";
        header('Location: ../pages/registro_de_documentos.php');
        exit();
    }
} else {
    echo "No se ha enviado el formulario";
}
?>
