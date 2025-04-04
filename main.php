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

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="icon" href="img/uttInnova.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


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
                    <h2 class="position-absolute top-4 start-0"><i class="bi bi-speedometer2"></i> Dashboard</h2>
                </div>
                <br><br>
                <hr>
                <?php include("components/cardContadores/contadoresCards.php"); ?>

                <?php //include("components/aceptUsers/updateStatus.php");  ?>
                <div class="row">
                    <div class="col-sm-12 col-md-3 col-lg-3">
                  
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3">
                  
                        <?php //include("components/graphics/stratum.php");  ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
    <!-- <script src="js/real-time-update-contadores.js?v=0.3"></script> -->
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
    <script>
        /*
    // Función para hacer la solicitud AJAX y mostrar el total en una alerta
    function mostrarTotalEnAlerta() {
        // Hacer la solicitud AJAX al script PHP
        fetch('controller/obtener_proporciones.php')
            .then(response => response.json())
            .then(data => {
                // Mostrar el total de estudiantes en un alert
                alert('Total de Estudiantes: ' + data.total_estudiantes);
            })
            .catch(error => console.error('Error al obtener los datos:', error));
    }

    // Llamar a la función al cargar la página
    window.onload = mostrarTotalEnAlerta;
    */
</script>

</body>

</html>
