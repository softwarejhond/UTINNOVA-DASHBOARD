<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include("../../conexion.php");

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $id = mysqli_real_escape_string($conn, $data['id']);
    
    $query = "DELETE FROM headquarters_registrations WHERE id = $id";
    
    if(mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Sede de asistencia eliminada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar la sede de asistencia: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID no proporcionado'
    ]);
}