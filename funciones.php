<?php
//Función para obtener el registro de la configuración del sitio
function obtenerConfiguracion()
{
    include("conexion.php");
    //Comprobamos si existe el registro 1 que mantiene la configuraciòn
    //Añadimos un alias AS total para identificar mas facil
    $query = "SELECT COUNT(*) AS total FROM configuracion";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);


    if ($row['total'] == '0') {
        //No existe el registro 1 - DEBO INSERTAR el registro por primera vez
        $query = "INSERT INTO configuracion (id,nombre,user,password,rol,estado,foto)
        VALUES (NULL, 'Eagle Software','admin', 'admin','1','1','')";

        if (mysqli_query($conn, $query)) { //Se insertó correctamente

        } else {
            echo "No se pudo insertar en la BD" . mysqli_errno($conn);
        }
    }

    //Selecciono el registro dela configuración
    $query = "SELECT * FROM configuracion  WHERE id='1'";
    $result = mysqli_query($conn, $query);
    $config = mysqli_fetch_assoc($result);
    return $config;
}

//Función que obtiene el total de registros de una tabla
function obtenerTotalRegistros($tabla)
{

    include("conexion.php");
    $query = "SELECT COUNT(*) id FROM $tabla";
    $result = mysqli_query($conn, $query);
    $fila = mysqli_fetch_assoc($result);
    return $fila['id'];
}

//funcion para agrear un nuevo tipo de propiedad a la BD

function agregarNuevoTipoDePropiedad($tipo)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include("conexion.php");
    $query = "INSERT INTO tipos (id, nombre_tipo) VALUES (NULL, '$tipo')";

    if (mysqli_query($conn, $query)) {
        $mensaje = "Tipo de Propiedad agregado correctamente";
        $tipo_mensaje = "success"; // Tipo de mensaje para éxito
    } else {
        $mensaje = "No se pudo insertar en la BD: " . mysqli_error($conn);
        $tipo_mensaje = "error"; // Tipo de mensaje para error
    }
    return [$mensaje, $tipo_mensaje];
}

if (isset($_POST['agregar'])) {
    $tipo = trim($_POST['tipo']);
    list($mensaje, $tipo_mensaje) = agregarNuevoTipoDePropiedad($tipo);
}

if (isset($_POST['agregar'])) {
    // Tomamos los datos que vienen del formulario
    $tipo = trim($_POST['tipo']);
    list($mensaje, $tipo_mensaje) = agregarNuevoTipoDePropiedad($tipo);
}


//funcion para agrear un nuevo pais a la BD
function agregarNuevoPais($pais)
{
    include("conexion.php");
    //armamos el query para insertar en la tabla paises
    $query = "INSERT INTO paises (id, nombre_pais)
    VALUES (NULL, '$pais')";

    //insertamos en la tabla paises
    if (mysqli_query($conn, $query)) { //Se insertó correctamente
        $mensaje = "Pais agregado correctamente";
    } else {
        $mensaje = "No se pudo insertar en la BD" . mysqli_errno($conn);
    }
    return $mensaje;
}

//Función que obtiene el total de registros de una tabla
function obtenerEmpresas()
{
    include("conexion.php"); // Incluye el archivo de conexión

    $query = "SELECT nombre, logo FROM company"; // Consulta para obtener nombres y logos de las empresas
    $result = mysqli_query($conn, $query); // Ejecuta la consulta usando $conn

    if ($result) {
        // Inicializar un array para almacenar los datos de las empresas
        $empresas = [];

        // Recorrer los resultados y almacenar los nombres y logos en el array
        while ($empresaLog = mysqli_fetch_array($result)) {
            // Añadir cada empresa como un array asociativo con 'nombre' y 'logo'
            $empresas[] = [
                'nombre' => htmlspecialchars($empresaLog['nombre']),
                'logo' => htmlspecialchars($empresaLog['logo'])
            ];
        }

        return $empresas; // Retornar el array de empresas
    } else {
        error_log("Error en la consulta: " . mysqli_error($conn)); // Registro del error
        return []; // Retornar un array vacío en caso de error
    }
}

