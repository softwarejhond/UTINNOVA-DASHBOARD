<?php
include("conexion.php");

// Manejo de guardado de imagen (modificado para el automatizador)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image'])) {
    $data = $_POST['image'];
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = str_replace(' ', '+', $data);
    $data = base64_decode($data);
    
    $number_id = $_POST['number_id'] ?? 'unknown';
    $tipo = $_POST['tipo'] ?? 'desconocido';
    
    // Si viene del automatizador, usa la ruta y el timestamp proporcionados
    if (isset($_POST['studentDir']) && isset($_POST['timestamp'])) {
        $studentDir = $_POST['studentDir'];
        $timestamp = $_POST['timestamp'];
        
        if (!file_exists($studentDir)) {
            mkdir($studentDir, 0755, true);
        }
        $file = "{$studentDir}/{$tipo}_{$number_id}_{$timestamp}.png";

    } else {
        // Comportamiento original: guardar en la carpeta 'img'
        $timestamp = date('Y-m-d_H-i-s');
        $file = "img/tarjeta_{$tipo}_{$number_id}_{$timestamp}.png";
    }

    file_put_contents($file, $data);
    echo 'Imagen guardada en ' . $file;
    exit;
}

// Obtener datos del estudiante
$number_id = isset($_GET['number_id']) ? $_GET['number_id'] : '1234567890'; // ID por defecto para prueba

$sql = "SELECT 
    g.full_name,
    g.program,
    g.headquarters,
    g.id_bootcamp,
    g.bootcamp_name,
    ur.lote,
    ur.level,
    ur.dayUpdate
FROM groups g
LEFT JOIN user_register ur ON g.number_id = ur.number_id
WHERE g.number_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $number_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    // Datos por defecto si no se encuentra el estudiante
    $student = [
        'full_name' => 'Juan Carlos Pérez González',
        'program' => 'Desarrollo Web Full Stack',
        'headquarters' => 'Bogotá - Centro',
        'lote' => '1',
        'level' => 'Intermedio',
        'dayUpdate' => date('Y-m-d'),
        'id_bootcamp' => null,
        'bootcamp_name' => null
    ];
}

// Extraer año de dayUpdate - MODIFICADO: usar 2025 por defecto
$year = 2025; // Año fijo 2025 para certificación

// Función para obtener notas del curso técnico (copiada de export_excel_general_all.php)
function obtenerNotasTecnico($conn, $studentId, $courseCode) {
    if (empty($courseCode) || empty($studentId)) {
        return ['grade1' => 0, 'grade2' => 0];
    }
    
    try {
        // 1. Intentar obtener las notas desde course_approvals (tabla de notas finales/oficiales)
        $sql_approvals = "SELECT grade_1, grade_2 FROM course_approvals 
                          WHERE student_number_id = ? AND course_code = ?";
        
        $stmt_approvals = $conn->prepare($sql_approvals);
        if (!$stmt_approvals) {
            error_log("Error preparando consulta de notas aprobadas: " . $conn->error);
        } else {
            $stmt_approvals->bind_param("ss", $studentId, $courseCode);
            if ($stmt_approvals->execute()) {
                $result_approvals = $stmt_approvals->get_result();
                $row_approvals = $result_approvals->fetch_assoc();
                $stmt_approvals->close();
                
                if ($row_approvals) {
                    // Las notas en course_approvals ya están en escala 5.0
                    $grade1 = floatval($row_approvals['grade_1']);
                    $grade2 = floatval($row_approvals['grade_2']);
                    
                    return [
                        'grade1' => $grade1,
                        'grade2' => $grade2
                    ];
                }
            } else {
                $stmt_approvals->close();
            }
        }

        // 2. Si no está en la tabla de aprobados, obtener desde notas_estudiantes
        $sql_notas = "SELECT nota1, nota2 FROM notas_estudiantes WHERE number_id = ? AND code = ?";
        $stmt_notas = $conn->prepare($sql_notas);

        if (!$stmt_notas) {
            return ['grade1' => 0, 'grade2' => 0];
        }

        $stmt_notas->bind_param("si", $studentId, $courseCode);
        if (!$stmt_notas->execute()) {
            $stmt_notas->close();
            return ['grade1' => 0, 'grade2' => 0];
        }

        $result_notas = $stmt_notas->get_result();
        $row_notas = $result_notas->fetch_assoc();
        $stmt_notas->close();

        if ($row_notas) {
            $grade1_raw = floatval($row_notas['nota1']);
            $grade2_raw = floatval($row_notas['nota2']);
            
            // Determinar si las notas están en escala 10
            $enEscala10 = ($grade1_raw > 5.0 || $grade2_raw > 5.0);
            
            if ($enEscala10) {
                $grade1_normalized = ($grade1_raw / 10.0) * 5.0;
                $grade2_normalized = ($grade2_raw / 10.0) * 5.0;
            } else {
                $grade1_normalized = $grade1_raw;
                $grade2_normalized = $grade2_raw;
            }
            
            return [
                'grade1' => round($grade1_normalized, 2),
                'grade2' => round($grade2_normalized, 2)
            ];
        }

        return ['grade1' => 0, 'grade2' => 0];
        
    } catch (Exception $e) {
        return ['grade1' => 0, 'grade2' => 0];
    }
}

