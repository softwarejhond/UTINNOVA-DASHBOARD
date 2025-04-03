<?php
// Conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Procesar peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'count') {
        $count = contarEstudiantesAusentes();
        echo json_encode(['count' => $count]);
        exit;
    }

    if ($_GET['action'] === 'list') {
        $ausentes = [];
        $ausentes = listarEstudiantesAusentes();
        echo json_encode($ausentes);
        exit;
    }
}

// Función para contar estudiantes con 3 o más ausencias
function contarEstudiantesAusentes()
{
    global $conn;
    $sql = "SELECT COUNT(DISTINCT student_id) as total 
            FROM (
                SELECT DISTINCT a1.student_id
                FROM attendance_records a1
                INNER JOIN courses c1 ON a1.course_id = c1.id
                INNER JOIN attendance_records a2 ON a1.student_id = a2.student_id 
                    AND a2.attendance_status = 'ausente'
                    AND a2.class_date > a1.class_date
                INNER JOIN courses c2 ON a2.course_id = c2.id
                INNER JOIN attendance_records a3 ON a2.student_id = a3.student_id 
                    AND a3.attendance_status = 'ausente'
                    AND a3.class_date > a2.class_date
                INNER JOIN courses c3 ON a3.course_id = c3.id
                WHERE a1.attendance_status = 'ausente'
                AND (
                    (DAYOFWEEK(a1.class_date) = 2 AND c1.monday_hours > 0) OR
                    (DAYOFWEEK(a1.class_date) = 3 AND c1.tuesday_hours > 0) OR
                    (DAYOFWEEK(a1.class_date) = 4 AND c1.wednesday_hours > 0) OR
                    (DAYOFWEEK(a1.class_date) = 5 AND c1.thursday_hours > 0) OR
                    (DAYOFWEEK(a1.class_date) = 6 AND c1.friday_hours > 0) OR
                    (DAYOFWEEK(a1.class_date) = 7 AND c1.saturday_hours > 0) OR
                    (DAYOFWEEK(a1.class_date) = 1 AND c1.sunday_hours > 0)
                )
                AND (
                    (DAYOFWEEK(a2.class_date) = 2 AND c2.monday_hours > 0) OR
                    (DAYOFWEEK(a2.class_date) = 3 AND c2.tuesday_hours > 0) OR
                    (DAYOFWEEK(a2.class_date) = 4 AND c2.wednesday_hours > 0) OR
                    (DAYOFWEEK(a2.class_date) = 5 AND c2.thursday_hours > 0) OR
                    (DAYOFWEEK(a2.class_date) = 6 AND c2.friday_hours > 0) OR
                    (DAYOFWEEK(a2.class_date) = 7 AND c2.saturday_hours > 0) OR
                    (DAYOFWEEK(a2.class_date) = 1 AND c2.sunday_hours > 0)
                )
                AND (
                    (DAYOFWEEK(a3.class_date) = 2 AND c3.monday_hours > 0) OR
                    (DAYOFWEEK(a3.class_date) = 3 AND c3.tuesday_hours > 0) OR
                    (DAYOFWEEK(a3.class_date) = 4 AND c3.wednesday_hours > 0) OR
                    (DAYOFWEEK(a3.class_date) = 5 AND c3.thursday_hours > 0) OR
                    (DAYOFWEEK(a3.class_date) = 6 AND c3.friday_hours > 0) OR
                    (DAYOFWEEK(a3.class_date) = 7 AND c3.saturday_hours > 0) OR
                    (DAYOFWEEK(a3.class_date) = 1 AND c3.sunday_hours > 0)
                )
                GROUP BY a1.student_id, a1.class_date
            ) consecutive_absences";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

// Función para listar estudiantes ausentes
function listarEstudiantesAusentes()
{
    global $conn;
    $sql = "WITH consecutive_absences AS (
        SELECT DISTINCT 
            a1.student_id,
            a1.class_date as primera_falta,
            MIN(a2.class_date) as segunda_falta,
            MIN(a3.class_date) as tercera_falta
        FROM attendance_records a1
        INNER JOIN courses c1 ON a1.course_id = c1.id
        INNER JOIN attendance_records a2 ON a1.student_id = a2.student_id 
            AND a2.attendance_status = 'ausente'
            AND a2.class_date > a1.class_date
        INNER JOIN courses c2 ON a2.course_id = c2.id
        INNER JOIN attendance_records a3 ON a2.student_id = a3.student_id 
            AND a3.attendance_status = 'ausente'
            AND a3.class_date > a2.class_date
        INNER JOIN courses c3 ON a3.course_id = c3.id
        WHERE a1.attendance_status = 'ausente'
        AND (
            (DAYOFWEEK(a1.class_date) = 2 AND c1.monday_hours > 0) OR
            (DAYOFWEEK(a1.class_date) = 3 AND c1.tuesday_hours > 0) OR
            (DAYOFWEEK(a1.class_date) = 4 AND c1.wednesday_hours > 0) OR
            (DAYOFWEEK(a1.class_date) = 5 AND c1.thursday_hours > 0) OR
            (DAYOFWEEK(a1.class_date) = 6 AND c1.friday_hours > 0) OR
            (DAYOFWEEK(a1.class_date) = 7 AND c1.saturday_hours > 0) OR
            (DAYOFWEEK(a1.class_date) = 1 AND c1.sunday_hours > 0)
        )
        AND (
            (DAYOFWEEK(a2.class_date) = 2 AND c2.monday_hours > 0) OR
            (DAYOFWEEK(a2.class_date) = 3 AND c2.tuesday_hours > 0) OR
            (DAYOFWEEK(a2.class_date) = 4 AND c2.wednesday_hours > 0) OR
            (DAYOFWEEK(a2.class_date) = 5 AND c2.thursday_hours > 0) OR
            (DAYOFWEEK(a2.class_date) = 6 AND c2.friday_hours > 0) OR
            (DAYOFWEEK(a2.class_date) = 7 AND c2.saturday_hours > 0) OR
            (DAYOFWEEK(a2.class_date) = 1 AND c2.sunday_hours > 0)
        )
        AND (
            (DAYOFWEEK(a3.class_date) = 2 AND c3.monday_hours > 0) OR
            (DAYOFWEEK(a3.class_date) = 3 AND c3.tuesday_hours > 0) OR
            (DAYOFWEEK(a3.class_date) = 4 AND c3.wednesday_hours > 0) OR
            (DAYOFWEEK(a3.class_date) = 5 AND c3.thursday_hours > 0) OR
            (DAYOFWEEK(a3.class_date) = 6 AND c3.friday_hours > 0) OR
            (DAYOFWEEK(a3.class_date) = 7 AND c3.saturday_hours > 0) OR
            (DAYOFWEEK(a3.class_date) = 1 AND c3.sunday_hours > 0)
        )
        GROUP BY a1.student_id, a1.class_date
    ),
    total_faltas AS (
        SELECT 
            student_id,
            COUNT(*) as num_faltas
        FROM attendance_records
        WHERE attendance_status = 'ausente'
        GROUP BY student_id
    )
    SELECT 
        ca.student_id,
        g.full_name,
        tf.num_faltas as total_faltas
    FROM consecutive_absences ca
    JOIN groups g ON ca.student_id = g.number_id
    JOIN total_faltas tf ON ca.student_id = tf.student_id
    GROUP BY ca.student_id, g.full_name, tf.num_faltas
    ORDER BY tf.num_faltas DESC";

    $result = $conn->query($sql);
    $ausentes = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ausentes[] = [
                'student_id' => $row["student_id"],
                'nombre' => $row["full_name"],
                'total_faltas' => $row["total_faltas"]
            ];
        }
    }
    return $ausentes;
}

