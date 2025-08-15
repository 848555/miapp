<?php
session_start();
// Verificar si 'usuario' está definido en la sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === '') {
    header("Location: ../../../../index.php");
    exit;
}
include(__DIR__ . '../../../config/conexion.php');

// Respuesta AJAX para actualizar solo la tabla sin recargar
if (isset($_GET['ajax']) && $_GET['ajax'] === 'tabla') {
    $sql = "SELECT s.id_solicitud, s.origen, s.destino, s.cantidad_personas, s.cantidad_motos, s.metodo_pago, s.estado, u.Nombres, u.Apellidos
            FROM solicitudes s
            INNER JOIN usuarios u ON s.id_usuarios = u.id_usuarios";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        while ($datos = $resultado->fetch_object()) {
            echo "<tr>
                <td>" . htmlspecialchars($datos->id_solicitud) . "</td>
                <td>" . htmlspecialchars($datos->Nombres) . "</td>
                <td>" . htmlspecialchars($datos->Apellidos) . "</td>
                <td>" . htmlspecialchars($datos->origen) . "</td>
                <td>" . htmlspecialchars($datos->destino) . "</td>
                <td>" . htmlspecialchars($datos->cantidad_personas) . "</td>
                <td>" . htmlspecialchars($datos->cantidad_motos) . "</td>
                <td>" . htmlspecialchars($datos->metodo_pago) . "</td>
                <td>" . htmlspecialchars($datos->estado) . "</td>
                <td>
                    <a onclick=\"return eliminar()\" href=\"/admin/include/eliminar_servivios.php?id_solicitud=" . htmlspecialchars($datos->id_solicitud) . "\" class=\"btn btn-danger btn-sm\">
                        <i class=\"fa-solid fa-trash\"></i>
                    </a>
                </td>
            </tr>";
        }
    } else {
        echo '<tr><td colspan="10" class="text-center">No hay solicitudes registradas.</td></tr>';
    }
    exit;
}

// Consulta original
$sql = "SELECT s.id_solicitud, s.origen, s.destino, s.cantidad_personas, s.cantidad_motos, s.metodo_pago, s.estado, u.Nombres, u.Apellidos
        FROM solicitudes s
        INNER JOIN usuarios u ON s.id_usuarios = u.id_usuarios";

$resultado = $conexion->query($sql);
$id_usuario_sesion = $_SESSION['id_usuario'];

$sql_usuario_sesion = "SELECT Nombres, Apellidos, DNI FROM usuarios WHERE id_usuarios = ?";
$stmt_usuario_sesion = $conexion->prepare($sql_usuario_sesion);
$stmt_usuario_sesion->bind_param("i", $id_usuario_sesion);
$stmt_usuario_sesion->execute();
$result_usuario_sesion = $stmt_usuario_sesion->get_result();
$usuario_sesion = $result_usuario_sesion->fetch_assoc();

$sql_acciones = "SELECT id_usuarios FROM usuarios";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/admin/css/mototaxista.css">
  <script src="https://kit.fontawesome.com/35f3448c23.js" crossorigin="anonymous"></script>
  <title>SERVICIOS</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm p-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="/admin/pages/principal.php">
      <img src="/app/assets/imagenes/imagen.jpeg" alt="Logo" width="30" height="30">
      ADMIN PANEL
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
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

<h1 class="text-center p-3" style="font-size: 2.5rem; font-weight: bold; color: #007bff;">
  SOLICITUDES DE MOTOTAXI
</h1>

<div class="container-fluid mt-3">
  <div class="d-flex align-items-start">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accionesModal">
      <i class="fas fa-clipboard-list me-2"></i> Intermediación
    </button>
  </div>
</div>

<?php 
if (isset($_SESSION['mensaje'])) {
    echo '<div class="alert alert-info">' . $_SESSION['mensaje'] . '</div>';
    unset($_SESSION['mensaje']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-message">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-message">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>

<div class="container-fluid my-4">
  <form class="d-flex">
    <input class="form-control me-2 light-table-filter" data-table="table" type="text" placeholder="Buscar">
  </form>

  <div class="row">
    <div class="col-12">
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="bg-info text-white">
            <tr>
              <th>ID</th>
              <th>Nombres</th>
              <th>Apellidos</th>
              <th>Origen</th>
              <th>Destino</th>
              <th>Cantidad de Personas</th>
              <th>Cantidad de Motos</th>
              <th>Método de Pago</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tabla-solicitudes-body">
            <!-- Se llenará automáticamente por JavaScript -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="accionesModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Acción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="/admin/include/registrar_acciones_soli.php" method="POST">
          <div class="mb-3">
            <label for="id_administrador" class="form-label">ID del Administrador</label>
            <input type="text" class="form-control" name="id_administrador" value="<?= $_SESSION['id_usuario'] ?? '' ?>" readonly>
          </div>
          <div class="mb-3">
            <label for="nombres" class="form-label">Nombres del Administrador</label>
            <input type="text" class="form-control" name="nombres" value="<?= $usuario_sesion['Nombres'] ?? '' ?>" readonly>
          </div>
          <div class="mb-3">
            <label for="apellidos" class="form-label">Apellidos del Administrador</label>
            <input type="text" class="form-control" name="apellidos" value="<?= $usuario_sesion['Apellidos'] ?? '' ?>" readonly>
          </div>
          <div class="mb-3">
            <label for="dni" class="form-label">Documento del Administrador</label>
            <input type="num" class="form-control" name="DNI" value="<?= $usuario_sesion['DNI'] ?? '' ?>" readonly>
          </div>
          <div class="mb-3">
            <label for="tipo_accion" class="form-label">Tipo de Intervención</label>
            <select class="form-select" name="tipo_accion">
              <option value="3">Eliminar Solicitud</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción de la Acción</label>
            <textarea class="form-control" name="descripcion" rows="3"></textarea>
          </div>
          <input type="hidden" name="id_usuario_objetivo" id="id_usuario_objetivo">
          <button type="submit" class="btn btn-primary">Registrar Acción</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/admin/assets/js/buscador.js"></script>
<script src="/admin/assets/js/script.js"></script>

<script>
function cargarSolicitudes() {
  fetch(window.location.href + '?ajax=tabla')
    .then(response => response.text())
    .then(data => {
      document.getElementById('tabla-solicitudes-body').innerHTML = data;
    })
    .catch(error => console.error('Error al cargar solicitudes:', error));
}

document.addEventListener('DOMContentLoaded', function () {
  cargarSolicitudes();
  setInterval(cargarSolicitudes, 10000); // actualiza cada 10s
});
</script>

</body>
</html>
