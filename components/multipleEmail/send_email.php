<?php
// Asegurarse de que no haya salida antes
ob_start();

require __DIR__ . '/../../conexion.php';
require __DIR__ . '../../../vendor/autoload.php';

require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Establecer headers antes de cualquier salida
header('Content-Type: application/json');

// Capturar errores PHP
set_error_handler(function($errno, $errstr, $errfile, $errline) {
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
    $query = "SELECT * FROM smtpconfig WHERE id=1";
    $querySMTP = mysqli_query($conn, $query);
    
    if ($querySMTP && mysqli_num_rows($querySMTP) > 0) {
        $configSMTP = mysqli_fetch_assoc($querySMTP);
        $host = $configSMTP['host'];
        $port = $configSMTP['port'];
        $emailSmtp = $configSMTP['email'];
        $password = $configSMTP['password'];
        
        // Creamos una instancia de PHPMailer
        $mail = new PHPMailer(true);
        
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $emailSmtp;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $port;
        $mail->setFrom($emailSmtp, 'Servicio al cliente');
        $mail->CharSet = 'UTF-8';
        
        // Configurar destinatario
        $mail->addAddress($_POST['email']);
        
        // Procesar el contenido HTML para las imágenes
        $content = $_POST['content'];
        
        // Buscar imágenes base64 incrustadas en el contenido
        $pattern = '/<img[^>]*src=[\'"](data:image\/[^;]+;base64,[^\'\"]+)[\'"][^>]*>/i';
        preg_match_all($pattern, $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $base64Img) {
                // Extraer tipo de imagen y datos
                list($type, $data) = explode(';', $base64Img);
                list(, $type) = explode(':', $type);
                list(, $data) = explode(',', $data);
                
                // Decodificar datos base64
                $imgData = base64_decode($data);
                
                // Generar nombre único para la imagen
                $imgName = 'img_' . md5(uniqid() . $index) . '.' . str_replace('image/', '', $type);
                
                // Crear un Content ID único para la imagen
                $cid = md5($imgName) . '@phpmailer';
                
                // Adjuntar la imagen al correo
                $mail->addStringEmbeddedImage($imgData, $cid, $imgName, 'base64', $type);
                
                // Reemplazar la imagen base64 en el HTML por referencia CID
                $content = str_replace($base64Img, 'cid:' . $cid, $content);
            }
        }
        
        // Contenido del correo con imágenes procesadas
        $mail->isHTML(true);
        $mail->Subject = $_POST['subject'];
        $mail->Body = $content;
        $mail->AltBody = strip_tags($_POST['content']);
        
        // Enviar el correo
        $mail->send();
        
        // Limpiar cualquier salida anterior
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Correo enviado exitosamente'
        ]);
    } else {
        throw new Exception('Error en la consulta de configuración SMTP: ' . mysqli_error($conn));
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el correo: ' . $e->getMessage()
    ]);
}

// Asegurarse de que no haya más salida
exit();
?>