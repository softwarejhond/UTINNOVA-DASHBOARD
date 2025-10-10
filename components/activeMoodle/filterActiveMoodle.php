<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../controller/conexion.php';
session_start();

try {
    // Verificar sesión
    if (!isset($_SESSION['rol'])) {
        throw new Exception('No autorizado');
    }

    $infoUsuario = $_SESSION;
    $rol = $infoUsuario['rol']; // Este es el número
    $rolesPermitidos = [1, 6, 7, 8, 10, 12, 13, 14]; // <-- Agrega esto

    // Obtener filtros
    $headquarters = isset($_POST['headquarters']) ? trim($_POST['headquarters']) : '';
    $bootcamp = isset($_POST['bootcamp']) ? trim($_POST['bootcamp']) : '';
    $number_id = isset($_POST['number_id']) ? trim($_POST['number_id']) : '';

    // Verificar conexión
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Error de conexión: ' . (isset($conn) ? $conn->connect_error : 'Conexión no establecida'));
    }

    // Construir consulta simple sin CAST que puede causar problemas
    $sql = "SELECT g.*, ur.*, c.classroom_name, cp.cohort AS course_cohort, cp.start_date, cp.end_date
            FROM groups g 
            LEFT JOIN user_register ur ON g.number_id = ur.number_id 
            LEFT JOIN classrooms c ON g.id_bootcamp = c.bootcamp_id
            LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code
            WHERE 1=1";

    $params = [];
    $types = "";

    // Agregar condiciones
    if (!empty($headquarters)) {
        $sql .= " AND g.headquarters = ?";
        $params[] = $headquarters;
        $types .= "s";
    }

    if (!empty($bootcamp)) {
        $sql .= " AND g.bootcamp_name = ?";
        $params[] = $bootcamp;
        $types .= "s";
    }

    if (!empty($number_id)) {
        $sql .= " AND g.number_id = ?";
        $params[] = $number_id;
        $types .= "s"; // Mantener como string para evitar problemas de conversión
    }

    $sql .= " ORDER BY g.creation_date DESC LIMIT 1000"; // Limitar resultados por seguridad

    // Preparar y ejecutar
    if (!($stmt = $conn->prepare($sql))) {
        throw new Exception("Error en prepare: " . $conn->error . " - SQL: $sql");
    }

    // Vincular parámetros si existen
    if (!empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception("Error en bind_param: " . $stmt->error);
        }
    }

    if (!$stmt->execute()) {
        throw new Exception("Error en execute: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    $html = '';
    $count = 0;

    // Función para obtener puntaje y nivel
    function obtenerNivelesUsuarios($conn)
    {
        $sql = "SELECT cedula, nivel FROM usuarios";
        $result = $conn->query($sql);

        $niveles = array();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $niveles[$row['cedula']] = $row['nivel'];
            }
        }
        return $niveles;
    }

    $nivelesUsuarios = obtenerNivelesUsuarios($conn);

    // Construir HTML de resultados
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;

            // Puntaje de prueba y nivel obtenido
            $puntaje = 'No presentó';
            $nivelObtenido = 'No presentó';
            if (isset($nivelesUsuarios[$row['number_id']])) {
                $puntaje = $nivelesUsuarios[$row['number_id']];
                if ($puntaje >= 0 && $puntaje <= 5) {
                    $nivelObtenido = 'Básico';
                } elseif ($puntaje >= 6 && $puntaje <= 10) {
                    $nivelObtenido = 'Intermedio';
                } elseif ($puntaje >= 11 && $puntaje <= 15) {
                    $nivelObtenido = 'Avanzado';
                } else {
                    $nivelObtenido = 'Sin clasificar';
                }
            }

            $html .= '<tr style="white-space:nowrap;">'
                . '<td>' . htmlspecialchars($row['type_id'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($row['number_id'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($row['full_name'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($row['first_phone'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($row['email'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($row['institutional_email'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($row['schedules'] ?? '-') . '</td>'
                . '<td>' . ( ($row['department'] ?? '') === 'BOGOTÁ' || ($row['department'] ?? '') === '11' ? '<button class="btn bg-teal-light w-100"><b>BOGOTÁ</b></button>' : '<span>' . htmlspecialchars($row['department'] ?? '-') . '</span>' ) . '</td>'
                . '<td><b>' . htmlspecialchars($row['headquarters'] ?? '-') . '</b></td>'
                . '<td>' . (!empty($row['classroom_name']) ? htmlspecialchars($row['classroom_name']) : '-') . '</td>'
                . '<td>' . htmlspecialchars($row['mode'] ?? '-') . '</td>'
                . '<td>' . (!empty($row['id_bootcamp']) && !empty($row['bootcamp_name']) ? htmlspecialchars($row['id_bootcamp']) . ' - ' . htmlspecialchars($row['bootcamp_name']) : '-') . '</td>'
                . '<td>' . (!empty($row['id_leveling_english']) && !empty($row['leveling_english_name']) ? htmlspecialchars($row['id_leveling_english']) . ' - ' . htmlspecialchars($row['leveling_english_name']) : '-') . '</td>'
                . '<td>' . (!empty($row['id_english_code']) ? htmlspecialchars($row['id_english_code']) . (!empty($row['english_code_name']) ? ' - ' . htmlspecialchars($row['english_code_name']) : '') : '-') . '</td>'
                . '<td>' . (!empty($row['id_skills']) && !empty($row['skills_name']) ? htmlspecialchars($row['id_skills']) . ' - ' . htmlspecialchars($row['skills_name']) : '-') . '</td>'
                . '<td class="text-center">' . (!empty($row['creation_date']) ? date('d/m/Y', strtotime($row['creation_date'])) : '-') . '</td>'
                . '<td>' . (!empty($row['course_cohort']) ? htmlspecialchars($row['course_cohort']) : '-') . '</td>'
                . '<td>' . (!empty($row['level']) ? htmlspecialchars($row['level']) : '-') . '</td>'
                // Puntaje de prueba desde usuarios
                . '<td>' . htmlspecialchars($puntaje) . '</td>'
                // Nivel obtenido desde usuarios
                . '<td>' . htmlspecialchars($nivelObtenido) . '</td>'
                // Botón de desmatricula
                . '<td class="text-center">' . (in_array($rol, $rolesPermitidos)
                    ? '<button class="btn btn-danger btn-sm btn-delete" data-id="' . htmlspecialchars($row['number_id']) . '" data-name="' . htmlspecialchars($row['full_name']) . '"><i class="bi bi-trash"></i></button>'
                    : '<button class="btn btn-danger btn-sm" data-toggle="popover" data-placement="top" title="Acceso Denegado" data-content="No tienes permisos para realizar esta acción"><i class="bi bi-slash-circle"></i></button>'
                ) . '</td>'
                . '</tr>';
        }
    } else {
        $html = '<tr><td colspan="15" class="text-center"><i class="bi bi-info-circle"></i> No se encontraron registros</td></tr>';
    }

    if ($stmt) {
        $stmt->close();
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => $count
    ]);

} catch (Exception $e) {
    error_log('Error en filterActiveMoodle.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    error_log('Error fatal en filterActiveMoodle.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error fatal: ' . $e->getMessage()
    ]);
}
?>