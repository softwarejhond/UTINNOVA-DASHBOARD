<?php
session_start();
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $period_id = intval($_POST['period_id'] ?? 0);
        
        if ($period_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de período inválido']);
            exit;
        }

        // Consulta para obtener los detalles del período
        $sql = "SELECT * FROM course_periods WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $period = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'data' => $period
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Período no encontrado']);
        }
        
        $stmt->close();

    } catch (Exception $e) {
        error_log("Error en get_period_details.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>