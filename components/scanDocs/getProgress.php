<?php
// filepath: c:\xampp\htdocs\DASBOARD-ADMIN-MINTICS\components\scanDocs\getProgress.php
header('Content-Type: application/json');
session_start();

// Verificar caché del navegador para la respuesta más reciente
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_SESSION['verification_progress'])) {
    echo json_encode($_SESSION['verification_progress']);
} else {
    echo json_encode([
        'current' => 0,
        'total' => 0,
        'percentage' => 0,
        'status' => 'preparando'
    ]);
}
?>