<!DOCTYPE html>
<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

$infoUsuario = obtenerInformacionUsuario(); // Obtén la información del usuario
$rol = $infoUsuario['rol'];
$usaurio = htmlspecialchars($_SESSION["username"]);


?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/estilo.css?v=0.0.1">
    <link rel="stylesheet" href="css/slidebar.css?v=0.0.2">
    <link rel="stylesheet" href="css/contadores.css?v=0.7">
    <title>Dashboard</title>
    <link rel="icon" href="img/utt.png" type="image/x-icon">
</head>

<?php include("controller/header.php"); ?>
<?php include("components/sliderBar.php"); ?>

<div style="margin-top: 50px;">
        <br><br>
        <div id="dashboard">
            <div class="position-relative">
                <h2><i class="bi bi-person-bounding-box"></i> Perfil</h2>
                <hr>
                <div class="row bg-transparent">

                    <div class="col-lg-12 col-md-12 col-sm-12 px-2 mt- bg-transparent">
                        <div class="container">
                            <!-- Toast Container -->
                            <div class="toast-container top-0 bottom-0 end-0 p-3">
                                <div id="myToast" class="toast <?php echo $tipo_mensaje === 'success' ? 'bg-lime-light' : 'bg-amber-light'; ?>" role="alert" aria-live="assertive" aria-atomic="true" style="display: <?php echo !empty($mensaje) ? 'block' : 'none'; ?>;" data-bs-autohide="true" data-bs-delay="5000">
                                    <div class="toast-header">
                                        <strong class="me-auto"><i class="bi bi-exclamation-square-fill"></i> <?php echo $tipo_mensaje === 'success' ? 'Éxito' : 'Error'; ?></strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body">
                                        <?php echo $mensaje; ?>
                                    </div>
                                </div>

                            </div>
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
                                    <img src="<?php echo $foto; ?>" alt='Perfil' class="rounded p-1" width="100%" />
                                    <br>
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="formType" value="updatePictureProfile">
                                        <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usaurio); ?>"> <!-- Campo oculto para el identificador -->

                                        Selecciona una imagen para subir
                                        <input type="file" name="image" style="cursor: pointer" title="Seleccionar una imagen" />
                                        <br><br>
                                        <input type="submit" name="submit" class="btn bg-magenta-dark text-white" value="ACTUALIZAR FOTO" />
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
                                            <input type="password" id="password" name="new_password" class="form-control" required>
                                            <span class="help-block"><?php echo isset($new_password_err) ? $new_password_err : ''; ?></span>
                                        </div>

                                        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                            <label for="confirm_password">Confirmar contraseña</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                            <span class="help-block"><?php echo isset($confirm_password_err) ? $confirm_password_err : ''; ?></span>
                                        </div>

                                        <div class="form-group m-3">
                                            <input type="submit" class="btn bg-magenta-dark text-white" value="Actualizar contraseña">
                                            <a class="btn btn-outline-danger" href="main.php">Cancelar</a>
                                        </div>
                                    </form>
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
                                    <br><br>  <br><br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Toast -->

<!-- Toast Container -->
<div class="toast-container top-0 bottom-0 end-0 p-3">
    <div id="toastPas" class="toast <?php echo $tipo_mensaje === 'success' ? 'bg-lime-light' : 'bg-amber-light'; ?>" role="alert" aria-live="assertive" aria-atomic="true" style="display: <?php echo !empty($mensaje) ? 'block' : 'none'; ?>;">
        <div class="toast-header">
            <strong class="me-auto"><i class="bi bi-exclamation-square-fill"></i> <?php echo $tipo_mensaje === 'success' ? '<i class="bi bi-check-circle-fill"></i> Éxito' : '<i class="bi bi-x-circle-fill"></i> Error'; ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?php echo $mensaje; ?>
        </div>
    </div>
</div>
<!-- Toast Container -->
<div class="toast-container top-0 end-0 p-3">
    <div id="liveToast" class="toast <?php echo $_SESSION['tipo_mensaje'] === 'success' ? 'bg-lime-light' : 'bg-amber-light'; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000" style="display: <?php echo isset($_SESSION['mensaje']) ? 'block' : 'none'; ?>;">
        <div class="toast-header">
            <strong class="me-auto"><?php echo $_SESSION['tipo_mensaje'] === 'success' ? '<i class="bi bi-check-circle-fill"></i> Éxito' : '<i class="bi bi-x-circle-fill"></i> Error'; ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?php echo $_SESSION['mensaje']; ?>
        </div>
    </div>
</div>

<?php include("controller/botonFlotanteDerecho.php"); ?>
<?php include("components/sliderBarBotton.php"); ?>
<?php include("controller/footer.php"); ?>

<script src="js/real-time-inquilino-proximo-retiro.js?v=0.1"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toastPas = document.getElementById('toastPas');
        if (toastPas.style.display === 'block') {
            const toastBootstrap = new bootstrap.Toast(toastPas);
            toastBootstrap.show();
        }
    });
</script>
<?php if (isset($_SESSION['resultado_foto'])): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="fotoToast" class="toast <?php echo $_SESSION['resultado_foto']['success'] ? 'bg-success' : 'bg-danger'; ?>" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto"><?php echo $_SESSION['resultado_foto']['success'] ? '<i class="bi bi-check-circle-fill"></i> Éxito' : '<i class="bi bi-x-circle-fill"></i> Error'; ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php echo $_SESSION['resultado_foto']['message']; ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastMessage = <?php echo json_encode($toastMessage); ?>;
            const toastType = <?php echo json_encode($toastType); ?>;

            if (toastMessage) {
                const toastElement = document.getElementById('toastMessage');
                const toastText = document.getElementById('toastMessageText');

                // Establecer el mensaje y el tipo de toast
                toastText.textContent = toastMessage;
                toastElement.classList.add(toastType);

                // Crear instancia y mostrar el toast
                const toast = new bootstrap.Toast(toastElement);
                toastElement.style.display = 'block';
                toast.show();
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('fotoToast');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
    <?php unset($_SESSION['resultado_foto']); ?>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const liveToast = document.getElementById('liveToast');
        if (liveToast && liveToast.style.display === 'block') {
            const toast = new bootstrap.Toast(liveToast, {
                delay: 3000
            }); // 3000 ms
            toast.show();

            // Limpiar el mensaje de sesión después de mostrar el toast
            liveToast.addEventListener('hidden.bs.toast', function() {
                // Eliminar el mensaje de sesión si el toast se ha cerrado
                <?php unset($_SESSION['mensaje']); ?>
                <?php unset($_SESSION['tipo_mensaje']); ?>
            });
        }
    });
</script>
</body>

</html>