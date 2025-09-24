<?php
// Función para establecer headers anti-caché para PDFs
function setPdfNoCacheHeaders() {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('ETag: "' . md5(uniqid()) . '"');
}

// Si se solicita directamente un PDF, aplicar headers
if (isset($_GET['pdf']) && $_GET['pdf']) {
    setPdfNoCacheHeaders();
    $pdfPath = $_GET['pdf'];
    
    // Validar que el archivo existe y es seguro
    $safePath = realpath($pdfPath);
    if ($safePath && file_exists($safePath) && strpos($safePath, realpath('.')) === 0) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
        readfile($safePath);
        exit;
    } else {
        http_response_code(404);
        echo 'Archivo no encontrado';
        exit;
    }
}
?>