//funcion para actualizar smtp
function obtenerConfiguracionSMTP()
{
    include("conexion.php");

    // Realizar la consulta
    $query = "SELECT * FROM smtpConfig WHERE id='1'";
    $result = mysqli_query($conn, $query);

    // Verificar si se obtuvieron resultados y devolver los datos
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return null; // Retorna null si no se encontró configuración SMTP
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("conexion.php");

    if (isset($_POST['formType'])) {
        switch ($_POST['formType']) {
            case 'smtpConfig':
                $resultado = actualizarSmtpConfig($conn, $_POST, $_FILES);
                echo json_encode($resultado);
                break;

            case 'updateUser':
                if (isset($_POST['usaurio'])) {
                    $resultado = actualizarUsuario($conn, $_POST['usaurio']);
                    echo json_encode($resultado);
                } else {
                    echo json_encode(['error' => 'El usuario no está definido.']);
                }
                break;

            case 'updatePassword':
                if (isset($_POST['usaurio']) && isset($_POST['newPassword'])) {
                    $resultado = actualizarPassword($conn, $_POST['usaurio'], $_POST['newPassword']);
                    echo json_encode($resultado);
                } else {
                    echo json_encode(['error' => 'El usuario o la nueva contraseña no están definidos.']);
                }
                break;

            case 'updatePictureProfile':
                if (isset($_POST['usuario']) && isset($_FILES['image'])) {
                    $resultado = actualizarFotoPerfil($conn, $_POST['usuario'], $_FILES['image']);
                    // Almacena el mensaje para mostrarlo en el frontend
                    $_SESSION['resultado_foto'] = $resultado;
                    echo json_encode($resultado); // Opcional: puedes devolver el resultado como JSON
                } else {
                    echo json_encode(['error' => 'El usuario o la imagen no están definidos.']);
                }
                break;

            case 'obtenerRegistro':
                if (isset($_POST['tabla']) && isset($_POST['id'])) {
                    $registro = obtenerRegistroPorId($_POST['tabla'], $_POST['id']);
                    echo json_encode($registro);
                } else {
                    echo json_encode(null);
                }
                break;
            case 'actualizarRegistro':
                $resultado = actualizarRegistro($conn, $_POST);
                echo json_encode($resultado);
                break;

            default:
                echo json_encode(['error' => 'Tipo de formulario no reconocido.']);
                break;
        }
    } else {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'obtenerRegistro':
                    if (isset($_POST['tabla']) && isset($_POST['id'])) {
                        $registro = obtenerRegistroPorId($_POST['tabla'], $_POST['id']);
                        echo json_encode($registro);
                    } else {
                        echo json_encode(null);
                    }
                    break;
                case 'actualizarRegistro':
                    $resultado = actualizarRegistro($conn, $_POST);
                    echo json_encode($resultado);
                    break;
            }
        } else {
            echo json_encode(['error' => 'El tipo de formulario no está definido.']);
        }
    }
}

