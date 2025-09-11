<?php
require_once __DIR__ . '/../../controller/conexion.php';

// AJAX endpoints
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'count') {
        echo json_encode(['count' => contarGruposCursosSinPeriodo()]);
        exit;
    }
    if ($_GET['action'] === 'list') {
        echo json_encode(listarGruposCursosSinPeriodo());
        exit;
    }
}

// Función para extraer el código base del curso
function obtenerCodigoGrupo($nombreCurso)
{
    // Buscar formato C{num}L{num}-G{num}{letra} primero
    if (preg_match('/(C\d+L\d+-G\d+[A-Z]?)/', $nombreCurso, $match)) {
        return $match[1];
    }
    // Buscar formato G{num}{letra}
    if (preg_match('/(G\d+[A-Z]?)/', $nombreCurso, $match)) {
        return $match[1];
    }
    return null;
}

// Contar grupos de cursos sin periodo
function contarGruposCursosSinPeriodo()
{
    global $conn;
    $sql = "SELECT c.name, c.cohort
            FROM courses c
            WHERE NOT EXISTS (
                SELECT 1 FROM course_periods cp
                WHERE (cp.bootcamp_code = c.code OR cp.leveling_english_code = c.code OR cp.english_code_code = c.code OR cp.skills_code = c.code)
                AND cp.cohort = c.cohort
            )";
    $result = $conn->query($sql);
    $gruposUnicos = [];

    while ($row = $result->fetch_assoc()) {
        $codigoGrupo = obtenerCodigoGrupo($row['name']);
        if ($codigoGrupo) {
            // Agrupar por código + cohorte para evitar duplicados entre cohortes
            $claveGrupo = $codigoGrupo . '_' . $row['cohort'];
            $gruposUnicos[$claveGrupo] = true;
        }
    }

    return count($gruposUnicos);
}

// Listar grupos de cursos sin periodo
function listarGruposCursosSinPeriodo()
{
    global $conn;
    $sql = "SELECT c.id, c.code, c.name, c.cohort
            FROM courses c
            WHERE NOT EXISTS (
                SELECT 1 FROM course_periods cp
                WHERE (cp.bootcamp_code = c.code OR cp.leveling_english_code = c.code OR cp.english_code_code = c.code OR cp.skills_code = c.code)
                AND cp.cohort = c.cohort
            )
            ORDER BY c.cohort DESC, c.creation_date DESC";
    $result = $conn->query($sql);
    $grupos = [];
    $excluir = ['inglés', 'english', 'skills', 'habilidades'];

    while ($row = $result->fetch_assoc()) {
        $codigoGrupo = obtenerCodigoGrupo($row['name']);
        if ($codigoGrupo) {
            $claveGrupo = $codigoGrupo . '_' . $row['cohort'];
            // Filtrar nombres que NO sean de inglés ni habilidades
            $nombreMinuscula = strtolower($row['name']);
            $esTecnico = true;
            foreach ($excluir as $palabra) {
                if (strpos($nombreMinuscula, $palabra) !== false) {
                    $esTecnico = false;
                    break;
                }
            }
            if ($esTecnico && !isset($grupos[$claveGrupo])) {
                $grupos[$claveGrupo] = [
                    'codigo_grupo' => $codigoGrupo,
                    'nombre' => $row['name'],
                    'cohort' => $row['cohort'],
                    'ejemplo_codigo' => $row['code']
                ];
            }
        }
    }
    return array_values($grupos);
}

//$totalGruposSinPeriodo = contarGruposCursosSinPeriodo();
?>

<div class="periods-notification-container">
    <button id="periodsNotificationBtn" type="button" class="btn text-white bg-teal-dark position-relative">
        <i class="fas fa-calendar-times"></i>
        <span id="periodsCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-orange-dark">

        </span>
    </button>
    <div id="periodsDropdown" class="dropdown-menu periods-dropdown">
        <h6 class="dropdown-header">Grupos de cursos sin periodo asignado</h6>
        <div id="periodsList" class="periods-list text-uppercase">
            <div class="text-center p-2">Cargando...</div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="bootcamp_period.php">Ver todos</a>
    </div>
</div>

<style>
    .periods-notification-container {
        position: relative;
        margin-right: 20px;
    }

    .periods-dropdown {
        width: 350px;
        max-height: 400px;
        overflow-y: auto;
    }

    .periods-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .periods-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .periods-item:hover {
        background-color: #fffbe6;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btn = document.getElementById("periodsNotificationBtn");
        const dropdown = document.getElementById("periodsDropdown");

        // Cargar el contador al iniciar
        updatePeriodsCounter();

        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            dropdown.classList.toggle("show");
            if (dropdown.classList.contains("show")) {
                loadPeriods();
            }
        });

        document.addEventListener("click", function(e) {
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.remove("show");
            }
        });

        function loadPeriods() {
            fetch("components/bootcampPeriods/periods_button.php?action=list")
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById("periodsList");
                    list.innerHTML = "";
                    if (data.length === 0) {
                        list.innerHTML = '<div class="text-center p-3">Todos los cursos tienen periodo</div>';
                        return;
                    }
                    data.forEach(grupo => {
                        const item = document.createElement("div");
                        item.className = "periods-item";
                        item.innerHTML = `
                        <strong>Grupo ${grupo.codigo_grupo}</strong> 
                        <small class="text-muted d-block">Cohorte: ${grupo.cohort}</small>
                        <small class="text-muted">${grupo.nombre}</small>
                    `;
                        item.addEventListener("click", function() {
                            // Redirige con parámetros para autocompletar el modal
                            window.location.href = `bootcamp_period.php?modal=addPeriod&codigo_grupo=${encodeURIComponent(grupo.codigo_grupo)}&cohort=${encodeURIComponent(grupo.cohort)}&nombre=${encodeURIComponent(grupo.nombre)}`;
                        });
                        list.appendChild(item);
                    });
                })
                .catch(error => {
                    console.error("Error al cargar cursos:", error);
                });
        }

        function updatePeriodsCounter() {
            fetch("components/bootcampPeriods/periods_button.php?action=count")
                .then(response => response.json())
                .then(data => {
                    const counter = document.getElementById("periodsCounter");
                    counter.textContent = data.count;
                    lastCount = data.count;
                })
                .catch(error => {
                    console.error("Error al actualizar contador:", error);
                });
        }

        setInterval(updatePeriodsCounter, 600000);
    });
</script>