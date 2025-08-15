<?php
// Control de errores y configuración de tiempo de ejecución
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/export_errors.log');

// Configurar tiempo de ejecución a 10 minutos (aumentado)
set_time_limit(600);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '768M'); // Aumentado

// Configurar timeout para MySQL
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);

try {
    require __DIR__ . '/../../vendor/autoload.php';
    require __DIR__ . '/../../controller/conexion.php';
    
    // Verificar conexión a la base de datos
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Configurar timeout de MySQL
    mysqli_query($conn, "SET SESSION wait_timeout = 600");
    mysqli_query($conn, "SET SESSION interactive_timeout = 600");
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error al cargar dependencias: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al inicializar la aplicación: ' . $e->getMessage()
    ]);
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Sistema de caché para respuestas de API - Evita múltiples peticiones
class ApiCache {
    private static $userCache = [];
    private static $notasCache = [];
    
    public static function getUser($studentId) {
        if (isset(self::$userCache[$studentId])) {
            return self::$userCache[$studentId];
        }
        return null;
    }
    
    public static function setUser($studentId, $userData) {
        self::$userCache[$studentId] = $userData;
    }
    
    public static function getNotas($studentId, $courseCode) {
        $key = $studentId . '_' . $courseCode;
        if (isset(self::$notasCache[$key])) {
            return self::$notasCache[$key];
        }
        return null;
    }
    
    public static function setNotas($studentId, $courseCode, $notasData) {
        $key = $studentId . '_' . $courseCode;
        self::$notasCache[$key] = $notasData;
    }
}

// Función para realizar peticiones HTTP con reintentos
function realizarPeticionHttp($url, $postfields, $timeout = 15, $maxRetries = 3) {
    $retry = 0;
    $response = false;
    
    while ($retry < $maxRetries && $response === false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $retry++;
            error_log("Intento $retry fallido: " . curl_error($ch));
            curl_close($ch);
            
            if ($retry < $maxRetries) {
                // Espera exponencial entre reintentos
                $sleepTime = pow(2, $retry); 
                sleep($sleepTime);
            }
        } else {
            curl_close($ch);
        }
    }
    
    return $response;
}

// Función simplificada para obtener notas del curso técnico
function obtenerNotasTecnico($conn, $studentId, $courseCode) {
    // Validar parámetros con ternarios
    $studentId = !empty($studentId) ? $studentId : null;
    $courseCode = !empty($courseCode) ? $courseCode : null;
    
    if (!$studentId || !$courseCode) {
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
    
    try {
        // Intentar obtener las notas desde course_approvals
        $sql = "SELECT final_grade, grade_1, grade_2 FROM course_approvals 
                WHERE student_number_id = ? AND course_code = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta de notas aprobadas: " . $conn->error);
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }
        
        $stmt->bind_param("ss", $studentId, $courseCode);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de notas aprobadas: " . $stmt->error);
            $stmt->close();
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return [
                'final' => $row['final_grade'] ? floatval($row['final_grade']) : 0,
                'grade1' => $row['grade_1'] ? floatval($row['grade_1']) : 0,
                'grade2' => $row['grade_2'] ? floatval($row['grade_2']) : 0
            ];
        }
        
        // Verificar caché antes de consultar la API
        $cachedNotas = ApiCache::getNotas($studentId, $courseCode);
        if ($cachedNotas) {
            return $cachedNotas;
        }
        
        // Si no está en la tabla de aprobados, obtener desde Moodle API
        $apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
        $token = '3f158134506350615397c83d861c2104';
        $format = 'json';
        
        // Paso 1: Obtener el userid
        $cachedUser = ApiCache::getUser($studentId);
        $userid = null;
        
        if ($cachedUser) {
            $userid = $cachedUser;
        } else {
            $functionGetUser = 'core_user_get_users_by_field';
            $paramsUser = [
                'field' => 'username',
                'values[0]' => $studentId
            ];
            
            $postdataUser = http_build_query([
                'wstoken' => $token,
                'wsfunction' => $functionGetUser,
                'moodlewsrestformat' => $format
            ] + $paramsUser);
            
            $responseUser = realizarPeticionHttp($apiUrl, $postdataUser);
            
            if ($responseUser === false) {
                return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
            }
            
            $userData = json_decode($responseUser, true);
            
            // Usar ternarios para validar userData
            $userid = (isset($userData[0]['id'])) ? $userData[0]['id'] : null;
            
            if (!$userid) {
                return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
            }
            
            // Guardar en caché
            ApiCache::setUser($studentId, $userid);
        }
        
        // Paso 2: Obtener las notas
        $function = 'gradereport_user_get_grade_items';
        $params = [
            'courseid' => $courseCode,
            'userid' => $userid
        ];
        
        $postdata = http_build_query([
            'wstoken' => $token,
            'wsfunction' => $function,
            'moodlewsrestformat' => $format
        ] + $params);
        
        $response = realizarPeticionHttp($apiUrl, $postdata, 20);
        
        if ($response === false) {
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }
        
        $data = json_decode($response, true);
        
        // Validar respuesta con ternarios
        $usergrade = (isset($data['usergrades'][0])) ? $data['usergrades'][0] : null;
        
        if (!$usergrade) {
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }
        
        // Procesar las notas con ternarios
        $notas = [];
        $sumaNotas = 0;
        
        $gradeitems = isset($usergrade['gradeitems']) ? $usergrade['gradeitems'] : [];
        
        foreach ($gradeitems as $item) {
            $esNotaValida = (isset($item['itemtype']) && $item['itemtype'] === 'course') ||
                           (isset($item['graderaw']) && $item['graderaw'] !== null);
            
            if ($esNotaValida) {
                $notaRaw = isset($item['graderaw']) ? floatval($item['graderaw']) : 0;
                $grademax = isset($item['grademax']) ? floatval($item['grademax']) : 5.0;
                
                $notaNormalizada = ($grademax > 0) ? ($notaRaw / $grademax) * 5.0 : 0;
                $notas[] = $notaNormalizada;
                $sumaNotas += $notaNormalizada;
            }
            
            if (count($notas) >= 2) break;
        }
        
        // Usar ternarios para asignar notas
        $grade1 = isset($notas[0]) ? $notas[0] : 0;
        $grade2 = isset($notas[1]) ? $notas[1] : 0;
        $notaFinal = (count($notas) > 0) ? round($sumaNotas / count($notas), 2) : 0;
        
        $resultado = [
            'final' => $notaFinal,
            'grade1' => $grade1,
            'grade2' => $grade2
        ];
        
        // Guardar en caché
        ApiCache::setNotas($studentId, $courseCode, $resultado);
        
        return $resultado;
        
    } catch (Exception $e) {
        error_log("Excepción en obtenerNotasTecnico para estudiante $studentId: " . $e->getMessage());
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
}

