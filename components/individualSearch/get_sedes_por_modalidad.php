<?php
include_once('../../controller/conexion.php');

header('Content-Type: application/json');

// Agregar logging para debug
error_log("get_sedes_por_modalidad.php - Datos recibidos: " . json_encode($_POST));

if (!isset($_POST['mode'])) {
    echo json_encode(['success' => false, 'message' => 'Modalidad no proporcionada']);
    exit;
}

$mode = $_POST['mode'];

try {
    $sql = "SELECT DISTINCT name FROM headquarters WHERE mode = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $mode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sedes = [];
    while ($row = $result->fetch_assoc()) {
        $sedes[] = $row;
    }
    
    // Log para debug
    error_log("Sedes encontradas para modalidad '$mode': " . count($sedes));
    
    echo json_encode([
        'success' => true,
        'data' => $sedes
    ]);
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error en get_sedes_por_modalidad.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>