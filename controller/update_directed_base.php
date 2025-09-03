<?php
header('Content-Type: application/json');
require_once '../controller/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['documentos']) || !is_array($data['documentos']) ||
    !isset($data['baseValue'])
) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$documentos = array_map('trim', $data['documentos']);
$documentos = array_filter($documentos, function($d) { return $d !== ''; });
$baseValue = ($data['baseValue'] === "1" || $data['baseValue'] === 1) ? 1 : 0;

if (count($documentos) === 0) {
    echo json_encode(['success' => false, 'message' => 'No hay documentos']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($documentos), '?'));
$sql = "UPDATE user_register SET directed_base = ? WHERE number_id IN ($placeholders)";

$params = array_merge([$baseValue], $documentos);

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
    exit;
}

$stmt->execute($params);

echo json_encode(['success' => true]);