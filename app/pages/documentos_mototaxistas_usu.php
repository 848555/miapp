<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Incluir la conexión a la base de datos
include(__DIR__ . '../../../config/conexion.php');

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['id_usuario']; 

// Función para obtener los datos actuales del usuario
function obtenerDatos($conexion, $id_usuario) {
    $query = "SELECT * FROM documentos WHERE id_usuarios = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        die('Error al preparar la consulta: ' . $conexion->error);
    }
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $datos = ($result->num_rows > 0) ? $result->fetch_assoc() : null;
    $stmt->close();
    return $datos;
}

// Obtener los datos actuales
$documentos_moto = obtenerDatos($conexion, $id_usuario);
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/app/assets/css/perfil_style.css">
  <title>Editar Documentos</title>
</head>
<body>
  <div class="container">
    <!-- Enlace para regresar -->
    <a href="/app/pages/inicio.php?uid=<?php echo $_SESSION['id_usuario']; ?>&php=<?php echo uniqid(); ?>"></a>
     
    <h2>Editar Documentos de <?php echo $_SESSION['usuario']; ?></h2>
    
    <?php if ($documentos_moto): ?>
      <!-- Formulario pre-llenado con los datos actuales. Se envía a /app/include/editar_docu.php -->
      <form  method="POST" action="/app/include/editar_docu.php" enctype="multipart/form-data">
        <!-- Campos de texto para datos básicos -->

         

        <label for="placa"><strong>Placa:</strong></label>
        <input type="text" id="placa" name="placa" value="<?php echo htmlspecialchars($documentos_moto['placa']); ?>" required>
        <br><br>

        <label for="marca"><strong>Marca:</strong></label>
        <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($documentos_moto['marca']); ?>" required>
        <br><br>

        <label for="modelo"><strong>Modelo:</strong></label>
        <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($documentos_moto['modelo']); ?>" required>
        <br><br>

        <label for="color"><strong>Color:</strong></label>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($documentos_moto['color']); ?>" required>
        <br><br>

        <!-- Campos para imágenes -->

        <!-- Licencia -->
        <label for="licencia_img"><strong>Licencia:</strong></label>
        <input type="file" id="licencia_img" name="licencia_img">
        <!-- Campo oculto para conservar la imagen actual -->
        <input type="hidden" name="licencia_actual" value="<?php echo htmlspecialchars($documentos_moto['licencia_de_conducir']); ?>">
        <?php if (!empty($documentos_moto['licencia_de_conducir'])): ?>
        <?php endif; ?>
        <br><br>

        <!-- Tarjeta de propiedad -->
        <label for="tarjeta_img"><strong>Tarjeta de propiedad:</strong></label>
        <input type="file" id="tarjeta_img" name="tarjeta_img">
        <input type="hidden" name="tarjeta_actual" value="<?php echo htmlspecialchars($documentos_moto['tarjeta_de_propiedad']); ?>">
        <?php if (!empty($documentos_moto['tarjeta_de_propiedad'])): ?>
         
        <?php endif; ?>
        <br><br>

        <!-- Soat -->
        <label for="soat_img"><strong>Soat:</strong></label>
        <input type="file" id="soat_img" name="soat_img">
        <input type="hidden" name="soat_actual" value="<?php echo htmlspecialchars($documentos_moto['soat']); ?>">
        <?php if (!empty($documentos_moto['soat'])): ?>
         
          </p>
        <?php endif; ?>
        <br><br>

        <!-- Tecnomecánica -->
        <label for="tecno_img"><strong>Tecnomecánica:</strong></label>
        <input type="file" id="tecno_img" name="tecno_img">
        <input type="hidden" name="tecno_actual" value="<?php echo htmlspecialchars($documentos_moto['tecno_mecanica']); ?>">
        <?php if (!empty($documentos_moto['tecno_mecanica'])): ?>
          
        <?php endif; ?>
        <br><br>
        <a href="/app/pages/inicio.php" class="btn">Regresar</a>
        <button type="submit" class="btn">Guardar Cambios</button>
        
      </form>
    <?php else: ?>
      <p>No se encontraron documentos para editar. Asegúrate de haber subido tus documentos previamente.</p>
    <?php endif; ?>
    <br>
  </div>
  
  <!-- Ícono y controles de accesibilidad (opcional) -->
  <div id="accessibility-icon">
    <ion-icon name="accessibility-outline"></ion-icon>
  </div>
  <div id="accessibility-panel" class="accessibility-controls" style="display: none;">
    <button id="increaseText">Aumentar letra</button>
    <button id="decreaseText">Disminuir letra</button>
  </div>
  <script src="/app/assets/js/script.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
