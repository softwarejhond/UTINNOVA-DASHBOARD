<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = mysqli_real_escape_string($conn, $data['id']);

        $query = "DELETE FROM headquarters WHERE id = $id";

        if(mysqli_query($conn, $query)) {
            echo json_encode([
                'success' => true,
                'message' => 'Sede eliminada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la sede: ' . mysqli_error($conn)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}