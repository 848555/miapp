<?php

// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Verificar si 'usuario' está definido en la sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === '') {
    header("Location: ../../../../index.php");
    exit;
}

include(__DIR__ . '../../../config/conexion.php');

$sql = $conexion->query("
    SELECT d.id_documentos, 
           d.licencia_de_conducir, 
           d.tarjeta_de_propiedad, 
           d.soat, 
           d.tecno_mecanica, 
           d.placa, 
           d.marca, 
           d.modelo, 
           d.color, 
           d.documento_verificado,
           u.nombres AS nombre_usuario, 
           u.apellidos AS apellido_usuario
    FROM documentos d
    INNER JOIN usuarios u ON d.id_usuarios = u.id_usuarios
");
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
$supabase_url = "https://ccfwmhwwjbzhsdtqusrw.supabase.co/storage/v1/object/public/documentos/";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/admin/assets/css/mototaxista.css">
    <script src="https://kit.fontawesome.com/35f3448c23.js" crossorigin="anonymous"></script>
    <title>Control de Documentos</title>
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
                        <a class="nav-link active" aria-current="page" href="/admin/pages/principal.php">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- fin del nav -->
        <h1 class="text-center text-primary mb-4">Control de Documentos de Mototaxistas</h1>
 <script >
function eliminar() {
    return confirm("¿Estás seguro de eliminar este documento?");
}
</script>
    <?php


// Mostrar mensajes de éxito o error
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-message">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Limpiar mensaje de éxito
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-message">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Limpiar mensaje de error
}
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
    <!-- Botón para abrir el modal de registrar acciones -->
    <div class="container-fluid mt-3">
        <div class="d-flex align-items-start">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accionesModal">
                <i class="fas fa-clipboard-list me-2"></i> Intermediación
            </button>
        </div>
    </div>
<!-- Contenedor del formulario y tabla -->
<div class="container-fluid my-1">
    <!-- Barra de búsqueda -->
    <form class="d-flex">
        <input class="form-control me-2 light-table-filter" data-table="table" type="text" placeholder="Buscar">
    </form>

    <!-- Tabla de documentos -->
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-info text-white">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Licencia de Conducir</th>
                            <th scope="col">Tarjeta de Propiedad</th>
                            <th scope="col">SOAT</th>
                            <th scope="col">Tecno Mecanica</th>
                            <th scope="col">Placa</th>
                            <th scope="col">Marca</th>
                            <th scope="col">Modelo</th>
                            <th scope="col">Color</th>
                            <th scope="col">Usuario que Subió</th>
                            <th scope="col">Estado del documento</th>
                            <th scope="col">Acciones</th>
                            <th scope="col">Ver Documentos</th> <!-- Nuevo encabezado -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sql->num_rows > 0) {
                            while ($datos = $sql->fetch_object()) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($datos->id_documentos) ?></td>
                                  <td><img src="<?= $supabase_url . htmlspecialchars($datos->licencia_de_conducir) ?>" width="100" alt="Licencia de Conducir"></td>
<td><img src="<?= $supabase_url . htmlspecialchars($datos->tarjeta_de_propiedad) ?>" width="100" alt="Tarjeta de Propiedad"></td>
<td><img src="<?= $supabase_url . htmlspecialchars($datos->soat) ?>" width="100" alt="SOAT"></td>
<td><img src="<?= $supabase_url . htmlspecialchars($datos->tecno_mecanica) ?>" width="100" alt="Tecno Mecanica"></td>
                                    <td><?= htmlspecialchars($datos->placa) ?></td>
                                    <td><?= htmlspecialchars($datos->marca) ?></td>
                                    <td><?= htmlspecialchars($datos->modelo) ?></td>
                                    <td><?= htmlspecialchars($datos->color) ?></td>
                                    <td><?= htmlspecialchars($datos->nombre_usuario) . ' ' . htmlspecialchars($datos->apellido_usuario) ?></td>
                                    <td><?= htmlspecialchars($datos->documento_verificado) ?></td>
                                    <td>
<a onclick="return eliminar()" 
   href="/admin/include/eliminar_img.php?id=<?= htmlspecialchars($datos->id_documentos) ?>" 
   class="btn btn-danger btn-sm">
   <i class="fa-solid fa-trash"></i>
</a>


                                        <!-- Botones para validar los documentos -->
                                        <form action="/admin/include/verificar_documento.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_documentos" value="<?= $datos->id_documentos ?>">
                                            <input type="hidden" name="estado_documento" value="validado">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-check"></i> 
                                            </button>
                                        </form>

                                        <!-- Botón para rechazar los documentos -->
                                        <form action="/admin/include/verificar_documento.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_documentos" value="<?= $datos->id_documentos ?>">
                                            <input type="hidden" name="estado_documento" value="rechazado">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa-solid fa-times"></i> 
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <!-- Botones para ver documentos en una nueva pestaña -->
<a href="<?= $supabase_url . htmlspecialchars($datos->licencia_de_conducir) ?>" target="_blank">
                                            <i class="fa-solid fa-eye"></i> Licencia
                                        </a>
<a href="<?= $supabase_url . htmlspecialchars($datos->licencia_de_conducir) ?>" target="_blank">
                                            <i class="fa-solid fa-eye"></i> Tarjeta
                                        </a>
<a href="<?= $supabase_url . htmlspecialchars($datos->licencia_de_conducir) ?>" target="_blank">
                                            <i class="fa-solid fa-eye"></i> SOAT
                                        </a>
<a href="<?= $supabase_url . htmlspecialchars($datos->licencia_de_conducir) ?>" target="_blank">
                                            <i class="fa-solid fa-eye"></i> Tecno Mecanica
                                        </a>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



    <!-- Modal para acciones -->
    <div class="modal fade" id="accionesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Registrar Acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Contenido del formulario de registro de acción -->
                    <form action="/admin/include/registrar_acciones_docu.php" method="POST">
                        <div class="mb-3">
                            <label for="id_administrador" class="form-label">ID del Administrador</label>
                            <input type="text" class="form-control" name="id_administrador" value="<?php echo isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : ''; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres del Administrador</label>
                            <input type="text" class="form-control" name="nombres" value="<?php echo isset($usuario_sesion['Nombres']) ? $usuario_sesion['Nombres'] : ''; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos del Administrador</label>
                            <input type="text" class="form-control" name="apellidos" value="<?php echo isset($usuario_sesion['Apellidos']) ? $usuario_sesion['Apellidos'] : ''; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">Documento del Administrador</label>
                            <input type="num" class="form-control" name="DNI" value="<?php echo isset($usuario_sesion['DNI']) ? $usuario_sesion['DNI'] : ''; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_accion" class="form-label">Tipo de Intervención</label>
                            <select class="form-select" name="tipo_accion">
                                <option value="3">Eliminar Documentos</option>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="/admin/assets/js/buscador.js"></script>
    <script src="/admin/assets/js/script.js"></script>

</body>

</html>
