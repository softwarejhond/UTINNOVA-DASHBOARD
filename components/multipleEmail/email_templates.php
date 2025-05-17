<?php
require_once '../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'save':
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $subject = mysqli_real_escape_string($conn, $_POST['subject']);
            $content = mysqli_real_escape_string($conn, $_POST['content']); // Contenido HTML completo
            
            $sql = "INSERT INTO email_templates (name, subject, content) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $subject, $content);
            
            echo json_encode(['success' => $stmt->execute()]);
            break;
            
        case 'list':
            $sql = "SELECT id, name FROM email_templates ORDER BY name ASC";
            $result = mysqli_query($conn, $sql);
            $templates = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $templates[] = $row;
            }
            
            echo json_encode(['success' => true, 'templates' => $templates]);
            break;
            
        case 'load':
            $id = (int)$_POST['id'];
            $sql = "SELECT * FROM email_templates WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($template = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'template' => $template]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Plantilla no encontrada']);
            }
            break;
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);