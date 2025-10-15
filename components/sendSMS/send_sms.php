<?php
session_start(); // Iniciar sesión para acceder al username
header('Content-Type: application/json');

// Incluir conexión a la BD
include_once '../../controller/conexion.php'; // Ajusta la ruta si es necesario

// Verificar si se recibió POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del POST
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Teléfono y mensaje son obligatorios']);
    exit;
}

// Agregar '57' al inicio del teléfono para la API
$sDestination = '57' . $phone;

// Función AltiriaSMS adaptada
function AltiriaSMS($sDestination, $sMessage, $sSenderId, $debug = false) {
    // URL base de los recursos REST
    $baseUrl = 'https://www.altiria.net:8443/apirest/ws';
     
    // Se inicia el objeto CUrl 
    $ch = curl_init($baseUrl.'/sendSms');

    // Credenciales
    $credentials = array(
        'apiKey'    => 'mz7Y5j47vK',
        'apiSecret' => 'a7fcgcxbme'
    );

    $destinations = array($sDestination); // Un solo destino

    $jsonMessage = array(
        'msg' => substr($sMessage, 0, 160),
        'senderId' => $sSenderId 
    );

    $jsonData = array(
        'credentials' => $credentials, 
        'destination' => $destinations,
        'message'     => $jsonMessage
    );
     
    // Se construye el mensaje JSON
    $jsonDataEncoded = json_encode($jsonData);
     
    // Indicamos que nuestra petición será Post
    curl_setopt($ch, CURLOPT_POST, 1);

    // Se fija el tiempo máximo de espera para conectar con el servidor (5 segundos)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
     
    // Se fija el tiempo máximo de espera de la respuesta del servidor (60 segundos)
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
     
    // Para que la petición no imprima el resultado como un 'echo' común
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     
    // Se añade el JSON al cuerpo de la petición codificado en UTF-8
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
     
    // Se fija el tipo de contenido de la petición POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8'));

    // Se envía la petición y se consigue la respuesta
    $response = curl_exec($ch);

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($debug) {   
        // Error en la respuesta del servidor      
        if ($statusCode != 200) { 
            echo 'ERROR GENERAL: ' . $statusCode;
            echo $response;
        } else {
            // Se procesa la respuesta capturada 
            echo 'Código de estado HTTP: ' . $statusCode . '<br/>';
            $json_parsed = json_decode($response);
            $status = $json_parsed->status;
            echo 'Código de estado Altiria: ' . $status . '<br/>';
            if ($status != '000')
                echo 'Error: ' . $response . '<br/>';
            else {
                echo 'Cuerpo de la respuesta: <br/>';
                foreach ($json_parsed->details as $i => $detail) {
                    echo "details[$i][destination]: " . $detail->destination . "<br/>";
                    echo "details[$i][status]: " . $detail->status . "<br/>";
                }
            }
        }
    }

    // Si ha ocurrido algún error se lanza una excepción
    if (curl_errno($ch))
        throw new Exception(curl_error($ch));

    return $response;
}

try {
    // Enviar SMS
    $response = AltiriaSMS($sDestination, $message, '', false); // Sin debug
    
    // Procesar respuesta
    $json_parsed = json_decode($response);
    if ($json_parsed && isset($json_parsed->status)) {
        $status = $json_parsed->status;
        if ($status == '000') {
            // --- Registro en la base de datos ---
            $sender = isset($_SESSION['username']) ? $_SESSION['username'] : 'Desconocido';
            $stmt = $conn->prepare("INSERT INTO sms_logs (phone, message, sender) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $phone, $message, $sender);
            $stmt->execute();
            $stmt->close();
            // --- Fin registro ---
            echo json_encode(['success' => true, 'message' => 'SMS enviado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en el envío: ' . $response]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Respuesta inválida de la API']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>