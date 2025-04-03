<?php
// Iniciar sesión si no está iniciada
session_start();

// Verificar que se haya recibido el ID del usuario
if (!isset($_POST['number_id']) || empty($_POST['number_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$number_id = $_POST['number_id'];

// Incluir archivo de configuración de base de datos
require  '../../controller/conexion.php';

try {
    // 1. Obtener información del usuario desde la tabla groups
    $stmt = $conn->prepare("SELECT institutional_email FROM groups WHERE number_id = ?");
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

    // 4.1 Eliminar de la tabla groups
    $deleteStmt = $conn->prepare("DELETE FROM groups WHERE number_id = ?");
    $deleteStmt->bind_param("s", $number_id);
    $deleteStmt->execute();

    // 4.2 Actualizar statusAdmin a 1 en la tabla user_register
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

    // 4.3 Eliminar registros de asistencia
    $deleteAttendanceStmt = $conn->prepare("DELETE FROM attendance_records WHERE student_id = ?");
    $deleteAttendanceStmt->bind_param("s", $number_id);
    $deleteAttendanceStmt->execute();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente de Moodle y registros actualizados']);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
