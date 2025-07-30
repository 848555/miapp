<?php
// Iniciar la sesión
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página de inicio de sesión
if (headers_sent()) {
    echo "<script> window.location.href='login.php?vista=login'</script>";
} else {
    header("location:index.php?vista=index");
    exit(); // Asegúrate de detener el script después de la redirección
}
?>
