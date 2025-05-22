<?php
require_once '../../conexion.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Obtener detalles del correo principal
    $sql = "SELECT * FROM email_history WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Obtener los destinatarios
            $sqlRecipients = "SELECT recipient_email, recipient_name, status, error_message 
                            FROM email_recipients 
                            WHERE email_id = ?";
            $stmtRecipients = $conn->prepare($sqlRecipients);
            $stmtRecipients->bind_param("i", $id);
            $stmtRecipients->execute();
            $recipients = [];
            
            $resultRecipients = $stmtRecipients->get_result();
            while ($recipient = $resultRecipients->fetch_assoc()) {
                $recipients[] = [
                    'email' => $recipient['recipient_email'],
                    'name' => $recipient['recipient_name'],
                    'status' => $recipient['status'],
                    'error_message' => $recipient['error_message']
                ];
            }
            
            // Preparar la respuesta
            $response = [
                'success' => true,
                'subject' => $row['subject'],
                'content' => $row['content'],
                'sent_by' => $row['sent_by'],
                'sent_from' => $row['sent_from'],
                'date' => date('d/m/Y H:i', strtotime($row['created_at'])),
                'recipients' => $recipients,
                'stats' => [
                    'total' => $row['recipients_count'],
                    'successful' => $row['successful_count'],
                    'failed' => $row['failed_count']
                ]
            ];
            
            echo json_encode($response);
            exit;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró el registro solicitado'
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'ID no proporcionado'
]);