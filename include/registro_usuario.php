<<?php
include(__DIR__ . '../../config/conexion.php');

// Iniciar la sesión
session_start();

if (isset($_POST['usuario']) && isset($_POST['password'])) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // 🔹 Buscar solo por usuario para obtener el hash
    $consulta = "SELECT * FROM usuarios WHERE Usuario=?";
    $stmt = mysqli_prepare($conexion, $consulta);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (!$resultado) {
        die("Error en la ejecución de la consulta: " . mysqli_error($conexion));
    }

    $filas = mysqli_fetch_array($resultado);

    // 🔹 Verificar que exista el usuario y validar la contraseña encriptada
    if ($filas && password_verify($password, $filas['Password'])) {
        $estado_usuario = trim($filas['Estado']);

        if (strcasecmp($estado_usuario, 'Sancionado') == 0) {
            $_SESSION['error'] = "El usuario está sancionado y no puede iniciar sesión.";
            header('Location: ../index.php');
            exit();
        } elseif (strcasecmp($estado_usuario, 'Inactivo') == 0) {
            $_SESSION['error'] = "El usuario está inactivo y no puede iniciar sesión.";
            header('Location: ../index.php');
            exit();
        }

        // ✅ Asignar variables de sesión
        $_SESSION['usuario'] = $usuario;
        $_SESSION['id_usuario'] = $filas['id_usuarios'];
        $_SESSION['rol'] = $filas['rol'];

        // ✅ Redirección según el rol
        if ($filas['rol'] == 1) {
            header('Location: ../../../../admin/pages/principal.php');
            exit();
        } elseif ($filas['rol'] == 2) {
            $_SESSION['mensaje'] = "¡Inicio de sesión exitoso!";
            header('Location: ../../../../app/pages/inicio.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Error: El usuario o la contraseña son incorrectos, por favor verifica e intenta de nuevo.";
        header('Location: ../index.php');
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
