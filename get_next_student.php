<?php
include("conexion.php");

header('Content-Type: application/json');

// Modo POST: valida una lista de IDs enviados desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $inputIds = $_POST['ids'];

    if (!is_array($inputIds) || empty($inputIds)) {
        echo json_encode(['error' => 'No se proporcionaron IDs']);
        exit;
    }

    $validStudents = [];
    foreach ($inputIds as $studentId) {
        $studentId = trim($studentId);
        if ($studentId === '') continue;

        $sql = "SELECT number_id FROM user_register WHERE number_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $validStudents[] = $studentId;
        }
        $stmt->close();
    }

    if (empty($validStudents)) {
        echo json_encode(['error' => 'Ningún ID fue encontrado en user_register']);
    } else {
        echo json_encode($validStudents);
    }
    exit;
}

// Si no es POST válido, retornar error
echo json_encode(['error' => 'Método no permitido. Usa POST con ids[]']);
?>
