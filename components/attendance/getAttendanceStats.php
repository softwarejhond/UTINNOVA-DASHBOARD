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
    
    // Calcular porcentajes
    if ($totalClasses > 0) {
        $attendancePercentage = round(($totalAttendance / $totalClasses) * 100, 1);
        $absencePercentage = round(100 - $attendancePercentage, 1);
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
            'attendancePercentage' => $attendancePercentage,
            'absencePercentage' => $absencePercentage
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