// Función para actualizar la configuración SMTP
function actualizarSmtpConfig($conn, $data, $files)
{
    // Procesar y limpiar los datos recibidos
    $host = mysqli_real_escape_string($conn, $data['host']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = mysqli_real_escape_string($conn, $data['password']);
    $port = mysqli_real_escape_string($conn, $data['port']);
    $nameBody = mysqli_real_escape_string($conn, $data['nameBody']);
    $subject = mysqli_real_escape_string($conn, $data['Subject']);
    $body = mysqli_real_escape_string($conn, $data['body']);
    $nameFile = mysqli_real_escape_string($conn, $data['nameFile']);

    // Inicializar las variables de imagen
    $urlPicture = "";
    $logoEncabezado = "";

    // Manejo de la imagen para el cuerpo del correo
    if (!empty($files['imagen']['name'])) {
        $imagenFile = $files['imagen'];
        $imagenPath = 'img/empresa/' . basename($imagenFile['name']);

        // Verifica que el archivo se haya movido exitosamente
        if (move_uploaded_file($imagenFile['tmp_name'], $imagenPath)) {
            $urlPicture = $imagenPath; // Ruta de la imagen subida
        } else {
            return crearRespuesta('Error al subir la imagen del cuerpo del correo.', 'error');
        }
    }

    // Manejo del logo para el encabezado del PDF
    if (!empty($files['logo']['name'])) {
        $logoFile = $files['logo'];
        $logoPath = 'img/empresa/' . basename($logoFile['name']);

        // Verifica que el archivo se haya movido exitosamente
        if (move_uploaded_file($logoFile['tmp_name'], $logoPath)) {
            $logoEncabezado = $logoPath; // Ruta del logo subido
        } else {
            return crearRespuesta('Error al subir el logo del encabezado.', 'error');
        }
    }

    // Construir la consulta de actualización
    $query = "UPDATE smtpConfig SET host='$host', email='$email', password='$password', port='$port', nameBody='$nameBody', Subject='$subject', body='$body', nameFile='$nameFile'";

    // Añadir las imágenes a la consulta si están presentes
    if (!empty($urlPicture)) {
        $query .= ", urlpicture='$urlPicture'";
    }
    if (!empty($logoEncabezado)) {
        $query .= ", logoEncabezado='$logoEncabezado'";
    }

    // Añadir condición para el registro específico
    $query .= " WHERE id='1'";

    // Ejecutar la consulta y manejar el resultado
    if (mysqli_query($conn, $query)) {
        return crearRespuesta('Configuración SMTP actualizada correctamente.', 'success', $urlPicture, $logoEncabezado);
    } else {
        return crearRespuesta('Error al actualizar la configuración: ' . mysqli_error($conn), 'error');
    }
}
// Función para crear la respuesta en formato JSON en actualizar smtp
function crearRespuesta($mensaje, $tipo_mensaje, $urlPicture = null, $logoEncabezado = null)
{
    $response = [
        'mensaje' => $mensaje,
        'tipo_mensaje' => $tipo_mensaje,
        'urlPicture' => $urlPicture,
        'logoEncabezado' => $logoEncabezado
    ];
    // Asegúrate de que siempre se devuelva un JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Asegúrate de salir después de enviar la respuesta
}


//actualizar foto de perfil
function actualizarFotoPerfil($conn, $usuario, $imagen)
{
    $target_dir = "img/fotosUsuarios/";
    $target_file = $target_dir . basename($imagen["name"]);
    $uploadOk = 1;

    // Verificar si el archivo es una imagen
    $check = getimagesize($imagen["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }

    // Obtener la foto actual del usuario
    $sql = "SELECT foto FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && !empty($row['foto']) && file_exists($row['foto'])) {
        // Eliminar la foto anterior
        unlink($row['foto']);
    }

    // Mover el archivo a la carpeta destino
    if (move_uploaded_file($imagen["tmp_name"], $target_file)) {
        $sql = "UPDATE users SET foto = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $target_file, $usuario);
        if ($stmt->execute()) {
            $_SESSION['resultado_foto'] = [
                'success' => true,
                'message' => 'Foto actualizada exitosamente.'
            ];
            return ['success' => true, 'message' => 'Foto actualizada exitosamente.'];
        } else {
            $_SESSION['resultado_foto'] = [
                'success' => false,
                'message' => 'Error al actualizar la foto en la base de datos.'
            ];
            return ['success' => false, 'message' => 'Error al actualizar la foto en la base de datos.'];
        }
    } else {
        $_SESSION['resultado_foto'] = [
            'success' => false,
            'message' => 'Error al subir el archivo.'
        ];
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
}

// Asegúrate de incluir la conexión antes de usarla
include("conexion.php");

// Función para actualizar los datos del usuario
// Verifica si el usuario está logueado
if (session_status() === PHP_SESSION_NONE) {
     session_start();
}

// Para depuración
//var_dump($_SESSION);

// Función para actualizar los datos del usuario
// Verifica si el usuario está logueado
if (isset($_SESSION['username'])) {
    $usaurios = $_SESSION['username']; // Asegúrate de que esta es la clave correcta
} else {
    echo '<div class="alert alert-danger alert-dismissable">Error: No se ha encontrado el usuario.</div>';
    exit; // Salir si no hay usuario
}
function actualizarUsuario($conn, $usaurios)
{
    // Verifica si se ha enviado el formulario
    if (isset($_POST['actualizarUsuario'])) {
        // Asegúrate de que cada valor existe antes de usarlo
        $updName = isset($_POST["updName"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updName"])) : '';
        $updPhone = isset($_POST["updPhone"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updPhone"])) : '';
        $updEmail = isset($_POST["updEmail"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updEmail"])) : '';
        $updYear = isset($_POST["updYear"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updYear"])) : '';
        $updAdress = isset($_POST["updAdress"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updAdress"])) : '';
        $updGenero = isset($_POST["updGenero"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updGenero"])) : '';
        $updDepartmen = isset($_POST["updDepartmen"]) ? mysqli_real_escape_string($conn, strip_tags($_POST["updDepartmen"])) : '';

        // Verifica si el usuario existe
        if (empty($usaurios)) {
            return 'El identificador del usuario no puede estar vacío.';
        }

        // Realizar la actualización en la base de datos
        $update = mysqli_query($conn, "UPDATE users SET nombre='$updName', telefono='$updPhone', email='$updEmail', edad='$updYear', direccion='$updAdress', genero='$updGenero', rol='$updDepartmen' WHERE username='$usaurios'");

        // Verificar el resultado de la actualización
        if ($update) {
            return 'Los datos se han actualizado y guardados con éxito.';
        } else {
            return 'Error al guardar los datos: ' . mysqli_error($conn);
        }
    }
    return '';
}

// Verifica si se envió el formulario de actualización
if (isset($_POST['actualizarUsuario'])) {
    $usaurios = isset($_POST['usaurio']) ? mysqli_real_escape_string($conn, strip_tags($_POST['usaurio'])) : '';
    $mensaje = actualizarUsuario($conn, $usaurios); // Llama a la función y almacena el mensaje

    // Determina el tipo de mensaje
    $tipo_mensaje = strpos($mensaje, 'Error') === false ? 'success' : 'error';

    // Almacena el mensaje y tipo de mensaje en la sesión
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo_mensaje;

    // Redireccionar para evitar que el formulario se vuelva a enviar al recargar la página
    header("Location: profile.php?mensaje=" . urlencode($mensaje) . "&tipo_mensaje=" . $tipo_mensaje);
    exit;
}




// Función para actualizar la contraseña del usuario

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['formType']) && $_POST['formType'] === 'updatePassword') {
    include("conexion.php");

    // Variables de error y mensaje
    $new_password_err = $confirm_password_err = "";
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $usuario = $_POST['usuario'];

    // Validación de la nueva contraseña
    if (empty($new_password)) {
        $new_password_err = "Por favor, ingrese la nueva contraseña.";
    } elseif (strlen($new_password) < 6) {
        $new_password_err = "La contraseña debe tener al menos 6 caracteres.";
    }

    // Validación de la confirmación de la contraseña
    if (empty($confirm_password)) {
        $confirm_password_err = "Por favor, confirme la contraseña.";
    } elseif ($new_password != $confirm_password) {
        $confirm_password_err = "Las contraseñas no coinciden.";
    }

    // Si no hay errores, proceder con la actualización de la contraseña
    if (empty($new_password_err) && empty($confirm_password_err)) {
        $resultado = actualizarPassword($conn, $usuario, password_hash($new_password, PASSWORD_DEFAULT));

        if ($resultado['success']) {
            $_SESSION['mensaje_exito'] = $resultado['message'];
        } else {
            $_SESSION['mensaje_error'] = $resultado['message'];
        }

        // Redireccionar para evitar que el formulario se vuelva a enviar al recargar la página
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    } else {
        // Si hay errores, asignar a sesión para mostrar en los campos del formulario
        $_SESSION['new_password_err'] = $new_password_err;
        $_SESSION['confirm_password_err'] = $confirm_password_err;
    }
}

// Configuración para los toasts
$mensaje = '';
$tipo_mensaje = 'success'; // Valor predeterminado

// Mensaje de éxito o error en el toast
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $mensaje = $_SESSION['mensaje_error'];
    $tipo_mensaje = 'error'; // Cambiar a error si hay mensaje de error
    unset($_SESSION['mensaje_error']);
}

// Mostrar errores de validación en los campos del formulario
$new_password_err = isset($_SESSION['new_password_err']) ? $_SESSION['new_password_err'] : "";
$confirm_password_err = isset($_SESSION['confirm_password_err']) ? $_SESSION['confirm_password_err'] : "";

// Limpiar errores de la sesión después de mostrarlos
unset($_SESSION['new_password_err'], $_SESSION['confirm_password_err']);


// Función para actualizar la contraseña en la base de datos
function actualizarPassword($conn, $usuario, $new_password_hash)
{
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $new_password_hash, $usuario);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Contraseña actualizada exitosamente.'];
    } else {
        return ['success' => false, 'message' => 'Error al actualizar la contraseña.'];
    }
}
//ESTA FUNCION MANEJA VER LOS DETALLES DE LAS TABLAS DINAMICAS

function obtenerRegistroPorId($tabla, $id)
{
    global $conn;

    $sql = "SELECT *
            FROM proprieter
            INNER JOIN municipios ON proprieter.Municipio = municipios.id_municipio
            INNER JOIN departamentos ON municipios.departamento_id = departamentos.id_departamento
            WHERE proprieter.codigo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}


//ESTA FUNCION ACTUALIZA LOS REGISTROS POR EL ID
function actualizarRegistro($conn, $datos)
{
    $codigo = $datos['codigo'];
    $tabla = $datos['tabla'];

    $setClauses = [];
    $types = '';
    $bindParams = [];

    foreach ($datos as $key => $value) {
        if ($key != 'codigo' && $key != 'tabla' && $key != 'action') {
            $setClauses[] = "$key = ?";
            $types .= 's';
            $bindParams[] = $value;
        }
    }
    $setString = implode(", ", $setClauses);
    $sql = "UPDATE `$tabla` SET $setString WHERE codigo = ?";

    $bindParams[] = $codigo;
    $types .= 's';

  

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        return ['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error];
    }

    $stmt->bind_param($types, ...$bindParams);

    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Error en la ejecución de la consulta: ' . $stmt->error];
    }
}
?>