<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

function quitar_tildes($cadena) {
    $originales = ['Á','É','Í','Ó','Ú','Ü','Ñ','á','é','í','ó','ú','ü','ñ'];
    $modificadas = ['A','E','I','O','U','U','N','A','E','I','O','U','U','N'];
    return str_replace($originales, $modificadas, $cadena);
}

$number_id = isset($_POST['number_id']) ? intval($_POST['number_id']) : 0;
if ($number_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Número de identificación inválido']);
    exit;
}

// Buscar en user_register
$sql = "SELECT first_name, second_name, first_last, second_last, number_id, first_phone, second_phone, email FROM user_register WHERE number_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $number_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Armar nombre completo
    $full_name = trim(
        strtoupper(
            quitar_tildes(
                $row['first_name'] . ' ' .
                $row['second_name'] . ' ' .
                $row['first_last'] . ' ' .
                $row['second_last']
            )
        )
    );
    // Teléfonos
    $phones = trim($row['first_phone'] . ($row['second_phone'] ? ' --- ' . $row['second_phone'] : ''));
    $email = $row['email'];

    // Buscar cursos en groups
    $sql2 = "SELECT bootcamp_name, leveling_english_name, english_code_name, skills_name FROM groups WHERE number_id = ? LIMIT 1";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "i", $number_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    $bootcamp_name = $leveling_english_name = $english_code_name = $skills_name = null;
    if ($curso = mysqli_fetch_assoc($result2)) {
        $bootcamp_name = !empty($curso['bootcamp_name']) ? $curso['bootcamp_name'] : null;
        $leveling_english_name = !empty($curso['leveling_english_name']) ? $curso['leveling_english_name'] : null;
        $english_code_name = !empty($curso['english_code_name']) ? $curso['english_code_name'] : null;
        $skills_name = !empty($curso['skills_name']) ? $curso['skills_name'] : null;
    }

    echo json_encode([
        'success' => true,
        'persona' => [
            'full_name' => $full_name,
            'number_id' => $row['number_id'],
            'phones' => $phones,
            'email' => $email,
            'bootcamp_name' => $bootcamp_name,
            'leveling_english_name' => $leveling_english_name,
            'english_code_name' => $english_code_name,
            'skills_name' => $skills_name
        ]
    ]);
    mysqli_stmt_close($stmt2);
} else {
    echo json_encode(['success' => false, 'message' => 'No se encontró ninguna persona con ese número de identificación.']);
}
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>