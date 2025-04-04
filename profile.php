<?php
session_start();
include("conexion.php");
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores

// Inicializar variables
$new_password = "";
$new_password_err = "";
$confirm_password_err = "";
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: login.php');
    exit;
}
include("funciones.php");

include("components/filters/takeUser.php");

$infoUsuario = obtenerInformacionUsuario(); // Obtén la información del usuario
$rol = $infoUsuario['rol'];
$usaurio = htmlspecialchars($_SESSION["username"]);


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


</head>

<?php include("controller/header.php"); ?>
    <?php include("components/sliderBar.php"); ?>
    <?php include("components/modals/userNew.php"); ?>
    <?php include("components/modals/newAdvisor.php"); ?>

<div style="margin-top: 50px;">
    <br>
        <div id="dashboard">
            <div class="position-relative">
                <h2><i class="bi bi-person-bounding-box"></i> Perfil</h2>
                <hr>
                <div class="row bg-transparent">

                    <div class="col-lg-12 col-md-12 col-sm-12 px-2 mt- bg-transparent">
                        <div class="container">
                   
                            <div class="row">
                                <!--Actualizar datos usuario-->
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM users WHERE username like '%$usaurio%'");
                                while ($userLog = mysqli_fetch_array($query)) {
                                    $name = $userLog['nombre'];
                                    $phone = $userLog['telefono'];
                                    $email = $userLog['email'];
                                    $year = $userLog['edad'];
                                    $genero = $userLog['genero'];
                                    $department = $userLog['rol'];
                                    $direccion = $userLog['direccion'];
                                    $foto = $userLog['foto'];
                                }


                                ?>
                                <div class="col-lg-4 col-md-4 col-sm-12 px-2 mt-1">
                                    <h4>Actualizar foto</h4>
                                    <img src="<?php echo $foto; ?>" alt='Perfil' class="rounded p-1" width="90%" />
                                    <br>
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="formType" value="updatePictureProfile">
                                        <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Campo oculto para el identificador -->

                                        Selecciona una imagen para subir
                                        <input type="file" name="image" style="cursor: pointer" title="Seleccionar una imagen" />
                                        <br><br>
                                        <input type="submit" name="submit" class="btn bg-magenta-dark text-white" value="Actualizar foto" />
                                    </form>


                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 px-2 mt-1">
                                    <img src="img/icons/pass.png" alt="pass" width="100%">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                        <input type="hidden" name="formType" value="updatePassword">
                                        <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Campo oculto para el identificador -->

                                        <h4>Actualizar contraseña</h4>

                                        <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                                            <label for="password">Nueva contraseña</label>
                                            <input type="password" id="password" name="new_password" class="form-control" placeholder="*********"  required>
                                            <span class="help-block"><?php echo isset($new_password_err) ? $new_password_err : ''; ?></span>
                                        </div>

                                        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                            <label for="confirm_password">Confirmar contraseña</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="*********" required>
                                            <span class="help-block"><?php echo isset($confirm_password_err) ? $confirm_password_err : ''; ?></span>
                                        </div>

                                        <div class="form-group m-3">
                                            <input type="submit" class="btn bg-magenta-dark text-white" value="Actualizar contraseña">
                                            <a class="btn btn-outline-danger" href="main.php">Cancelar</a>
                                        </div>
                                    </form>

                                    <?php if (isset($_SESSION['resultado_password'])): ?>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                Swal.fire({
                                                    icon: '<?php echo $_SESSION['resultado_password']['success'] ? 'success' : 'error'; ?>',
                                                    title: '<?php echo $_SESSION['resultado_password']['success'] ? 'Éxito' : 'Error'; ?>',
                                                    text: '<?php echo $_SESSION['resultado_password']['message']; ?>'
                                                });
                                            });
                                        </script>
                                        <?php unset($_SESSION['resultado_password']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 px-2 mt-1">
                                    <?php
                                    function getRoleName($department)
                                    {
                                        switch ($department) {
                                         
                                            case 1:
                                                return "Administrador";
                                            case 2:
                                                return "Editor";
                                            case 3:
                                                return "Asesor";
                                            case 4:
                                                return "Visualizador";
                                            case 5:
                                                return "Docente";
                                            case 6:
                                                return "Académico";
                                            case 7:
                                                return "Monitor";
                                            case 8:
                                                return "Mentor";
                                            case 9:
                                                return "Supervisor";
                                            case 10:
                                                return "Empleabilidad";
                                            case 11:
                                                return "Superacademico";
                                            case 12:
                                                return "Control maestro";
                                            default:
                                                return "Rol desconocido";
                                        }
                                    }

                                    ?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="usaurio" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Cambia 'username' a 'usaurio' -->

                                        <input type="hidden" name="formType" value="updateUser">
                                        <h4>Actualizar información personal</h4>
                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input type="text" name="updName" class="form-control" value="<?php echo $name; ?>" require>
                                        </div>
                                        <div class="form-group">
                                            <label>Teléfono</label>
                                            <input type="text" name="updPhone" class="form-control" value="<?php echo $phone; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="updEmail" class="form-control" value="<?php echo $email; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Edad</label>
                                            <input type="number" name="updYear" class="form-control" value="<?php echo $year; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Dirección</label>
                                            <input type="text" name="updAdress" class="form-control" value="<?php echo $direccion; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Género</label>
                                            <select class="form-control" name="updGenero">
                                                <option value="<?php echo $genero; ?>"><?php echo $genero; ?>
                                                </option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Femenino">Femenino</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Dependencia</label>
                                            <select class="form-control" name="updDepartmen">
                                                <option value="<?php echo htmlspecialchars($department); ?>">
                                                    <?php echo htmlspecialchars(getRoleName($department)); ?>
                                                </option>
                                            
                                            </select>
                                        </div>
                                        <div class="form-group m-3">
                                            <input type="submit" class="btn bg-magenta-dark text-white" value="Actualizar datos" name="actualizarUsuario">
                                            <a class="btn btn-outline-danger" href="main.php">Cancelar</a>
                                        </div>
                                    </form>

                                    <?php if (isset($_SESSION['mensaje'])): ?>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                Swal.fire({
                                                    icon: '<?php echo $_SESSION['tipo_mensaje'] === "success" ? "success" : "error"; ?>',
                                                    title: '<?php echo $_SESSION['tipo_mensaje'] === "success" ? "Éxito" : "Error"; ?>',
                                                    text: '<?php echo $_SESSION['mensaje']; ?>'
                                                });
                                            });
                                        </script>
                                        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                                    <?php endif; ?>
                                    <br><br>  <br><br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- swhit alert-->

    <?php if (isset($_SESSION['resultado_foto'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $_SESSION['resultado_foto']['success'] ? 'success' : 'error'; ?>',
                    title: '<?php echo $_SESSION['resultado_foto']['success'] ? 'Éxito' : 'Error'; ?>',
                    text: '<?php echo $_SESSION['resultado_foto']['message']; ?>'
                });
            });
        </script>
        <?php unset($_SESSION['resultado_foto']); ?>
    <?php endif; ?>


<?php include("controller/botonFlotanteDerecho.php"); ?>
<?php include("components/sliderBarBotton.php"); ?>
<?php include("controller/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


</body>

</html>