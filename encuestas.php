<?php
session_start();
include("conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

include("components/filters/takeUser.php"); // Asegúrate de que este archivo existe y es necesario aquí
$infoUsuario = obtenerInformacionUsuario();
$rol = $infoUsuario['rol'];

// Incluir el controlador de encuestas
include("components/survey/surveyController.php");

// Procesa las acciones (Crear, Editar, Eliminar) antes de mostrar la lista
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? ''; // Obtener la acción
    switch ($action) {
        case 'editar':
            $response = editarEncuesta($conn, $_POST); // Implementa esta función en el controlador
            echo $response;
            exit();
            break;
        case 'eliminar':
            $response = eliminarEncuesta($conn, $_POST['id']); // Implementa esta función en el controlador
            echo $response;
            exit();
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/estilo.css?v=0.0.2">
    <link rel="stylesheet" href="css/contadores.css?v=0.7">
    <link rel="stylesheet" href="css/slidebar.css?v=0.0.3">
    <link rel="stylesheet" href="css/dataTables.css?v=0.3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>Gestión de Encuestas</title>
    <link rel="icon" href="img/utt.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
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
                <h2 class="position-absolute top-4 start-0">
                    <i class="fas fa-clipboard-list"></i> Gestión de Encuestas
                </h2>

            </div>
            <br><br>
            <hr>
            <?php include("components/survey/list_survey.php"); ?><!-- Incluye la lista de encuestas -->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <?php //include("components/aceptUsers/updateStatus.php"); 

                    ?>
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
<script src="https://cdn.datatables.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="js/dataTables.js?v=0.2"></script>
<script src="js/survey.js"></script>

<script>
   $(document).ready(function() {
  if ( ! $.fn.DataTable.isDataTable( '#tablaEncuestas' ) ) {
      $('#tablaEncuestas').DataTable({
          "language": {
              "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json",
              "searchPlaceholder": "Buscar por cédula..."
          },
          "aaSorting": []
      });
  }
});
</script>

<script>
    $(document).ready(function() {
        $('.modal[id^="editarEncuestaModal-"]').on('shown.bs.modal', function(event) {
            var modalId = $(this).attr('id');
            // Divide el ID del modal para obtener el ID de la encuesta
            var encuestaId = modalId.split('-')[1];
            var encuestaIdElement = document.querySelector(`#${modalId} #encuestaId-${encuestaId}`);
            console.log('encuestaIdElement:', encuestaIdElement);
            if (encuestaIdElement) {
                var encuestaIdValue = encuestaIdElement.value;
                console.log('ID de la Encuesta (desde JavaScript):', encuestaIdValue);
            } else {
                console.log('¡No se encontró el elemento #encuestaId dentro del modal!');
            }
        });
    });
</script>
</body>

</html>