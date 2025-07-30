<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Asegúrate de tener PHPMailer con Composer
include(__DIR__ . '/../config/conexion.php');
session_start();

$telefono = $correo = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['telefono']) && !empty($_POST['correo'])) {
        $telefono = trim($_POST['telefono']);
        $correo = trim($_POST['correo']);

        // Verificar si el teléfono existe en la base de datos
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE telefono = ?");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Generar token y fecha de expiración (1 hora)
            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Guardar en la tabla recuperacion_password
            $stmt_insert = $conexion->prepare("INSERT INTO recuperacion_password (telefono, correo, token, expiracion) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $telefono, $correo, $token, $expiracion);
            $stmt_insert->execute();

            // Generar enlace de recuperación
            $enlace = "https://tusitio.com/include/resetear_password.php?token=$token"; // Coloca aquí tu URL real

            // Enviar correo con PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';               // Cambia esto por tu servidor SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'mercadosaltarin@gmail.com';       // Tu correo
                $mail->Password = 'rqpnwtwwusskqebt';        // Tu contraseña de app
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Remitente y destinatario
                $mail->setFrom('mercadosaltarin@gmail.com', 'MotoRider');
                $mail->addAddress($correo);

              $mail->isHTML(true);
$mail->Subject = 'Restablecimiento de contraseña - MotoRider';
$mail->Body = "
    <div style='font-family: Arial, sans-serif; color: #333; padding: 20px; max-width: 600px; margin: auto;'>
        <h2 style='color: #000;'>MotoRider - Recuperación de contraseña</h2>
        <p>Hola,</p>
        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Para continuar con el proceso, haz clic en el siguiente enlace:</p>
        <p style='margin: 20px 0;'>
            <a href='$enlace' style='background-color: #000; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer contraseña</a>
        </p>
        <p>Este enlace estará activo durante las próximas <strong>1 hora</strong>. Si no realizaste esta solicitud, puedes ignorar este mensaje de forma segura.</p>
        <p>Gracias por confiar en nosotros.</p>
        <br>
        <p style='color: #555;'>Atentamente,<br><strong>Equipo MotoRider</strong></p>
    </div>
";


                $mail->send();
                $mensaje = "Se ha enviado un enlace de recuperación al correo proporcionado.";
            } catch (Exception $e) {
                $mensaje = "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }
        } else {
            $mensaje = "El número de teléfono no está registrado.";
        }
    } else {
        $mensaje = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/app/assets/css/recordar_contraseñastyle.css">
    <title>Recuperar Contraseña</title>
</head>
<body>

<div class="form-recuperar">
    <h2>Recuperar Contraseña</h2>
    <form method="POST">
        <input type="number" name="telefono" placeholder="Ingresa tu número de teléfono" required>
        <input type="email" name="correo" placeholder="Ingresa tu correo electrónico" required>
        <button type="submit">Enviar enlace</button>
        <a href="/index.php" class="button">Regresar</a>
    </form>
    <?php if ($mensaje): ?>
        <p class="mensaje <?= strpos($mensaje, 'no está') !== false ? 'error' : '' ?>"><?= $mensaje ?></p>
    <?php endif; ?>
</div>
   <script>
        // Esperar 5 segundos (5000 ms) y ocultar el mensaje
        setTimeout(function() {
            var mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.display = 'none';
            }
        }, 5000);
    </script
</body>
</html>
