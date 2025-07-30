<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está autenticado
$validar = $_SESSION['usuario'];

// Si el usuario no está autenticado, redirigir al formulario de inicio de sesión
if ($validar == null || $validar == '') {
    header("Location:../login/login.php");
    die();
}
include(__DIR__ . '../../../config/conexion.php');

// Preparar consulta SQL para obtener retenciones agrupadas por usuario
$query = "
    SELECT 
        retenciones.id,
        retenciones.id_usuarios,
        usuarios.Nombres,
        usuarios.Apellidos,
        usuarios.Estado,
        GROUP_CONCAT(retenciones.id_solicitud SEPARATOR ', ') AS id_solicitudes,
        SUM(retenciones.retencion) AS total_retencion,
        MAX(retenciones.fecha) AS fecha_ultima_retencion,
        MAX(retenciones.pagado) AS pagado
    FROM 
        retenciones
    INNER JOIN 
        usuarios ON retenciones.id_usuarios = usuarios.id_usuarios
    GROUP BY 
        retenciones.id_usuarios
";
$sql = $conexion->query($query);

// Obtener el ID del usuario de la sesión
$id_usuario_sesion = $_SESSION['id_usuario'];

// Consulta para obtener nombres, apellidos y DNI del usuario de la sesión
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
    <title>Ganancias - Retenciones de Usuarios</title>
</head>
<body>
<!-- Nav -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm p-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="/admin/pages/principal.php">
      <img src="/app/assets/imagenes/imagen.jpeg" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
      ADMIN PANEL
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="/admin/pages/principal.php"><i class="fas fa-home"></i> Inicio</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<h1 class="text-center p-3" style="font-size: 2.5rem; font-weight: bold; color: #007bff;">
Ganancias - Retenciones de Usuarios
</h1>

<!-- Botón para abrir el modal de registrar acciones -->
<div class="container-fluid mt-3">
    <div class="d-flex align-items-start">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accionesModal">
            <i class="fas fa-clipboard-list me-2"></i> Intermediación
        </button>
    </div>
</div>

<div class="container-fluid"><br>
    <form class="d-flex">
        <input class="form-control me-2 light-table-filter" data-table="table" type="text" placeholder="Buscar">
        <hr>
        <script>
            function eliminar() {
                return confirm("¿Estás seguro que quieres eliminar esta retención?");
            }

            function sancionar() {
                return confirm("¿Estás seguro que quieres sancionar a este usuario?");
            }
        </script>
    </form>
</div>

<!-- Tabla -->
<div class="container-fluid my-4">
 <?php
    // Mostrar mensajes de error si existen
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-message">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']); // Limpiar el mensaje después de mostrarlo
    }

    // Mostrar mensaje de usuario actualizado si existe
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-message">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']); // Limpiar el mensaje después de mostrarlo
    }
    ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-info text-white">
                <tr>
                    <th>ID Retención</th>
                    <th>ID Usuario</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Total Retención</th>
                    <th>Fecha Última Retención</th>
                    <th>Pagado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($sql && $sql->num_rows > 0): ?>
                    <?php while ($datos = $sql->fetch_object()): ?>
                        <tr>
                            <td><?= htmlspecialchars($datos->id) ?></td>
                            <td><?= htmlspecialchars($datos->id_usuarios) ?></td>
                            <td><?= htmlspecialchars($datos->Nombres) ?></td>
                            <td><?= htmlspecialchars($datos->Apellidos) ?></td>
                            <td><?= htmlspecialchars($datos->total_retencion) ?></td>
                            <td><?= htmlspecialchars($datos->fecha_ultima_retencion) ?></td>
                            <td><?= $datos->pagado ? 'Sí' : 'No' ?></td>
                            <td><?= htmlspecialchars($datos->Estado) ?></td>
                            <td>
                                <a onclick="return eliminar()" href="/admin/include/eliminar_retencion.php?id=<?= htmlspecialchars($datos->id) ?>" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                                <?php if (!$datos->pagado): ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No hay retenciones registradas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para registrar acción -->
<div class="modal fade" id="accionesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Acción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="/admin/include/registrar_acciones_retenciones.php" method="POST">
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
                <input type="number" class="form-control" name="DNI" value="<?= $usuario_sesion['DNI'] ?? '' ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="tipo_accion" class="form-label">Tipo de Intervención</label>
                <select class="form-select" name="tipo_accion">
                    <option value="1">Sancionar Usuario</option>
                    <option value="2">Editar Estado</option>
                    <option value="3">Eliminar Retención</option>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/admin/assets/js/buscador.js"></script>
<script src="/admin/assets/js/script.js"></script>

</body>
</html>
