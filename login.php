<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar la sesión
session_start();

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Si el usuario ya está logueado, redirigir a main.php
    header("location: main.php");
    exit;
}

// Establecer tiempo de vida de la sesión en segundos
$inactividad = 86400; // 24 horas

// Comprobar si $_SESSION["timeout"] está establecida
if (isset($_SESSION["timeout"])) {
    // Calcular el tiempo de vida de la sesión (TTL = Time To Live)
    $sessionTTL = time() - $_SESSION["timeout"];
    if ($sessionTTL > $inactividad) {
        session_unset();
        session_destroy();
        header("location: login.php"); // Redirigir a la página de inicio de sesión
        exit;
    }
}

// El siguiente key se crea cuando se inicia sesión
$_SESSION["timeout"] = time();

// Incluir el archivo de conexión
require_once "conexion.php";

// Definir variables y inicializar con valores vacíos
$username = $password = "";
$username_err = $password_err = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar que el nombre de usuario no esté vacío
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingrese su usuario.";
    } else if (!filter_var(trim($_POST["username"]), FILTER_VALIDATE_INT)) {
        $username_err = "El usuario debe ser un número.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validar que la contraseña no esté vacía
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Manejar la funcionalidad de "Recordarme"
    if (isset($_POST['rememberMe'])) {
        // Guardar cookies por 30 días
        setcookie('username', $username, time() + (30 * 24 * 60 * 60), "/");
        setcookie('password', $password, time() + (30 * 24 * 60 * 60), "/");
    } else {
        // Eliminar cookies si "Recordarme" no está seleccionado
        setcookie('username', '', time() - 3600, "/");
        setcookie('password', '', time() - 3600, "/");
    }

    // Validar credenciales
    if (empty($username_err) && empty($password_err)) {
        // Preparar una declaración SQL - INCLUIR VERIFICACIÓN DE ORDEN Y CAMPOS ADICIONALES
        $sql = "SELECT id, username, password, nombre, rol, foto, extra_rol, orden, email, genero, telefono, direccion, edad FROM users WHERE username = ? AND orden = 1";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "s", $param_username); // "s" indica que $username es una cadena
            $param_username = $username;

            // Intentar ejecutar la declaración preparada
            if (mysqli_stmt_execute($stmt)) {
                // Almacenar resultado
                mysqli_stmt_store_result($stmt);

                // Verificar si el nombre de usuario existe y está habilitado
                if (mysqli_stmt_num_rows($stmt) === 1) {
                    // Vincular variables de resultado
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $nombre, $rol, $foto, $extra_rol, $orden, $email, $genero, $telefono, $direccion, $edad);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // La contraseña es correcta, iniciar una nueva sesión

                            // Almacenar datos del usuario en la sesión
                            $_SESSION['loggedin'] = true; // Indica que el usuario ha iniciado sesión
                            $_SESSION['nombre'] = htmlspecialchars($nombre); // Establecer el nombre real del usuario
                            $_SESSION['rol'] = $rol; // Asignar un rol real basado en tu base de datos
                            $_SESSION['username'] = htmlspecialchars($username); // Asignar nombre de usuario
                            $_SESSION['foto'] = htmlspecialchars($foto); // Ruta de la foto del usuario
                            $_SESSION['extra_rol'] = $extra_rol; // Campo extra_rol
                            $_SESSION['orden'] = $orden; // Estado del usuario

                            // Verificar si hay campos vacíos
                            $campos_incompletos = false;
                            $campos_faltantes = array();

                            if (empty($email)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Email";
                            }
                            if (empty($genero)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Género";
                            }
                            if (empty($telefono)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Teléfono";
                            }
                            if (empty($direccion)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Dirección";
                            }
                            if (empty($edad) || $edad == 0) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Edad";
                            }

                            // Guardar la información de campos incompletos en la sesión
                            $_SESSION['campos_incompletos'] = $campos_incompletos;
                            $_SESSION['campos_faltantes'] = $campos_faltantes;

                            // Redirigir al usuario a la página principal
                            header("location: main.php");
                            exit;
                        } else {
                            $password_err = "Contraseña incorrecta.";
                        }
                    }
                } else {
                    // Verificar si el usuario existe pero está deshabilitado
                    $sql_check = "SELECT orden FROM users WHERE username = ?";
                    if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
                        mysqli_stmt_bind_param($stmt_check, "s", $username);
                        if (mysqli_stmt_execute($stmt_check)) {
                            mysqli_stmt_store_result($stmt_check);
                            if (mysqli_stmt_num_rows($stmt_check) === 1) {
                                mysqli_stmt_bind_result($stmt_check, $user_orden);
                                mysqli_stmt_fetch($stmt_check);
                                if ($user_orden == 0) {
                                    $username_err = "Usuario deshabilitado. Contacte al administrador.";
                                } else {
                                    $username_err = "Usuario no existe.";
                                }
                            } else {
                                $username_err = "Usuario no existe.";
                            }
                        }
                        mysqli_stmt_close($stmt_check);
                    } else {
                        $username_err = "Usuario no existe.";
                    }
                }
            } else {
                echo "Algo salió mal, por favor vuelve a intentarlo.";
            }
        }
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
}
include("conexion.php");
$queryCompany = mysqli_query($conn, "SELECT nombre,nit FROM company");
while ($empresaLog = mysqli_fetch_array($queryCompany)) {
    $empresa = $empresaLog['nombre'] . '</label>';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYGNIA - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login1.css?v=1.1">
    <link rel="icon" href="img/uttInnova.png" type="image/x-icon">
    <style>
        /* Estilos para el modo claro */
        body.light-mode {
            background-color: #ffffff;
            color: #000000;
        }

        /* Estilos para el modo oscuro */
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .login-container {
            transition: background-color 0.3s, color 0.3s;
        }

        /* Fuente Sparose igual que el footer */
        @font-face {
            font-family: 'Sparose';
            src: url('css/fonts/fonnts.com-Sparose.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        .eagle-link-footer {
            font-family: 'Sparose', sans-serif !important;
            font-size: 14px;
            color: #fff !important;
            text-decoration: none !important;
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-sidebar" style="position: relative;">
            <div class="login-logo">
                <img src="./img/innovablanco.png" alt="Logo UTT" class="img-fluid pb-2">
            </div>

            <p class="login-text text-white">Inicia sesión con tus credenciales para acceder al sistema</p>

            <br>
            <small class="text-white-50 d-block text-center" style="position: absolute; bottom: 20px; left: 0; width: 100%;">
                Made by <img src="img/eagle_blanco.svg" alt="Eagle Software" style="height: 24px; vertical-align: middle;"> <a href="https://www.agenciaeaglesoftware.com/" class="eagle-link-footer" target="_blank">Eagle Software</a> &copy; <?php echo date("Y"); ?>. <br>Todos los derechos reservados a <?php echo $empresa ?>.
            </small>
        </div>
        <div class="login-form">
            <h2 class="form-title">Iniciar sesión</h2>
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                <div class="mb-4">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        required
                        placeholder="Ingrese su número de documento"
                        value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>">
                    <?php if (!empty($username_err)) : ?>
                        <div class="error-message"><?php echo $username_err ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required
                            placeholder="Ingrese su contraseña"
                            value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; ?>">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border: none;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($password_err)) : ?>
                        <div class="error-message"><?php echo $password_err ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe"
                        <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="rememberMe">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-login" name="iniciar">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>

    <script src="js/tooglePassword.js"></script>
    <script src="components/hooks/lineLogin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            // Alternar el tipo de entrada entre 'password' y 'text'
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Cambiar automáticamente entre modo oscuro y claro según la hora
        const currentHour = new Date().getHours(); // Obtener la hora actual
        if (currentHour >= 18 || currentHour < 6) {
            // Si es después de las 6 PM o antes de las 6 AM, activar modo oscuro
            document.body.classList.add('dark-mode');
        } else {
            // De lo contrario, activar modo claro
            document.body.classList.add('dark-mode');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>

</html>