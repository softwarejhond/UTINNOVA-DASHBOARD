<?php
// filepath: c:\xampp\htdocs\UTINNOVA-DASHBOARD\import_excel_to_sql.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(600); // Aumentar el tiempo de ejecución a 10 minutos
ini_set('memory_limit', '512M'); // Aumentar el límite de memoria

require 'vendor/autoload.php';
include 'conexion.php'; // Tu script de conexión a la BD

use PhpOffice\PhpSpreadsheet\IOFactory;

// --- Configuración ---
$excelFilePath = __DIR__ . '/fechas_todos.xlsx';
$startRow = 2; // Fila donde comienzan los datos de estudiantes

// --- Funciones de Ayuda ---

/**
 * Parsea una fecha de Excel a un objeto DateTime de PHP.
 */
function parseExcelDate($dateValue) {
    if (is_numeric($dateValue)) {
        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
        } catch (\Exception $e) {
            return null;
        }
    } elseif (is_string($dateValue)) {
        try {
            return new DateTime($dateValue);
        } catch (\Exception $e) {
            $formats = ['d/m/Y', 'm-d-Y', 'Y-m-d'];
            foreach ($formats as $format) {
                $d = DateTime::createFromFormat($format, $dateValue);
                if ($d && $d->format($format) === $dateValue) {
                    return $d;
                }
            }
        }
    }
    return null;
}

/**
 * Normaliza el estado de asistencia.
 */
function normalizeAttendanceStatus($status) {
    $status = strtolower(trim($status));
    $map = [
        'presente' => 'Presente', 'present' => 'Presente', 'p' => 'Presente',
        'ausente' => 'Ausente', 'absent' => 'Ausente', 'a' => 'Ausente', 'f' => 'Ausente',
        'tarde' => 'Tarde', 'late' => 'Tarde', 't' => 'Tarde',
        'excusa' => 'Excusa', 'excused' => 'Excusa', 'e' => 'Excusa', 'x' => 'Excusa'
    ];
    return $map[$status] ?? 'N/A';
}

// --- Lógica Principal ---

echo "<h1>Iniciando importación de asistencias a JSON en la base de datos...</h1>";

if (!file_exists($excelFilePath)) {
    die("Error: El archivo Excel '{$excelFilePath}' no fue encontrado.");
}

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

try {
    $spreadsheet = IOFactory::load($excelFilePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

    // Leer encabezados (fechas de clase)
    $headerRow = $worksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false)[0];
    $classDates = [];
    for ($col = 2; $col < $highestColumnIndex; $col++) { // Cédula en A, Nombre en B
        $dateValue = $headerRow[$col];
        $dateTime = parseExcelDate($dateValue);
        if ($dateTime) {
            $classDates[$col] = $dateTime->format('Y-m-d');
        }
    }

    echo "<p>Fechas de clase encontradas en el Excel: " . count($classDates) . "</p>";

    // Preparar la consulta de inserción/actualización
    $sql = "INSERT INTO attendance_excel_data (number_id, attendance_data, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE attendance_data = VALUES(attendance_data), updated_at = NOW()";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $totalStudentsProcessed = 0;

    // Iterar sobre cada estudiante (fila)
    for ($row = $startRow; $row <= $highestRow; $row++) {
        $number_id = trim($worksheet->getCell('A' . $row)->getValue());
        
        if (empty($number_id) || !is_numeric($number_id)) {
            continue; // Saltar filas sin cédula válida
        }
        
        $studentAttendanceData = [];

        // Iterar sobre las columnas de fechas para este estudiante
        foreach ($classDates as $colIndex => $classDate) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $cellValue = $worksheet->getCell($columnLetter . $row)->getValue();
            
            if (!empty($cellValue)) {
                $status = normalizeAttendanceStatus($cellValue);
                
                // Agregamos al array de asistencias del estudiante
                $studentAttendanceData[] = [
                    'fecha' => $classDate,
                    'estado' => $status
                    // Nota: La hora no está en el Excel, se omite por ahora.
                ];
            }
        }

        // Si encontramos datos de asistencia, los guardamos en la BD
        if (!empty($studentAttendanceData)) {
            $jsonData = json_encode($studentAttendanceData);

            // Vincular parámetros y ejecutar
            $stmt->bind_param("ss", $number_id, $jsonData);
            if ($stmt->execute()) {
                $totalStudentsProcessed++;
            } else {
                echo "<p style='color:red;'>Error al insertar datos para {$number_id}: " . $stmt->error . "</p>";
            }
        }
    }

    $stmt->close();
    $conn->close();

    echo "<h2>Proceso de importación completado.</h2>";
    echo "<p>Estudiantes procesados y guardados en 'attendance_excel_data': {$totalStudentsProcessed}</p>";

} catch (\Exception $e) {
    die("Error al procesar el archivo Excel: " . $e->getMessage());
}
?>