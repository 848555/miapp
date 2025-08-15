<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');
include(__DIR__ . '/validar_permiso_directo.php');

// ✅ Obtener ID del admin desde la sesión
$id_admin = $_SESSION['id_usuario'] ?? 0;

// ✅ Validar permiso: módulo 1 = Gestión de Usuarios, acción 1 = insertar
if (!tienePermiso($id_admin, 1, 1)) {
    echo "<script>alert('No tienes permiso para insertar usuarios'); window.location='../pages/principal.php';</script>";
    exit();
}
$nombres = $_POST["nombres"] ?? '';
$apellidos = $_POST["apellidos"] ?? '';
$dni = $_POST["dni"] ?? '';
$fecha = $_POST["fecha"] ?? '';
$telefono = $_POST["telefono"] ?? '';
$departamento = $_POST["departamento"] ?? '';
$ciudad = $_POST["ciudad"] ?? '';
$direccion = $_POST["direccion"] ?? '';
$usuario = $_POST["usuario"] ?? '';
$contraseña = $_POST["contraseña"] ?? '';
$estado = $_POST["estado"] ?? '';
$rol = $_POST["rol"] ?? '';

if (empty($nombres) || empty($apellidos) || empty($dni) || empty($fecha) || empty($telefono) || empty($departamento) || empty($ciudad) || empty($direccion) || empty($usuario) || empty($contraseña) || empty($estado)) {
    $_SESSION['error_message'] = "Error: Todos los campos son obligatorios.";
    header("Location: ../pages/principal.php");
    exit();
}

if (!preg_match('/^\d{10}$/', $telefono)) {
    $_SESSION['error_message'] = "Error: El número de teléfono debe tener exactamente 10 dígitos.";
    header("Location: ../pages/principal.php");
    exit();
}

$fecha_actual = new DateTime();
$fecha_nacimiento = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fecha_nacimiento) {
    $_SESSION['error_message'] = "Error: Formato de fecha inválido.";
    header("Location: ../pages/principal.php");
    exit();
}
$edad = $fecha_nacimiento->diff($fecha_actual)->y;
if ($edad < 18) {
    $_SESSION['error_message'] = "Error: Debes ser mayor de edad para registrarte.";
    header("Location: ../pages/principal.php");
    exit();
}

$check_sql = $conexion->prepare("SELECT DNI, telefono, Usuario FROM usuarios WHERE DNI = ? OR telefono = ? OR Usuario = ?");
$check_sql->bind_param("sss", $dni, $telefono, $usuario);
$check_sql->execute();
$check_result = $check_sql->get_result();

if ($check_result->num_rows > 0) {
    $duplicated_fields = [];
    while ($row = $check_result->fetch_assoc()) {
        if ($row['DNI'] == $dni) $duplicated_fields[] = "DNI";
        if ($row['telefono'] == $telefono) $duplicated_fields[] = "teléfono";
        if ($row['Usuario'] == $usuario) $duplicated_fields[] = "usuario";
    }
    $_SESSION['error_message'] = "Error: Los siguientes campos ya están registrados: " . implode(', ', $duplicated_fields) . ".";
    header("Location: ../pages/principal.php");
    exit();
}
// 🔹 Encriptar la contraseña antes de insertar
$contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

$sql = $conexion->prepare("INSERT INTO usuarios (Nombres, Apellidos, DNI, fecha_de_nacimiento, telefono, Departamento, Ciudad, Direccion, Usuario, Password, Estado, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($sql) {
    $sql->bind_param("ssssssssssss", $nombres, $apellidos, $dni, $fecha, $telefono, $departamento, $ciudad, $direccion, $usuario, $contraseña, $estado, $rol);
    if ($sql->execute()) {
        $_SESSION['success_message'] = "Registro realizado correctamente";
    } else {
        $_SESSION['error_message'] = "Error al ejecutar la consulta: " . $sql->error;
    }
} else {
    $_SESSION['error_message'] = "Error al preparar la consulta: " . $conexion->error;
}
$conexion->close(); // 🔹 cerrar la conexión explícitamente
header("Location: ../pages/principal.php");
exit();
?>