// Función simplificada para verificar aprobación
function estaAprobado($conn, $studentId, $courseCode) {
    try {
        $sql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
        $stmt = $conn->prepare($sql);
        
        $prepared = $stmt ? true : false;
        if (!$prepared) {
            error_log("Error preparando consulta de aprobación: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ss", $studentId, $courseCode);
        $executed = $stmt->execute() ? true : false;
        
        if (!$executed) {
            error_log("Error ejecutando consulta de aprobación: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $result = $stmt->get_result();
        $approved = ($result->num_rows > 0) ? true : false;
        $stmt->close();
        
        return $approved;
        
    } catch (Exception $e) {
        error_log("Excepción en estaAprobado: " . $e->getMessage());
        return false;
    }
}

// Función simplificada para obtener nombre del programa
function obtenerNombrePrograma($conn, $courseId) {
    $courseId = !empty($courseId) ? $courseId : null;
    
    if (!$courseId) return 'No asignado';
    
    try {
        $sql = "SELECT name FROM courses WHERE code = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta de nombre programa: " . $conn->error);
            return 'Error al consultar';
        }
        
        $stmt->bind_param("s", $courseId);
        $executed = $stmt->execute() ? true : false;
        
        if (!$executed) {
            error_log("Error ejecutando consulta de nombre programa: " . $stmt->error);
            $stmt->close();
            return 'Error al ejecutar';
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row ? ($row['name'] ? $row['name'] : 'Programa sin nombre') : 'Programa no encontrado';
        
    } catch (Exception $e) {
        error_log("Excepción en obtenerNombrePrograma: " . $e->getMessage());
        return 'Error al obtener nombre';
    }
}

try {
    // Crear nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Informe Solo Notas Técnico');

    // Encabezados simplificados (solo notas)
    $headers = [
        'A1' => 'ID',
        'B1' => 'Número de Identificación',
        'C1' => 'Nombre Completo',
        'D1' => 'Correo Institucional',
        'E1' => 'Modalidad',
        'F1' => 'Sede',
        'G1' => 'Programa Técnico',
        'H1' => 'Nota 1',
        'I1' => 'Nota 2',
        'J1' => 'Nota Final',
        'K1' => 'Estado',
        'L1' => 'Fecha Exportación'
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Estilo del encabezado
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '30336b']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];

    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

    // Consultar estudiantes - Implementamos procesamiento por lotes
    $sql = "SELECT DISTINCT g.number_id, g.full_name, g.institutional_email, g.mode, g.headquarters,
                   g.id_bootcamp
            FROM groups g
            WHERE g.number_id IS NOT NULL AND g.number_id != ''
            AND g.id_bootcamp IS NOT NULL AND g.id_bootcamp != ''
            ORDER BY g.full_name ASC";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('Error al obtener estudiantes: ' . mysqli_error($conn));
    }

    // Obtener total de registros para mostrar progreso
    $totalRegistros = mysqli_num_rows($result);
    error_log("Total de registros a procesar: $totalRegistros");

    // Preparar procesamiento por lotes
    $tamanoLote = 25; // procesar 25 estudiantes a la vez
    $estudiantes = [];
    
    while ($data = mysqli_fetch_assoc($result)) {
        $estudiantes[] = $data;
    }
    
    $row = 2;
    $contador = 1;
    $procesados = 0;
    $errores = 0;
    $totalLotes = ceil(count($estudiantes) / $tamanoLote);

    error_log("Iniciando procesamiento por lotes: $totalLotes lotes de $tamanoLote estudiantes");

    // Procesar por lotes
    for ($lote = 0; $lote < $totalLotes; $lote++) {
        $inicio = $lote * $tamanoLote;
        $fin = min(($lote + 1) * $tamanoLote, count($estudiantes));
        
        error_log("Procesando lote " . ($lote + 1) . " de $totalLotes (registros $inicio a $fin)");
        
        // Procesar cada estudiante en el lote
        for ($i = $inicio; $i < $fin; $i++) {
            $data = $estudiantes[$i];
            
            try {
                // Obtener notas solo del técnico
                $notasTecnico = obtenerNotasTecnico($conn, $data['number_id'], $data['id_bootcamp']);
                
                // Verificar aprobación usando ternarios
                $aprobadoTecnico = estaAprobado($conn, $data['number_id'], $data['id_bootcamp']) ? true : false;
                
                // Determinar estado usando ternarios
                $estadoTecnico = $aprobadoTecnico ? 'Aprobado' : 
                                (($notasTecnico['final'] >= 3.0) ? 'Apto' : 'No Apto');
                
                // Llenar fila usando ternarios para validar datos
                $sheet->setCellValue('A' . $row, $contador);
                $sheet->setCellValue('B' . $row, $data['number_id'] ? $data['number_id'] : 'N/A');
                $sheet->setCellValue('C' . $row, $data['full_name'] ? strtoupper($data['full_name']) : 'NOMBRE NO DISPONIBLE');
                $sheet->setCellValue('D' . $row, $data['institutional_email'] ? $data['institutional_email'] : 'Email no disponible');
                $sheet->setCellValue('E' . $row, $data['mode'] ? $data['mode'] : 'N/A');
                $sheet->setCellValue('F' . $row, $data['headquarters'] ? $data['headquarters'] : 'N/A');
                
                // Solo datos del técnico usando ternarios
                $sheet->setCellValue('G' . $row, obtenerNombrePrograma($conn, $data['id_bootcamp']));
                $sheet->setCellValue('H' . $row, ($notasTecnico['grade1'] > 0) ? number_format($notasTecnico['grade1'], 1) : '0.0');
                $sheet->setCellValue('I' . $row, ($notasTecnico['grade2'] > 0) ? number_format($notasTecnico['grade2'], 1) : '0.0');
                $sheet->setCellValue('J' . $row, ($notasTecnico['final'] > 0) ? number_format($notasTecnico['final'], 1) : '0.0');
                $sheet->setCellValue('K' . $row, $estadoTecnico);
                $sheet->setCellValue('L' . $row, date('Y-m-d H:i:s'));
                
                // Aplicar colores usando ternarios
                $colorAprobado = 'FFFFD700'; // Dorado
                $colorApto = 'FF66CC00'; // Verde
                $colorNoApto = 'FFFF0000'; // Rojo
                
                $color = ($estadoTecnico === 'Aprobado') ? $colorAprobado : 
                        (($estadoTecnico === 'Apto') ? $colorApto : $colorNoApto);
                
                $sheet->getStyle('K' . $row)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB($color);
                
                $row++;
                $contador++;
                $procesados++;
                
            } catch (Exception $e) {
                $errores++;
                error_log("Error procesando estudiante {$data['number_id']}: " . $e->getMessage());
                continue;
            }
        }
        
        // Liberamos memoria después de cada lote
        if ($lote % 2 == 1) { // cada 2 lotes
            gc_collect_cycles();
        }
        
        error_log("Lote " . ($lote + 1) . " completado. Procesados: $procesados, Errores: $errores");
    }

    error_log("Procesamiento completo. Total procesados: $procesados, Total errores: $errores");

    // Verificar si hay datos usando ternarios
    $hayDatos = ($contador === 1) ? false : true;
    
    if (!$hayDatos) {
        $sheet->setCellValue('A2', 'No hay estudiantes matriculados en cursos técnicos');
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Auto-ajustar columnas
    foreach(range('A','L') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Aplicar bordes
    $totalRows = $row - 1;
    if ($totalRows >= 1) {
        $sheet->getStyle('A1:L' . $totalRows)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // Generar archivo
    $fechaHora = date('Y-m-d_H-i-s');
    $filename = "informe_solo_notas_tecnico_{$fechaHora}.xlsx";

    // Limpiar buffer
    ob_end_clean();

    // Headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');
    header('Expires: 0');

    // Crear y enviar archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    ob_end_clean();
    
    error_log("Error crítico en export_excel_notas_only.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Respuesta de error
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al generar el archivo: ' . $e->getMessage(),
        'details' => 'Revise los logs del servidor para más información'
    ]);
}

exit;
?>