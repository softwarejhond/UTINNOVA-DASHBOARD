<?php
require_once __DIR__ . '/../../controller/conexion.php';

// AJAX endpoints
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'count') {
        echo json_encode(['count' => contarSedesSinSalones()]);
        exit;
    }
    if ($_GET['action'] === 'list') {
        echo json_encode(listarSedesActivas());
        exit;
    }
    if ($_GET['action'] === 'set' && isset($_POST['headquarters'], $_POST['cantidad'])) {
        guardarCantidadSalones($_POST['headquarters'], intval($_POST['cantidad']));
        echo json_encode(['success' => true]);
        exit;
    }
}

// Devuelve sedes activas (distinct headquarters en groups) junto con cantidad de salones
function listarSedesActivas() {
    global $conn;
    $sql = "SELECT DISTINCT headquarters FROM groups WHERE mode = 'Presencial'";
    $result = $conn->query($sql);
    $sedes = [];
    while ($row = $result->fetch_assoc()) {
        $hq = $row['headquarters'];
        $check = $conn->query("SELECT classrooms_count FROM headquarters_classrooms WHERE headquarters = '$hq'");
        $classrooms_count = null;
        if ($check && $check->num_rows > 0) {
            $classrooms_count = $check->fetch_assoc()['classrooms_count'];
        }
        // Obtener aulas ocupadas
        $ocupadas = 0;
        if ($classrooms_count !== null) {
            $resOcupadas = $conn->query("SELECT COUNT(*) as ocupadas FROM classrooms WHERE headquarters = '$hq'");
            $ocupadas = $resOcupadas ? (int)$resOcupadas->fetch_assoc()['ocupadas'] : 0;
        }
        $disponibles = ($classrooms_count !== null) ? max(0, (int)$classrooms_count - $ocupadas) : null;
        $sedes[] = [
            'headquarters' => $hq,
            'classrooms_count' => $classrooms_count,
            'ocupadas' => $ocupadas,
            'disponibles' => $disponibles
        ];
    }
    return $sedes;
}

// Devuelve el número de sedes sin cantidad de salones establecida
function contarSedesSinSalones() {
    global $conn;
    $sql = "SELECT DISTINCT headquarters FROM groups WHERE mode = 'Presencial'";
    $result = $conn->query($sql);
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $hq = $row['headquarters'];
        $check = $conn->query("SELECT COUNT(*) as cnt FROM headquarters_classrooms WHERE headquarters = '$hq'");
        $cnt = $check->fetch_assoc()['cnt'];
        if ($cnt == 0) $total++;
    }
    return $total;
}

// Guarda la cantidad de salones para una sede
function guardarCantidadSalones($headquarters, $cantidad) {
    global $conn;
    $conn->query("REPLACE INTO headquarters_classrooms (headquarters, classrooms_count) VALUES ('$headquarters', $cantidad)");
}

?>

<div class="classrooms-notification-container">
    <button id="classroomsNotificationBtn" type="button" class="btn text-white bg-orange-dark position-relative">
        <i class="fas fa-door-open"></i>
        <span id="classroomsCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-teal-dark ">
            <!-- El contador se llenará por JS -->
        </span>
    </button>
    <div id="classroomsDropdown" class="dropdown-menu classrooms-dropdown">
        <h6 class="dropdown-header">Sedes activas</h6>
        <div id="classroomsList" class="classrooms-list text-uppercase">
            <div class="text-center p-2">Cargando...</div>
        </div>
    </div>
</div>

<style>
.classrooms-notification-container { position: relative; margin-right: 20px; }
.classrooms-dropdown { width: 350px; max-height: 400px; overflow-y: auto; }
.classrooms-list { max-height: 300px; overflow-y: auto; }
.classrooms-item { padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; }
.classrooms-item:hover { background-color: #fffbe6; }
</style>

<!-- Incluye SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("classroomsNotificationBtn");
    const dropdown = document.getElementById("classroomsDropdown");
    let lastCount = 0; // Inicializa en 0

    // Cargar el contador al iniciar
    updateClassroomsCounter();

    btn.addEventListener("click", function(e) {
        e.stopPropagation();
        dropdown.classList.toggle("show");
        if (dropdown.classList.contains("show")) {
            loadClassrooms();
        }
    });

    document.addEventListener("click", function(e) {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.remove("show");
        }
    });

    function loadClassrooms() {
        fetch("components/classrooms/classroom_button.php?action=list")
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById("classroomsList");
                list.innerHTML = "";
                if (data.length === 0) {
                    list.innerHTML = '<div class="text-center p-3">No hay sedes activas</div>';
                    return;
                }
                data.forEach(sede => {
                    const item = document.createElement("div");
                    item.className = "classrooms-item";
                    item.innerHTML = `
                        <strong>${sede.headquarters}</strong>
                        <div class="text-muted" style="font-size: 0.95em;">
                            Total: ${sede.classrooms_count !== null ? sede.classrooms_count : 'No asignado'} |
                            Ocupadas: ${sede.classrooms_count !== null ? sede.ocupadas : '-'} |
                            Disponibles: ${sede.classrooms_count !== null ? sede.disponibles : '-'}
                        </div>
                    `;
                    item.addEventListener("click", function() {
                        showSwal(sede.headquarters, sede.classrooms_count);
                    });
                    list.appendChild(item);
                });
            })
            .catch(error => {
                console.error("Error al cargar sedes:", error);
            });
    }

    function showSwal(sede, cantidadActual) {
        Swal.fire({
            title: `Cantidad de salones para ${sede}`,
            input: 'number',
            inputLabel: 'Ingrese la cantidad de salones disponibles',
            inputAttributes: { min: 1, step: 1 },
            inputValue: cantidadActual !== null ? cantidadActual : '',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            preConfirm: (cantidad) => {
                if (!cantidad || cantidad < 1) {
                    Swal.showValidationMessage('Ingrese un número válido');
                    return false;
                }
                return fetch("components/classrooms/classroom_button.php?action=set", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `headquarters=${encodeURIComponent(sede)}&cantidad=${cantidad}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) throw new Error("Error al guardar");
                })
                .catch(() => {
                    Swal.showValidationMessage('No se pudo guardar');
                });
            }
        }).then(result => {
            if (result.isConfirmed) {
                updateClassroomsCounter();
                Swal.fire('Guardado', 'Cantidad de salones actualizada', 'success');
            }
        });
    }

    function updateClassroomsCounter() {
        fetch("components/classrooms/classroom_button.php?action=count")
            .then(response => response.json())
            .then(data => {
                const counter = document.getElementById("classroomsCounter");
                counter.textContent = data.count;
                lastCount = data.count;
            })
            .catch(error => {
                console.error("Error al actualizar contador:", error);
            });
    }

    setInterval(updateClassroomsCounter, 600000);
});
</script>