// Obtener conteo inicial
$totalAusentes = contarEstudiantesAusentes();
?>

<div class="absence-notification-container">
    <button id="absenceNotificationBtn" type="button" class="btn bg-indigo-dark position-relative">
        <i class="fas fa-user-times"></i>
        <span id="absenceCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-magenta-dark">
            <?php echo $totalAusentes; ?>
        </span>
    </button>

    <div id="absenceDropdown" class="dropdown-menu absence-dropdown">
        <h6 class="dropdown-header text-center">Estudiantes con 3 faltas consecutivas</h6>
        <div id="absenceList" class="absence-list">
            <!-- La lista se cargará mediante AJAX -->
            <div class="text-center p-2">Cargando...</div>
        </div>
        <div class="dropdown-divider"></div>
        <button id="exportAbsences" class="dropdown-item text-center">Exportar ausentes</button>
    </div>
</div>

<style>
    .absence-notification-container {
        position: relative;
        margin-right: 20px;
    }

    .absence-dropdown {
        width: 300px;
        max-height: 400px;
        overflow-y: auto;
    }

    .absence-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .absence-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .absence-item:hover {
        background-color: rgb(250, 248, 250);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const absenceBtn = document.getElementById("absenceNotificationBtn");
        const absenceDropdown = document.getElementById("absenceDropdown");
        let lastCount = <?php echo $totalAusentes; ?>;

        // Toggle dropdown
        absenceBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            absenceDropdown.classList.toggle("show");
            if (absenceDropdown.classList.contains("show")) {
                loadAbsences();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function(e) {
            if (!absenceDropdown.contains(e.target) && !absenceBtn.contains(e.target)) {
                absenceDropdown.classList.remove("show");
            }
        });

        // Función para cargar las ausencias
        function loadAbsences() {
            fetch("components/attendance/absence_button.php?action=list")
                .then(response => response.json())
                .then(data => {
                    const absenceList = document.getElementById("absenceList");
                    absenceList.innerHTML = "";

                    if (data.length === 0) {
                        absenceList.innerHTML = '<div class="text-center p-3">No hay estudiantes con 3 o más ausencias</div>';
                        return;
                    }

                    data.forEach(student => {
                        const absenceItem = document.createElement("div");
                        absenceItem.className = "absence-item";
                        absenceItem.innerHTML = `
                            <strong>C.C: ${student.student_id}</strong>
                            <div>${student.nombre}</div>
                            <small class="text-muted">Total faltas: ${student.total_faltas}</small>
                        `;
                        absenceItem.addEventListener("click", function() {
                            window.location.href = "attendanceGroup.php";
                        });

                        absenceList.appendChild(absenceItem);
                    });
                })
                .catch(error => {
                    console.error("Error al cargar ausencias:", error);
                });
        }

        // Función para actualizar el contador
        function updateAbsenceCounter() {
            fetch("components/attendance/absence_button.php?action=count")
                .then(response => response.json())
                .then(data => {
                    const absenceCounter = document.getElementById("absenceCounter");
                    absenceCounter.textContent = data.count;
                })
                .catch(error => {
                    console.error("Error al actualizar contador:", error);
                });
        }

        // Actualizar cada 5 segundos
        setInterval(updateAbsenceCounter, 5000);

        const exportBtn = document.getElementById("exportAbsences");
        exportBtn.addEventListener("click", function() {
            Swal.fire({
                title: 'Generando listado',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("components/attendance/export_absences.php", {
                method: 'POST'
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'listado_ausentes.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                Swal.close();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al generar el listado'
                });
            });
        });
    });
</script>