<?php
// Iniciar la sesión
session_start();

// Verificar si 'usuario' está definido en la sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === '') {
    header("Location: ../../../../index.php");
    exit;
}

include(__DIR__ . '../../../config/conexion.php');

$sql = $conexion->query("
    SELECT u.id_usuarios,
           u.nombres AS Nombres,
           u.apellidos AS Apellidos,
           u.DNI,
           u.fecha_de_nacimiento,
           u.telefono,
           d.departamentos AS Departamento,
           c.ciudades AS Ciudad,
           u.Direccion,
           u.Usuario,
           u.Password,
           u.Estado,
           r.roles
    FROM usuarios u
    INNER JOIN roles r ON u.rol = r.id
    LEFT JOIN ciudades c ON u.Ciudad = c.id_ciudades
    LEFT JOIN departamentos d ON c.id_departamentos = d.id_departamentos
");


$sql_departamentos = "SELECT id_departamentos, departamentos FROM departamentos";
$resultado_departamentos = $conexion->query($sql_departamentos);
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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crud</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/35f3448c23.js" crossorigin="anonymous"></script>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <!--inicio del nav -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm p-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="/admin/pages/principal.php">
      <img src="/app/assets/imagenes/imagen.jpeg" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
      MotoRide
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
  <a class="nav-link" href="/admin/pages/mototaxistas.php">
    <i class="fa-solid fa-id-card"></i> Control de documentos
  </a>
</li>

        <li class="nav-item">
          <a class="nav-link" href="/admin/pages/servicios_solicitados.php">
            <i class="fas fa-tasks"></i> Solicitudes de servicio
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/admin/pages/ver_retenciones.php">
            <i class="fas fa-money-check-alt"></i> Ganancias de la app
          </a>
        </li>
        <li class="nav-item">
  <a class="nav-link" href="/admin/pages/acciones_registradas.php">
    <i class="fas fa-clipboard-list"></i> Acciones Registradas
  </a>
</li>
<li class="nav-item">
  <a class="nav-link" href="/admin/pages/gestion_de_permisos.php">
    <i class="fas fa-user-shield"></i> Gestión de Permisos
  </a>
</li>


        <li class="nav-item">
          <a class="nav-link text-danger fw-bold" href="/index.php?vista=logout">
            <i class="fas fa-sign-out-alt"></i> Salir
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- fin del nav -->

    <h1 class="text-center p-4 bg-light shadow-sm rounded">
        <span class="fw-bold text-primary">Bienvenido, Admin</span>
        <span class="text-dark">
            <?php echo isset($usuario_sesion['Nombres']) ? $usuario_sesion['Nombres'] : ''; ?>
            <?php echo isset($usuario_sesion['Apellidos']) ? $usuario_sesion['Apellidos'] : ''; ?>
        </span>
    </h1>

    <!-- Botón para abrir el modal de agregar usuario -->
    <div class="container-fluid mt-3">
        <div class="d-flex align-items-start">
            <!-- Botón para abrir el modal de agregar usuario -->
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#insertarUsuarioModal">
                <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
            </button>

            <!-- Botón para abrir el modal de registrar acciones -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accionesModal">
                <i class="fas fa-clipboard-list me-2"></i> Intermediación
            </button>
        </div>
    </div>



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
    <script>
        function eliminar(mensaje) {
            return confirm(mensaje);
        }
    </script>
    <div class="container-fluid py-5">
        <!-- Barra de búsqueda -->
        <form class="d-flex">
            <input class="form-control me-2 light-table-filter" data-table="table" type="text" placeholder="Buscar">
        </form>
        <!-- Tabla de usuarios -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="bg-info text-white">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">NOMBRES</th>
                                <th scope="col">APELLIDOS</th>
                                <th scope="col">DNI</th>
                                <th scope="col">FECHA DE NACIMIENTO</th>
                                <th scope="col">TELÉFONO</th>
                                <th scope="col">DEPARTAMENTO</th>
                                <th scope="col">CIUDAD</th>
                                <th scope="col">DIRECCIÓN</th>
                                <th scope="col">USUARIO</th>
                                <th scope="col">CONTRASEÑA</th>
                                <th scope="col">ESTADO</th>
                                <th scope="col">ROL</th>
                                <th scope="col">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            if ($sql->num_rows > 0) {
                                while ($datos = $sql->fetch_object()) {
                            ?>
                                    <tr>
                                        <td><?= $datos->id_usuarios ?></td>
                                        <td><?= $datos->Nombres ?></td>
                                        <td><?= $datos->Apellidos ?></td>
                                        <td><?= $datos->DNI ?></td>
                                        <td><?= $datos->fecha_de_nacimiento ?></td>
                                        <td><?= $datos->telefono ?></td>
                                        <td><?= $datos->Departamento ?></td>
                                        <td><?= $datos->Ciudad ?></td>
                                        <td><?= $datos->Direccion ?></td>
                                        <td><?= $datos->Usuario ?></td>
                                        <td><input type="password" value="<?= $datos->Password ?>" class="form-control" readonly></td>
                                        <td><?= $datos->Estado ?></td>
                                        <td><?= $datos->roles ?></td>
                                        <td>
                                            <div class="d-flex gap-2"> <!-- Usando d-flex para alinear en fila y gap-2 para espacio -->
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal<?= $datos->id_usuarios ?>" class="btn btn-sm btn-warning">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="/admin/include/eliminar.php?id=<?= $datos->id_usuarios ?>&confirm=true"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return eliminar('¿Estás seguro de que deseas eliminar este usuario?');">
                                                    <i class="fa-solid fa-trash"></i> <!-- Icono de prohibición -->
                                                </a>



                                                <!-- Nuevo botón para sancionar -->
                                                <a href="/admin/include/sancionar_usuario.php?id=<?= $datos->id_usuarios ?>"
                                                    class="btn btn-sm btn-dark"
                                                    onclick="return confirm('¿Estás seguro de que deseas sancionar a este usuario?');">
                                                    <i class="fa-solid fa-ban"></i> <!-- Icono de prohibición -->
                                                </a>
                                                <!-- Nuevo botón para abrir el modal de edición de estado -->
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#editarEstadoModal<?= $datos->id_usuarios ?>" class="btn btn-sm btn-secondary">
                                                    <i class="fa-solid fa-pencil-alt"></i> <!-- Icono de lápiz -->
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal para editar usuario -->
                                    <div class="modal fade" id="editarUsuarioModal<?= $datos->id_usuarios ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title" id="exampleModalLabel">Editar Usuario</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Formulario de edición -->
                                                    <form action="/admin/include/update.php" method="POST">
                                                        <input type="hidden" name="id" value="<?= $datos->id_usuarios ?>">
                                                        <div class="mb-3">
                                                            <label for="nombres" class="form-label">Nombres</label>
                                                            <input type="text" class="form-control" name="nombres" value="<?= $datos->Nombres ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="apellidos" class="form-label">Apellidos</label>
                                                            <input type="text" class="form-control" name="apellidos" value="<?= $datos->Apellidos ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="dni" class="form-label">DNI</label>
                                                            <input type="text" class="form-control" name="dni" value="<?= $datos->DNI ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="fecha" class="form-label">Fecha de Nacimiento</label>
                                                            <input type="date" class="form-control" name="fecha" value="<?= $datos->fecha_de_nacimiento ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="telefono" class="form-label">Teléfono</label>
                                                            <input type="text" class="form-control" name="telefono" value="<?= $datos->telefono ?>">
                                                        </div>
                                                        <div class="mb-3">
    <label for="departamento<?= $datos->id_usuarios ?>" class="form-label">Departamento</label>
    <select name="departamento" id="departamento<?= $datos->id_usuarios ?>" class="form-select"
        onchange="getCiudadesEdit(<?= $datos->id_usuarios ?>, this.value, '')" required>
        <option value="">Selecciona un departamento</option>
        <?php
        $resultado_departamentos = $conexion->query("SELECT * FROM departamentos");
        while ($dep = $resultado_departamentos->fetch_assoc()) {
            $selected = ($datos->Departamento == $dep['departamentos']) ? 'selected' : '';
            echo "<option value='" . $dep['id_departamentos'] . "' $selected>" . $dep['departamentos'] . "</option>";
        }
        ?>
    </select>
</div>
<div class="mb-3">
    <label for="ciudad<?= $datos->id_usuarios ?>" class="form-label">Ciudad</label>
    <select name="ciudad" id="ciudad<?= $datos->id_usuarios ?>" class="form-select" required>
        <option value="">Selecciona una ciudad</option>
    </select>
</div>

                                                        <div class="mb-3">
                                                            <label for="direccion" class="form-label">Dirección</label>
                                                            <input type="text" class="form-control" name="direccion" value="<?= $datos->Direccion ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="usuario" class="form-label">Usuario</label>
                                                            <input type="text" class="form-control" name="usuario" value="<?= $datos->Usuario ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="password" class="form-label">Contraseña</label>
                                                            <input type="password" class="form-control" name="password" value="<?= $datos->Password ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="estado" class="form-label">Estado</label>
                                                            <input type="text" class="form-control" name="estado" value="<?= $datos->Estado ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="rol" class="form-label">Rol</label>
                                                            <input type="text" class="form-control" name="rol" value="<?= $datos->roles ?>">
                                                        </div>
<button type="submit" class="btn btn-primary w-100" id="btn-guardar-cambios-<?= $datos->id_usuarios ?>">Guardar Cambios</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editarUsuarioModal<?= $datos->id_usuarios ?>');
    modal.addEventListener('shown.bs.modal', function () {
        const idDep = document.getElementById('departamento<?= $datos->id_usuarios ?>').value;
        const ciudadActual = "<?= $datos->Ciudad ?>";
        getCiudadesEdit(<?= $datos->id_usuarios ?>, idDep, ciudadActual);
    });
});
</script>
                                    <!-- Modal para editar estado -->
                                    <div class="modal fade" id="editarEstadoModal<?= $datos->id_usuarios ?>" tabindex="-1" aria-labelledby="editarEstadoModalLabel<?= $datos->id_usuarios ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editarEstadoModalLabel<?= $datos->id_usuarios ?>">Editar Estado de Usuario</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="/admin/include/editar_estado_usuario.php" method="POST">
                                                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($datos->id_usuarios) ?>">
                                                        <label for="estadoUsuario<?= $datos->id_usuarios ?>" class="form-label">Nuevo Estado:</label>
                                                        <select class="form-select" id="estadoUsuario<?= $datos->id_usuarios ?>" name="estadoUsuario">
                                                            <option value="activo">Activo</option>
                                                            <option value="inactivo">Inactivo</option>
                                                        </select>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='13'>No hay usuarios registrados.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
// Volver a consultar los departamentos solo para el formulario de insertar
$sql_departamentos_insertar = "SELECT id_departamentos, departamentos FROM departamentos";
$resultado_departamentos_insertar = $conexion->query($sql_departamentos_insertar);
?>

    <!-- Modal para agregar nuevo usuario -->
    <div class="modal fade" id="insertarUsuarioModal" tabindex="-1" aria-labelledby="insertarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertarUsuarioLabel">Agregar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario para agregar nuevo usuario -->
                    <form action="/admin/include/insertar.php" method="POST">
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" name="dni" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" required>
                        </div>
                        <!-- Combo box para seleccionar departamento -->
                        <div class="mb-3">
                            <label for="departamento" class="form-label">Departamento</label>
                            <select name="departamento" id="departamento" class="form-select" onchange="getCiudades()" required>
                                <option value="">Selecciona un departamento</option>
<?php while ($departamento = $resultado_departamentos_insertar->fetch_assoc()) : ?>
                                    <option value="<?= $departamento['id_departamentos'] ?>"><?= $departamento['departamentos'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <!-- Combo box para seleccionar ciudad -->
                        <div class="mb-3">
                            <label for="ciudad" class="form-label">Ciudad</label>
                            <select name="ciudad" id="ciudad" class="form-select" required>
                                <option value="">Selecciona una ciudad</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" required>
                        </div>
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control" name="usuario" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contraseña" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" name="estado" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <input type="text" class="form-control" name="rol" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar acción -->
    <div class="modal fade" id="accionesModal" tabindex="-1" aria-labelledby="accionesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accionesModalLabel">Registrar Acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Contenido del formulario de registro de acción -->
                    <form action="/admin/include/registrar_accion.php" method="POST">
                        <div class="mb-3">
                            <label for="id_administrador" class="form-label">ID del Administrador</label>
                            <input type="text" class="form-control" name="id_administrador" value="<?= isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : '' ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres del Administrador</label>
                            <input type="text" class="form-control" name="nombres" value="<?= isset($usuario_sesion['Nombres']) ? $usuario_sesion['Nombres'] : '' ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos del Administrador</label>
                            <input type="text" class="form-control" name="apellidos" value="<?= isset($usuario_sesion['Apellidos']) ? $usuario_sesion['Apellidos'] : '' ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">Documento del Administrador</label>
                            <input type="text" class="form-control" name="DNI" value="<?= isset($usuario_sesion['DNI']) ? $usuario_sesion['DNI'] : '' ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_accion" class="form-label">Tipo de Intervención</label>
                            <select class="form-select" name="tipo_accion" required>
                                <option value="1">Agregar Usuario</option>
                                <option value="2">Editar Usuario</option>
                                <option value="3">Eliminar Usuario</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción de la Acción</label>
                            <textarea class="form-control" name="descripcion" rows="3" required></textarea>
                        </div>
                        <input type="hidden" name="id_usuario_objetivo" id="id_usuario_objetivo">
                        <button type="submit" class="btn btn-primary">Registrar Acción</button>
                    </form>
                </div>
            </div>
        </div>
    </div>






    <!-- Script JavaScript para obtener ciudades según el departamento seleccionado -->
    <script>
        function getCiudades() {
            var departamentoId = document.getElementById("departamento").value;
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "/admin/include/obtener_ciudades.php?departamento=" + departamentoId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var ciudades = JSON.parse(xhr.responseText);
                    var ciudadSelect = document.getElementById("ciudad");
                    ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
                    ciudades.forEach(function(ciudad) {
                        var option = document.createElement("option");
                        option.value = ciudad.id_ciudades;
                        option.textContent = ciudad.ciudades;
                        ciudadSelect.appendChild(option);
                    });
                }
            };
            xhr.send();
        }
    </script>
    <script>
