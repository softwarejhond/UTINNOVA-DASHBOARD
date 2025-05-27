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
    $query = "SELECT * FROM smtpConfig WHERE id=1"; // Asegúrate de que esta consulta tenga sentido en tu lógica

    if (mysqli_query($conn, $query)) {
        // Continúa con el envío de correo...
        $querySMTP = mysqli_query($conn, $query);
        $smtpConfig = mysqli_fetch_array($querySMTP);
        $host = $smtpConfig['host'];
        $emailSmtp = $smtpConfig['email'];
        $password = $smtpConfig['password'];
        $port = $smtpConfig['port'];
        $subject = $smtpConfig['Subject'];

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

        try {
            $mail->isSMTP();
            $mail->Host = $host; // Servidor SMTP
            $mail->SMTPAuth = true; // Habilita la autenticación SMTP
            $mail->Username = $emailSmtp; // Usuario SMTP
            $mail->Password = $password; // Contraseña SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Habilita SSL (seguridad)
            $mail->Port = $port; // Puerto SSL
            $mail->setFrom($emailSmtp, 'Servicio al cliente'); // Remitente del correo
            $mail->CharSet = 'UTF-8';  // Establece la codificación en UTF-8
            $mail->addAddress($data['email']); // Destinatario del correo
            $mail->isHTML(true);
            $mail->Subject = '¡Bienvenido al Bootcamp de ' . $data['program'] . ' de Talento Tech del MINTIC!';

            // Aquí va tu mensaje HTML
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
                    }
                    .content p {
                        margin: 10px 0;
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
                        <p>Hola <b>" . $data['first_name'] . "</b>,</p>
                        <p>¡Estás un paso más cerca de alcanzar tus metas! 🎉</p>
                        <p>Queremos contarte que has sido admitido como beneficiario del programa <b>Talento Tech de MINTIC</b> como campista.</p>
                        
                        <h3>Acceso a la plataforma</h3>
                        <p>A continuación, encontrarás tu usuario y contraseña para formalizar tu matrícula en el programa y acceder a nuestra plataforma de formación:</p>
                        <p><b>Usuario:</b> Tu número de cédula</p>
                        <p><b>Contraseña:</b> " . $data['password'] . "</p>
                        <p>Puedes iniciar sesión y completar tu registro haciendo clic en el siguiente botón:</p>
                        <a class='button' href='https://talento-tech.uttalento.co/login/index.php' target='_blank'>Acceder a la Plataforma</a>
                        
                        <p>O también puedes acceder manualmente copiando y pegando el siguiente enlace en tu navegador:</p>
                        <p><b>🔗 <a href='https://talento-tech.uttalento.co/login/index.php' target='_blank'>https://talento-tech.uttalento.co/login/index.php</a></b></p>

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
            ob_clean();
            
            echo json_encode([
                'success' => true,
                'message' => 'Correo enviado exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ]);
        }
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
