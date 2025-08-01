<?php
// Iniciar sesión si no está iniciada
session_start();

// Obtener el contenido JSON de la solicitud
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['number_id'])) {
    // Si viene en formato JSON (nueva implementación)
    $number_id = $input['number_id'];
} else if (isset($_POST['number_id'])) {
    // Mantener compatibilidad con la versión anterior
    $number_id = $_POST['number_id'];
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

// Incluir archivo de configuración de base de datos
require  '../../controller/conexion.php';

try {
    // 1. Obtener información completa del usuario desde la tabla groups ANTES de eliminarlo
    $stmt = $conn->prepare("SELECT 
        type_id, number_id, full_name, email, institutional_email, 
        department, headquarters, program, mode,
        id_bootcamp, bootcamp_name,
        id_leveling_english, leveling_english_name,
        id_english_code, english_code_name,
        id_skills, skills_name,
        creation_date
        FROM groups WHERE number_id = ?");
    $stmt->bind_param("s", $number_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    $userInfo = $result->fetch_assoc();
    $institutional_email = $userInfo['institutional_email'];

    // 2. Obtener el usuario ID de Moodle mediante la API
    $apiUrl = "https://talento-tech.uttalento.co/webservice/rest/server.php";
    $token   = "3f158134506350615397c83d861c2104";
    $format  = "json";

    // Buscar el usuario en Moodle por su correo institucional
    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_user_get_users_by_field',
        'moodlewsrestformat' => $format,
        'field' => 'email',
        'values[0]' => $institutional_email
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);

    if (curl_error($ch)) {
        echo json_encode(['success' => false, 'message' => 'Error al comunicarse con Moodle: ' . curl_error($ch)]);
        exit;
    }

    $userData = json_decode($response, true);

    if (empty($userData) || !isset($userData[0]['id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en Moodle']);
        exit;
    }

    $moodleUserId = $userData[0]['id'];

    // 3. Eliminar usuario de Moodle
    $deleteParams = [
        'wstoken' => $token,
        'wsfunction' => 'core_user_delete_users',
        'moodlewsrestformat' => $format,
        'userids[0]' => $moodleUserId
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($deleteParams),
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $deleteResponse = curl_exec($ch);

    if (curl_error($ch)) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario de Moodle: ' . curl_error($ch)]);
        exit;
    }

    curl_close($ch);

    // Verificar respuesta de eliminación de Moodle (null significa éxito)
    $deleteResult = json_decode($deleteResponse, true);
    if ($deleteResult !== null && isset($deleteResult['exception'])) {
        echo json_encode(['success' => false, 'message' => 'Error en API de Moodle: ' . ($deleteResult['message'] ?? 'Error desconocido')]);
        exit;
    }

    // 4. Actualizar registros en la base de datos local
    $conn->begin_transaction();

    // 4.1 Guardar el historial de matrícula ANTES de eliminar el registro
    // Establecer zona horaria de Colombia
    date_default_timezone_set('America/Bogota');
    $unenrollment_date = date('Y-m-d H:i:s');
    $username = $_SESSION['username'] ?? 'Sistema';

    $historyStmt = $conn->prepare("INSERT INTO enrollment_history (
        type_id, number_id, full_name, email, institutional_email,
        department, headquarters, program, mode,
        id_bootcamp, bootcamp_name,
        id_leveling_english, leveling_english_name,
        id_english_code, english_code_name,
        id_skills, skills_name,
        enrollment_date, unenrollment_date, unenrolled_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $historyStmt->bind_param("sisssssssisisissssss",
        $userInfo['type_id'],
        $userInfo['number_id'],
        $userInfo['full_name'],
        $userInfo['email'],
        $userInfo['institutional_email'],
        $userInfo['department'],
        $userInfo['headquarters'],
        $userInfo['program'],
        $userInfo['mode'],
        $userInfo['id_bootcamp'],
        $userInfo['bootcamp_name'],
        $userInfo['id_leveling_english'],
        $userInfo['leveling_english_name'],
        $userInfo['id_english_code'],
        $userInfo['english_code_name'],
        $userInfo['id_skills'],
        $userInfo['skills_name'],
        $userInfo['creation_date'],
        $unenrollment_date,
        $username
    );

    if (!$historyStmt->execute()) {
        throw new Exception("Error al guardar el historial de matrícula: " . $historyStmt->error);
    }
    $historyStmt->close();

    // 4.2.1 Eliminar registros de asistencia del curso técnico (attendance_records) ANTES de eliminar de groups
    // Antes del borrado
    error_log("Intentando borrar asistencia: student_id=$number_id, course_id={$userInfo['id_bootcamp']}");

    if (!empty($userInfo['id_bootcamp'])) {
        $deleteAttendanceStmt = $conn->prepare("DELETE FROM attendance_records WHERE student_id = ? AND course_id = ?");
        $deleteAttendanceStmt->bind_param("ss", $number_id, $userInfo['id_bootcamp']);
        $deleteAttendanceStmt->execute();
        error_log("Filas afectadas: " . $deleteAttendanceStmt->affected_rows);
        $deleteAttendanceStmt->close();
    }

    // 4.2 Eliminar de la tabla groups
    $deleteStmt = $conn->prepare("DELETE FROM groups WHERE number_id = ?");
    $deleteStmt->bind_param("s", $number_id);
    $deleteStmt->execute();
    $deleteStmt->close();

    // 4.3 Actualizar statusAdmin a 1 en la tabla user_register
    $updateStmt = $conn->prepare("UPDATE user_register SET statusAdmin = 1 WHERE number_id = ?");
    $updateStmt->bind_param("s", $number_id);
    $updateStmt->execute();

    // Verificar que el cambio se haya aplicado correctamente
    $verifyStmt = $conn->prepare("SELECT statusAdmin FROM user_register WHERE number_id = ?");
    $verifyStmt->bind_param("s", $number_id);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    $row = $verifyResult->fetch_assoc();

    // Si el valor no es 1, intentar actualizarlo nuevamente con fuerza
    if ($row && $row['statusAdmin'] != 1) {
        // Forzar la actualización con una segunda consulta más directa
        $forceUpdateStmt = $conn->prepare("UPDATE user_register SET statusAdmin = 1 WHERE number_id = ? LIMIT 1");
        $forceUpdateStmt->bind_param("s", $number_id);
        $forceUpdateStmt->execute();
        $forceUpdateStmt->close();
    }

    $verifyStmt->close();

    // Registrar en el historial de cambios
    $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
    $stmtHistorial = $conn->prepare($historialSql);
    
    if ($stmtHistorial) {
        // Verificar si la solicitud viene de una desmatriculación múltiple
        $descripcion = isset($input['isMultiple']) && $input['isMultiple'] === true 
            ? "Se elimina matrícula de Moodle (Desmatricula multiple)"
            : "Se elimina matrícula de Moodle";
        
        $stmtHistorial->bind_param('iss', $number_id, $username, $descripcion);
        
        if (!$stmtHistorial->execute()) {
            throw new Exception("Error al registrar el historial");
        }
        $stmtHistorial->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Usuario eliminado correctamente y historial guardado',
        'details' => [
            'number_id' => $number_id,
            'institutional_email' => $institutional_email,
            'moodle_user_id' => $moodleUserId,
            'unenrollment_date' => $unenrollment_date,
            'unenrolled_by' => $username
        ]
    ]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'details' => [
            'number_id' => $number_id ?? null,
            'error_code' => $e->getCode(),
            'error_trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
