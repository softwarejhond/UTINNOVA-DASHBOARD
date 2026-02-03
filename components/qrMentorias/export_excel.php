<?php
// Configurar manejo de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Cargar dependencias
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {
    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        throw new Exception('Acceso no autorizado');
    }

    // Obtener información del usuario
    require __DIR__ . '/../filters/takeUser.php';
    $infoUsuario = obtenerInformacionUsuario();
    $rol = $infoUsuario['rol'];
    $username = $_SESSION['username'] ?? null;

    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Verificar conexión a la base de datos
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Construir consulta SQL - SIEMPRE EXPORTAR TODOS LOS QR
    $whereClause = '';
    $params = [];
    $types = '';

    // Solo aplicar filtro por rol si es Mentor
    if ($rol === 'Mentor' && $username) {
        $whereClause = 'WHERE qr.created_by = ?';
        $params[] = (int)$username;
        $types = 'i';
    } else {
        // Para otros roles, exportar TODOS los QR sin filtros
        $whereClause = 'WHERE 1=1';
    }

    // Consulta principal - SIN filtros de estado ni búsqueda
    $sql = "SELECT 
                qr.id,
                qr.title,
                qr.url,
                qr.clases_equivalentes,
                qr.authorized_by,
                qr.authorized,
                qr.created_at,
                u.username as mentor_cedula,
                u.nombre as mentor_nombre,
                auth_user.username as authorized_by_cedula,
                auth_user.nombre as authorized_by_nombre
            FROM qr_mentorias qr 
            LEFT JOIN users u ON qr.created_by = u.username 
            LEFT JOIN users auth_user ON qr.authorized_by = auth_user.username
            $whereClause
            ORDER BY qr.created_at DESC";

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
    }

    if (empty($data)) {
        throw new Exception('No hay datos para exportar');
    }

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configurar título del documento
    $sheet->setTitle('Informe QR Mentorías');

    // Agregar encabezado principal
    $sheet->setCellValue('A1', 'INFORME COMPLETO DE CÓDIGOS QR - MENTORÍAS');
    $sheet->mergeCells('A1:J1');
    
    // Estilo del título
    $sheet->getStyle('A1')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 16,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '30336b']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    // Agregar fecha de generación
    $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A2:J2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Agregar información de que incluye todos los registros
    $sheet->setCellValue('A3', 'Incluye: TODOS los códigos QR generados');
    $sheet->mergeCells('A3:J3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezados de columnas
    $headers = [
        'A5' => 'ID',
        'B5' => 'Título',
        'C5' => 'URL',
        'D5' => 'Clases Equiv.',
        'E5' => 'Estado',
        'F5' => 'Fecha Creación',
        'G5' => 'Mentor Cédula',
        'H5' => 'Mentor Nombre',
        'I5' => 'Autorizado por Cédula',
        'J5' => 'Autorizado por Nombre'
    ];

    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    // Estilo de encabezados
    $sheet->getStyle('A5:J5')->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '5a6c7d']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ]);

    // Llenar datos
    $row = 6;
    foreach ($data as $item) {
        $estado = ($item['authorized_by'] == 0 || $item['authorized'] == 0) ? 'Pendiente' : 'Autorizada';
        
        $sheet->setCellValue('A' . $row, $item['id']);
        $sheet->setCellValue('B' . $row, $item['title']);
        $sheet->setCellValue('C' . $row, $item['url']);
        $sheet->setCellValue('D' . $row, $item['clases_equivalentes']);
        $sheet->setCellValue('E' . $row, $estado);
        $sheet->setCellValue('F' . $row, date('d/m/Y H:i', strtotime($item['created_at'])));
        $sheet->setCellValue('G' . $row, $item['mentor_cedula'] ?? 'N/A');
        $sheet->setCellValue('H' . $row, $item['mentor_nombre'] ?? 'N/A');
        $sheet->setCellValue('I' . $row, $item['authorized_by_cedula'] ?? 'N/A');
        $sheet->setCellValue('J' . $row, $item['authorized_by_nombre'] ?? 'N/A');

        // Estilo de filas de datos
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Color de fondo alternado
        if ($row % 2 == 0) {
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ]
            ]);
        }

        // Color según estado
        if ($estado === 'Pendiente') {
            $sheet->getStyle('E' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF3CD']
                ]
            ]);
        } else {
            $sheet->getStyle('E' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D1E7DD']
                ]
            ]);
        }

        $row++;
    }

    // Ajustar ancho de columnas
    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(40);
    $sheet->getColumnDimension('C')->setWidth(50);
    $sheet->getColumnDimension('D')->setWidth(12);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(18);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(25);
    $sheet->getColumnDimension('I')->setWidth(20);
    $sheet->getColumnDimension('J')->setWidth(25);

    // Ajustar altura de filas de encabezado
    $sheet->getRowDimension(1)->setRowHeight(30);
    $sheet->getRowDimension(5)->setRowHeight(25);

    // Generar nombre de archivo
    $filename = 'informe_completo_qr_mentorias_' . date('Y-m-d_H-i-s') . '.xlsx';

    // Configurar headers para descarga directa
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1'); // Para IE9
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Fecha en el pasado
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // Siempre modificado
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    // Crear writer y enviar directamente al navegador
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Limpiar memoria
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

} catch (Exception $e) {
    // En caso de error, enviar respuesta JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>