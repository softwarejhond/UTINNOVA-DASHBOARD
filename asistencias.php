<?php
session_start();
include("conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

include("components/filters/takeUser.php");
$infoUsuario = obtenerInformacionUsuario();
$rol = $infoUsuario['rol'];

// Incluir el controlador de asistencia
include("components/assists/assistsController.php");

// Procesa las acciones (Crear, Editar, Eliminar) antes de mostrar la lista
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'editar':
            $response = editarAsistencia($conn, $_POST);
            echo $response;
            exit();
            break;
        case 'eliminar':
            $response = eliminarAsistencia($conn, $_POST['id']);
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

    <title>Gestión de Empleabilidad</title>
    <link rel="icon" href="img/uttInnova.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include("controller/header.php"); ?>
    <?php include("components/sliderBar.php"); ?>
    <?php include("components/modals/userNew.php"); ?>
    <?php include("components/modals/newAdvisor.php"); ?>
    <br><br>

    <div style="margin-top: 50px;">
        <div class="mt-3">
            <div id="dashboard">
                <div class="position-relative bg-transparent">
                    <h2 class="position-absolute top-4 start-0">
                        <i class="fas fa-clipboard-list"></i> Gestión asistencias
                    </h2>
                </div>
                <br><br>
                <hr>
                <?php include("components/assist/list_assists.php"); ?>
            </div>
        </div>
    </div>

    <?php include("controller/footer.php"); ?>
    <?php include("controller/botonFlotanteDerecho.php"); ?>
    <?php include("components/sliderBarBotton.php"); ?>

    <?php
  
    foreach ($asistencias as $fila) {
        $id_asistencia_actual = $fila['id'];
        include 'components/assist/details_assists.php';
    }
    ?>

    <!-- Scripts de Bootstrap, DataTables y personalizaciones -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dataTables.js?v=0.2"></script>

    <script>
        $(document).ready(function() {
            $('#tablaAsistencias').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json",
                    "searchPlaceholder": "Buscar por cédula..."
                },
                "aaSorting": [],
                "data": <?php echo json_encode($asistencias); ?>,
                "columns": [{
                        "data": "cedula",
                        "title": "Cédula"
                    },
                    {
                        "data": "full_name",
                        "title": "Nombre"
                    },
                    {
                        "data": "activity_type",
                        "title": "Tipo de Actividad"
                    },
                    {
                        "data": "created_at",
                        "title": "Fecha de Creación"
                    },
                    {
                        "data": null,
                        "title": "Acciones",
                        "render": function(data, type, row) {
                            return `
                            <button type='button' class='btn bg-indigo-dark btn-sm' data-bs-toggle='modal' data-bs-target='#verAsistenciaModal-${row.id}' title='Ver Detalles'><i class='fas fa-eye'></i></button>
                        `;
                        }
                    }
                ]
            });
        });
        //     <button type='button' class='btn bg-magenta-dark btn-sm' onclick='confirmarEliminacion(${row.id})' title='Eliminar'><i class='fas fa-trash-alt'></i></button>
        //boton sis e requiere
    </script>

    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará la asistencia. ¿Deseas continuar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    $.ajax({
                        type: "POST",
                        url: "asistencias.php", 
                        data: {
                            action: 'eliminar',
                            id: id
                        },
                        success: function(response) {
                           
                            var data = JSON.parse(response);
                            if (data.status === 'success') {
                                
                                Swal.fire(
                                    '¡Eliminado!',
                                    'La asistencia ha sido eliminada correctamente.',
                                    'success'
                                ).then(() => {
                                    
                                    location.reload();
                                });
                            } else {
                                
                                Swal.fire(
                                    '¡Error!',
                                    'Ocurrió un error al eliminar la asistencia: ' + data.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            
                            Swal.fire(
                                '¡Error!',
                                'Ocurrió un error al comunicarse con el servidor.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>