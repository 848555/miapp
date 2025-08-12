<?php
ob_start(); // Evita el error de headers ya enviados

include(__DIR__ . '../../../config/conexion.php');
include(__DIR__ . '/validar_permiso_directo.php');

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔑 Configuración Supabase
define('SUPABASE_URL', 'https://ccfwmhwwjbzhsdtqusrw.supabase.co');
define('SUPABASE_KEY', 'TU_SERVICE_ROLE_API_KEY'); // Clave con rol service_role
define('SUPABASE_BUCKET', 'documentos');

// Función para eliminar archivos de Supabase
function eliminarDeSupabase($archivos = []) {
    foreach ($archivos as $archivo) {
        $url = SUPABASE_URL . "/storage/v1/object/" . SUPABASE_BUCKET . "/" . $archivo;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . SUPABASE_KEY
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            error_log("Error eliminando archivo de Supabase: $archivo - Respuesta: $response");
        }
    }
}

// Validar permiso: módulo 2 = Control de Documentos, acción 3 = eliminar
$id_admin = $_SESSION['id_usuario'] ?? 0;
if (!tienePermiso($id_admin, 2, 3)) {
    $_SESSION['error'] = "No tienes permiso para eliminar documentos.";
    header("Location: ../pages/mototaxistas.php");
    exit();
}

// Verificar si 'id' está configurado
if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        $_SESSION['error'] = "El ID no es válido.";
    } else {
        // 1️⃣ Obtener rutas de imágenes antes de borrar
        $sql_img = "SELECT licencia_de_conducir, tarjeta_de_propiedad, soat, tecno_mecanica 
                    FROM documentos WHERE id_documentos = ?";
        $stmt_img = $conexion->prepare($sql_img);
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $result_img = $stmt_img->get_result();
        $archivos_a_borrar = [];

        if ($row = $result_img->fetch_assoc()) {
            $archivos_a_borrar = array_filter($row); // Elimina valores vacíos
        }

        // 2️⃣ Eliminar registro en MySQL
        $sql = "DELETE FROM documentos WHERE id_documentos = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Registro eliminado exitosamente.";

                // 3️⃣ Eliminar imágenes en Supabase
                if (!empty($archivos_a_borrar)) {
                    eliminarDeSupabase($archivos_a_borrar);
                }

            } else {
                $_SESSION['error'] = "Error al eliminar el registro: " . $stmt->error;
            }
        } else {
            $_SESSION['error'] = "Error al preparar la consulta: " . $conexion->error;
        }
    }
} else {
    $_SESSION['error'] = "ID no configurado.";
}

$conexion->close();

// Redirigir
header("Location: ../pages/mototaxistas.php");
ob_end_flush();
exit();
?>

