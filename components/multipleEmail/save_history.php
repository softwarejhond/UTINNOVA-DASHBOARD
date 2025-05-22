<?php
session_start();
require_once '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $recipients_count = (int)$_POST['recipients_count'];
    $successful_count = (int)$_POST['successful_count'];
    $failed_count = (int)$_POST['failed_count'];
    $sent_from = mysqli_real_escape_string($conn, $_POST['sent_from']);
    
    // Obtener el nombre del usuario desde la tabla users
    $sent_by = 'Sistema'; // Valor por defecto
    if (isset($_SESSION['username'])) {
        $username = mysqli_real_escape_string($conn, $_SESSION['username']);
        $sql_user = "SELECT nombre FROM users WHERE username = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("s", $username);
        
        if ($stmt_user->execute()) {
            $result_user = $stmt_user->get_result();
            if ($user_row = $result_user->fetch_assoc()) {
                $sent_by = $user_row['nombre'];
            } else {
                $sent_by = $_SESSION['username']; // Si no encuentra el nombre, usa el username
            }
        }
    }

    // Insertar en email_history
    $sql = "INSERT INTO email_history (subject, content, recipients_count, successful_count, 
            failed_count, sent_by, sent_from) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiisss", $subject, $content, $recipients_count, $successful_count, 
                      $failed_count, $sent_by, $sent_from);
    
    if ($stmt->execute()) {
        $email_id = $conn->insert_id;
        
        // Procesar destinatarios
        $recipients = json_decode($_POST['recipients'], true);
        $errors = json_decode($_POST['errors'], true);
        
        // Crear mapa de errores para búsqueda rápida
        $errorMap = array();
        foreach ($errors as $error) {
            $errorMap[$error['recipient']] = $error['message'];
        }
        
        // Insertar destinatarios
        $sql = "INSERT INTO email_recipients (email_id, recipient_email, recipient_name, status, error_message) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($recipients as $recipient) {
            $status = isset($errorMap[$recipient['email']]) ? 'failed' : 'success';
            $error_message = $errorMap[$recipient['email']] ?? null;
            
            $stmt->bind_param("issss", $email_id, $recipient['email'], $recipient['name'], 
                            $status, $error_message);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el historial']);
    }
}