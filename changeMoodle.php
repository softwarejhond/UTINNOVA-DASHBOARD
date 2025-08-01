<?php
session_start();
include("conexion.php");
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores
require 'vendor/autoload.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: index.php');
    exit;
}
include("components/filters/takeUser.php");

$infoUsuario = obtenerInformacionUsuario(); // Obtén la información del usuario
$rol = $infoUsuario['rol'];
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
    <link rel="icon" href="img/utt.png" type="image/x-icon">
  
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
                <h2 class="position-absolute top-4 start-0"><i class="fa-solid fa-user-tag"></i> Cambiar bootcamp de campista</h2>
            </div>
            <br><br>
            <hr>
            <?php include("components/registerMoodle/bootcampChange.php"); ?>
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
$(document).ready(function () {
    // Agregar clase activa al enlace del dashboard
    $('#link-dashboard').addClass('pagina-activa');

    // Verificar si DataTable ya está inicializado para evitar errores
    if ($.fn.DataTable.isDataTable('#listaInscritos')) {
        $('#listaInscritos').DataTable().destroy(); // Destruir instancia previa si existe
    }

    // Inicializar DataTable con configuración personalizada
    var table = $('#listaInscritos').DataTable({
        responsive: true,
        language: {
            url: "controller/datatable_esp.json"
        },
        pagingType: "simple",
        columnDefs: [
            { orderable: false, targets: [4] } // Desactivar ordenación en la columna 5 (checkbox)
        ],
        // Agregar atributos personalizados a cada fila
        createdRow: function (row, data) {
            $(row).attr({
                'data-department': data.departamento,
                'data-headquarters': data.sede,
                'data-program': data.program,
                'data-mode': data.mode,
                'data-level': data.level,
                'data-schedule': data.schedule
            });
        }
    });

    // Aplicar filtros cuando cambian los select de filtros
    $('#filterDepartment, #filterHeadquarters, #filterProgram, #filterMode, #filterLevel, #filterSchedule').on('change', function () {
        table.draw();
    });

    // Personalizar la función de filtrado de DataTable
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var selectedDepartment = $('#filterDepartment').val();
        var selectedHeadquarters = $('#filterHeadquarters').val();
        var selectedProgram = $('#filterProgram').val();
        var selectedMode = $('#filterMode').val();
        var selectedLevel = $('#filterLevel').val();
        var selectedSchedule = $('#filterSchedule').val();

        var rowDepartment = $(table.row(dataIndex).node()).data('department');
        var rowHeadquarters = $(table.row(dataIndex).node()).data('headquarters');
        var rowProgram = $(table.row(dataIndex).node()).data('program');
        var rowMode = $(table.row(dataIndex).node()).data('mode');
        var rowLevel = $(table.row(dataIndex).node()).data('level');
        var rowSchedule = $(table.row(dataIndex).node()).data('schedule');

        return (!selectedDepartment || rowDepartment === selectedDepartment) &&
               (!selectedHeadquarters || rowHeadquarters === selectedHeadquarters) &&
               (!selectedProgram || rowProgram === selectedProgram) &&
               (!selectedMode || rowMode === selectedMode) &&
               (!selectedLevel || rowLevel === selectedLevel) &&
               (!selectedSchedule || rowSchedule === selectedSchedule);
    });
});

</script>

</body>

</html>