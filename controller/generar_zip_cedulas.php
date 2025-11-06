<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Verificar permisos (ejemplo: solo para roles específicos)
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], [1, 12])) {
    http_response_code(403);
    die('Acceso denegado.');
}

require_once __DIR__ . '/../controller/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer los datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['number_ids']) || !is_array($input['number_ids']) || empty($input['number_ids'])) {
        http_response_code(400);
        die('Datos inválidos.');
    }
    
    $numberIds = $input['number_ids'];

    // Carpeta temporal
    $carpetaTemp = __DIR__ . '/../uploads/temp_cedulas_' . session_id() . '/';
    if (!is_dir($carpetaTemp)) {
        mkdir($carpetaTemp, 0755, true);
    }

    // Consultar DB usando MySQLi
    $placeholders = str_repeat('?,', count($numberIds) - 1) . '?';
    $stmt = mysqli_prepare($conn, "SELECT number_id, pdf_path FROM cedulas_pdf WHERE number_id IN ($placeholders)");
    
    // Crear los tipos de datos para bind_param (todos strings)
    $types = str_repeat('s', count($numberIds));
    mysqli_stmt_bind_param($stmt, $types, ...$numberIds);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $results = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Obtener los number_ids encontrados en la BD
    $foundIds = array_column($results, 'number_id');
    $notFoundInDB = array_diff($numberIds, $foundIds);

    if (empty($results)) {
        // Si no se encontró ningún PDF en la base de datos
        $response = [
            'success' => false,
            'message' => 'No se encontraron PDFs para los number_id proporcionados.',
            'not_found_in_db' => $notFoundInDB,
            'files_not_found' => [],
            'total_requested' => count($numberIds),
            'total_processed' => 0
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Copiar archivos
    $archivosCopiados = [];
    $archivosNoEncontrados = [];
    
    foreach ($results as $row) {
        $rutaOrigen = __DIR__ . '/../' . $row['pdf_path']; // Asumir pdf_path relativo
        $nombreArchivo = $row['number_id'] . '_' . basename($row['pdf_path']); // Agregar number_id al nombre
        $rutaDestino = $carpetaTemp . $nombreArchivo;
        
        if (file_exists($rutaOrigen) && copy($rutaOrigen, $rutaDestino)) {
            $archivosCopiados[] = $rutaDestino;
        } else {
            $archivosNoEncontrados[] = $row['number_id'];
        }
    }

    if (empty($archivosCopiados)) {
        // Si no se pudo copiar ningún archivo
        $response = [
            'success' => false,
            'message' => 'Error al copiar archivos.',
            'not_found_in_db' => $notFoundInDB,
            'files_not_found' => $archivosNoEncontrados,
            'total_requested' => count($numberIds),
            'total_processed' => 0
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Crear ZIP
    $zipNombre = 'cedulas_' . date('Y-m-d_H-i-s') . '.zip';
    $zipRuta = __DIR__ . '/../uploads/' . $zipNombre;
    
    $zip = new ZipArchive();
    if ($zip->open($zipRuta, ZipArchive::CREATE) === TRUE) {
        foreach ($archivosCopiados as $archivo) {
            $zip->addFile($archivo, basename($archivo));
        }
        $zip->close();

        // Limpiar temp
        array_map('unlink', glob("$carpetaTemp*"));
        rmdir($carpetaTemp);

        // Verificar que el ZIP se creó correctamente
        if (!file_exists($zipRuta) || filesize($zipRuta) === 0) {
            $response = [
                'success' => false,
                'message' => 'Error: ZIP creado pero está vacío o corrupto.',
                'not_found_in_db' => $notFoundInDB,
                'files_not_found' => $archivosNoEncontrados,
                'total_requested' => count($numberIds),
                'total_processed' => 0
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        // Agregar headers especiales para indicar que hay información adicional
        $totalNoEncontrados = array_merge($notFoundInDB, $archivosNoEncontrados);
        header('X-Total-Requested: ' . count($numberIds));
        header('X-Total-Processed: ' . count($archivosCopiados));
        header('X-Not-Found-Count: ' . count($totalNoEncontrados));
        header('X-Not-Found-IDs: ' . implode(',', $totalNoEncontrados));
        header('X-Not-Found-DB: ' . implode(',', $notFoundInDB));
        header('X-Files-Not-Found: ' . implode(',', $archivosNoEncontrados));

        // Configurar headers para descarga
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipNombre . '"');
        header('Content-Length: ' . filesize($zipRuta));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Limpiar cualquier salida previa que pueda corromper el ZIP
        ob_clean();
        flush();
        
        readfile($zipRuta);
        unlink($zipRuta);
        exit;
    } else {
        $response = [
            'success' => false,
            'message' => 'Error al crear ZIP.',
            'not_found_in_db' => $notFoundInDB,
            'files_not_found' => $archivosNoEncontrados,
            'total_requested' => count($numberIds),
            'total_processed' => 0
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
} else {
    http_response_code(405);
    die('Método no permitido.');
}
?>