function getCiudadesEdit(userId, idDepartamento, ciudadActual) {
    const ciudadSelect = document.getElementById("ciudad" + userId);
    ciudadSelect.innerHTML = '<option value="">Cargando ciudades...</option>';

    fetch("/admin/include/obtener_ciudades.php?departamento=" + idDepartamento)
        .then(response => response.json())
        .then(ciudades => {
            ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
            ciudades.forEach(ciudad => {
                const option = document.createElement("option");
                option.value = ciudad.id_ciudades;
                option.textContent = ciudad.ciudades;
                if (ciudad.ciudades === ciudadActual) {
                    option.selected = true;
                }
                ciudadSelect.appendChild(option);
            });
        });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editarUsuarioModal<?= $datos->id_usuarios ?>');
    modal.addEventListener('shown.bs.modal', function () {
        const idDep = document.getElementById('departamento<?= $datos->id_usuarios ?>').value;
        const ciudadActual = "<?= $datos->Ciudad ?>";
        getCiudadesEdit(<?= $datos->id_usuarios ?>, idDep, ciudadActual);
    });
});
</script>

    <!-- Scripts necesarios de Bootstrap y JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/admin/assets/js/buscador.js"></script>
    <script src="/admin/assets/js/script.js"></script>
</body>

</html>
<?php
$conexion->close();
?>

