<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Agregar log para debug
error_log("Aprobar estudiante - Datos recibidos: " . print_r($_POST, true));

// Verificar que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    error_log("Error: Usuario no autenticado");
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$studentId = isset($_POST['studentId']) ? $_POST['studentId'] : null;
$courseCode = isset($_POST['courseCode']) ? $_POST['courseCode'] : null;
$approvedBy = $_SESSION['username'];

error_log("StudentId: $studentId, CourseCode: $courseCode, ApprovedBy: $approvedBy");

if (!$studentId || !$courseCode) {
    error_log("Error: Faltan datos requeridos");
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Función para obtener las notas desde Moodle (copia de obtenerNotaFinal de buscar_aprovados.php)
function obtenerNotasEstudiante($conn, $studentId, $courseCode) {
    // Configuración básica para la API de Moodle
    $apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
    $token = '3f158134506350615397c83d861c2104';
    $format = 'json';
    
    // Paso 1: Obtener el userid a partir del número de identificación (username)
    $functionGetUser = 'core_user_get_users_by_field';
    
    // Parámetros para buscar usuario
    $paramsUser = [
        'field' => 'username',
        'values[0]' => $studentId
    ];
    
    $postdataUser = http_build_query([
        'wstoken' => $token,
        'wsfunction' => $functionGetUser,
        'moodlewsrestformat' => $format
    ] + $paramsUser);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdataUser);
    
    $responseUser = curl_exec($ch);
    $userData = json_decode($responseUser, true);
    
    if (empty($userData)) {
        curl_close($ch);
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
    
    // Obtener el userid del primer usuario encontrado
    $userid = $userData[0]['id'];
    
    // Paso 2: Obtener las notas usando el userid encontrado
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
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    
    $response = curl_exec($ch);
    
    if ($response === false) {
        curl_close($ch);
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        curl_close($ch);
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
    
    curl_close($ch);
    
    // Procesar las notas
    if (isset($data['usergrades'][0])) {
        $usergrade = $data['usergrades'][0];
        $notas = [];
        $sumaNotas = 0;
        
        if (isset($usergrade['gradeitems'])) {
            foreach ($usergrade['gradeitems'] as $item) {
                if (
                    (isset($item['itemtype']) && $item['itemtype'] === 'course') ||
                    (isset($item['graderaw']) && $item['graderaw'] !== null)
                ) {
                    $notaRaw = isset($item['graderaw']) ? $item['graderaw'] : null;
                    $grademax = isset($item['grademax']) ? $item['grademax'] : 5.0;
                    
                    if ($notaRaw !== null && $grademax > 0) {
                        // Convertir la nota a escala 5.0 estándar
                        $notaNormalizada = ($notaRaw / $grademax) * 5.0;
                        $notas[] = $notaNormalizada;
                        $sumaNotas += $notaNormalizada;
                    }
                }
                if (count($notas) == 2) break; // Solo las dos primeras notas
            }
        }
        
        // Asegurarnos de tener los valores de las notas (incluso si faltan)
        $grade1 = isset($notas[0]) ? $notas[0] : 0;
        $grade2 = isset($notas[1]) ? $notas[1] : 0;
        
        // CAMBIO AQUÍ: Siempre dividir por 2, sin importar cuántas notas haya
        $notaFinal = round(($grade1 + $grade2) / 2, 2);
        
        return [
            'final' => $notaFinal,
            'grade1' => $grade1,
            'grade2' => $grade2
        ];
    }
    
    return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
}

try {
    // Iniciar transacción para asegurar consistencia
    $conn->autocommit(false);
    
    // Verificar si ya está aprobado
    $checkSql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $studentId, $courseCode);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->rollback();
        $conn->autocommit(true);
        echo json_encode(['success' => false, 'message' => 'El estudiante ya está aprobado']);
        exit;
    }
    $checkStmt->close();
    
    // Obtener las notas del estudiante
    $notasData = obtenerNotasEstudiante($conn, $studentId, $courseCode);
    $notaFinal = $notasData['final'];
    $nota1 = $notasData['grade1'];
    $nota2 = $notasData['grade2'];
    
    // Insertar la nueva aprobación incluyendo las notas
    $insertSql = "INSERT INTO course_approvals (course_code, student_number_id, approved_by, created_at, final_grade, grade_1, grade_2) 
                  VALUES (?, ?, ?, NOW(), ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    
    if (!$insertStmt) {
        throw new Exception('Error en la preparación de inserción: ' . $conn->error);
    }
    
    $insertStmt->bind_param("sssddd", $courseCode, $studentId, $approvedBy, $notaFinal, $nota1, $nota2);
    
    if (!$insertStmt->execute()) {
        throw new Exception('Error al aprobar estudiante: ' . $insertStmt->error);
    }
    
    $approvalId = $insertStmt->insert_id;
    $insertStmt->close();
    
    error_log("Aprobación insertada con ID: $approvalId");
    
    // Actualizar status_admin en user_register
    $updateSql = "UPDATE user_register SET statusAdmin = 10 WHERE number_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    if (!$updateStmt) {
        throw new Exception('Error en la preparación de actualización: ' . $conn->error);
    }
    
    $updateStmt->bind_param("s", $studentId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Error al actualizar status_admin: ' . $updateStmt->error);
    }
    
    $affectedRows = $updateStmt->affected_rows;
    $updateStmt->close();
    
    error_log("Status_admin actualizado. Filas afectadas: $affectedRows");
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Estudiante aprobado exitosamente',
        'approvalId' => $approvalId,
        'approved_by' => $approvedBy,
        'timestamp' => date('Y-m-d H:i:s'),
        'status_updated' => $affectedRows > 0 ? true : false,
        'affected_rows' => $affectedRows,
        'grades' => [
            'final' => $notaFinal,
            'grade1' => $nota1,
            'grade2' => $nota2
        ]
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    error_log("Error en aprobar_estudiante.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>