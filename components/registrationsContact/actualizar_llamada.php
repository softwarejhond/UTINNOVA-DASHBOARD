<?php
include_once '../../controller/conexion.php';

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asegurarse de que la conexión a la base de datos esté configurada
if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Verificar que los datos necesarios se hayan enviado mediante POST
if (isset($_POST['number_id']) && isset($_POST['idAdvisor']) && isset($_POST['details']) && 
    isset($_POST['contact_established']) && isset($_POST['continues_interested']) && 
    isset($_POST['observation'])) {
    
    $number_id = $_POST['number_id'];
    $idAdvisor = $_POST['idAdvisor'];
    $details = $_POST['details'];
    $contact_established = intval($_POST['contact_established']); // Convertir a entero
    $continues_interested = intval($_POST['continues_interested']); // Convertir a entero
    $observation = $_POST['observation'];

    // Verificar que el number_id sea un número entero
    if (!is_numeric($number_id)) {
        echo "invalid_data"; // Si el number_id no es válido
        exit;
    }

    // Verificar si el asesor existe
    $advisorQuery = "SELECT idAdvisor FROM advisors WHERE idAdvisor = ?";
    $stmtAdvisor = $conn->prepare($advisorQuery);
    $stmtAdvisor->bind_param('s', $idAdvisor);
    $stmtAdvisor->execute();
    $resultAdvisor = $stmtAdvisor->get_result();
    
    if ($resultAdvisor->num_rows > 0) {
        $advisorRow = $resultAdvisor->fetch_assoc();
        $advisor_id = $advisorRow['idAdvisor'];
    } else {
        echo "advisor_not_found";
        exit;
    }

    // Insertar un nuevo registro en contact_log
    $insertSql = "INSERT INTO contact_log 
                  (number_id, idAdvisor, details, contact_established, continues_interested, observation) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);

    if ($stmt) {
        $stmt->bind_param('issiis', 
            $number_id, 
            $idAdvisor, 
            $details, 
            $contact_established, 
            $continues_interested, 
            $observation
        );

        if ($stmt->execute()) {
            echo "success"; // Éxito en la inserción
        } else {
            echo "error: {$stmt->error}"; // Error en la ejecución

        }

        $stmt->close();
    } else {
        echo "prepare_error: {$conn->error}"; // Error en la preparación
    }
} else {
    // Mostrar qué datos específicos faltan
    echo "invalid_data. Datos recibidos: " . json_encode($_POST);

}