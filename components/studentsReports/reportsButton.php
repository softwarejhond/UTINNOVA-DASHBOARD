<?php
// Conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Procesar peticiones AJAX si es necesario
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'count') {
        // Endpoint para contar reportes pendientes
        $count = contarReportesPendientes();
        echo json_encode(['count' => $count]);
        exit;
    }

    if ($_GET['action'] === 'list') {
        // Endpoint para listar reportes pendientes
        $reportes = listarReportesPendientes();
        echo json_encode($reportes);
        exit;
    }
}

// Función para contar reportes con estado PENDIENTE
function contarReportesPendientes()
{
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM student_reports WHERE status = 'PENDIENTE'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["total"];
    }
    return 0;
}

// Función para listar reportes pendientes
function listarReportesPendientes()
{
    global $conn;
    $sql = "SELECT 
                sr.id, 
                sr.number_id,
                CONCAT_WS(' ', ur.first_name, ur.second_name, ur.first_last, ur.second_last) AS nombre_completo,
                DATE_FORMAT(sr.fecha_registro, '%d/%m/%Y %H:%i') as fecha 
            FROM student_reports sr
            LEFT JOIN user_register ur ON sr.number_id = ur.number_id
            WHERE sr.status = 'PENDIENTE' 
            ORDER BY sr.fecha_registro DESC 
            LIMIT 10";
    $result = $conn->query($sql);

    $reportes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reportes[] = [
                'id' => $row["id"],
                'number_id' => $row["number_id"],
                'nombre' => $row["nombre_completo"] ?: 'No encontrado',
                'fecha' => $row["fecha"]
            ];
        }
    }
    return $reportes;
}

// Obtener el conteo inicial
$totalReportes = contarReportesPendientes();
?>

<div class="reports-notification-container">
    <button id="reportsNotificationBtn" type="button" class="btn bg-magenta-dark position-relative text-white">
        <i class="fas fa-clipboard-list"></i>
        <span id="reportsCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-indigo-dark">
            <?php echo $totalReportes; ?>
        </span>
    </button>

    <div id="reportsDropdown" class="dropdown-menu reports-dropdown">
        <h6 class="dropdown-header">Reportes pendientes</h6>
        <div id="reportsList" class="reports-list text-uppercase">
            <!-- Los reportes se cargarán aquí mediante AJAX -->
            <div class="text-center p-2">Cargando...</div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="gestionarReportes.php">Ver todos</a>
    </div>
</div>

<audio id="reportsNotificationSound" src="components/studentsReports/sounds/notificacion_reporte.mp3" preload="auto"></audio>

<style>
    .reports-notification-container {
        position: relative;
        margin-right: 20px;
    }

    .reports-dropdown {
        width: 320px;
        max-height: 400px;
        overflow-y: auto;
    }

    .reports-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .report-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .report-item:hover {
        background-color: rgb(250, 248, 250);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const reportsBtn = document.getElementById("reportsNotificationBtn");
        const reportsDropdown = document.getElementById("reportsDropdown");
        const notificationSound = document.getElementById("reportsNotificationSound");
        let lastCount = <?php echo $totalReportes; ?>;

        // Toggle dropdown
        reportsBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            reportsDropdown.classList.toggle("show");
            if (reportsDropdown.classList.contains("show")) {
                loadReports();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function(e) {
            if (!reportsDropdown.contains(e.target) && !reportsBtn.contains(e.target)) {
                reportsDropdown.classList.remove("show");
            }
        });

        // Función para cargar los reportes
        function loadReports() {
            fetch("components/studentsReports/reportsButton.php?action=list")
                .then(response => response.json())
                .then(data => {
                    const reportsList = document.getElementById("reportsList");
                    reportsList.innerHTML = "";

                    if (data.length === 0) {
                        reportsList.innerHTML = '<div class="text-center p-3">No hay reportes pendientes</div>';
                        return;
                    }

                    data.forEach(reporte => {
                        const reportItem = document.createElement("div");
                        reportItem.className = "report-item";
                        reportItem.innerHTML = `
                            <strong>ID: ${reporte.number_id}</strong>
                            <div class="text-muted small">${reporte.nombre}</div>
                            <small class="text-muted d-block">${reporte.fecha}</small>
                        `;
                        reportItem.addEventListener("click", function() {
                            window.location.href = "gestionarReportes.php";
                        });

                        reportsList.appendChild(reportItem);
                    });
                })
                .catch(error => {
                    console.error("Error al cargar reportes:", error);
                });
        }

        // Función para actualizar el contador
        function updateReportsCounter() {
            fetch("components/studentsReports/reportsButton.php?action=count")
                .then(response => response.json())
                .then(data => {
                    const reportsCounter = document.getElementById("reportsCounter");
                    reportsCounter.textContent = data.count;
                    
                    // Reproducir sonido si hay nuevos reportes
                    if (data.count > lastCount) {
                        const tempAudio = new Audio("components/studentsReports/notificacion_reporte.mp3");
                        tempAudio.play().catch(err => {
                            console.log("Error reproduciendo sonido:", err.message);
                        });
                    }
                    
                    lastCount = data.count;
                })
                .catch(error => {
                    console.error("Error al actualizar contador:", error);
                });
        }

        // Actualizar cada 30 segundos
        setInterval(updateReportsCounter, 30000);
    });
</script>