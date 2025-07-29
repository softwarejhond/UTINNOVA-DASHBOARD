<?php
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

$student_id        = $_POST['student_id'] ?? '';
$course_id         = $_POST['course_id'] ?? '';
$class_date        = $_POST['class_date'] ?? '';
$attendance_status = $_POST['attendance_status'] ?? '';
$modality          = $_POST['modality'] ?? '';
$sede              = $_POST['sede'] ?? '';

// Validar datos mínimos
if (!$student_id || !$course_id || !$class_date || !$attendance_status || !$modality || !$sede) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Obtener el teacher_id desde la tabla courses
$teacher_id = null;
$stmt_teacher = $conn->prepare("SELECT teacher FROM courses WHERE code = ?");
$stmt_teacher->bind_param("i", $course_id);
$stmt_teacher->execute();
$stmt_teacher->bind_result($teacher_id);
$stmt_teacher->fetch();
$stmt_teacher->close();

if (!$teacher_id) {
    echo json_encode(['success' => false, 'error' => 'No se encontró el docente para este curso']);
    exit;
}

// Verifica si ya existe un registro
$stmt = $conn->prepare("SELECT id FROM attendance_records WHERE student_id = ? AND course_id = ? AND class_date = ?");
$stmt->bind_param("sis", $student_id, $course_id, $class_date);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Actualiza el registro existente, incluyendo teacher_id, modality y sede
    $stmt_update = $conn->prepare("UPDATE attendance_records SET attendance_status = ?, modality = ?, sede = ?, teacher_id = ? WHERE student_id = ? AND course_id = ? AND class_date = ?");
    $stmt_update->bind_param("sssssis", $attendance_status, $modality, $sede, $teacher_id, $student_id, $course_id, $class_date);
    $stmt_update->execute();
    $stmt_update->close();
} else {
    // Inserta un nuevo registro, incluyendo teacher_id, modality y sede
    $stmt_insert = $conn->prepare("INSERT INTO attendance_records (student_id, course_id, class_date, attendance_status, modality, sede, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("sissssi", $student_id, $course_id, $class_date, $attendance_status, $modality, $sede, $teacher_id);
    $stmt_insert->execute();
    $stmt_insert->close();
}

$stmt->close();

echo json_encode(['success' => true]);