// Obtener solo notas del curso técnico
$gradesToshow = [];
if (!empty($student['id_bootcamp'])) {
    $notasTecnico = obtenerNotasTecnico($conn, $number_id, $student['id_bootcamp']);
    $gradesToshow['tecnico'] = [
        'course_name' => $student['bootcamp_name'] ?? 'Técnico',
        'grade1' => $notasTecnico['grade1'],
        'grade2' => $notasTecnico['grade2']
    ];
}

// Función para obtener asistencias del estudiante (MODIFICADA CON NORMALIZACIÓN)
function getStudentAttendance($conn, $number_id) {
    $attendance = [
        'tecnico' => [],
        'ingles_nivelado' => [],
        'english_code' => [],
        'habilidades' => []
    ];
    
    // Obtener datos de asistencia por tipo de curso
    $courseTypes = [
        'tecnico' => ['table' => 'groups', 'course_field' => 'id_bootcamp', 'name_field' => 'bootcamp_name'],
        'ingles_nivelado' => ['table' => 'groups', 'course_field' => 'id_leveling_english', 'name_field' => 'leveling_english_name'],
        'english_code' => ['table' => 'groups', 'course_field' => 'id_english_code', 'name_field' => 'english_code_name'],
        'habilidades' => ['table' => 'groups', 'course_field' => 'id_skills', 'name_field' => 'skills_name']
    ];
    
    foreach ($courseTypes as $type => $config) {
        // Obtener el curso del estudiante actual
        $courseSql = "SELECT {$config['course_field']} as course_id, {$config['name_field']} as course_name 
                      FROM {$config['table']} 
                      WHERE number_id = ? AND {$config['course_field']} IS NOT NULL";
        
        $courseStmt = $conn->prepare($courseSql);
        $courseStmt->bind_param("s", $number_id);
        $courseStmt->execute();
        $courseResult = $courseStmt->get_result();
        
        if ($courseResult->num_rows > 0) {
            $course = $courseResult->fetch_assoc();
            $course_id = $course['course_id'];
            
            // PASO 1: Obtener todas las fechas únicas de asistencia para este curso (de TODOS los estudiantes)
            $allCourseDatesSql = "SELECT DISTINCT ar.class_date 
                                 FROM attendance_records ar
                                 INNER JOIN groups g ON ar.student_id = g.number_id 
                                 WHERE ar.course_id = ? AND g.{$config['course_field']} = ?
                                 ORDER BY ar.class_date ASC";
            
            $allDatesStmt = $conn->prepare($allCourseDatesSql);
            $allDatesStmt->bind_param("ii", $course_id, $course_id);
            $allDatesStmt->execute();
            $allDatesResult = $allDatesStmt->get_result();
            
            $allCourseDates = [];
            while ($dateRow = $allDatesResult->fetch_assoc()) {
                $allCourseDates[] = $dateRow['class_date'];
            }
            $allDatesStmt->close();
            
            // PASO 2: Obtener las asistencias existentes del estudiante actual
            $attendanceSql = "SELECT class_date, attendance_status 
                             FROM attendance_records 
                             WHERE student_id = ? AND course_id = ? 
                             ORDER BY class_date ASC";
            
            $attendanceStmt = $conn->prepare($attendanceSql);
            $attendanceStmt->bind_param("si", $number_id, $course['course_id']);
            $attendanceStmt->execute();
            $attendanceResult = $attendanceStmt->get_result();
            
            $existingAttendances = [];
            while ($row = $attendanceResult->fetch_assoc()) {
                $existingAttendances[$row['class_date']] = $row['attendance_status'];
            }
            $attendanceStmt->close();
            
            // PASO 3: Crear el conjunto completo de asistencias normalizadas
            $normalizedRecords = [];
            foreach ($allCourseDates as $date) {
                if (isset($existingAttendances[$date])) {
                    // Si tiene registro, usar el estado real
                    $normalizedRecords[] = [
                        'class_date' => $date,
                        'attendance_status' => $existingAttendances[$date]
                    ];
                } else {
                    // Si no tiene registro, marcar como ausente
                    $normalizedRecords[] = [
                        'class_date' => $date,
                        'attendance_status' => 'ausente'
                    ];
                }
            }
            
            $attendance[$type] = [
                'course_name' => $course['course_name'],
                'course_id' => $course['course_id'],
                'records' => $normalizedRecords
            ];
        }
    }
    
    return $attendance;
}

