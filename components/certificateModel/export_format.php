<?php
session_start();
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '../../../vendor/phpmailer/phpmailer/src/Exception.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Habilitar reporte de errores detallado ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// IMPORTANTE: Establecer el Content-Type para JSON desde el inicio
header('Content-Type: application/json');

// Función para generar un código de serie único
function generarSerieUnica($conn)
{
    do {
        $serie = strtoupper(uniqid('CERT-') . '-' . bin2hex(random_bytes(3)));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM certificados_emitidos WHERE serie_certificado = ?");
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

// Recibir datos del formulario
$nombre_estudiante = $_POST['nombre_estudiante'] ?? 'N/A';
$cedula = $_POST['cedula'] ?? 'N/A';
$nombre_bootcamp = $_POST['nombre_bootcamp'] ?? 'N/A';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$modalidad_asistencia = $_POST['modalidad'] ?? 'N/A';
$schedules = $_POST['schedules'] ?? 'N/A';
$email = $_POST['email'] ?? '';

// Validar email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(false, 'El email proporcionado no es válido: ' . $email);
}

// Detectar entorno (producción vs desarrollo)
$isProduction = strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
if ($isProduction) {
    $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/';
    $certFolder = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/certificados/';
} else {
    $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/';
    $certFolder = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/certificados/';
}

// --- 1. Verificar si ya existe certificado ---
$stmt = $conn->prepare("SELECT serie_certificado FROM certificados_emitidos WHERE number_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($serie_existente);
$certificado_existente = false;
$filePath = '';

if ($stmt->fetch() && file_exists($certFolder . $serie_existente . '.pdf')) {
    $certificado_existente = true;
    $filePath = $certFolder . $serie_existente . '.pdf';
}
$stmt->close();

// Si existe certificado, enviarlo por correo (no descargarlo)
if ($certificado_existente) {
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
        $mail->Subject = 'Certificado de asistencia Bootcamp Talento Tech';

        $mail->Body = "
            <p>Hola <b>" . htmlspecialchars($nombre_estudiante) . "</b>,</p>
            <p>Adjuntamos tu certificado de asistencia al bootcamp <b>" . htmlspecialchars($nombre_bootcamp) . "</b> del programa Talento Tech.</p>
            <p>¡Felicitaciones por tu participación!</p>
            <p>Si tienes dudas, contáctanos a <b>servicioalcliente.ut2@cendi.edu.co</b></p>
            <p>Equipo Talento Tech</p>
        ";

        $mail->addAttachment($filePath, 'certificacion_' . $cedula . '.pdf');

        if (!$mail->send()) {
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }

        sendJsonResponse(true, 'Certificado existente enviado por correo a ' . $email);
    } catch (Exception $e) {
        error_log("PHPMailer Exception (certificado existente): " . $e->getMessage());
        sendJsonResponse(false, 'Error al enviar el certificado existente: ' . $e->getMessage(), $mail->ErrorInfo ?? 'Error desconocido');
    }
}

// --- 2. Generar nuevo certificado ---

// Formatear fechas
$fecha_actual = date("d") . ' de ' . nombreMes(date("m")) . ' de ' . date("Y");
$modalidad_asistencia = 'presencial';

// Procesar fecha de inicio y fin
$dia_inicio = date('d', strtotime($fecha_inicio));
$mes_inicio = nombreMes(date('m', strtotime($fecha_inicio)));
$anio_inicio = date('Y', strtotime($fecha_inicio));
$dia_fin = date('d', strtotime($fecha_fin));
$mes_fin = nombreMes(date('m', strtotime($fecha_fin)));
$anio_fin = date('Y', strtotime($fecha_fin));

// Obtener username de la sesión
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'sistema';

// Generar código de serie
$codigo_serie = generarSerieUnica($conn);

// Registrar en la base de datos
$stmt = $conn->prepare("INSERT INTO certificados_emitidos (number_id, serie_certificado, emitido_por) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $cedula, $codigo_serie, $username);
if (!$stmt->execute()) {
    $stmt->close();
    sendJsonResponse(false, 'Error al registrar el certificado en la base de datos: ' . $conn->error);
}
$stmt->close();

// Cargar imágenes y convertirlas a base64
$headerImg = imgToBase64($rootPath . 'img/header_certificado.png');
$footerImg1 = imgToBase64($rootPath . 'img/footer_certificado.png');
$firma = imgToBase64($rootPath . 'img/firma_certificado.jpg');
$marcaAgua = imgToBase64($rootPath . 'img/innova_opaco.png');

// Verificar que las imágenes se cargaron correctamente
if (empty($headerImg) || empty($footerImg1) || empty($firma)) {
    sendJsonResponse(false, 'Error al cargar las imágenes del certificado. Verifica las rutas de las imágenes.');
}

// Opciones para mejorar el manejo de imágenes
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

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
            line-height: 1.0; 
            /* Marca de agua */
            background-image: url(' . $marcaAgua . ');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .justificado { text-align: justify; }

        .header-img {
            display: block;
            margin-left: -35px;
            margin-right: 0;
            width: 110%;
            max-width: none;
            height: auto;
            margin-top: -70px;
            margin-bottom: 12px;
        }
        .footer-img {
            display: block;
            margin-left: -35px;
            margin-right: 0;
            width: 110%;
            max-width: none;
            height: auto;
            position: fixed;
            left: 0;
            bottom: -45px;
        }

        .fecha { margin-top: 20px; margin-bottom: 30px; }
        .asunto { margin-bottom: 20px; }
        .firma { margin-top: 40px; margin-bottom: 10px; text-align: left; }
        .linea { border-top: 1px solid #222; width: 250px; margin: 10px 0 0 0; }
        .nombre { font-weight: bold; margin-top: 10px; }
        .cargo { margin-bottom: 0; }
    </style>
</head>
<body>
    <img src="' . $headerImg . '" alt="Header" class="header-img">
    <div class="fecha">
        Bogotá, ' . $fecha_actual . '
    </div>
    <div>
        Señores:<br><br>
        <span style="font-weight:bold;">A QUIEN INTERESE</span><br><br>
    </div>
    <div class="asunto" style="margin-top:20px;">
        <b>Asunto:</b> Certificación Asistencia Bootcamp ' . $nombre_bootcamp . ' - Programa Talento Tech - Bogotá
    </div>
    <div style="margin-bottom:20px;">
        Cordial saludo,
    </div><br><br>
    <div class="justificado" style="margin-bottom:20px;">
        En mi calidad de Directora de Proyecto para la ejecución del contrato 1107 de 2025 suscrito entre el Fondo Único de Tecnologías de la Información y las Comunicaciones y la Unión Temporal Innova Digital, me dirijo a usted con el fin de informar que el (la) señor(a) <b>' . strtoupper($nombre_estudiante) . '</b> identificado (a) con cédula de ciudadanía No <b>' . $cedula . '</b>, se encuentra matriculada en el <b>Programa Talento Tech - Bogotá</b> en el bootcamp de <b>' . $nombre_bootcamp . '</b> de manera <b>' . $modalidad_asistencia . '</b> en los horarios <b>' . $schedules . '</b>, el cual se desarrollará del <b>' . $dia_inicio . ' de ' . $mes_inicio . '</b> al <b>' . $dia_fin . ' de ' . $mes_fin . '</b> de ' . $anio_fin . '.
    </div><br><br>
    <div style="margin-bottom:20px;">
        Cordialmente,
    </div>
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
    <div style="margin-top:18px; font-size:12px; color:#444;">
        <i>Nota: En las fechas especificadas se desarrolla la totalidad del bootcamp.</i>
    </div>
    <div style="margin-top:10px; font-size:12px; color:#333;">
        <b>Código de serie del certificado:</b> ' . $codigo_serie . '
    </div>
    <img src="' . $footerImg1 . '" alt="Footer" class="footer-img">
</body>
</html>';

// Crear y configurar DOMPDF
$dompdf = new Dompdf($options);
$dompdf->setPaper('letter', 'portrait');
$dompdf->loadHtml($html);

try {
    $dompdf->render();
    $pdfOutput = $dompdf->output();
} catch (Exception $e) {
    error_log("Error al generar PDF: " . $e->getMessage());
    sendJsonResponse(false, 'Error al generar el PDF: ' . $e->getMessage());
}

// Guardar el PDF en el servidor
try {
    if (!is_dir($certFolder)) {
        if (!mkdir($certFolder, 0777, true)) {
            throw new Exception("No se pudo crear la carpeta de certificados: $certFolder");
        }
    }

    $pdfPath = $certFolder . $codigo_serie . '.pdf';
    if (file_put_contents($pdfPath, $pdfOutput) === false) {
        throw new Exception("No se pudo guardar el archivo PDF en: " . $pdfPath);
    }
} catch (Exception $e) {
    error_log("Error al guardar PDF: " . $e->getMessage());
    sendJsonResponse(false, 'Error al guardar el PDF: ' . $e->getMessage());
}

// --- 3. Enviar el certificado por correo electrónico ---

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
    $mail->Subject = 'Certificado académico - Bootcamp Talento Tech';

    // Reemplazar el cuerpo del correo por la imagen adjunta
    $mail->Body = '
        <div style="text-align:center;">
            <img src="https://dashboard.utinnova.co/dashboard/img/cuerpo_certificado.png" alt="Certificado académico" style="max-width:100%;height:auto;">
        </div>
    ';

    $mail->addAttachment($pdfPath, 'certificacion_' . $cedula . '.pdf');

    if (!$mail->send()) {
        throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
    }

    sendJsonResponse(true, 'Certificado generado y enviado por correo a ' . $email);
} catch (Exception $e) {
    error_log("PHPMailer Exception: " . $e->getMessage());
    sendJsonResponse(false, 'Error al enviar el correo: ' . $e->getMessage(), $mail->ErrorInfo ?? 'Error desconocido');
}
