<?php
session_start();
include(__DIR__ . '../../../config/conexion.php');

// Obtener todos los administradores con rol = 1
$sql_admins = "SELECT id_usuarios, Nombres, Apellidos FROM usuarios WHERE rol = 1";
$result_admins = $conexion->query($sql_admins);

// Obtener todos los módulos
$sql_modulos = "SELECT * FROM modulos ORDER BY nombre_modulo ASC";
$modulos = $conexion->query($sql_modulos);

// Obtener todas las acciones permitidas por módulo (JOIN con acciones_modulo)
$sql_acciones_modulos = "
  SELECT ma.id_modulo, a.id_accion_modulo, a.nombre_accion 
  FROM modulo_acciones ma
  JOIN acciones_modulo a ON ma.id_accion_modulo = a.id_accion_modulo
  ORDER BY ma.id_modulo, a.nombre_accion ASC
";
$result_acciones_modulos = $conexion->query($sql_acciones_modulos);

// Organizar las acciones por módulo en un array
$acciones_por_modulo = [];
while ($row = $result_acciones_modulos->fetch_assoc()) {
    $acciones_por_modulo[$row['id_modulo']][] = $row;
}

// Obtener permisos existentes del administrador seleccionado
$permisos_existentes = [];
if (isset($_GET['id_admin'])) {
  $id_admin = intval($_GET['id_admin']);
  $sql_permisos = "SELECT id_modulo, id_accion_modulo FROM permisos_detallados WHERE id_admin = ? AND permitido = 1";
  $stmt = $conexion->prepare($sql_permisos);
  $stmt->bind_param("i", $id_admin);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $permisos_existentes[$row['id_modulo'] . '-' . $row['id_accion_modulo']] = true;
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/35f3448c23.js" crossorigin="anonymous"></script>
  <title>Gestión de Permisos</title>
  <style>
    .toggle-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      padding: 0.5rem;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      background-color: #f9f9f9;
    }
    .form-switch .form-check-input {
      width: 3rem;
      height: 1.5rem;
    }
    .form-switch .form-check-input:checked {
      background-color: #198754;
    }
    .accordion-button {
      font-weight: bold;
      background: #f8f9fa;
    }
  </style>
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

<h1 class="text-center p-3 text-primary">GESTIÓN DE PERMISOS</h1>
<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Éxito:</strong> Los permisos se han guardado correctamente.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php endif; ?>


<div class="container my-4">
  <form action="" method="GET">
    <div class="mb-3">
      <label for="id_admin" class="form-label">Seleccione Administrador</label>
      <select name="id_admin" class="form-select" onchange="this.form.submit()" required>
        <option value="">-- Seleccione --</option>
        <?php while ($admin = $result_admins->fetch_assoc()): 
          $selected = (isset($_GET['id_admin']) && $_GET['id_admin'] == $admin['id_usuarios']) ? 'selected' : '';
        ?>
          <option value="<?= $admin['id_usuarios'] ?>" <?= $selected ?>>
            <?= htmlspecialchars($admin['Nombres'] . ' ' . $admin['Apellidos']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
  </form>

  <?php if (isset($_GET['id_admin'])): ?>
<form action="/admin/include/guardar_permisos.php" method="POST">
    <input type="hidden" name="id_admin" value="<?= intval($_GET['id_admin']) ?>">

    <div class="accordion" id="accordionPermisos">
      <?php 
      $modulos->data_seek(0);
      while ($modulo = $modulos->fetch_assoc()):
        $id_modulo = $modulo['id_modulo'];
        if (!isset($acciones_por_modulo[$id_modulo])) continue; // ignorar módulos sin acciones
      ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="heading<?= $id_modulo ?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $id_modulo ?>">
              <?= htmlspecialchars($modulo['nombre_modulo']) ?>
            </button>
          </h2>
          <div id="collapse<?= $id_modulo ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPermisos">
            <div class="accordion-body">
              <?php 
              foreach ($acciones_por_modulo[$id_modulo] as $accion):
                $clave = $id_modulo . '-' . $accion['id_accion_modulo'];
                $checked = isset($permisos_existentes[$clave]) ? 'checked' : '';
              ?>
                <div class="toggle-container">
                  <span><?= htmlspecialchars(ucfirst($accion['nombre_accion'])) ?></span>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="permisos[]" value="<?= $clave ?>" id="perm_<?= $clave ?>" <?= $checked ?>>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <button type="submit" class="btn btn-success mt-4 w-100">
      <i class="fas fa-save me-2"></i>Guardar Permisos
    </button>
  </form>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
