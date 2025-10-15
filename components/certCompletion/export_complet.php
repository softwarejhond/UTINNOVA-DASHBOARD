<?php
session_start();
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/Exception.php';

// Cambiar las importaciones de DOMPDF por mPDF
use Mpdf\Mpdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Habilitar reporte de errores detallado ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// IMPORTANTE: Establecer el Content-Type para JSON desde el inicio
header('Content-Type: application/json');

// Función para generar un código de serie único para constancias
function generarSerieUnicaConstancia($conn)
{
    do {
        $serie = strtoupper(uniqid('CONST-') . '-' . bin2hex(random_bytes(3)));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM constancias_emitidas WHERE serie_constancia = ?");
        $stmt->bind_param("s", $serie);
        $stmt->execute();
        $count = 0;
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0);
    return $serie;
}

// Función para convertir número de mes a nombre
function nombreMes($numero_mes)
{
    $meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
    return $meses[$numero_mes] ?? 'Desconocido';
}

// Función para convertir imagen a base64
function imgToBase64($path)
{
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

// Función para enviar respuesta JSON y terminar
function sendJsonResponse($success, $message, $phpmailer_error = null)
{
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($phpmailer_error) {
        $response['phpmailer_error'] = $phpmailer_error;
    }

    if (ob_get_length()) ob_clean();
    echo json_encode($response);
    exit;
}

// Función mejorada para limpiar el nombre del bootcamp
function limpiarNombreBootcamp($nombreBootcamp) {
    // Eliminar espacios y saltos de línea al inicio y final
    $nombreBootcamp = trim($nombreBootcamp);
    // Reemplazar múltiples espacios, tabs y saltos de línea por un solo espacio
    $nombreBootcamp = preg_replace('/\s+/', ' ', $nombreBootcamp);

    // Si el nombre está vacío o es N/A, devolverlo tal cual
    if (empty($nombreBootcamp) || $nombreBootcamp === 'N/A') {
        return $nombreBootcamp;
    }

    // Eliminar cualquier código numérico al inicio (ej: "254 - ")
    $nombreLimpio = preg_replace('/^\d+\s*-\s*/', '', $nombreBootcamp);

    // Eliminar cualquier código alfanumérico al final (ej: " - ABC123XYZ")
    $nombreLimpio = preg_replace('/\s*-\s*[A-Z0-9]+$/i', '', $nombreLimpio);

    // Eliminar todo lo que esté después de "Nivel Explorador", "Nivel Integrador" o "Nivel Innovador" (incluyendo el espacio)
    $nombreLimpio = preg_replace('/(Explorador|Integrador|Innovador)\b.*/i', '$1', $nombreLimpio);

    // Eliminar espacios extra finales
    $nombreLimpio = trim($nombreLimpio);

    return $nombreLimpio;
}

// Recibir datos del formulario
$nombre_estudiante = $_POST['nombre_estudiante'] ?? 'N/A';
$cedula = $_POST['cedula'] ?? 'N/A';
$nombre_bootcamp_original = $_POST['nombre_bootcamp'] ?? 'N/A';
$nombre_bootcamp = limpiarNombreBootcamp($nombre_bootcamp_original); // Aplicar limpieza

// Obtener modalidad real desde la base de datos (tabla groups)
$modalidad_asistencia = 'N/A';
$stmt = $conn->prepare("SELECT mode FROM groups WHERE number_id = ? LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($mode_db);
if ($stmt->fetch()) {
    $modalidad_asistencia = strtolower($mode_db); // Convertir a minúsculas
}
$stmt->close();

$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$email = $_POST['email'] ?? '';

// Validar email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(false, 'El email proporcionado no es válido: ' . $email);
}

// Detectar entorno (producción vs desarrollo)
$isProduction = strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
if ($isProduction) {
    $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/';
    $certFolder = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/constancias/';
} else {
    $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/';
    $certFolder = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/constancias/';
}

// --- 1. Verificar si ya existe constancia ---
$stmt = $conn->prepare("SELECT serie_constancia FROM constancias_emitidas WHERE number_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($serie_existente);
$constancia_existente = false;
$filePath = '';

if ($stmt->fetch() && file_exists($certFolder . $serie_existente . '.pdf')) {
    $constancia_existente = true;
    $filePath = $certFolder . $serie_existente . '.pdf';
}
$stmt->close();

// Si existe constancia, enviarla por correo (no descargarla)
if ($constancia_existente) {
    // Obtener configuración SMTP
    $query = "SELECT * FROM smtpConfig WHERE id=3";
    $querySMTPResult = mysqli_query($conn, $query);
    $smtpConfig = mysqli_fetch_array($querySMTPResult);

    $host = $smtpConfig['host'];
    $emailSmtp = $smtpConfig['email'];
    $passwordSmtp = $smtpConfig['password'];
    $port = $smtpConfig['port'];

    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0; // Desactivar debug en producción
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $emailSmtp;
        $mail->Password = $passwordSmtp;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        // Desactivar verificación de certificado
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom('noreply@utinnova.co', 'Talento Tech');
        $mail->CharSet = 'UTF-8';
        $mail->addAddress($email, $nombre_estudiante);
        $mail->isHTML(true);
        $mail->Subject = 'Constancia de Participación y Finalización Satisfactoria - Bootcamp Talento Tech';

        $mail->Body = "
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
                    overflow: hidden;
                }
                .header-image {
                    width: 100%;
                    height: auto;
                    display: block;
                    margin: 0;
                    padding: 0;
                }
                .content {
                    padding: 20px;
                    line-height: 1.6;
                    color: #000000;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    color: #777;
                    font-size: 12px;
                    padding: 0 20px 20px 20px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <img src='https://dashboard.utinnova.co/dashboard/img/header_correo_const.png' alt='Header Constancia' class='header-image'>
                <div class='content'>
                    <p>Hola <b>" . htmlspecialchars($nombre_estudiante) . "</b>,</p>
                    <p>Adjuntamos tu constancia de participación al bootcamp <b>" . htmlspecialchars($nombre_bootcamp) . "</b> del programa Talento Tech.</p>
                    <p><strong>Nota:</strong> Tu constancia está protegida con contraseña. Usa tu número de documento para abrirla.</p>
                    <p>¡Felicitaciones por tu participación!</p>
                    <p>Si tienes dudas, contáctanos a <b>servicioalcliente.ut2@cendi.edu.co</b></p>
                </div>
                <div class='footer'>
                    <p>Equipo Talento Tech - Unión Temporal Innova Digital</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->addAttachment($filePath, 'constancia_' . $cedula . '.pdf');

        if (!$mail->send()) {
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }

        sendJsonResponse(true, 'Constancia existente enviada por correo a ' . $email);
    } catch (Exception $e) {
        error_log("PHPMailer Exception (constancia existente): " . $e->getMessage());
        sendJsonResponse(false, 'Error al enviar la constancia existente: ' . $e->getMessage(), $mail->ErrorInfo ?? 'Error desconocido');
    }
}

// --- 2. Generar nueva constancia ---

// Formatear fechas
$dia_actual = date("d");
$mes_actual = nombreMes(date("m"));
$anio_actual = date("Y");
$modalidad_asistencia = $_POST['modalidad'] ?? 'N/A';

// Procesar fecha de inicio y fin
$dia_inicio = date('d', strtotime($fecha_inicio));
$mes_inicio = nombreMes(date('m', strtotime($fecha_inicio)));
$anio_inicio = date('Y', strtotime($fecha_inicio));
$dia_fin = date('d', strtotime($fecha_fin));
$mes_fin = nombreMes(date('m', strtotime($fecha_fin)));
$anio_fin = date('Y', strtotime($fecha_fin));

// Obtener username de la sesión
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'sistema';

// Generar código de serie para constancia
$codigo_serie = generarSerieUnicaConstancia($conn);

// Registrar en la base de datos de constancias
$stmt = $conn->prepare("INSERT INTO constancias_emitidas (number_id, serie_constancia, emitido_por) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $cedula, $codigo_serie, $username);
if (!$stmt->execute()) {
    $stmt->close();
    sendJsonResponse(false, 'Error al registrar la constancia en la base de datos: ' . $conn->error);
}
$stmt->close();

// Cargar imágenes y convertirlas a base64
$headerImg = imgToBase64($rootPath . 'img/header_constancia.png');
$footerImg1 = imgToBase64($rootPath . 'img/footer_certificado.png');
$firma = imgToBase64($rootPath . 'img/firma_certificado.jpg');
$marcaAgua = imgToBase64($rootPath . 'img/innova_opaco.png');
$headerCorreo = imgToBase64($rootPath . 'img/header_correo_const.png'); // Agregar esta línea

// Verificar que las imágenes se cargaron correctamente
if (empty($headerImg) || empty($footerImg1) || empty($firma) || empty($headerCorreo)) {
    sendJsonResponse(false, 'Error al cargar las imágenes de la constancia. Verifica las rutas de las imágenes.');
}

$html = '<html>
<head>
    <style>
        @page { 
            margin-left: 2.5cm; 
            margin-right: 2.5cm; 
            margin-top: 2.5cm; 
            margin-bottom: 2.5cm; 
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 15px; 
            color: #000; 
            margin: 0; 
            padding: 0; 
            line-height: 1.4; 
            /* Marca de agua */
            background-image: url(' . $marcaAgua . ');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .justificado { text-align: justify; }

        .header-img {
            display: block;
            margin: 0 auto; /* Centrar horizontalmente */
            width: 95%; /* Reducir el ancho para que no se corte */
            max-width: 600px; /* Limitar el ancho máximo */
            height: auto;
            margin-top: 20px; /* Separar del borde superior */
            margin-bottom: 5px;
        }
        .footer-img {
            display: block;
            margin: 0 auto; /* Centrar horizontalmente */
            width: 95%; /* Reducir el ancho para que no se corte */
            max-width: 600px; /* Limitar el ancho máximo */
            height: auto;
            margin-top: 30px; /* Separar del contenido anterior */
            margin-bottom: 20px; /* Separar del borde inferior */
        }

        .fecha { margin-top: 20px; margin-bottom: 30px; }
        .asunto { margin-bottom: 20px; text-align: center; }
        .firma { margin-top: 120px; margin-bottom: 10px; text-align: left; }
        .linea { border-top: 1px solid #222; width: 250px; margin: 10px 0 0 0; }
        .nombre { font-weight: bold; margin-top: 10px; }
        .cargo { margin-bottom: 0; }
    </style>
</head>
<body>
    <img src="' . $headerImg . '" alt="Header" class="header-img">
    
    <div class="asunto" style="margin-top:10px; margin-bottom:30px;">
        <b>CONSTANCIA DE PARTICIPACIÓN Y FINALIZACIÓN SATISFACTORIA</b>
    </div>
    
    <div class="justificado" style="margin-bottom:20px;">
        La <b>Unión Temporal Innova Digital</b>, en el marco del programa <b>Talento Tech - Entrenamiento Intensivo en Innovación y Tecnología</b>, liderado por el <b>Ministerio de Tecnologías de la Información y las Comunicaciones - MinTIC</b>, se permite hacer constar que:
    </div>
    
    <div class="justificado" style="margin-bottom:25px;">
        <b>' . strtoupper($nombre_estudiante) . '</b>, identificado(a) con cédula de ciudadanía No. <b>' . $cedula . '</b>, ha atendido y cumplido con la intensidad horaria establecida para el BootCamp <b>' . $nombre_bootcamp . '</b> en modalidad <b>' . $modalidad_asistencia . '</b>, en concordancia con lo dispuesto en el programa Talento Tech, dentro del <b>Contrato de Prestación de Servicios No. 1107-2025</b>, suscrito entre el MinTIC y la Unión Temporal Innova Digital.
    </div>
    
    <div class="justificado" style="margin-bottom:30px;">
        En este sentido PRUEBA PRTUEBA PRUEBA PRUEBA, se expide la presente constancia como documento válido que da cuenta de la participación y cumplimiento del beneficiario en el proceso formativo, la cual tendrá validez hasta la expedición del certificado oficial por parte del <b>Ministerio de Tecnologías de la Información y las Comunicaciones - MinTIC</b>.
    </div>
    
    <div style="margin-bottom:40px;">
        Se firma en la ciudad de <b>Bogotá</b>, a los <b>' . $dia_actual . '</b> del mes de <b>' . $mes_actual . '</b> de <b>' . $anio_actual . '</b>.
    </div>
    <br><br>
    <div class="firma">
        <img src="' . $firma . '" alt="Firma" style="max-width:200px;">
        <div class="linea"></div>
    </div>
    <div class="nombre">
        Giovanni Andrés Caicedo Castro 
    </div>
    <div class="cargo">
        Director del proyecto<br>
        <b>UT INNOVA DIGITAL - Bogotá</b>
    </div>
    <div style="margin-top:25px; font-size:13px;">
        <b>Contacto:</b> servicioalcliente.ut2@cendi.edu.co<br>
        <b>Telefono:</b> 3125410929
    </div>
    <div style="margin-top:300px; margin-bottom:30px; font-size:12px; color:#333; text-align:center;">
         ' . $codigo_serie . '
    </div>
    
    <img src="' . $footerImg1 . '" alt="Footer" class="footer-img">
</body>
</html>';

// Reemplazar la configuración de mPDF y los headers/footers:
try {
    // Crear y configurar mPDF con márgenes ajustados a las imágenes reales
    $mpdf = new Mpdf([
        'format' => 'Letter',
        'orientation' => 'P',
        'margin_left' => 25,
        'margin_right' => 25,
        'margin_top' => 70,    // Más espacio para el header de 584px de altura
        'margin_bottom' => 50,   // Espacio adecuado para el footer
        'margin_header' => 10,
        'margin_footer' => 10,
        'default_font' => 'Arial'
    ]);

    // Configurar protección con contraseña
    $mpdf->SetProtection(array(), $cedula, $cedula, 128);

    // --- Header con altura proporcional a 584px ---
    $mpdf->SetHTMLHeader('
        <div style="width: 100%; margin-top: 5px; margin-bottom: 5px;">
            <img src="' . $headerImg . '" style="
                width: 100%; 
                height: auto;
                display: block;
                margin: 0 auto;
            ">
        </div>
    ');

    // --- Footer con altura proporcional ---
    $mpdf->SetHTMLFooter('
        <div style="width: 100%; margin-top: 5px; margin-bottom: 5px;">
            <img src="' . $footerImg1 . '" style="
                width: 100%; 
                height: auto;
                display: block;
                margin: 0 auto;
            ">
        </div>
    ');

    // --- Contenido Principal compactado para aprovechar mejor el espacio ---
    $htmlContent = '
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                font-size: 16px; /* Reducido de 14px a 13px */
                color: #000; 
                line-height: 1.1; /* Más compactado */
                text-align: justify;
                margin: 0;
                padding: 0;
                background-image: url(' . $marcaAgua . ');
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
            }
            .asunto { 
                text-align: center; 
                margin-bottom: 15px; 
                font-size: 16px;
                font-weight: bold;
            }
            .parrafo { 
                margin-bottom: 12px; 
                text-align: justify;
            }
            .firma-bloque { 
                margin-top: 20px; /* Reducido de 25px */
            }
            .linea { 
                border-top: 1px solid #222; 
                width: 220px; 
                margin-top: 5px; 
            }
            .nombre { 
                font-weight: bold; 
                margin-top: 8px;
            }
            .cargo { 
                font-size: 12px; 
                margin-top: 5px;
            }
            .info-final { 
                font-size: 10px; 
                line-height: 1.3; 
                margin-top: 15px; /* Reducido de 20px */
            }
        </style>
    </head>
    <body>
        <div class="asunto">
            CONSTANCIA DE PARTICIPACIÓN Y FINALIZACIÓN SATISFACTORIA
        </div>
        
        <div class="parrafo">
            La <b>Unión Temporal Innova Digital</b>, en el marco del programa <b>Talento Tech - Entrenamiento Intensivo en Innovación y Tecnología</b>, liderado por el <b>Ministerio de Tecnologías de la Información y las Comunicaciones - MinTIC</b>, se permite hacer constar que:
        </div>
        
        <div class="parrafo">
            <b>' . strtoupper($nombre_estudiante) . '</b>, identificado(a) con cédula de ciudadanía No. <b>' . $cedula . '</b>, ha atendido y cumplido con la intensidad horaria establecida para el BootCamp <b>' . $nombre_bootcamp . '</b> en modalidad <b>' . $modalidad_asistencia . '</b>, en concordancia con lo dispuesto en el programa Talento Tech, dentro del <b>Contrato de Prestación de Servicios No. 1107-2025</b>, suscrito entre el MinTIC y la Unión Temporal Innova Digital.
        </div>
        
        <div class="parrafo">
            En este sentido, se expide la presente constancia como documento válido que da cuenta de la participación y cumplimiento del beneficiario en el proceso formativo, la cual tendrá validez hasta la expedición del certificado oficial por parte del <b>Ministerio de Tecnologías de la Información y las Comunicaciones - MinTIC</b>.
        </div>
        
        <div class="parrafo">
            Se firma en la ciudad de <b>Bogotá</b>, a los <b>' . $dia_actual . '</b> del mes de <b>' . $mes_actual . '</b> de <b>' . $anio_actual . '</b>.
        </div>
        
        <div class="firma-bloque" style="margin-top: 40px;">
            <img src="' . $firma . '" alt="Firma" style="max-width:200px; height: auto;">
            <div class="linea"></div>
            <div class="nombre">Giovanni Andrés Caicedo Castro</div>
            <div class="cargo">
            Director del proyecto<br>
            <b>UT INNOVA DIGITAL - Bogotá</b>
            </div>
        </div>

        <div class="info-final">
            <b>Contacto:</b> servicioalcliente.ut2@cendi.edu.co<br>
            <b>Telefono:</b> 3125410929<br>
            
            
            <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
                ' . $codigo_serie . '
            </div>
        </div>
    </body>
    </html>';

    // Escribir HTML
    $mpdf->WriteHTML($htmlContent);

    // Crear directorio si no existe
    if (!is_dir($certFolder)) {
        if (!mkdir($certFolder, 0777, true)) {
            throw new Exception("No se pudo crear la carpeta de constancias: $certFolder");
        }
    }

    // Guardar PDF con protección
    $pdfPath = $certFolder . $codigo_serie . '.pdf';
    $mpdf->Output($pdfPath, 'F');

} catch (Exception $e) {
    error_log("Error al generar PDF protegido: " . $e->getMessage());
    sendJsonResponse(false, 'Error al generar el PDF protegido: ' . $e->getMessage());
}

// --- 3. Enviar la constancia por correo electrónico ---

// Obtener configuración SMTP
$query = "SELECT * FROM smtpConfig WHERE id=3";
$querySMTPResult = mysqli_query($conn, $query);
$smtpConfig = mysqli_fetch_array($querySMTPResult);

if (!$smtpConfig) {
    sendJsonResponse(false, 'No se pudo obtener la configuración SMTP');
}

$host = $smtpConfig['host'];
$emailSmtp = $smtpConfig['email'];
$passwordSmtp = $smtpConfig['password'];
$port = $smtpConfig['port'];

try {
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0; // Desactivar debug en producción
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $emailSmtp;
    $mail->Password = $passwordSmtp;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $port;
    // Desactivar verificación de certificado
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->setFrom('noreply@utinnova.co', 'Talento Tech');
    $mail->CharSet = 'UTF-8';
    $mail->addAddress($email, $nombre_estudiante);
    $mail->isHTML(true);
    $mail->Subject = 'Constancia de Participación y Finalización Satisfactoria - Bootcamp Talento Tech';

    $mail->Body = "
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
                overflow: hidden; /* Para que la imagen del header se vea bien */
            }
            .header-image {
                width: 100%;
                height: auto;
                display: block;
                margin: 0;
                padding: 0;
            }
            .header {
                text-align: center;
                background: #066aab;
                color: #fff;
                padding: 15px 20px;
                margin: 0;
            }
            .header h1 {
                margin: 0;
                font-size: 22px;
                font-weight: bold;
            }
            .content {
                line-height: 1.6;
                color: #000000;
                padding: 20px;
            }
            .content p {
                margin: 10px 0;
                color: #000000;
            }
            .highlight-box {
                background: #f0f0f0;
                color: #333;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
                border-left: 4px solid #066aab;
            }
            .highlight-box h4 {
                margin: 0 0 10px 0;
                color: #066aab;
            }
            .achievement-badge {
                background: linear-gradient(135deg, #ffd700, #ffed4e);
                color: #333;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: center;
                border: 2px solid #ffc107;
            }
            .password-alert {
                background: #e74c3c;
                color: #fff;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: center;
                border: 2px solid #c0392b;
            }
            .password-alert h4 {
                margin: 0 0 10px 0;
                font-size: 16px;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                color: #777;
                font-size: 12px;
                padding: 0 20px 20px 20px;
            }
            ul {
                color: #000000;
                line-height: 1.6;
                padding-left: 20px;
            }
            ul li {
                margin-bottom: 5px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <img src='https://dashboard.utinnova.co/dashboard/img/header_correo_const.png' alt='Header Constancia' class='header-image'>
            <div class='header'>
                <h1>¡Felicitaciones por Finalizar tu Formación!</h1>
            </div>
            
            <div class='content'>
                <p style='text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0;'>Estimado(a) <br> <b>" . htmlspecialchars($nombre_estudiante) . "</b>,</p>
                <br>
                <div class='achievement-badge'>
                    <h3 style='margin: 0; font-size: 18px;'>🎓 ¡Has completado exitosamente tu proceso formativo! 🎓</h3>
                </div>
                
                <p>Es un honor para la <b>Unión Temporal Innova Digital</b> informarte que has culminado satisfactoriamente tu participación en el bootcamp <b>" . htmlspecialchars($nombre_bootcamp) . "</b> del programa <b>Talento Tech - Entrenamiento Intensivo en Innovación y Tecnología</b>, liderado por el <b>Ministerio de Tecnologías de la Información y las Comunicaciones - MinTIC</b>.</p>
                
                <div class='highlight-box'>
                    <h4>📋 Tu Constancia de Participación y Finalización Satisfactoria</h4>
                    <p style='margin: 0; line-height: 1.4;'>
                        Adjunto a este correo encontrarás tu <b>Constancia de Participación y Finalización Satisfactoria</b>, la cual documenta oficialmente que has atendido y cumplido con la intensidad horaria establecida para el programa en modalidad <b>" . htmlspecialchars($modalidad_asistencia) . "</b>.
                    </p>
                </div>
                
                <div class='password-alert'>
                    <h4>🔒 INFORMACIÓN IMPORTANTE SOBRE SEGURIDAD</h4>
                    <p style='margin: 0; font-size: 14px; line-height: 1.4; color: #ffffff;'>
                        <span style='color: #ffffff;'>Tu constancia está protegida con contraseña por motivos de seguridad.</span><br>
                        <strong>La contraseña para abrir el archivo PDF es tu número de documento de identidad</strong><br>
                    </p>
                </div>
                
                <p><b>Información importante sobre tu constancia:</b></p>
                <ul>
                    <li>Este documento valida tu participación completa y satisfactoria en el proceso formativo</li>
                    <li>Confirma que has cumplido con todos los requisitos de asistencia e intensidad horaria</li>
                    <li>Ha sido emitida en concordancia con el <b>Contrato de Prestación de Servicios No. 1107-2025</b></li>
                    <li>Está protegida con tu número de documento para garantizar su autenticidad</li>
                    <li>Representa tu compromiso y dedicación durante toda la formación</li>
                </ul>
                
                <div class='highlight-box'>
                    <h4>🚀 ¡Tu crecimiento profesional continúa!</h4>
                    <p style='margin: 0; line-height: 1.4;'>
                        Has demostrado perseverancia y compromiso durante tu proceso formativo. Las competencias y conocimientos adquiridos te posicionan para nuevas oportunidades en el sector tecnológico.
                    </p>

                    <p>Si tienes dudas o necesitas información adicional sobre tu constancia, no dudes en contactarnos:</p>
                    <p>📞 <b>3125410929</b></p>
                    <p>📧 <b><a href='mailto:servicioalcliente.ut2@cendi.edu.co' style='color: #066aab; text-decoration: none;'>servicioalcliente.ut2@cendi.edu.co</a></b></p>
                </div>
                
                <p>Esta constancia es el reconocimiento a tu esfuerzo y dedicación. Esperamos que esta experiencia haya sido enriquecedora y que puedas aplicar todo lo aprendido en tu desarrollo profesional. ¡Estamos orgullosos de haber acompañado tu proceso de crecimiento! 🌟</p>
                
                <p style='margin-top: 25px;'><b>¡Felicitaciones nuevamente por este importante logro!</b></p>
            </div>
            
            <div class='footer'>
                <p>Equipo Talento Tech - Unión Temporal Innova Digital</p>
                <p>En el marco del programa Talento Tech del MINTIC</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->addAttachment($pdfPath, 'constancia_' . $cedula . '.pdf');

    if (!$mail->send()) {
        throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
    }

    sendJsonResponse(true, 'Constancia generada y enviada por correo a ' . $email);
} catch (Exception $e) {
    error_log("PHPMailer Exception: " . $e->getMessage());
    sendJsonResponse(false, 'Error al enviar el correo: ' . $e->getMessage(), $mail->ErrorInfo ?? 'Error desconocido');
}


