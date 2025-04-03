<?php
include_once '../../controller/conexion.php';

header('Content-Type: application/json');

try {
    // Validar datos recibidos
    if (!isset($_POST['id_curso'], $_POST['mentor'], $_POST['tipo_campo'])) {
        throw new Exception("Datos incompletos");
    }

    $id_curso = (int)$_POST['id_curso'];
    $mentorUsername = $_POST['mentor'];
    $tipo_campo = $_POST['tipo_campo'];

    // Mapeo de campos válidos
    $camposPermitidos = [
        'bootcamp_mentor_id' => 'id_bootcamp',
        'le_mentor_id' => 'id_leveling_english',
        'ec_mentor_id' => 'id_english_code',
        'skills_mentor_id' => 'id_skills'
    ];

    if (!array_key_exists($tipo_campo, $camposPermitidos)) {
        throw new Exception("Tipo de campo inválido");
    }

    // Obtener ID del mentor
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? AND rol = 8");
    $stmt->bind_param("s", $mentorUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("mentor no encontrado");
    }
    
    $mentorData = $result->fetch_assoc();
    $mentorId = $mentorData['username'];

    // Actualizar la tabla groups
    $campoGrupo = $camposPermitidos[$tipo_campo];
    $sql = "UPDATE groups SET {$tipo_campo} = ? WHERE {$campoGrupo} = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $mentorId, $id_curso);
    
    if (!$stmt->execute()) {
        throw new Exception("Error en la actualización: " . $stmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'mentor actualizado correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();