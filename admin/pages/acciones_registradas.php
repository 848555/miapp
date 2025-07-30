<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');
include(__DIR__ . '/../include/validar_permiso_directo.php'); // Ruta según tu estructura

// Validar permiso: módulo 5 = Acciones Registradas, acción 7 = ver
$id_admin = $_SESSION['id_usuario'] ?? 0;

if (!tienePermiso($id_admin, 5, 7)) {
    echo "<script>
        alert('No tienes permiso para ver las acciones registradas.');
        window.location = '../pages/principal.php';
    </script>";
    exit();
}
// Consulta de acciones registradas
$sql_acciones = "SELECT id_accion, id_administrador, nombres, apellidos, DNI, tipo_accion, descripcion, fecha FROM acciones ORDER BY fecha DESC";
$resultado_acciones = $conexion->query($sql_acciones);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/35f3448c23.js" crossorigin="anonymous"></script>
  <title>Acciones Registradas</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm p-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="/admin/pages/principal.php">
      <img src="/app/assets/imagenes/imagen.jpeg" alt="Logo" width="30" height="30">
      ADMIN PANEL
    </a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="/admin/pages/principal.php">
            <i class="fas fa-home"></i> Inicio
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<h1 class="text-center p-3 text-primary">ACCIONES REGISTRADAS</h1>

<div class="container my-4">
  <form class="d-flex mb-3">
    <input class="form-control me-2 light-table-filter" data-table="table" type="text" placeholder="Buscar">
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
      <thead class="bg-info text-white">
        <tr>
          <th>ID</th>
          <th>ID Administrador</th>
          <th>Nombres</th>
          <th>Apellidos</th>
          <th>DNI</th>
          <th>Tipo de Acción</th>
          <th>Descripción</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resultado_acciones->num_rows > 0): ?>
          <?php while ($accion = $resultado_acciones->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($accion['id_accion']) ?></td>
              <td><?= htmlspecialchars($accion['id_administrador']) ?></td>
              <td><?= htmlspecialchars($accion['nombres']) ?></td>
              <td><?= htmlspecialchars($accion['apellidos']) ?></td>
              <td><?= htmlspecialchars($accion['DNI']) ?></td>
              <td><?= htmlspecialchars($accion['tipo_accion']) ?></td>
              <td><?= htmlspecialchars($accion['descripcion']) ?></td>
              <td><?= htmlspecialchars($accion['fecha']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center">No hay acciones registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/admin/assets/js/buscador.js"></script>

</body>
</html>
