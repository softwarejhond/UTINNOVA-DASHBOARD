<?php
session_start();
include("conexion.php");
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: index.php');
    exit;
}
include("components/filters/takeUser.php");

$infoUsuario = obtenerInformacionUsuario(); // Obtén la información del usuario
$rol = $infoUsuario['rol'];

// Validar que se recibió un ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$number_id = $_GET['id'];

// Consulta para obtener los datos del usuario
$sqlUser = "SELECT first_name, second_name, first_last, second_last, number_id 
            FROM user_register 
            WHERE number_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param('s', $number_id);
$stmtUser->execute();
$userData = $stmtUser->get_result()->fetch_assoc();

// Consulta para obtener el historial de contactos
$sqlHistory = "SELECT cl.*, a.name AS advisor_name, 
               DATE_FORMAT(cl.contact_date, '%d-%m-%Y %H:%i') as fecha_contacto
               FROM contact_log cl
               LEFT JOIN advisors a ON cl.idAdvisor = a.idAdvisor
               WHERE cl.number_id = ?
               ORDER BY cl.contact_date DESC";
$stmtHistory = $conn->prepare($sqlHistory);
$stmtHistory->bind_param('s', $number_id);
$stmtHistory->execute();
$contactHistory = $stmtHistory->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Integración de jquery para lectura en tiempo real -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Integración de Bootstrap y DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/estilo.css?v=0.0.2">
    <link rel="stylesheet" href="css/slidebar.css?v=0.0.3">
    <link rel="stylesheet" href="css/contadores.css?v=0.7">
    <link rel="stylesheet" href="css/dataTables.css?v=0.3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Dashboard</title>
    <link rel="icon" href="img/uttInnova.png" type="image/x-icon">
</head>

<body style="background-color:white">
    <?php include("controller/header.php"); ?>
    <?php include("components/sliderBar.php"); ?>
    <?php include("components/modals/userNew.php"); ?>
    <?php include("components/modals/newAdvisor.php"); ?>
    <br><br>
</body>
<div style="margin-top: 50px;">
    <div class="mt-3">

        <div id="dashboard">
            <div class="position-relative bg-transparent">
                <h2 class="position-absolute top-4 start-0"><i class="fa-solid fa-file-contract"></i> Registro de contactos a estudiante</h2>
            </div>
            <br><br>
            <hr>
            <div class="m-4">
                <h2>
                    Estudiante:
                    <span class="badge bg-magenta-dark text-capitalize"><?php echo htmlspecialchars($userData['first_name'] . ' ' .
                                                            $userData['second_name'] . ' ' .
                                                            $userData['first_last'] . ' ' .
                                                            $userData['second_last']); ?></span>

                </h2>
                <h5>Documento: <?php echo htmlspecialchars($userData['number_id']); ?></h5><br>
                <button id="exportarExcel" class="btn btn-success mb-3"
                    onclick="window.location.href='components/contactLogs/studentLogs.php?action=export&id=<?php echo $number_id; ?>'">
                    <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                </button><br>

                <div class="table-responsive">
                    <table id="listaInscritos" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Asesor</th>
                                <th>Detalles</th>
                                <th>Contacto establecido</th>
                                <th>Continúa interesado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contactHistory)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay registros de contacto</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contactHistory as $contact): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contact['fecha_contacto']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['advisor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['details']); ?></td>
                                        <td class="text-center">
                                            <?php if ($contact['contact_established'] == 1): ?>
                                                <span class="badge bg-success">Sí</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($contact['continues_interested'] == 1): ?>
                                                <span class="badge bg-success">Sí</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($contact['observation']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <?php //include("components/aceptUsers/updateStatus.php"); 

                    ?>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("controller/footer.php"); ?>
<?php include("controller/botonFlotanteDerecho.php"); ?>
<?php include("components/sliderBarBotton.php"); ?>

<!-- Scripts de Bootstrap, DataTables y personalizaciones -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="js/dataTables.js?v=0.2"></script>

<script>
    $(document).ready(function() {
        $('#link-dashboard').addClass('pagina-activa');

        // Inicialización de DataTable
        $('#listaInscritos').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pagingType: "simple"
        });
    });
</script>

</body>

</html>