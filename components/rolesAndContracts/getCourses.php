<?php
include_once '../../controller/conexion.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT code, name FROM courses";
    $result = $conn->query($sql);
    $courses = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = [
                'code' => $row['code'],
                'nombre' => $row['name']
            ];
        }
    }
    echo json_encode(['success' => true, 'courses' => $courses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>