// Obtener datos de asistencia
$attendanceData = getStudentAttendance($conn, $number_id);

// Función para obtener la clase CSS según el estado de asistencia
function getAttendanceClass($status) {
    switch (strtolower($status)) {
        case 'presente':
        case 'present':
            return 'bg-success text-white';
        case 'ausente':
        case 'absent':
            return 'bg-danger text-white';
        case 'tarde':
        case 'late':
            return 'bg-warning text-dark';
        case 'excusa':
        case 'excused':
            return 'bg-info text-white';
        default:
            return 'bg-secondary text-white';
    }
}

// Función para obtener el texto del estado
function getAttendanceText($status) {
    switch (strtolower($status)) {
        case 'presente':
        case 'present':
            return 'Presente';
        case 'ausente':
        case 'absent':
            return 'Ausente';
        case 'tarde':
        case 'late':
            return 'Tarde';
        case 'excusa':
        case 'excused':
            return 'Excusa';
        default:
            return 'N/A';
    }
}

// Función para obtener el color según la nota
function getGradeColor($grade) {
    if ($grade >= 3.0) return 'bg-success text-white'; // Verde para apto
    return 'bg-danger text-white'; // Rojo para no apto
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarjeta de Estudiante - Sistema Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/contadores.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .student-card {
            width: 1200px;
            min-height: 500px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: scale(1.1);
            transform-origin: top center;
            margin: 40px auto;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--bs-indigo-dark) 0%, var(--bs-purple-dark) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        
        .student-id {
            font-size: 2.8rem;
            font-weight: 600;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .student-name-small {
            position: absolute;
            top: 20px;
            right: 30px;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            padding: 30px 30px 20px 30px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            padding: 10px 12px;
            border-left: 4px solid;
            background: #f5f5f5;
            border-radius: 8px;
            min-height: 50px;
            max-height: 60px;
            overflow: hidden;
        }
        
        .info-item:nth-child(odd) {
            border-left-color: var(--bs-indigo-dark);
        }
        
        .info-item:nth-child(even) {
            border-left-color: var(--bs-magenta-dark);
        }
        
        .info-item:nth-child(3n) {
            border-left-color: var(--bs-teal-dark);
        }
        
        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #57595c;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #2c3e50;
            line-height: 1.1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            max-height: 1.1em;
        }
        
        .attendance-section {
            padding: 0 30px 30px 30px;
        }
        
        .course-attendance {
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background: white;
        }
        
        .course-header {
            background: var(--bs-indigo-dark);
            color: white;
            padding: 12px 20px;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .attendance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 8px;
            padding: 20px;
        }
        
        .attendance-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 6px;
            border-radius: 6px;
            font-size: 0.8rem;
            min-height: 50px;
            justify-content: center;
        }
        
        .attendance-date {
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .attendance-status {
            font-size: 0.7rem;
            opacity: 0.9;
        }
        
        .no-attendance {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
        
        /* Estilos para tarjeta de notas */
        .grades-section {
            padding: 0 30px 30px 30px;
        }
        
        .course-grades {
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background: white;
        }
        
        .grades-header {
            background: var(--bs-magenta-dark);
            color: white;
            padding: 12px 20px;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .grades-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px 60px;
            justify-items: stretch;
        }
        
        .grade-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 30px;
            border-radius: 15px;
            font-size: 1.2rem;
            min-height: 150px;
            justify-content: center;
            border: 4px solid;
            width: 100%;
        }
        
        .grade-label {
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.4rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .grade-value {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .no-grades {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
        
        .card-separator {
            height: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-5 mb-5">
        <h1 class="text-center mb-5">Sistema de Gestión Académica - Tarjetas de Estudiantes</h1>
        
        <!-- Formulario para buscar estudiante -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" class="form-control me-2" name="number_id" 
                           placeholder="Ingresa el número de documento" 
                           value="<?php echo htmlspecialchars($number_id); ?>">
                    <button type="submit" class="btn btn-outline-primary">Buscar Estudiante</button>
                </form>
            </div>
        </div>
        
        <!-- TARJETA DE ASISTENCIAS -->
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center mb-3">
                <div class="card student-card" id="tarjetaAsistencia">
                    <div class="position-relative">
                        <div class="student-name-small"><?php echo htmlspecialchars($student['full_name']); ?></div>
                        <div class="header-section">
                            <h2 class="student-id">CC: <?php echo htmlspecialchars($number_id); ?></h2>
                            <p style="margin: 10px 0 0 0; font-size: 1.2rem; opacity: 0.9;">Registro de Asistencias</p>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Programa</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['program']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Sede</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['headquarters']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Región y Lote</div>
                                <div class="info-value">Región 8 - Lote <?php echo htmlspecialchars($student['lote']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Nivel</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['level']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Año de Certificación</div>
                                <div class="info-value"><?php echo $year; ?></div>
                            </div>
                        </div>
                        
                        <!-- Sección de Asistencias -->
                        <div class="attendance-section">
                            <?php 
                            $courseLabels = [
                                'tecnico' => 'Técnico',
                                'ingles_nivelado' => 'Inglés Nivelado',
                                'english_code' => 'English Code',
                                'habilidades' => 'Habilidades'
                            ];
                            
                            foreach ($attendanceData as $type => $data): 
                                if (!empty($data) && !empty($data['course_name'])): ?>
                                    <div class="course-attendance">
                                        <div class="course-header">
                                            <?php echo $courseLabels[$type] . ': ' . htmlspecialchars($data['course_name']); ?>
                                        </div>
                                        <?php if (!empty($data['records'])): ?>
                                            <div class="attendance-grid">
                                                <?php foreach ($data['records'] as $record): ?>
                                                    <div class="attendance-item <?php echo getAttendanceClass($record['attendance_status']); ?>">
                                                        <div class="attendance-date">
                                                            <?php echo date('d/m/y', strtotime($record['class_date'])); ?>
                                                        </div>
                                                        <div class="attendance-status">
                                                            <?php echo getAttendanceText($record['attendance_status']); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-attendance">No hay registros de asistencia</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; 
                            endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-separator"></div>
        
        <!-- TARJETA DE NOTAS -->
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center mb-5">
                <div class="card student-card" id="tarjetaNotas">
                    <div class="position-relative">
                        <div class="student-name-small"><?php echo htmlspecialchars($student['full_name']); ?></div>
                        <div class="header-section">
                            <h2 class="student-id">CC: <?php echo htmlspecialchars($number_id); ?></h2>
                            <p style="margin: 10px 0 0 0; font-size: 1.2rem; opacity: 0.9;">Registro de Notas</p>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Programa</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['program']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Sede</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['headquarters']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Región y Lote</div>
                                <div class="info-value">Región 8 - Lote <?php echo htmlspecialchars($student['lote']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Nivel</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['level']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Año de Certificación</div>
                                <div class="info-value"><?php echo $year; ?></div>
                            </div>
                        </div>
                        
                        <!-- Sección de Notas -->
                        <div class="grades-section">
                            <?php if (!empty($gradesToshow['tecnico'])): 
                                $data = $gradesToshow['tecnico']; ?>
                                <div class="course-grades">
                                    <div class="grades-header">
                                        Técnico: <?php echo htmlspecialchars($data['course_name']); ?>
                                    </div>
                                    <div class="grades-grid">
                                        <div class="grade-item <?php echo getGradeColor($data['grade1']); ?>" style="border-color: currentColor;">
                                            <div class="grade-label">Nota 1</div>
                                            <div class="grade-value"><?php echo number_format($data['grade1'], 1); ?></div>
                                        </div>
                                        
                                        <div class="grade-item <?php echo getGradeColor($data['grade2']); ?>" style="border-color: currentColor;">
                                            <div class="grade-label">Nota 2</div>
                                            <div class="grade-value"><?php echo number_format($data['grade2'], 1); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-grades">No hay registros de notas del curso técnico disponibles</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <button class="btn btn-success btn-lg px-4 me-3" onclick="capturarTarjeta('tarjetaAsistencia', 'asistencias')">
                📊 Exportar Tarjeta de Asistencias
            </button>
            <button class="btn btn-warning btn-lg px-4" onclick="capturarTarjeta('tarjetaNotas', 'notas')">
                📝 Exportar Tarjeta de Notas
            </button>
        </div>
    </div>

    <script>
        function capturarTarjeta(elementId, tipo, studentDir, timestamp) {
            return new Promise((resolve, reject) => {
                const element = document.getElementById(elementId);
                if (!element) {
                    return reject(new Error(`Elemento no encontrado: ${elementId}`));
                }

                html2canvas(element, {
                    scale: 3,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                }).then(function(canvas) {
                    var dataURL = canvas.toDataURL('image/png');
                    var formData = new FormData();
                    formData.append('image', dataURL);
                    formData.append('number_id', '<?php echo htmlspecialchars($number_id); ?>');
                    formData.append('tipo', tipo);
                    
                    // Si se ejecuta desde el automatizador, se pasan estos parámetros
                    if (studentDir && timestamp) {
                        formData.append('studentDir', studentDir);
                        formData.append('timestamp', timestamp);
                    }

                    fetch('', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.text()).then(data => {
                        // Si no es una ejecución automática, muestra la alerta.
                        if (!studentDir) {
                            alert(data);
                        }
                        resolve(data); // Resuelve la promesa para el automatizador
                    }).catch(err => {
                        if (!studentDir) {
                            alert('Error al guardar la imagen.');
                        }
                        reject(err); // Rechaza la promesa en caso de error
                    });
                });
            });
        }
    </script>
</body>
</html>