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
function contarEstudiantesAusentes() {
    global $conn;
    
    // Usar la misma consulta que listarEstudiantesAusentes() pero solo seleccionar el conteo
    $sql = "SELECT COUNT(*) as total FROM (
        SELECT 
            g.number_id
        FROM (
            SELECT DISTINCT student_id
            FROM (
                SELECT 
                    student_id,
                    class_date,
                    LAG(class_date,1) OVER (PARTITION BY student_id ORDER BY class_date) as fecha1,
                    LAG(class_date,2) OVER (PARTITION BY student_id ORDER BY class_date) as fecha2
                FROM attendance_records
                WHERE attendance_status = 'ausente'
            ) subconsulta
            WHERE DATEDIFF(class_date, fecha1) = 1 
            AND DATEDIFF(fecha1, fecha2) = 1
        ) fc
        JOIN groups g ON fc.student_id = g.number_id
        GROUP BY g.number_id
    ) resultado";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

// Función para listar estudiantes ausentes
function listarEstudiantesAusentes() {
    global $conn;
    $sql = "WITH FaltasConsecutivas AS (
        SELECT DISTINCT student_id
        FROM (
            SELECT 
                student_id,
                class_date,
                LAG(class_date,1) OVER (PARTITION BY student_id ORDER BY class_date) as fecha1,
                LAG(class_date,2) OVER (PARTITION BY student_id ORDER BY class_date) as fecha2
            FROM attendance_records
            WHERE attendance_status = 'ausente'
        ) subconsulta
        WHERE DATEDIFF(class_date, fecha1) = 1 
        AND DATEDIFF(fecha1, fecha2) = 1
    )
    SELECT 
        g.number_id as student_id,
        g.full_name,
        COUNT(DISTINCT ar.class_date) as total_faltas,
        GROUP_CONCAT(DISTINCT CONCAT(c.code, ' - ', c.name) SEPARATOR ', ') as courses
    FROM FaltasConsecutivas fc
    JOIN groups g ON fc.student_id = g.number_id
    LEFT JOIN attendance_records ar ON g.number_id = ar.student_id AND ar.attendance_status = 'ausente'
    LEFT JOIN courses c ON ar.course_id = c.code
    GROUP BY g.number_id, g.full_name
    ORDER BY total_faltas DESC";

    $result = $conn->query($sql);
    $ausentes = [];
    
    $contador = 1;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ausentes[] = [
                'numero' => $contador++,
                'student_id' => $row["student_id"],
                'nombre' => $row["full_name"],
                'total_faltas' => $row["total_faltas"],
                'courses' => $row["courses"]
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
                            <strong>${student.numero}.</strong>
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