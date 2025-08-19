<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <main class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="contenedor__todo text-center p-4 shadow rounded">
            <div class="caja__trasera mb-4">
                <div class="caja__trasera-login mb-3">
                    <h3>¿Ya tienes una cuenta?</h3>
                    <p>Inicia sesión para acceder</p>
                </div>
                <div class="caja__trasera-register">
                    <h3>¿Aún no tienes una cuenta?</h3>
                    <p>Regístrate para que puedas iniciar sesión</p>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='pages/register.php'">Registrarse</button>
                </div>
            </div>
            <!-- Formulario de Login -->
            <div class="contenedor__login">
                <form action="/include/validar.php" method="POST" class="formulario__login">
                    <h2 class="mb-3">Iniciar Sesión</h2>
                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <p class="alert alert-success p-2"> <?= $_SESSION['mensaje']; ?> </p>
                        <?php unset($_SESSION['mensaje']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <p class="alert alert-danger p-2"> <?= $_SESSION['error']; ?> </p>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <input type="text" placeholder="Usuario" name="usuario" id="usuario" class="form-control mb-2">

                          <div class="password-container">
                         <input type="password" placeholder="Contraseña" name="password" id="password" class="form-control mb-3">
                                  <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('password')"></i>
                    </div>
   

                    
                    <div class="d-grid gap-2">
            <button type="submit" class="btn btn-principal" name="btn" value="ok">Ingresar</button>
                    </div>
                    <div class="mt-3">
                        <a class="text-decoration-none" href="/pages/recuperar_contraseña.php">Recuperar contraseña</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
        }, 5000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// Función para alternar visibilidad de la contraseña
function togglePasswordVisibility(id) {
    var input = document.getElementById(id);
    var icon = input.nextElementSibling;
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
} </script>
     
</body>

</html>
