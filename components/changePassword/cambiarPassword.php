<?php
session_start();
header('Content-Type: application/json');

// Incluir archivo de configuración de base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Obtener datos del POST
$number_id = isset($_POST['number_id']) ? intval($_POST['number_id']) : 0;
$nueva_password = isset($_POST['nueva_password']) ? trim($_POST['nueva_password']) : '';
$confirmar_password = isset($_POST['confirmar_password']) ? trim($_POST['confirmar_password']) : '';

// Validaciones básicas
if ($number_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Número de identificación inválido']);
    exit;
}

if (empty($nueva_password) || empty($confirmar_password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if ($nueva_password !== $confirmar_password) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($nueva_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

try {
    // 1. Obtener el correo institucional del usuario desde la tabla groups
    $stmt = $conn->prepare("SELECT institutional_email, full_name FROM groups WHERE number_id = ?");
    $stmt->bind_param("i", $number_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en la base de datos']);
        exit;
    }

    $userInfo = $result->fetch_assoc();
    $institutional_email = $userInfo['institutional_email'];
    $full_name = $userInfo['full_name'];

    // 2. Configuración de la API de Moodle
    $apiUrl = "https://talento-tech.uttalento.co/webservice/rest/server.php";
    $token = "3f158134506350615397c83d861c2104";
    $format = "json";

    // 3. Buscar el usuario en Moodle por su correo institucional
    $searchParams = [
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
        CURLOPT_POSTFIELDS => http_build_query($searchParams),
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $searchResponse = curl_exec($ch);

    if (curl_error($ch)) {
        echo json_encode(['success' => false, 'message' => 'Error al comunicarse con Moodle: ' . curl_error($ch)]);
        exit;
    }

    $userData = json_decode($searchResponse, true);

    if (empty($userData) || !isset($userData[0]['id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en Moodle']);
        exit;
    }

    $moodleUserId = $userData[0]['id'];

    // 4. Actualizar la contraseña en Moodle
    $updateParams = [
        'wstoken' => $token,
        'wsfunction' => 'core_user_update_users',
        'moodlewsrestformat' => $format,
        'users[0][id]' => $moodleUserId,
        'users[0][password]' => $nueva_password
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($updateParams),
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $updateResponse = curl_exec($ch);

    if (curl_error($ch)) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar contraseña en Moodle: ' . curl_error($ch)]);
        exit;
    }

    curl_close($ch);

    // Verificar respuesta de actualización
    $updateResult = json_decode($updateResponse, true);
    
    if (isset($updateResult['exception'])) {
        echo json_encode(['success' => false, 'message' => 'Error en API de Moodle: ' . ($updateResult['message'] ?? 'Error desconocido')]);
        exit;
    }

    // 5. Actualizar la contraseña en la base de datos local (SIN HASHEAR)
    $updateLocalStmt = $conn->prepare("UPDATE groups SET password = ? WHERE number_id = ?");
    $updateLocalStmt->bind_param("si", $nueva_password, $number_id);
    $updateLocalStmt->execute();
    $updateLocalStmt->close();

    // 6. Registrar en el historial de cambios
    $username = $_SESSION['username'] ?? 'Sistema';
    $descripcion = "Cambio de contraseña realizado";
    
    $historialStmt = $conn->prepare("INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)");
    $historialStmt->bind_param('iss', $number_id, $username, $descripcion);
    $historialStmt->execute();
    $historialStmt->close();

    echo json_encode([
        'success' => true, 
        'message' => 'Contraseña actualizada correctamente',
        'details' => [
            'number_id' => $number_id,
            'full_name' => $full_name,
            'institutional_email' => $institutional_email,
            'moodle_user_id' => $moodleUserId,
            'changed_by' => $username
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>