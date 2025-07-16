<?php
// Incluir archivo de conexión a la base de datos
include_once('../../controller/conexion.php');

// Configuración de cabeceras para AJAX
header('Content-Type: application/json');

// Verificar si hay una solicitud de búsqueda
if (!isset($_POST['number_id']) || empty($_POST['number_id'])) {
    echo json_encode(['success' => false, 'message' => 'Número de identificación requerido']);
    exit;
}

$number_id = $_POST['number_id'];

try {
    // Consulta SQL con LEFT JOIN para obtener los datos de groups y los datos relacionados de courses
    $sql = "SELECT 
                g.full_name, g.type_id, g.number_id, g.program, g.id_bootcamp, g.bootcamp_name, g.mode,
                g.email, 
                cp.start_date AS period_start_date, cp.end_date AS period_end_date,
                ur.schedules
            FROM groups g
            LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.number_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $number_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // --- NUEVO: Verificar si existe certificado ---
    $certificado_existente = false;
    $serie_certificado = null;
    if ($student) {
        $cert_sql = "SELECT serie_certificado FROM certificados_emitidos WHERE number_id = ? ORDER BY id DESC LIMIT 1";
        $cert_stmt = $conn->prepare($cert_sql);
        $cert_stmt->bind_param("i", $number_id);
        $cert_stmt->execute();
        $cert_stmt->bind_result($serie_certificado);
        if ($cert_stmt->fetch()) {
            // Verifica si el archivo existe en el servidor
            $isProduction = strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
            if ($isProduction) {
                $certFolder = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/certificados/';
            } else {
                $certFolder = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/certificados/';
            }
            if ($serie_certificado && file_exists($certFolder . $serie_certificado . '.pdf')) {
                $certificado_existente = true;
            }
        }
        $cert_stmt->close();

        $periodData = null;
        if (!empty($student['period_start_date'])) {
            $periodData = [
                'start_date' => $student['period_start_date'],
                'end_date' => $student['period_end_date'] ?? 'N/A'
            ];
            unset($student['period_start_date']);
            unset($student['period_end_date']);
        }
        echo json_encode([
            'success' => true, 
            'student' => $student,
            'period' => $periodData,
            'cert_exists' => $certificado_existente,
            'schedules' => $student['schedules'] ?? ''
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró ningún estudiante con ese número de identificación']);
    }
} catch (Exception $e) {
    error_log("Error en search_student.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}