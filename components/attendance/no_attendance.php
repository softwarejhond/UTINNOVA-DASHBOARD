<?php
require_once __DIR__ . '/../../controller/conexion.php';

if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'count') {
        $count = contarEstudiantesSinAsistencia();
        echo json_encode(['count' => $count]);
        exit;
    }

    if ($_GET['action'] === 'list') {
        $sinAsistencia = listarEstudiantesSinAsistencia();
        echo json_encode($sinAsistencia);
        exit;
    }
}

function contarEstudiantesSinAsistencia() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM (
                SELECT g.number_id
                FROM groups g
                INNER JOIN attendance_records ar ON g.number_id = ar.student_id
                GROUP BY g.number_id
                HAVING SUM(CASE WHEN ar.attendance_status != 'ausente' THEN 1 ELSE 0 END) = 0
            ) subquery";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

function listarEstudiantesSinAsistencia() {
    global $conn;
    $sql = "SELECT 
                g.number_id as student_id,
                g.full_name,
                COUNT(ar.id) as total_ausencias
            FROM groups g
            INNER JOIN attendance_records ar ON g.number_id = ar.student_id
            GROUP BY g.number_id, g.full_name
            HAVING SUM(CASE WHEN ar.attendance_status != 'ausente' THEN 1 ELSE 0 END) = 0
            ORDER BY g.full_name";

    $result = $conn->query($sql);
    $sinAsistencia = [];
    
    $contador = 1;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sinAsistencia[] = [
                'contador' => $contador++,
                'student_id' => $row["student_id"],
                'nombre' => $row["full_name"],
                'total_ausencias' => $row["total_ausencias"]
            ];
        }
    }
    return $sinAsistencia;
}

$totalSinAsistencia = contarEstudiantesSinAsistencia();
?>

<div class="no-attendance-container">
    <button id="noAttendanceBtn" type="button" class="btn bg-indigo-dark position-relative">
        <i class="fas fa-user-slash"></i>
        <span id="noAttendanceCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-magenta-dark">
            <?php echo $totalSinAsistencia; ?>
        </span>
    </button>

    <div id="noAttendanceDropdown" class="dropdown-menu no-attendance-dropdown">
        <h6 class="dropdown-header text-center">Estudiantes totalmente ausentes</h6>
        <div id="noAttendanceList" class="no-attendance-list">
            <div class="text-center p-2">Cargando...</div>
        </div>
        <div class="dropdown-divider"></div>
        <button id="exportNoAttendance" class="dropdown-item text-center">Exportar listado</button>
    </div>
</div>

<style>
    .no-attendance-container {
        position: relative;
        margin-right: 20px;
    }

    .no-attendance-dropdown {
        width: 300px;
        max-height: 400px;
        overflow-y: auto;
    }

    .no-attendance-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .no-attendance-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .no-attendance-item:hover {
        background-color: rgb(250, 248, 250);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const noAttendanceBtn = document.getElementById("noAttendanceBtn");
        const noAttendanceDropdown = document.getElementById("noAttendanceDropdown");
        let lastCount = <?php echo $totalSinAsistencia; ?>;

        noAttendanceBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            noAttendanceDropdown.classList.toggle("show");
            if (noAttendanceDropdown.classList.contains("show")) {
                loadNoAttendance();
            }
        });

        document.addEventListener("click", function(e) {
            if (!noAttendanceDropdown.contains(e.target) && !noAttendanceBtn.contains(e.target)) {
                noAttendanceDropdown.classList.remove("show");
            }
        });

        function loadNoAttendance() {
            fetch("components/attendance/no_attendance.php?action=list")
                .then(response => response.json())
                .then(data => {
                    const noAttendanceList = document.getElementById("noAttendanceList");
                    noAttendanceList.innerHTML = "";

                    if (data.length === 0) {
                        noAttendanceList.innerHTML = '<div class="text-center p-3">Todos los estudiantes tienen asistencias registradas</div>';
                        return;
                    }

                    data.forEach(student => {
                        const noAttendanceItem = document.createElement("div");
                        noAttendanceItem.className = "no-attendance-item";
                        noAttendanceItem.innerHTML = `
                            <strong>${student.contador}.</strong>
                            <strong>C.C: ${student.student_id}</strong>
                            <div>${student.nombre}</div>
                            <div>Total ausencias: ${student.total_ausencias}</div>
                        `;
                        noAttendanceItem.addEventListener("click", function() {
                            window.location.href = "attendanceGroup.php";
                        });

                        noAttendanceList.appendChild(noAttendanceItem);
                    });
                })
                .catch(error => {
                    console.error("Error al cargar lista:", error);
                });
        }

        function updateNoAttendanceCounter() {
            fetch("components/attendance/no_attendance.php?action=count")
                .then(response => response.json())
                .then(data => {
                    const noAttendanceCounter = document.getElementById("noAttendanceCounter");
                    noAttendanceCounter.textContent = data.count;
                })
                .catch(error => {
                    console.error("Error al actualizar contador:", error);
                });
        }

        setInterval(updateNoAttendanceCounter, 5000);

        const exportBtn = document.getElementById("exportNoAttendance");
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

            fetch("components/attendance/export_no_attendance.php", {
                method: 'POST'
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'listado_sin_asistencia.xlsx';
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