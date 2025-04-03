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
    // Verificar reCAPTCHA
    $recaptcha_secret = "6Lf6w_oqAAAAAG7s5Q_dktqohWh4YTF9MCYTVOWH";
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $response_data = json_decode($response);

    if (!$response_data->success) {
        $username_err = "Por favor verifica el reCAPTCHA.";
    } else {
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

        // Validar credenciales
        if (empty($username_err) && empty($password_err)) {
            // Preparar una declaración SQL
            $sql = "SELECT id, username, password, nombre, rol, foto FROM users WHERE username = ?";

            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Vincular variables a la declaración preparada como parámetros
                mysqli_stmt_bind_param($stmt, "s", $param_username); // "s" indica que $username es una cadena
                $param_username = $username;

                // Intentar ejecutar la declaración preparada
                if (mysqli_stmt_execute($stmt)) {
                    // Almacenar resultado
                    mysqli_stmt_store_result($stmt);

                    // Verificar si el nombre de usuario existe, si sí, verificar la contraseña
                    if (mysqli_stmt_num_rows($stmt) === 1) {
                        // Vincular variables de resultado
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $nombre, $rol, $foto);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (password_verify($password, $hashed_password)) {
                                // La contraseña es correcta, iniciar una nueva sesión

                                // Almacenar datos del usuario en la sesión
                                $_SESSION['loggedin'] = true; // Indica que el usuario ha iniciado sesión
                                $_SESSION['nombre'] = htmlspecialchars($nombre); // Establecer el nombre real del usuario
                                $_SESSION['rol'] = $rol; // Asignar un rol real basado en tu base de datos
                                $_SESSION['username'] = htmlspecialchars($username); // Asignar nombre de usuario
                                $_SESSION['foto'] = htmlspecialchars($foto); // Ruta de la foto del usuario

                                // Redirigir al usuario a la página principal
                                header("location: main.php");
                                exit;
                            } else {
                                $password_err = "Contraseña incorrecta.";
                            }
                        }
                    } else {
                        $username_err = "Usuario no existe.";
                    }
                } else {
                    echo "Algo salió mal, por favor vuelve a intentarlo.";
                }
            }
            // Cerrar la declaración
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIVP - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login1.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="icon" href="img/utt.png" type="image/x-icon">
 </head>

<body>
    <div class="login-container">
        <div class="login-sidebar">
            <div class="login-logo">
            <img src="./img/utt.jpg" alt="Logo UTT" class="img-fluid">
            </div>
            
            <p class="login-text text-white">Inicia sesión con tus credenciales para acceder al sistema</p>
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
                        placeholder="Ingrese su número de documento">
                    <?php if (!empty($username_err)) : ?>
                        <div class="error-message"><?php echo $username_err ?></div>
                    <?php endif ?>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required
                        placeholder="Ingrese su contraseña">
                    <?php if (!empty($password_err)) : ?>
                        <div class="error-message"><?php echo $password_err ?></div>
                    <?php endif ?>
                </div>

                <div class="g-recaptcha mb-4" data-sitekey="6Lf6w_oqAAAAAHHGxCBYAxNDKL4xQrwJ_Ds5olO4"></div>

                <button type="submit" class="btn btn-login" name="iniciar">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>

    <script src="js/tooglePassword.js"></script>
    <script src="components/hooks/lineLogin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>