<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar que se recibieron los parámetros necesarios
if (!isset($_POST['student_id']) || !isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros necesarios']);
    exit;
}

$studentId = $_POST['student_id'];
$courseId = $_POST['course_id'];

try {
    // 1. Obtener el total de clases para el curso hasta la fecha actual
    $sqlTotalClasses = "SELECT COUNT(DISTINCT class_date) AS total_classes
                         FROM attendance_records
                         WHERE course_id = ? AND class_date <= CURRENT_DATE()";
    
    $stmtClasses = $conn->prepare($sqlTotalClasses);
    $stmtClasses->bind_param('i', $courseId);
    $stmtClasses->execute();
    $resultClasses = $stmtClasses->get_result();
    $rowClasses = $resultClasses->fetch_assoc();
    
    $totalClasses = $rowClasses['total_classes'];
    
    // 2. Obtener el número de asistencias (presente o tarde) del estudiante
    $sqlAttendance = "SELECT COUNT(*) AS total_attendance
                      FROM attendance_records
                      WHERE student_id = ? 
                      AND course_id = ?
                      AND class_date <= CURRENT_DATE() 
                      AND (attendance_status = 'presente' OR attendance_status = 'tarde')";
    
    $stmtAttendance = $conn->prepare($sqlAttendance);
    $stmtAttendance->bind_param('si', $studentId, $courseId);
    $stmtAttendance->execute();
    $resultAttendance = $stmtAttendance->get_result();
    $rowAttendance = $resultAttendance->fetch_assoc();
    
    $totalAttendance = $rowAttendance['total_attendance'];
    
    // 3. Obtener el número de inasistencias (solo cuando el status es 'ausente')
    $sqlAbsences = "SELECT COUNT(*) AS total_absences
                    FROM attendance_records
                    WHERE student_id = ? 
                    AND course_id = ?
                    AND class_date <= CURRENT_DATE() 
                    AND attendance_status = 'ausente'";
    
    $stmtAbsences = $conn->prepare($sqlAbsences);
    $stmtAbsences->bind_param('si', $studentId, $courseId);
    $stmtAbsences->execute();
    $resultAbsences = $stmtAbsences->get_result();
    $rowAbsences = $resultAbsences->fetch_assoc();
    
    $totalAbsences = $rowAbsences['total_absences'];
    
    // Calcular porcentajes basados en el total de registros (no en total de clases)
    $totalRecordsForStudent = $totalAttendance + $totalAbsences;
    
    if ($totalRecordsForStudent > 0) {
        $attendancePercentage = round(($totalAttendance / $totalRecordsForStudent) * 100, 1);
        $absencePercentage = round(($totalAbsences / $totalRecordsForStudent) * 100, 1);
    } else {
        $attendancePercentage = 0;
        $absencePercentage = 0;
    }
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => [
            'totalClasses' => $totalClasses,
            'totalAttendance' => $totalAttendance,
            'totalAbsences' => $totalAbsences,
            'totalRecordsForStudent' => $totalRecordsForStudent,
            'attendancePercentage' => $attendancePercentage,
            'absencePercentage' => $absencePercentage,
            'absencesDisplay' => $totalAbsences . '/' . $totalClasses
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estadísticas de asistencia: ' . $e->getMessage()
    ]);
}
?>