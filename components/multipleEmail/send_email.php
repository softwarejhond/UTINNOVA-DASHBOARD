<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
// Asegurarse de que no haya salida antes
ob_start();

require __DIR__ . '/../../conexion.php';
require __DIR__ . '../../../vendor/autoload.php';

require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Establecer headers antes de cualquier salida
header('Content-Type: application/json');

// Log para depuración en producción
function logError($message)
{
    $logFile = __DIR__ . '/email_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Capturar errores PHP
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    logError("PHP Error: $errstr in $errfile:$errline");
    throw new Exception($errstr);
});

try {
    // Validar entrada
    if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Correo electrónico no válido");
    }

    if (!isset($_POST['subject']) || empty($_POST['subject'])) {
        throw new Exception("El asunto es obligatorio");
    }

    if (!isset($_POST['content']) || empty($_POST['content'])) {
        throw new Exception("El contenido es obligatorio");
    }

    // Obtener la configuración SMTP
    $query = "SELECT * FROM smtpConfig WHERE id=1";
    $querySMTP = mysqli_query($conn, $query);

    if ($querySMTP && mysqli_num_rows($querySMTP) > 0) {
        $configSMTP = mysqli_fetch_assoc($querySMTP);
        $host = $configSMTP['host'];
        $port = $configSMTP['port'];
        $emailSmtp = $configSMTP['email'];
        $password = $configSMTP['password'];

        logError("Intentando conectar a: $host:$port con cuenta: $emailSmtp");

        // Creamos una instancia de PHPMailer
        $mail = new PHPMailer(true);

        // Habilitar modo debug para obtener más información del error
        $mail->SMTPDebug = 0; // 0 = sin debug, 1 = mensajes cliente, 2 = cliente y servidor

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $emailSmtp;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $port;

        // Aumentar tiempos de espera
        $mail->Timeout = 60; // Tiempo de espera de conexión en segundos
        $mail->SMTPKeepAlive = true; // Mantener la conexión abierta

        // Ignorar errores de certificado (útil para pruebas, pero considerar remover en producción final)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom($emailSmtp, 'Servicio al cliente');
        $mail->CharSet = 'UTF-8';

        // Configurar destinatario
        $mail->addAddress($_POST['email']);

        // Procesar el contenido HTML para las imágenes
        $content = $_POST['content'];

        // Buscar imágenes base64 incrustadas en el contenido
        $pattern = '/<img[^>]*src=[\'"](data:image\/[^;]+;base64,[^\'\"]+)[\'"][^>]*>/i';
        preg_match_all($pattern, $content, $matches);

        // Limitar el tamaño de las imágenes para evitar problemas
        $maxImageSize = 500 * 1024; // 500KB

        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $base64Img) {
                // Extraer tipo de imagen y datos
                list($type, $data) = explode(';', $base64Img);
                list(, $type) = explode(':', $type);
                list(, $data) = explode(',', $data);

                // Decodificar datos base64
                $imgData = base64_decode($data);

                // Verificar tamaño de imagen
                if (strlen($imgData) > $maxImageSize) {
                    logError("Imagen demasiado grande: " . strlen($imgData) . " bytes");
                    // Simplemente eliminar la imagen si es demasiado grande
                    $content = str_replace($base64Img, '', $content);
                    continue;
                }

                // Generar nombre único para la imagen
                $imgName = 'img_' . md5(uniqid() . $index) . '.' . str_replace('image/', '', $type);

                // Crear un Content ID único para la imagen
                $cid = md5($imgName) . '@phpmailer';

                try {
                    // Adjuntar la imagen al correo
                    $mail->addStringEmbeddedImage($imgData, $cid, $imgName, 'base64', $type);

                    // Reemplazar la imagen base64 en el HTML por referencia CID
                    $content = str_replace($base64Img, 'cid:' . $cid, $content);
                } catch (Exception $e) {
                    logError("Error adjuntando imagen: " . $e->getMessage());
                    // Si hay error con la imagen, simplemente la eliminamos del contenido
                    $content = str_replace($base64Img, '', $content);
                }
            }
        }

        // Contenido del correo con imágenes procesadas
        $mail->isHTML(true);
        $mail->Subject = $_POST['subject'];
        $mail->Body = $content;
        $mail->AltBody = strip_tags($_POST['content']);

        try {
            // Enviar el correo
            if (!$mail->send()) {
                throw new Exception('Error al enviar correo: ' . $mail->ErrorInfo);
            }

            // Limpiar cualquier salida anterior
            ob_clean();

            echo json_encode([
                'success' => true,
                'message' => 'Correo enviado exitosamente'
            ]);
        } catch (Exception $e) {
            logError("Error en envío: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    } else {
        logError("Error de consulta SMTP: " . mysqli_error($conn));
        throw new Exception('Error en la consulta de configuración SMTP: ' . mysqli_error($conn));
    }
} catch (Exception $e) {
    logError("Excepción capturada: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el correo: ' . $e->getMessage()
    ]);
}

// Asegurarse de que no haya más salida
exit();
