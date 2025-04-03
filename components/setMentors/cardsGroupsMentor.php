<?php
// Verifica la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener cursos con su tipo
$sql = 'WITH RankedGroups AS (
    SELECT 
        id_bootcamp AS id, 
        bootcamp_name AS nombre, 
        "bootcamp_mentor_id" AS tipocampo,
        bootcamp_mentor_id AS mentor_actual,
        ROW_NUMBER() OVER (PARTITION BY id_bootcamp) AS rn
    FROM groups WHERE id_bootcamp IS NOT NULL
    UNION ALL
    SELECT 
        id_leveling_english, 
        leveling_english_name, 
        "le_mentor_id",
        le_mentor_id AS mentor_actual,
        ROW_NUMBER() OVER (PARTITION BY id_leveling_english) AS rn
    FROM groups WHERE id_leveling_english IS NOT NULL
    UNION ALL
    SELECT 
        id_english_code, 
        english_code_name, 
        "ec_mentor_id",
        ec_mentor_id AS mentor_actual,
        ROW_NUMBER() OVER (PARTITION BY id_english_code) AS rn
    FROM groups WHERE id_english_code IS NOT NULL
    UNION ALL
    SELECT 
        id_skills, 
        skills_name, 
        "skills_mentor_id",
        skills_mentor_id AS mentor_actual,
        ROW_NUMBER() OVER (PARTITION BY id_skills) AS rn
    FROM groups WHERE id_skills IS NOT NULL
)
SELECT id, nombre, tipocampo, mentor_actual
FROM RankedGroups 
WHERE rn = 1
ORDER BY id';

$sql_mentors = "SELECT id, username, nombre FROM users WHERE rol = 8";

// Ejecuta la consulta de cursos
$resultado = $conn->query($sql);

// Obtener lista de mentors
$mentors = [];
$resultado_mentors = $conn->query($sql_mentors);
if ($resultado_mentors->num_rows > 0) {
    while ($mentor = $resultado_mentors->fetch_assoc()) {
        $mentors[] = $mentor;
    }
}

// Generar formulario
if ($resultado->num_rows > 0) {
    echo '<div class="container mt-4">';
    echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">';

    while ($row = $resultado->fetch_assoc()) {
        // Buscar el nombre del mentor actual (opcional, solo si quieres mostrarlo)
        $current_mentor_nombre = '';
        foreach ($mentors as $d) {
            if ($d['username'] == $row["mentor_actual"]) {
                $current_mentor_nombre = $d['nombre'];
                break;
            }
        }

        echo '
        <div class="col">
            <div class="card h-100 shadow-sm">
               <div class="bg-lime-dark p-3 text-center text-black" style="height:110px">
                  <h5 class="card-title text-center">' . htmlspecialchars($row["nombre"]) . '</h5>
                    <h4 class="card-text text-indigo-dark">ID: ' . htmlspecialchars($row["id"]) . '</h4>
               </div>
                <div class="card-body d-flex flex-column">
                 
                    
                    <div class="mt-auto">
                        <label class="form-label">Buscar mentor</label>
                        <!-- Select con opciones de mentors -->
                        <select class="form-select mentor-select"
                                data-idcurso="' . htmlspecialchars($row["id"]) . '"
                                data-tipocampo="' . htmlspecialchars($row["tipocampo"]) . '">
                            <option value="">Seleccione un mentor</option>';

        // Rellenar las opciones con la lista de mentors
        foreach ($mentors as $mentor) {
            // Marcar como "selected" si coincide con el mentor_actual
            $selected = ($mentor['username'] == $row['mentor_actual']) ? 'selected' : '';
            echo '<option value="' . $mentor['username'] . '" ' . $selected . '>'
                . htmlspecialchars($mentor['nombre']) . '</option>';
        }

        echo '          </select>
                        <button class="btn bg-indigo-dark text-white w-100 mt-2 actualizar-mentor">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>';
    }

    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="alert alert-info text-center mt-4">No hay cursos disponibles.</div>';
}

$conn->close();
?>

<!-- Estilos opcionales para Select2 (y SweetAlert) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajusta si lo deseas para tu diseño */
    .select2-container .select2-selection--single {
        height: 38px;
        /* similar a la altura de un form-control de Bootstrap */
    }
</style>

<!-- Scripts: jQuery, Select2 y SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializa Select2 en todos los <select> con la clase .mentor-select
        $('.mentor-select').select2({
            width: '100%' // se adapta al contenedor
        });

        // Manejo del botón "Actualizar"
        document.querySelectorAll('.actualizar-mentor').forEach(btn => {
            btn.addEventListener('click', function() {
                const cardBody = btn.closest('.card-body');
                const select = cardBody.querySelector('.mentor-select');
                const mentorUsername = select.value; // valor seleccionado
                const idCurso = select.dataset.idcurso;
                const tipoCampo = select.dataset.tipocampo;

                if (!mentorUsername) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Seleccione un mentor primero'
                    });
                    return;
                }

                // Mostrar spinner mientras se actualiza
                const originalHTML = btn.innerHTML;
                btn.innerHTML = `
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div> Actualizando...
            `;
                btn.disabled = true;

                // Realizar la petición a tu archivo update_mentor.php
                fetch("components/setMentors/update_mentor.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                            id_curso: idCurso,
                            mentor: mentorUsername,
                            tipo_campo: tipoCampo
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'mentor actualizado correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message
                        });
                    })
                    .finally(() => {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    });
            });
        });
    });
</script>