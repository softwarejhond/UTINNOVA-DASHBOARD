<?php
session_start();
require_once 'conexion.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Asistencias desde Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Importar Asistencias desde Excel</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
                            processExcelFile();
                        }
                        ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Seleccionar archivo Excel</label>
                                <input type="file" class="form-control" id="excel_file" name="excel_file" 
                                       accept=".xlsx,.xls" required>
                                <div class="form-text">
                                    El archivo debe contener 4 hojas nombradas con códigos de bootcamp.<br>
                                    Estructura: A = Documento, B = Sede, C+ = Fechas con estados de asistencia
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Procesar Asistencias</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    function processExcelFile() {
        global $conn;
        
        try {
            // Validar archivo subido
            if ($_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al subir el archivo: ' . $_FILES['excel_file']['error']);
            }

            $uploadFile = $_FILES['excel_file']['tmp_name'];
            
            // Cargar el archivo Excel
            $spreadsheet = IOFactory::load($uploadFile);
            $sheetNames = $spreadsheet->getSheetNames();
            
            $totalInserted = 0;
            $errors = [];
            $processed_sheets = [];
            
            foreach ($sheetNames as $sheetName) {
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                $sheetName = $worksheet->getTitle();
                $courseCode = $sheetName;
                
                echo "<div class='alert alert-info'>Procesando hoja: <strong>$sheetName</strong></div>";
                
                try {
                    // Obtener información del curso
                    $courseQuery = "SELECT code, teacher, name FROM courses WHERE code = ?";
                    $stmt = $conn->prepare($courseQuery);
                    $stmt->bind_param("s", $courseCode);
                    $stmt->execute();
                    $courseResult = $stmt->get_result();
                    
                    if ($courseResult->num_rows === 0) {
                        $errors[] = "No se encontró el curso con código: $courseCode";
                        continue;
                    }
                    
                    $courseData = $courseResult->fetch_assoc();
                    $courseId = $courseData['code'];
                    $teacherId = $courseData['teacher'];
                    $courseName = $courseData['name'];
                    
                    // Determinar modalidad basada en el final del nombre del curso
                    $modality = 'presencial'; // por defecto
                    if (strtoupper(substr($courseName, -1)) === 'V') {
                        $modality = 'virtual';
                    } elseif (strtoupper(substr($courseName, -1)) === 'P') {
                        $modality = 'presencial';
                    }
                    
                    // Obtener datos de la hoja
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();
                    
                    // Leer encabezados (primera fila) para obtener las fechas
                    $dateColumns = [];
                    for ($col = 'C'; $col <= $highestColumn; $col++) {
                        $headerValue = $worksheet->getCell($col . '1')->getValue();
                        if (!empty($headerValue)) {
                            $dateColumns[$col] = parseDate($headerValue);
                        }
                    }
                    
                    // Procesar cada fila de estudiantes (desde la fila 2)
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $studentId = $worksheet->getCell('A' . $row)->getValue();
                        $sedeCell = $worksheet->getCell('B' . $row);
                        
                        // Obtener el valor de la sede de diferentes maneras
                        $sede = $sedeCell->getValue();
                        
                        // Si el valor está vacío, intentar obtener el valor calculado
                        if (empty($sede) || $sede === 0) {
                            $sede = $sedeCell->getCalculatedValue();
                        }
                        
                        // Si aún está vacío, obtener el valor formateado
                        if (empty($sede) || $sede === 0) {
                            $sede = $sedeCell->getFormattedValue();
                        }
                        
                        // Convertir a string y limpiar
                        $sede = trim(strval($sede));
                        
                        // Si sigue siendo '0' o vacío, asignar valor por defecto
                        if ($sede === '0' || empty($sede)) {
                            $sede = 'No definido';
                        }
                        
                        if (empty($studentId)) continue;
                        
                        // Debug: mostrar valores para la primera fila
                        if ($row === 2) {
                            echo "<div class='alert alert-info'>";
                            echo "Debug - Primera fila: Student ID: '$studentId', Sede: '$sede'<br>";
                            echo "Valor original de sede: " . var_export($sedeCell->getValue(), true) . "<br>";
                            echo "Tipo de dato: " . gettype($sedeCell->getValue());
                            echo "</div>";
                        }
                        
                        // Procesar cada fecha de asistencia
                        foreach ($dateColumns as $col => $classDate) {
                            $cell = $worksheet->getCell($col . $row);
                            $cellValue = $cell->getValue();
                            
                            // Solo procesar si la celda tiene algún contenido o color
                            if (!empty($cellValue) || hasCellColor($cell)) {
                                
                                // Detectar estado por color de fondo
                                $attendanceStatus = detectAttendanceByColor($cell);
                                
                                // Si no se detectó por color, usar el valor de texto
                                if (!$attendanceStatus && !empty($cellValue)) {
                                    $attendanceStatus = strtolower(trim($cellValue));
                                }
                                
                                if ($attendanceStatus && $classDate) {
                                    // Excluir registros que digan 'no registra'
                                    if ($attendanceStatus === 'no registra') {
                                        continue;
                                    }
                                    
                                    // Insertar registro de asistencia
                                    $insertQuery = "INSERT INTO attendance_records 
                                                  (teacher_id, student_id, course_id, modality, sede, class_date, 
                                                   recorded_hours, attendance_status, created_at) 
                                                  VALUES (?, ?, ?, ?, ?, ?, 2, ?, NOW())
                                                  ON DUPLICATE KEY UPDATE 
                                                  attendance_status = VALUES(attendance_status),
                                                  recorded_hours = VALUES(recorded_hours)";
                                                  
                                    $insertStmt = $conn->prepare($insertQuery);
                                    $insertStmt->bind_param("isissss", 
                                        $teacherId, 
                                        $studentId, 
                                        $courseId, 
                                        $modality, 
                                        $sede, 
                                        $classDate, 
                                        $attendanceStatus
                                    );

                                    if ($insertStmt->execute()) {
                                        $totalInserted++;
                                    } else {
                                        $errors[] = "Error insertando registro: Estudiante $studentId, Sede $sede, Fecha $classDate - " . $conn->error;
                                    }
                                }
                            }
                        }
                    }
                    
                    $processed_sheets[] = [
                        'sheet' => $sheetName,
                        'course_name' => $courseName,
                        'modality' => $modality,
                        'dates_found' => count($dateColumns)
                    ];
                    
                } catch (Exception $e) {
                    $errors[] = "Error procesando hoja $sheetName: " . $e->getMessage();
                }
            }
            
            // Mostrar resultados
            echo "<div class='alert alert-success'>";
            echo "<h5>Proceso completado!</h5>";
            echo "<p><strong>Total de registros insertados:</strong> $totalInserted</p>";
            echo "<h6>Hojas procesadas:</h6>";
            foreach ($processed_sheets as $sheet) {
                echo "<li>{$sheet['sheet']} - {$sheet['course_name']} ({$sheet['modality']}) - {$sheet['dates_found']} fechas encontradas</li>";
            }
            echo "</div>";
            
            if (!empty($errors)) {
                echo "<div class='alert alert-warning'>";
                echo "<h6>Errores encontrados:</h6>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h5>Error procesando archivo:</h5>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }

    function parseDate($dateValue) {
        try {
            // Si es un número de serie de Excel
            if (is_numeric($dateValue)) {
                $date = Date::excelToDateTimeObject($dateValue);
                return $date->format('Y-m-d');
            }
            
            // Si es una cadena de fecha
            if (is_string($dateValue)) {
                // Intentar diferentes formatos comunes
                $formats = ['d/m/Y', 'd-m-Y', 'd/m/y', 'd-m-y', 'Y-m-d'];
                
                foreach ($formats as $format) {
                    $date = DateTime::createFromFormat($format, $dateValue);
                    if ($date !== false) {
                        return $date->format('Y-m-d');
                    }
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    function hasCellColor($cell) {
        $fill = $cell->getStyle()->getFill();
        $fillType = $fill->getFillType();
        
        // Verificar si tiene un color de fondo diferente al por defecto
        return $fillType !== \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE;
    }

    function detectAttendanceByColor($cell) {
        $fill = $cell->getStyle()->getFill();
        $fillType = $fill->getFillType();
        
        if ($fillType === \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID) {
            $color = $fill->getStartColor()->getRGB();
            
            // Convertir colores hex sin # a formato RGB para comparación
            $targetColors = [
                'EF5350' => 'ausente',      // Rojo
                '9CCC65' => 'presente',     // Verde  
                'FFD54F' => 'tardanza',     // Amarillo
            ];
            
            // Buscar coincidencia exacta
            if (isset($targetColors[$color])) {
                return $targetColors[$color];
            }
            
            // Si no hay coincidencia exacta, usar aproximación por tolerancia RGB
            $bestMatch = approximateColorMatch($color, $targetColors);
            
            // Si encontró una coincidencia cercana, devolverla
            if ($bestMatch) {
                return $bestMatch;
            }
            
            // Si tiene color pero no coincide con los definidos, es 'no registra'
            return 'no registra';
        }
        
        return false;
    }
    
    function approximateColorMatch($targetColor, $colorMap) {
        $targetRGB = hexToRgb($targetColor);
        if (!$targetRGB) return false;
        
        $minDistance = PHP_INT_MAX;
        $matchedStatus = false;
        
        foreach ($colorMap as $color => $status) {
            $colorRGB = hexToRgb($color);
            if (!$colorRGB) continue;
            
            // Calcular distancia euclidiana entre colores
            $distance = sqrt(
                pow($targetRGB['r'] - $colorRGB['r'], 2) +
                pow($targetRGB['g'] - $colorRGB['g'], 2) +
                pow($targetRGB['b'] - $colorRGB['b'], 2)
            );
            
            // Tolerancia más estricta para colores específicos (30 puntos)
            if ($distance < $minDistance && $distance < 30) {
                $minDistance = $distance;
                $matchedStatus = $status;
            }
        }
        
        return $matchedStatus;
    }
    
    function hexToRgb($hex) {
        // Remover # si existe
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) !== 6) return false;
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
