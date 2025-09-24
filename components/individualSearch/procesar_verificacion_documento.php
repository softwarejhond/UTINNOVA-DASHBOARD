<?php
session_start();
include_once('../../controller/conexion.php');

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $numberId = $_POST['numberId'] ?? '';
    $verifiedBy = $_SESSION['username'];
    
    // Obtener datos del formulario
    $primerNombre = trim($_POST['primerNombre'] ?? '');
    $segundoNombre = trim($_POST['segundoNombre'] ?? '');
    $primerApellido = trim($_POST['primerApellido'] ?? '');
    $segundoApellido = trim($_POST['segundoApellido'] ?? '');
    $numeroDocumento = trim($_POST['numeroDocumento'] ?? '');
    $fechaNacimiento = $_POST['fechaNacimiento'] ?? '';
    $tipoDocumento = $_POST['tipoDocumento'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Obtener estados de verificación
    $nombreCoincide = isset($_POST['nombreCoincide']) ? 1 : 0;
    $documentoCoincide = isset($_POST['documentoCoincide']) ? 1 : 0;
    $fechaCoincide = isset($_POST['fechaCoincide']) ? 1 : 0;
    $tipoCoincide = isset($_POST['tipoCoincide']) ? 1 : 0;
    
    if (empty($numberId)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
        exit;
    }

    // Validaciones básicas
    if (empty($primerNombre) || empty($primerApellido)) {
        echo json_encode(['success' => false, 'message' => 'Primer nombre y primer apellido son obligatorios']);
        exit;
    }

    if (empty($numeroDocumento) || !is_numeric($numeroDocumento)) {
        echo json_encode(['success' => false, 'message' => 'Número de documento inválido']);
        exit;
    }

    if (!in_array($tipoDocumento, ['CC', 'Otra'])) {
        echo json_encode(['success' => false, 'message' => 'Tipo de documento inválido']);
        exit;
    }

    // Verificar que el usuario existe
    $stmt = $conn->prepare("SELECT id, number_id FROM user_register WHERE number_id = ?");
    $stmt->bind_param("s", $numberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    $userData = $result->fetch_assoc();
    $userId = $userData['id'];
    $stmt->close();

    // Iniciar transacción
    $conn->autocommit(false);

    try {
        // 1. Consultar el estado actual de verificación
        $stmt = $conn->prepare("SELECT name_verified, document_number_verified, birth_date_verified, document_type_verified FROM document_verifications WHERE number_id = ?");
        $stmt->bind_param("s", $numberId);
        $stmt->execute();
        $currentResult = $stmt->get_result();
        $current = [
            'name_verified' => 0,
            'document_number_verified' => 0,
            'birth_date_verified' => 0,
            'document_type_verified' => 0
        ];
        if ($currentResult->num_rows > 0) {
            $current = $currentResult->fetch_assoc();
        }
        $stmt->close();

        // 2. Solo actualizar si el checkbox no está deshabilitado (es decir, si el usuario lo marcó)
        $nombreCoincideFinal = ($current['name_verified']) ? 1 : $nombreCoincide;
        $documentoCoincideFinal = ($current['document_number_verified']) ? 1 : $documentoCoincide;
        $fechaCoincideFinal = ($current['birth_date_verified']) ? 1 : $fechaCoincide;
        $tipoCoincideFinal = ($current['document_type_verified']) ? 1 : $tipoCoincide;

        // 3. Guardar verificación en tabla de verificaciones
        $stmt = $conn->prepare("
            INSERT INTO document_verifications 
            (number_id, name_verified, document_number_verified, birth_date_verified, document_type_verified, verified_by, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            name_verified = VALUES(name_verified),
            document_number_verified = VALUES(document_number_verified),
            birth_date_verified = VALUES(birth_date_verified),
            document_type_verified = VALUES(document_type_verified),
            verified_by = VALUES(verified_by),
            verification_date = CURRENT_TIMESTAMP,
            notes = VALUES(notes)
        ");
        
        $stmt->bind_param("siiiiss", 
            $numberId, 
            $nombreCoincideFinal, 
            $documentoCoincideFinal, 
            $fechaCoincideFinal, 
            $tipoCoincideFinal, 
            $verifiedBy, 
            $observaciones
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar verificación: " . $stmt->error);
        }
        $stmt->close();

        $updateFields = [];
        $updateValues = [];
        $updateTypes = "";
        $cambiosRealizados = [];

        // 2. Actualizar datos en user_register si es necesario
        
        // Verificar si el número de documento cambió
        if ($numeroDocumento !== $numberId) {
            // Verificar que el nuevo número no esté en uso
            $stmt = $conn->prepare("SELECT id FROM user_register WHERE number_id = ? AND id != ?");
            $stmt->bind_param("si", $numeroDocumento, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception("El número de documento ya está siendo usado por otro usuario");
            }
            $stmt->close();
            
            $updateFields[] = "number_id = ?";
            $updateValues[] = $numeroDocumento;
            $updateTypes .= "s";
            $cambiosRealizados[] = "número de documento";
        }

        // Actualizar nombres
        $updateFields[] = "first_name = ?";
        $updateValues[] = strtoupper($primerNombre);
        $updateTypes .= "s";
        
        $updateFields[] = "second_name = ?";
        $updateValues[] = strtoupper($segundoNombre);
        $updateTypes .= "s";
        
        $updateFields[] = "first_last = ?";
        $updateValues[] = strtoupper($primerApellido);
        $updateTypes .= "s";
        
        $updateFields[] = "second_last = ?";
        $updateValues[] = strtoupper($segundoApellido);
        $updateTypes .= "s";
        
        $cambiosRealizados[] = "nombres y apellidos";

        // Actualizar fecha de nacimiento
        $updateFields[] = "birthdate = ?";
        $updateValues[] = $fechaNacimiento;
        $updateTypes .= "s";
        $cambiosRealizados[] = "fecha de nacimiento";

        // Actualizar tipo de documento
        $updateFields[] = "typeID = ?";
        $updateValues[] = $tipoDocumento;
        $updateTypes .= "s";
        $cambiosRealizados[] = "tipo de documento";

        // Actualizar fecha de modificación
        $updateFields[] = "dayUpdate = NOW()";

        // Preparar y ejecutar la actualización
        if (!empty($updateFields)) {
            $sql = "UPDATE user_register SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $updateValues[] = $userId;
            $updateTypes .= "i";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($updateTypes, ...$updateValues);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar datos del usuario: " . $stmt->error);
            }
            $stmt->close();
        }

        // 3. Si cambió el número de documento, actualizar tablas relacionadas
        if ($numeroDocumento !== $numberId) {
            // Actualizar course_assignments
            $stmt = $conn->prepare("UPDATE course_assignments SET student_id = ? WHERE student_id = ?");
            $stmt->bind_param("ss", $numeroDocumento, $numberId);
            $stmt->execute();
            $stmt->close();

            // Actualizar contact_log
            $stmt = $conn->prepare("UPDATE contact_log SET number_id = ? WHERE number_id = ?");
            $stmt->bind_param("ss", $numeroDocumento, $numberId);
            $stmt->execute();
            $stmt->close();
        }

        // Confirmar transacción
        $conn->commit();
        
        $message = "Verificación completada exitosamente.";
        if (!empty($cambiosRealizados)) {
            $message .= " Se actualizaron: " . implode(", ", $cambiosRealizados) . ".";
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'verificaciones' => [
                'nombre' => $nombreCoincide,
                'documento' => $documentoCoincide,
                'fecha' => $fechaCoincide,
                'tipo' => $tipoCoincide
            ]
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción
        $conn->rollback();
        throw $e;
    }
    
    $conn->autocommit(true);

} catch (Exception $e) {
    error_log("Error en verificación de documento: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>