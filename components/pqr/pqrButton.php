<?php
// Conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Procesar peticiones AJAX si es necesario
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'count') {
        // Endpoint para contar PQRs
        $count = contarPQRsNuevas();
        echo json_encode(['count' => $count]);
        exit;
    }

    if ($_GET['action'] === 'list') {
        // Endpoint para listar PQRs
        $pqrs = listarPQRsNuevas();
        echo json_encode($pqrs);
        exit;
    }
}

// Función para contar PQRs con estado 1
function contarPQRsNuevas()
{
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM pqr WHERE estado = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["total"];
    }
    return 0;
}

// Función para listar PQRs nuevas
function listarPQRsNuevas()
{
    global $conn;
    $sql = "SELECT id, nombre, DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') as fecha 
            FROM pqr 
            WHERE estado = 1 
            ORDER BY fecha_creacion DESC 
            LIMIT 10";
    $result = $conn->query($sql);

    $pqrs = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pqrs[] = [
                'id' => $row["id"],
                'nombre' => $row["nombre"],
                'fecha' => date("d/m/Y h:i A", strtotime($row["fecha"]))
            ];
        }
    }
    return $pqrs;
}
?>

<div class="pqr-notification-container">
    <button id="pqrNotificationBtn" type="button" class="btn bg-indigo-dark position-relative">
        <i class="fas fa-bell"></i>
        <span id="pqrCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-magenta-dark">
            <!-- El contador se llenará por JS -->
        </span>
    </button>

    <div id="pqrDropdown" class="dropdown-menu pqr-dropdown">
        <h6 class="dropdown-header">Notificaciones de PQRS pendientes</h6>
        <div id="pqrList" class="pqr-list text-uppercase">
            <!-- Las PQR se cargarán aquí mediante AJAX -->
            <div class="text-center p-2">Cargando...</div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="seguimiento_pqr.php">Ver todas</a>
    </div>
</div>

<audio id="notificationSound" src="components/pqr/sounds/notification.mp3" preload="auto"></audio>

<style>
    .pqr-notification-container {
        position: relative;
        margin-right: 20px;
    }

    .pqr-dropdown {
        width: 300px;
        max-height: 400px;
        overflow-y: auto;
    }

    .pqr-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .pqr-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .pqr-item:hover {
        background-color:rgb(250, 248, 250);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const pqrBtn = document.getElementById("pqrNotificationBtn");
        const pqrDropdown = document.getElementById("pqrDropdown");
        const notificationSound = document.getElementById("notificationSound");
        let lastCount = 0; // Inicializa en 0

        // Cargar el contador al iniciar
        updatePQRCounter();

        // Toggle dropdown
        pqrBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            pqrDropdown.classList.toggle("show");
            if (pqrDropdown.classList.contains("show")) {
                loadPQRs();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function(e) {
            if (!pqrDropdown.contains(e.target) && !pqrBtn.contains(e.target)) {
                pqrDropdown.classList.remove("show");
            }
        });

        // Función para cargar las PQRs
        function loadPQRs() {
            fetch("components/pqr/pqrButton.php?action=list")
                .then(response => response.json())
                .then(data => {
                    const pqrList = document.getElementById("pqrList");
                    pqrList.innerHTML = "";

                    if (data.length === 0) {
                        pqrList.innerHTML = '<div class="text-center p-3">No hay notificaciones nuevas</div>';
                        return;
                    }

                    data.forEach(pqr => {
                        const pqrItem = document.createElement("div");
                        pqrItem.className = "pqr-item";
                        pqrItem.innerHTML = `
                            <strong>${pqr.nombre}</strong>
                            <small class="text-muted d-block">${pqr.fecha}</small>
                        `;
                        pqrItem.addEventListener("click", function() {
                            window.location.href = "seguimiento_pqr.php";
                        });

                        pqrList.appendChild(pqrItem);
                    });
                })
                .catch(error => {
                    console.error("Error al cargar PQRs:", error);
                });
        }

        // Función para actualizar el contador
        function updatePQRCounter() {
            fetch("components/pqr/pqrButton.php?action=count")
                .then(response => response.json())
                .then(data => {
                    const pqrCounter = document.getElementById("pqrCounter");
                    pqrCounter.textContent = data.count;
                    
                    // Reproducir sonido si hay nuevas PQRs
                    if (data.count > lastCount) {
                        const tempAudio = new Audio("components/pqr/sounds/notification.mp3");
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

        // Actualizar cada segundo
        setInterval(updatePQRCounter, 1000);
    });
</script>