<?php
// Carpeta y prefijo según el tipo de informe
$tipo = $_GET['tipo'] ?? 'semanal_L1';
$ruta = '';
$prefijo = '';

switch ($tipo) {
    case 'semanal_L1':
        $ruta = __DIR__ . '/../../reports/semanal_L1/';
        $prefijo = 'informe_semanal_';
        break;
    case 'semanal_lote2':
        $ruta = __DIR__ . '/../../reports/semanal_L2/';
        $prefijo = 'informe_semanal_L2_';
        break;
    case 'certificadosLote1':
        $ruta = __DIR__ . '/../../reports/adicionales_L1/';
        $prefijo = 'informe_adicionales_L1_';
        break;
    case 'certificadosLote2':
        $ruta = __DIR__ . '/../../reports/adicionales_L2/';
        $prefijo = 'informe_adicionales_L2_';
        break;
    case 'asistencia':
        $ruta = __DIR__ . '/../../reports/asistencia/';
        $prefijo = 'informe_asistencia_';
        break;
    case 'asistenciaLE':
        $ruta = __DIR__ . '/../../reports/asistenciaLE/';
        $prefijo = 'informe_asistencia_LE_';
        break;
    // Agrega más casos según tus informes
    default:
        http_response_code(400);
        exit('Tipo de informe no válido');
}

$archivos = glob($ruta . $prefijo . '*.xlsx');
if (!$archivos) {
    http_response_code(404);
    exit('No hay informes disponibles');
}

// Buscar el más reciente
usort($archivos, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
$archivo = $archivos[0];

// Descargar el archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
header('Content-Length: ' . filesize($archivo));
readfile($archivo);
exit;