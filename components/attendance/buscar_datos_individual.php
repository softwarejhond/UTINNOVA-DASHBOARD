<?php
// Asegurarnos de que no haya salida antes del JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

$student_id = $_POST['student_id'] ?? '';
$course_id = $_POST['bootcamp'] ?? '';
$class_date = $_POST['class_date'] ?? '';

if (empty($student_id) || empty($course_id) || empty($class_date)) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Obtener el día de la semana de la fecha seleccionada
    $dayOfWeek = date('N', strtotime($class_date));
    
    // Seleccionar la columna correcta según el día
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

    // Obtener las horas programadas para ese día
    $session_hours_query = "SELECT $hours_column as daily_hours FROM courses WHERE code = ?";
    $stmt_hours = $conn->prepare($session_hours_query);
    
    if (!$stmt_hours) {
        throw new Exception("Error en la preparación de la consulta para horas diarias: " . $conn->error);
    }
    
    $stmt_hours->bind_param("i", $course_id);
    
    if (!$stmt_hours->execute()) {
        throw new Exception("Error al ejecutar la consulta para horas diarias: " . $stmt_hours->error);
    }
    
    $result_hours = $stmt_hours->get_result();
    $session_hours = 0; // Valor por defecto
    
    if ($result_hours && $row_hours = $result_hours->fetch_assoc()) {
        $session_hours = intval($row_hours['daily_hours']);
        
        // Si no hay horas programadas para ese día, devolver error
        if ($session_hours === 0) {
            echo json_encode([
                'success' => false,
                'error' => 'No hay horas programadas para este día'
            ]);
            exit;
        }
    }
    
    $stmt_hours->close();

    // Modificamos la consulta para convertir los campos a UTF8 antes de comparar
    $query = "SELECT 
                g.number_id as student_id,
                g.full_name,
                ar.id,
                ar.attendance_status
              FROM groups g
              LEFT JOIN attendance_records ar ON 
                CONVERT(TRIM(g.number_id) USING utf8mb4) = CONVERT(TRIM(ar.student_id) USING utf8mb4) AND 
                ar.course_id = ? AND 
                ar.class_date = ?
              WHERE CONVERT(TRIM(g.number_id) USING utf8mb4) = CONVERT(TRIM(?) USING utf8mb4)";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("iss", $course_id, $class_date, $student_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $output = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . htmlspecialchars($row['student_id']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['full_name'] ?? 'No disponible') . '</td>';
            
            // Determinamos si es un nuevo registro
            $esNuevoRegistro = empty($row['id']);
            $estadoActual = $esNuevoRegistro ? 'ausente' : $row['attendance_status'];
            
            // Opciones de asistencia
            $estados = ['presente', 'tarde', 'ausente'];
            foreach ($estados as $estado) {
                // Para nuevos registros o ausente/tarde, habilitamos la edición
                $disabled = (!$esNuevoRegistro && 
                            $estadoActual !== 'ausente' && 
                            $estadoActual !== 'tarde') ? 'disabled' : '';
                $checked = ($estadoActual === $estado) ? 'checked' : '';
                
                $output .= '<td>
                    <input type="radio" 
                           name="attendance_' . ($esNuevoRegistro ? 'nuevo_'.$row['student_id'] : $row['id']) . '"
                           value="' . $estado . '"
                           data-record-id="' . ($esNuevoRegistro ? 'nuevo_'.$row['student_id'] : $row['id']) . '"
                           data-estado="' . $estado . '"
                           data-es-nuevo="' . ($esNuevoRegistro ? '1' : '0') . '"
                           data-student-id="' . $row['student_id'] . '"
                           ' . $checked . '
                           ' . $disabled . '>
                </td>';
            }
            $output .= '</tr>';
        }
    } else {
        $output = '<tr><td colspan="6" class="text-center">No se encontró información del estudiante</td></tr>';
    }
    
    // Devolver tanto el HTML como el valor de session_hours
    echo json_encode([
        'success' => true, 
        'html' => $output,
        'session_hours' => $session_hours
    ]);
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en buscar_datos_individual.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}