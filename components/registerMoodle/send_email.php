<?php
// Asegurarse de que no haya salida antes
ob_start();
session_start(); // Asegúrate de que la sesión esté iniciada si generate_carnet.php la necesita indirectamente o para consistencia.

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
    // No lanzar excepción para notices o warnings que no sean críticos para el JSON
    if (!(error_reporting() & $errno)) {
        return false;
    }
    // Para otros errores, sí lanzar excepción
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    $query = "SELECT * FROM smtpConfig WHERE id=3"; 

    $querySMTPResult = mysqli_query($conn, $query);
    if (!$querySMTPResult) {
        throw new Exception('Error en la consulta de configuración SMTP: ' . mysqli_error($conn));
    }
    
    $smtpConfig = mysqli_fetch_array($querySMTPResult);
    if (!$smtpConfig) {
        throw new Exception('No se encontró la configuración SMTP.');
    }

    $host = $smtpConfig['host'];
    $emailSmtp = $smtpConfig['email'];
    $passwordSmtp = $smtpConfig['password']; // Renombrado para evitar confusión con la contraseña del usuario
    $port = $smtpConfig['port'];

    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error decodificando JSON: ' . json_last_error_msg());
    }

    // Validar campos requeridos
    $required = ['email', 'program', 'first_name', 'usuario', 'password'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        throw new Exception('Campos faltantes: ' . implode(', ', $missing));
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $host; 
    $mail->SMTPAuth = true; 
    $mail->Username = $emailSmtp; 
    $mail->Password = $passwordSmtp; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port = $port; 

    // Desactivar verificación de certificado (igual que en multipleEmail)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('noreply@utinnova.co', 'Servicio al cliente'); 
    $mail->CharSet = 'UTF-8';  
    $mail->addAddress($data['email']); 
    $mail->isHTML(true);
    $mail->Subject = '¡Bienvenido al Bootcamp de ' . htmlspecialchars($data['program']) . ' de Talento Tech del MINTIC!';

    // Adjuntar carnet si se proporciona la ruta y el archivo existe
    $carnetAttached = false;
    if (isset($data['carnet_file_path']) && !empty($data['carnet_file_path'])) {
        $carnetPath = $data['carnet_file_path'];
        if (file_exists($carnetPath)) {
            $mail->addAttachment($carnetPath);
            $carnetAttached = true;
        } else {
            error_log("Advertencia: Archivo de carnet no encontrado para adjuntar: " . $carnetPath . " para usuario " . $data['email']);
        }
    }

    // Mensaje adicional sobre el carnet si está adjunto
    $carnetMessage = "";
    if ($carnetAttached) {
        $carnetMessage = "
                <div style='background: #f0f0f0; color: #333; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #066aab;'>
                    <h4 style='margin: 0 0 10px 0;'>Carnet digital adjunto</h4>
                    <p style='margin: 0; line-height: 1.4;'>
                        Descárgalo y guárdalo en tu dispositivo.
                    </p>
                    <p style='margin: 10px 0 0 0; font-size: 14px;'>
                        Si tu modalidad es presencial, debes presentar el carnet (impreso o digital) en cada ingreso.
                    </p>
                </div>";
    }

    // Mensaje HTML modificado para incluir el mensaje del carnet
    $mensaje = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f4f4f9;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                padding: 20px;
            }
            .header {
                text-align: center;
                background: #066aab;
                color: #fff;
                padding: 20px;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                line-height: 1.6;
                color: #000000;
            }
            .content p {
                margin: 10px 0;
                color: #000000;
            }
            a.button {
                display: inline-block;
                margin: 20px 0;
                padding: 10px 20px;
                background: #066aab;
                color: #fff;
                text-decoration: none;
                font-weight: bold;
                border-radius: 5px;
                text-align: center;
            }
            a.button:hover, 
            a.button:visited {
                color: #fff;
                text-decoration: none;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                color: #777;
                font-size: 12px;
            }
        </style>
    </head>

    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Bienvenido a Talento Tech del MINTIC!</h1>
            </div>
            <div class='content'>
                <p>Hola <b>" . htmlspecialchars($data['first_name']) . "</b>,</p>
                <p>¡Estás un paso más cerca de alcanzar tus metas! 🎉</p>
                <p>Queremos contarte que has sido admitido como beneficiario del programa <b>Talento Tech de MINTIC</b> como campista.</p>
                
                <h3>Acceso a la plataforma</h3>
                <p>A continuación, encontrarás tu usuario y contraseña para formalizar tu matrícula en el programa y acceder a nuestra plataforma de formación:</p>
                <p><b>Usuario:</b> Tu número de cédula (" . htmlspecialchars($data['usuario']) . ")</p>
                <p><b>Contraseña:</b> " . htmlspecialchars($data['password']) . "</p>
                <p>Puedes iniciar sesión y completar tu registro haciendo clic en el siguiente botón:</p>
                <a class='button' href='https://talento-tech.uttalento.co/login/index.php' target='_blank'>Acceder a la Plataforma</a>
                
                <p>O también puedes acceder manualmente copiando y pegando el siguiente enlace en tu navegador:</p>
                <p><b>🔗 <a href='https://talento-tech.uttalento.co/login/index.php' target='_blank'>https://talento-tech.uttalento.co/login/index.php</a></b></p>

                    " . $carnetMessage . "

                <p>Esperamos que este camino te acerque a tus objetivos y cuentes con nosotros hasta el final. Este es solo un paso más hacia la realización de tus sueños. 🚀</p>

                <p>Si tienes dudas o inquietudes, puedes comunicarte con nuestro equipo de soporte a través de:</p>
                <p>📞 <b>3125410929</b></p>
                <p>📧 <b><a href='mailto:servicioalcliente.ut2@cendi.edu.co'>servicioalcliente.ut2@cendi.edu.co</a></b></p>
            </div>

            <div class='footer'>
                <p>Equipo Talento Tech - MINTIC</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->Body = $mensaje;

    // Enviar el correo
    $mail->send();
    
    // Limpiar cualquier salida anterior
    ob_get_clean(); // Limpiar buffer antes de enviar JSON
    
    echo json_encode([
        'success' => true,
        'message' => 'Correo enviado exitosamente'
    ]);

} catch (Exception $e) {
    ob_get_clean(); // Limpiar buffer en caso de error general
    http_response_code(500); // Es buena práctica establecer un código de error HTTP
    echo json_encode([
        'success' => false,
        'message' => 'Error general en el proceso de envío de correo: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ' Archivo: ' . $e->getFile() . ')'
    ]);
}

// Asegurarse de que no haya más salida
exit();
