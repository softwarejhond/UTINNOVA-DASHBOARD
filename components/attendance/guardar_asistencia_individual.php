<?php
require_once __DIR__ . '/../../controller/conexion.php';

// Iniciar sesión para obtener el usuario actual
session_start();

header('Content-Type: application/json');

// Obtener y decodificar los datos JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['attendance']) || !isset($data['course_id']) || !isset($data['class_date']) ||
    !isset($data['modalidad']) || !isset($data['sede']) || !isset($data['course_type']) || !isset($data['intensity'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Obtener el día de la semana y las horas programadas
$dayOfWeek = date('N', strtotime($data['class_date']));
$hours_column = '';
switch ($dayOfWeek) {
    case 1: $hours_column = 'monday_hours'; break;
    case 2: $hours_column = 'tuesday_hours'; break;
    case 3: $hours_column = 'wednesday_hours'; break;
    case 4: $hours_column = 'thursday_hours'; break;
    case 5: $hours_column = 'friday_hours'; break;
    case 6: $hours_column = 'saturday_hours'; break;
    case 7: $hours_column = 'sunday_hours'; break;
}

// Verificar las horas programadas para ese día
$check_hours_sql = "SELECT $hours_column as daily_hours FROM courses WHERE code = ?";
$check_hours_stmt = $conn->prepare($check_hours_sql);
if (!$check_hours_stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar consulta de horas: ' . $conn->error]);
    exit;
}

$check_hours_stmt->bind_param("i", $data['course_id']);
if (!$check_hours_stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar consulta de horas: ' . $check_hours_stmt->error]);
    exit;
}

$hours_result = $check_hours_stmt->get_result();
$hours_row = $hours_result->fetch_assoc();
$daily_hours = intval($hours_row['daily_hours'] ?? 0);

if ($daily_hours === 0) {
    echo json_encode(['success' => false, 'error' => 'No hay horas programadas para este día']);
    exit;
}

// Actualizar la intensidad con el valor correcto de las horas diarias
$data['intensity'] = $daily_hours;
$check_hours_stmt->close();

// Obtener el usuario actual desde la sesión
$teacher_id = $_SESSION['username'] ?? 'sistema';

// Determinar qué columna de intensidad actualizar según el tipo de curso
$intensity_column = "";
switch ($data['course_type']) {
    case 'bootcamp':
        $intensity_column = "b_intensity";
        break;
    case 'english_code':
        $intensity_column = "ec_intensity";
        break;
    case 'skills':
        $intensity_column = "s_intensity";
        break;
    case 'leveling_english':
        $intensity_column = "le_intensity";
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Tipo de curso no válido']);
        exit;
}

// Convertir intensidad a entero
$intensity = intval($data['intensity']);
if ($intensity <= 0) {
    echo json_encode(['success' => false, 'error' => 'La intensidad debe ser un número positivo']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Actualizar registros de asistencia
    foreach ($data['attendance'] as $recordId => $status) {
        // Validar el estado
        $validStates = ['presente', 'tarde', 'ausente', 'festivo'];
        if (!in_array($status, $validStates)) {
            throw new Exception('Estado de asistencia no válido');
        }

        // Calcular recorded_hours basado en el estado de asistencia
        // Solo asignar horas si está presente o tarde, sino es 0
        $recorded_hours = ($status === 'presente' || $status === 'tarde') ? $intensity : 0;

        // Obtener student_id
        $student_id = '';
        
        // Verificar si es un nuevo registro
        if (strpos($recordId, 'nuevo_') === 0) {
            // Es un nuevo registro, extraer el student_id
            $student_id = substr($recordId, 6); // Elimina 'nuevo_' del principio
            
            // Insertar nuevo registro con modalidad, sede, teacher_id y recorded_hours
            $query = "INSERT INTO attendance_records 
                     (student_id, course_id, class_date, attendance_status, modality, sede, teacher_id, recorded_hours) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conn->error);
            }

            $stmt->bind_param("sisssssi", $student_id, $data['course_id'], $data['class_date'], 
                             $status, $data['modalidad'], $data['sede'], $teacher_id, $recorded_hours);
        } else {
            // Es un registro existente, actualizar incluyendo modalidad, sede, teacher_id y recorded_hours
            $query = "UPDATE attendance_records 
                     SET attendance_status = ?, modality = ?, sede = ?, teacher_id = ?, recorded_hours = ?
                     WHERE id = ? 
                     AND course_id = ? 
                     AND class_date = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conn->error);
            }

            $stmt->bind_param("ssssiiis", $status, $data['modalidad'], $data['sede'], 
                              $teacher_id, $recorded_hours, $recordId, $data['course_id'], $data['class_date']);
            
            // Obtener student_id para registro existente
            $queryStudent = "SELECT student_id FROM attendance_records WHERE id = ?";
            $stmtStudent = $conn->prepare($queryStudent);
            if (!$stmtStudent) {
                throw new Exception("Error al preparar consulta de estudiante: " . $conn->error);
            }
            
            $stmtStudent->bind_param("i", $recordId);
            if (!$stmtStudent->execute()) {
                throw new Exception("Error al ejecutar consulta de estudiante: " . $stmtStudent->error);
            }
            
            $resultStudent = $stmtStudent->get_result();
            if ($row = $resultStudent->fetch_assoc()) {
                $student_id = $row['student_id'];
            }
            $stmtStudent->close();
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la operación: " . $stmt->error);
        }
        
        $stmt->close();
        
        // 2. Actualizar la intensidad horaria solo si el estudiante está presente o tarde
        if (($status === 'presente' || $status === 'tarde') && !empty($student_id)) {
            // Obtener la intensidad actual del estudiante
            $check_sql = "SELECT $intensity_column FROM groups WHERE number_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            
            if (!$check_stmt) {
                throw new Exception('Error al preparar la consulta de verificación: ' . $conn->error);
            }
            
            $check_stmt->bind_param("i", $student_id);
            
            if (!$check_stmt->execute()) {
                throw new Exception('Error al ejecutar la verificación: ' . $check_stmt->error);
            }
            
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Obtener valor actual o inicializar en 0
            $current_intensity = ($row && isset($row[$intensity_column])) ? intval($row[$intensity_column]) : 0;
            
            // Calcular nueva intensidad
            $new_intensity = $current_intensity + $intensity;
            
            // Actualizar intensidad en groups
            $update_sql = "UPDATE groups SET $intensity_column = ? WHERE number_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            
            if (!$update_stmt) {
                throw new Exception('Error al preparar la actualización: ' . $conn->error);
            }
            
            $update_stmt->bind_param("ii", $new_intensity, $student_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception(
                    "Error al actualizar intensidad para estudiante $student_id. " .
                    "Actual: $current_intensity, " .
                    "Incremento: $intensity, " .
                    "Nuevo: $new_intensity. " .
                    "Error: " . $update_stmt->error
                );
            }
            
            $update_stmt->close();
            $check_stmt->close();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error en guardar_asistencia_individual.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}