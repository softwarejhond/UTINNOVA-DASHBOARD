<?php
session_start();
require_once '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del POST
$number_id = $_POST['number_id'] ?? '';
$usuario_subida = $_SESSION['username'] ?? 'Sistema';

if (empty($number_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de estudiante requerido']);
    exit;
}

// Verificar si se subió un archivo
if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'Error al subir el archivo';
    if (isset($_FILES['certificado']['error'])) {
        switch ($_FILES['certificado']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'El archivo es demasiado grande';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'El archivo se subió parcialmente';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'No se seleccionó ningún archivo';
                break;
            default:
                $error_message = 'Error desconocido al subir el archivo';
        }
    }
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

$archivo = $_FILES['certificado'];

// Validar tipo de archivo (solo PDF)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

if ($mime_type !== 'application/pdf') {
    echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos PDF']);
    exit;
}

// Validar tamaño del archivo (máximo 10MB)
$max_size = 10 * 1024 * 1024; // 10MB en bytes
if ($archivo['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'El archivo no debe exceder los 10MB']);
    exit;
}

// Verificar si el estudiante existe
$stmt = $conn->prepare("SELECT number_id FROM user_register WHERE number_id = ?");
$stmt->bind_param("s", $number_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
    exit;
}
$stmt->close();

// Verificar si ya existe un certificado para este estudiante
$stmt = $conn->prepare("SELECT id FROM certificados_senatics WHERE number_id = ?");
$stmt->bind_param("s", $number_id);
$stmt->execute();
$result = $stmt->get_result();

$actualizar_existente = $result->num_rows > 0;
$stmt->close();

// Crear directorio si no existe
$upload_dir = '../../certificadosST/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Error al crear directorio de destino']);
        exit;
    }
}

// Generar nombre del archivo
$nombre_archivo = "cert_ST_{$number_id}.pdf";
$ruta_completa = $upload_dir . $nombre_archivo;

// Eliminar archivo anterior si existe
if (file_exists($ruta_completa)) {
    unlink($ruta_completa);
}

// Mover el archivo subido
if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
    exit;
}

// Guardar o actualizar en la base de datos
try {
    if ($actualizar_existente) {
        // Actualizar registro existente
        $stmt = $conn->prepare("UPDATE certificados_senatics SET archivo_certificado = ?, fecha_subida = NOW(), usuario_subida = ? WHERE number_id = ?");
        $stmt->bind_param("sss", $nombre_archivo, $usuario_subida, $number_id);
        $accion = 'actualizado';
    } else {
        // Insertar nuevo registro
        $stmt = $conn->prepare("INSERT INTO certificados_senatics (number_id, archivo_certificado, fecha_subida, usuario_subida) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("sss", $number_id, $nombre_archivo, $usuario_subida);
        $accion = 'guardado';
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Certificado {$accion} correctamente",
            'archivo' => $nombre_archivo,
            'actualizado' => $actualizar_existente
        ]);
    } else {
        // Si falla la BD, eliminar el archivo subido
        if (file_exists($ruta_completa)) {
            unlink($ruta_completa);
        }
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Si falla la BD, eliminar el archivo subido
    if (file_exists($ruta_completa)) {
        unlink($ruta_completa);
    }
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

$conn->close();
?>