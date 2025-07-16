<?php
include_once('../../controller/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Obtener los valores actuales
    $selectSql = "SELECT program, level, headquarters, lote FROM user_register WHERE number_id = ?";
    $stmt = $conn->prepare($selectSql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "user_not_found";
        exit;
    }
    
    $currentData = $result->fetch_assoc();
    $stmt->close();

    // Usar los valores enviados solo si no están vacíos, otherwise mantener los actuales
    $nuevoPrograma = !empty($_POST['nuevoPrograma']) ? $_POST['nuevoPrograma'] : $currentData['program'];
    $nuevoNivel = !empty($_POST['nuevoNivel']) ? $_POST['nuevoNivel'] : $currentData['level'];
    $nuevoSede = !empty($_POST['nuevoSede']) ? $_POST['nuevoSede'] : $currentData['headquarters'];
    $nuevoLote = !empty($_POST['nuevoLote']) ? $_POST['nuevoLote'] : $currentData['lote'];

    // Verificar si hay algún cambio real
    $hayChanges = ($nuevoPrograma !== $currentData['program']) || 
                  ($nuevoNivel !== $currentData['level']) || 
                  ($nuevoSede !== $currentData['headquarters']) || 
                  ($nuevoLote !== $currentData['lote']);

    if (!$hayChanges) {
        echo "no_changes";
        exit;
    }

    // Iniciar transacción para asegurar que ambas actualizaciones se completen
    $conn->begin_transaction();

    try {
        // Actualizar user_register
        $updateSql = "UPDATE user_register SET 
                      program = ?, 
                      level = ?, 
                      headquarters = ?, 
                      lote = ?,
                      dayUpdate = NOW() 
                      WHERE number_id = ?";
        $stmt = $conn->prepare($updateSql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta user_register: " . $conn->error);
        }
        
        $stmt->bind_param('sssss', $nuevoPrograma, $nuevoNivel, $nuevoSede, $nuevoLote, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta user_register: " . $stmt->error);
        }
        
        $userRegisterAffected = $stmt->affected_rows;
        $stmt->close();

        // Si se cambió la sede, también actualizar la tabla groups
        if ($nuevoSede !== $currentData['headquarters']) {
            $updateGroupsSql = "UPDATE groups SET headquarters = ? WHERE number_id = ?";
            $stmtGroups = $conn->prepare($updateGroupsSql);
            
            if (!$stmtGroups) {
                throw new Exception("Error al preparar la consulta groups: " . $conn->error);
            }
            
            $stmtGroups->bind_param('ss', $nuevoSede, $id);
            
            if (!$stmtGroups->execute()) {
                throw new Exception("Error al ejecutar la consulta groups: " . $stmtGroups->error);
            }
            
            $groupsAffected = $stmtGroups->affected_rows;
            $stmtGroups->close();
            
            // Log para debugging
            error_log("Actualización groups - Filas afectadas: " . $groupsAffected . " para ID: " . $id);
        }

        // Si llegamos aquí, todas las operaciones fueron exitosas
        $conn->commit();
        
        if ($userRegisterAffected > 0) {
            echo "success";
        } else {
            echo "no_changes";
        }
        
    } catch (Exception $e) {
        // Si hay algún error, revertir todos los cambios
        $conn->rollback();
        error_log("Error en la transacción: " . $e->getMessage());
        echo "error";
    }
    
} else {
    error_log("ID no enviado: " . json_encode($_POST));
    echo "invalid_data";
}
?>
