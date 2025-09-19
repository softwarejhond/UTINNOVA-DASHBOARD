<?php
include_once('../../controller/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Función auxiliar para bind_param dinámico
function refValues($arr) {
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $refs = array();
        foreach($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    return $arr;
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Obtener los valores actuales
    $selectSql = "SELECT program, level, headquarters, lote, schedules, schedules_alternative FROM user_register WHERE number_id = ?";
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
    $nuevoHorarioPrincipal = isset($_POST['nuevoHorarioPrincipal']) ? trim($_POST['nuevoHorarioPrincipal']) : '';
    $nuevoHorarioAlternativo = isset($_POST['nuevoHorarioAlternativo']) ? trim($_POST['nuevoHorarioAlternativo']) : '';

    // Verificar si hay algún cambio real
    $hayChanges = ($nuevoPrograma !== $currentData['program']) || 
                  ($nuevoNivel !== $currentData['level']) || 
                  ($nuevoSede !== $currentData['headquarters']) || 
                  ($nuevoLote !== $currentData['lote']) ||
                  (!empty($nuevoHorarioPrincipal) && $nuevoHorarioPrincipal !== $currentData['schedules']) ||
                  (!empty($nuevoHorarioAlternativo) && $nuevoHorarioAlternativo !== $currentData['schedules_alternative']);

    if (!$hayChanges) {
        echo "no_changes";
        exit;
    }

    // Iniciar transacción para asegurar que ambas actualizaciones se completen
    $conn->begin_transaction();

    try {
        // Actualizar user_register (campos básicos)
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

        // Actualizar horarios si se proporcionaron
        if (!empty($nuevoHorarioPrincipal) || !empty($nuevoHorarioAlternativo)) {
            $updates = [];
            $params = [];
            $types = '';

            if (!empty($nuevoHorarioPrincipal)) {
                $updates[] = "schedules = ?";
                $params[] = $nuevoHorarioPrincipal;
                $types .= 's';
            }

            if (!empty($nuevoHorarioAlternativo)) {
                $updates[] = "schedules_alternative = ?";
                $params[] = $nuevoHorarioAlternativo;
                $types .= 's';
            }

            // Agregar el ID al final de los parámetros
            $params[] = $id;
            $types .= 's';

            $updateSchedulesSql = "UPDATE user_register SET " . implode(", ", $updates) . " WHERE number_id = ?";
            $stmtSchedules = $conn->prepare($updateSchedulesSql);
            
            if (!$stmtSchedules) {
                throw new Exception("Error al preparar la consulta de horarios: " . $conn->error);
            }

            // Usar bind_param con el número exacto de parámetros
            if (count($params) == 2) { // ID + 1 horario
                $stmtSchedules->bind_param($types, $params[0], $params[1]);
            } elseif (count($params) == 3) { // ID + 2 horarios
                $stmtSchedules->bind_param($types, $params[0], $params[1], $params[2]);
            }

            if (!$stmtSchedules->execute()) {
                throw new Exception("Error al ejecutar la consulta de horarios: " . $stmtSchedules->error);
            }
            
            $schedulesAffected = $stmtSchedules->affected_rows;
            $stmtSchedules->close();
            
            // Log para debugging
            error_log("Actualización de horarios - Filas afectadas: " . $schedulesAffected . " para ID: " . $id);
        }

        // Si se cambió la sede pero no se enviaron nuevos horarios, limpiar el horario alternativo
        if ($nuevoSede !== $currentData['headquarters'] && empty($nuevoHorarioAlternativo)) {
            $clearAltSql = "UPDATE user_register SET schedules_alternative = '' WHERE number_id = ?";
            $stmtClear = $conn->prepare($clearAltSql);
            $stmtClear->bind_param('s', $id);
            $stmtClear->execute();
            $stmtClear->close();
        }

        // Si llegamos aquí, todas las operaciones fueron exitosas
        $conn->commit();
        echo "success";
        
    } catch (Exception $e) {
        // Si hay algún error, revertir todos los cambios
        $conn->rollback();
        error_log("Error en la transacción: " . $e->getMessage());
        echo "error: " . $e->getMessage();
    }
    
} else {
    error_log("ID no enviado: " . json_encode($_POST));
    echo "invalid_data";
}
?>
