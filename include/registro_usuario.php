<?php
session_start();
include(__DIR__ . '../../config/conexion.php');

// Asegúrate de que las variables POST estén definidas
$nombres = isset($_POST["nombres"]) ? $_POST["nombres"] : '';
$apellidos = isset($_POST["apellidos"]) ? $_POST["apellidos"] : '';
$dni = isset($_POST["dni"]) ? $_POST["dni"] : '';
$fecha = isset($_POST["fecha"]) ? $_POST["fecha"] : '';
$telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : '';
$departamento = isset($_POST["departamento"]) ? $_POST["departamento"] : '';
$ciudad = isset($_POST["ciudad"]) ? $_POST["ciudad"] : '';
$direccion = isset($_POST["direccion"]) ? $_POST["direccion"] : '';
$usuario = isset($_POST["usuario"]) ? $_POST["usuario"] : '';
$contraseña = isset($_POST["contraseña"]) ? $_POST["contraseña"] : '';

// Verificar si todos los campos están llenos
if (empty($nombres) || empty($apellidos) || empty($dni) || empty($fecha) || empty($telefono) || empty($departamento) || empty($ciudad) || empty($direccion) || empty($usuario) || empty($contraseña)) {
    $_SESSION['error'] = "Error: Todos los campos son obligatorios.";
    header("Location: ../index.php");
    exit();
}

// Validar el número de teléfono (debe tener exactamente 10 dígitos)
if (!preg_match('/^\d{10}$/', $telefono)) {
    $_SESSION['error'] = "Error: El número de teléfono debe tener exactamente 10 dígitos.";
    header("Location: ../index.php");
    exit();
}

// Validar que la fecha indique que la persona es mayor de edad (18 años o más)
$fecha_actual = new DateTime();
$fecha_nacimiento = DateTime::createFromFormat('Y-m-d', $fecha);
$edad = $fecha_nacimiento->diff($fecha_actual)->y;

if ($edad < 18) {
    $_SESSION['error'] = "Error: Debes ser mayor de edad para registrarte.";
    header("Location: ../index.php");
    exit();
}

// Verificar duplicados
$check_sql = $conexion->prepare("SELECT * FROM usuarios WHERE DNI = ? OR telefono = ? OR Usuario = ?");
$check_sql->bind_param("sss", $dni, $telefono, $usuario);
$check_sql->execute();
$check_result = $check_sql->get_result();

if ($check_result->num_rows > 0) {
    $duplicated_fields = [];
    while ($row = $check_result->fetch_assoc()) {
        if ($row['DNI'] == $dni && !in_array("DNI", $duplicated_fields)) {
            $duplicated_fields[] = "DNI";
        }
        if ($row['telefono'] == $telefono && !in_array("teléfono", $duplicated_fields)) {
            $duplicated_fields[] = "teléfono";
        }
        if ($row['Usuario'] == $usuario && !in_array("usuario", $duplicated_fields)) {
            $duplicated_fields[] = "usuario";
        }
    }
    $_SESSION['error'] = "Error: Los siguientes campos ya están registrados: " . implode(', ', $duplicated_fields) . ".";
    header("Location: ../index.php");
    exit();
}

// Estado y rol fijo
$estado = 'Activo';
$rol = '2';

// Preparar la consulta SQL para insertar
$sql = $conexion->prepare("INSERT INTO usuarios (Nombres, Apellidos, DNI, fecha_de_nacimiento, telefono, Departamento, Ciudad, Direccion, Usuario, Password, Estado, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($sql) {
    $sql->bind_param("ssssssssssss", $nombres, $apellidos, $dni, $fecha, $telefono, $departamento, $ciudad, $direccion, $usuario, $contraseña, $estado, $rol);
    if ($sql->execute()) {
        $_SESSION['mensaje'] = "Te registraste correctamente, Inicia sesión.";
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al ejecutar la consulta: " . $sql->error;
        header("Location: ../index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Error al preparar la consulta: " . $conexion->error;
    header("Location: ../index.php");
    exit();
}

// Cerrar la conexión
$conexion->close();
?>
