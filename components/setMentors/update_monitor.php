<?php
include_once '../../controller/conexion.php';

header('Content-Type: application/json');

try {
    // Validar datos recibidos
    if (!isset($_POST['id_curso'], $_POST['monitor'], $_POST['tipo_campo'])) {
        throw new Exception("Datos incompletos");
    }

    $id_curso = (int)$_POST['id_curso'];
    $monitorUsername = $_POST['monitor'];
    $tipo_campo = $_POST['tipo_campo'];

    // Mapeo de campos válidos
    $camposPermitidos = [
        'bootcamp_monitor_id' => 'id_bootcamp',
        'le_monitor_id' => 'id_leveling_english',
        'ec_monitor_id' => 'id_english_code',
        'skills_monitor_id' => 'id_skills'
    ];

    if (!array_key_exists($tipo_campo, $camposPermitidos)) {
        throw new Exception("Tipo de campo inválido");
    }

    // Obtener ID del monitor
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? AND rol = 7");
    $stmt->bind_param("s", $monitorUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("monitor no encontrado");
    }
    
    $monitorData = $result->fetch_assoc();
    $monitorId = $monitorData['username'];

    // Actualizar la tabla groups
    $campoGrupo = $camposPermitidos[$tipo_campo];
    $sql = "UPDATE groups SET {$tipo_campo} = ? WHERE {$campoGrupo} = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $monitorId, $id_curso);
    
    if (!$stmt->execute()) {
        throw new Exception("Error en la actualización: " . $stmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'monitor actualizado correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();