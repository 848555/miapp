<?php
session_start();

include(__DIR__ . '/../../config/conexion.php');
include(__DIR__ . '/validar_permiso_directo.php');

// Asegura que sea un método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_admin = $_SESSION['id_usuario'] ?? 0;

    // Validar permiso: módulo 6 = Gestión de Permisos, acciones 8 o 9
    if (!tienePermiso($id_admin, 6, 8) && !tienePermiso($id_admin, 6, 9)) {
        echo "<script>
            alert('No tienes permiso para asignar o quitar permisos.');
            window.location = '../pages/principal.php';
        </script>";
        exit();
    }

    $id_admin_form = isset($_POST['id_admin']) ? intval($_POST['id_admin']) : 0;
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    // Validación de ID de administrador
    if ($id_admin_form <= 0) {
        header("Location:../pages/gestion_de_permisos.php?error=ID de administrador inválido.");
        exit();
    }

    // Eliminar permisos anteriores
    $sql_delete = "DELETE FROM permisos_detallados WHERE id_admin = ?";
    $stmt_delete = $conexion->prepare($sql_delete);
    if (!$stmt_delete) {
        header("Location:../pages/gestion_de_permisos.php?error=Error al preparar la eliminación.");
        exit();
    }
    $stmt_delete->bind_param("i", $id_admin_form);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Insertar nuevos permisos
    if (!empty($permisos)) {
        $sql_insert = "INSERT INTO permisos_detallados (id_admin, id_modulo, id_accion_modulo, permitido) VALUES (?, ?, ?, 1)";
        $stmt_insert = $conexion->prepare($sql_insert);
        if (!$stmt_insert) {
            header("Location:../pages/gestion_de_permisos.php?error=Error al preparar la inserción.");
            exit();
        }

        foreach ($permisos as $permiso) {
            list($id_modulo_val, $id_accion_val) = explode('-', $permiso);
            $id_modulo = intval($id_modulo_val);
            $id_accion_modulo = intval($id_accion_val);
            $stmt_insert->bind_param("iii", $id_admin_form, $id_modulo, $id_accion_modulo);
            $stmt_insert->execute();
        }

        $stmt_insert->close();
    }

    // Redirigir con éxito
    header("Location:../pages/gestion_de_permisos.php?id_admin=$id_admin_form&success=1");
    exit();
} else {
    header("Location:../pages/gestion_de_permisos.php?error=Acceso no permitido.");
    exit();
}
?>
