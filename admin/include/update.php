<?php
session_start();

// Conexión a la base de datos
include(__DIR__ . '../../../config/conexion.php');

// Validar que el ID no esté vacío
if (!isset($_POST["id"]) || empty($_POST["id"])) {
    $_SESSION['error_message'] = "ID de usuario no válido.";
    header("Location: ../../../pages/principal.php");
    exit;
}

$id = $_POST["id"];

// Escapar datos para evitar inyección SQL
$nombres = isset($_POST["nombres"]) ? mysqli_real_escape_string($conexion, trim($_POST["nombres"])) : '';
$apellidos = isset($_POST["apellidos"]) ? mysqli_real_escape_string($conexion, trim($_POST["apellidos"])) : '';
$dni = isset($_POST["dni"]) ? mysqli_real_escape_string($conexion, trim($_POST["dni"])) : '';
$fecha = isset($_POST["fecha"]) ? mysqli_real_escape_string($conexion, trim($_POST["fecha"])) : NULL;
$telefono = isset($_POST["telefono"]) ? mysqli_real_escape_string($conexion, trim($_POST["telefono"])) : '';
$departamento = isset($_POST["departamento"]) ? mysqli_real_escape_string($conexion, trim($_POST["departamento"])) : '';
$ciudad = isset($_POST["ciudad"]) ? mysqli_real_escape_string($conexion, trim($_POST["ciudad"])) : '';
$direccion = isset($_POST["direccion"]) ? mysqli_real_escape_string($conexion, trim($_POST["direccion"])) : '';
$usuario = isset($_POST["usuario"]) ? mysqli_real_escape_string($conexion, trim($_POST["usuario"])) : '';
$password = isset($_POST["password"]) ? mysqli_real_escape_string($conexion, trim($_POST["password"])) : '';
$estado = isset($_POST["estado"]) ? mysqli_real_escape_string($conexion, trim($_POST["estado"])) : '';
$rol = isset($_POST["rol"]) ? (int) $_POST["rol"] : 0;

// Consulta para obtener los roles permitidos
$sql_roles = "SELECT id FROM roles WHERE id IN (1, 2)";
$result_roles = $conexion->query($sql_roles);
$roles_permitidos = [];
while ($row = $result_roles->fetch_assoc()) {
    $roles_permitidos[] = (int) $row['id'];
}

// Validar rol permitido
if (!in_array($rol, $roles_permitidos)) {
    $_SESSION['error_message'] = "<p style='color: red;'>Rol seleccionado no válido.</p>";
    header("Location: ../pages/principal.php");
    exit;
}

// Obtener datos actuales del usuario
$sql_select = "SELECT * FROM usuarios WHERE id_usuarios='$id'";
$resultado_select = $conexion->query($sql_select);

if ($resultado_select->num_rows > 0) {
    $fila = $resultado_select->fetch_assoc();
    $update_fields = [];

    if ($nombres !== $fila['Nombres']) $update_fields[] = "Nombres='$nombres'";
    if ($apellidos !== $fila['Apellidos']) $update_fields[] = "Apellidos='$apellidos'";
    if ($dni !== $fila['DNI']) $update_fields[] = "DNI='$dni'";
    if ($fecha !== $fila['fecha_de_nacimiento']) $update_fields[] = "fecha_de_nacimiento='$fecha'";
    if ($telefono !== $fila['telefono']) $update_fields[] = "telefono='$telefono'";
    if ($departamento !== $fila['Departamento']) $update_fields[] = "Departamento='$departamento'";
    if ($ciudad !== $fila['Ciudad']) $update_fields[] = "Ciudad='$ciudad'";
    if ($direccion !== $fila['Direccion']) $update_fields[] = "Direccion='$direccion'";
    if ($usuario !== $fila['Usuario']) $update_fields[] = "Usuario='$usuario'";
    if ($password !== $fila['Password']) $update_fields[] = "Password='$password'";
    if ($estado !== $fila['Estado']) $update_fields[] = "Estado='$estado'";
    if ($rol !== (int) $fila['rol']) $update_fields[] = "rol='$rol'";

    if (!empty($update_fields)) {
        $update_query = "UPDATE usuarios SET " . implode(', ', $update_fields) . " WHERE id_usuarios='$id'";
        if ($conexion->query($update_query)) {
            $_SESSION['success_message'] = "<p style='color: green;'>Usuario actualizado correctamente.</p>";
        } else {
            $_SESSION['error_message'] = "Error al actualizar el usuario: " . $conexion->error;
        }
    }
    header("Location: ../pages/principal.php");
    exit;
} else {
    $_SESSION['error_message'] = "Usuario no encontrado.";
    header("Location: ../pages/principal.php");
    exit;
}

// Cerrar conexión
$conexion->close();
?>
