<?php
// Verifica la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener cursos con su tipo
$sql = 'SELECT 
    id_bootcamp AS id, 
    bootcamp_name AS nombre, 
    "bootcamp_teacher_id" AS tipocampo,
    bootcamp_teacher_id AS docente_actual 
FROM groups WHERE id_bootcamp IS NOT NULL
UNION
SELECT 
    id_leveling_english, 
    leveling_english_name, 
    "le_teacher_id",
    le_teacher_id AS docente_actual 
FROM groups WHERE id_leveling_english IS NOT NULL
UNION
SELECT 
    id_english_code, 
    english_code_name, 
    "ec_teacher_id",
    ec_teacher_id AS docente_actual 
FROM groups WHERE id_english_code IS NOT NULL
UNION
SELECT 
    id_skills, 
    skills_name, 
    "skills_teacher_id",
    skills_teacher_id AS docente_actual 
FROM groups WHERE id_skills IS NOT NULL';

$sql_docentes = "SELECT id, username, nombre FROM users WHERE rol = 5";

// Ejecuta la consulta de cursos
$resultado = $conn->query($sql);

// Obtener lista de docentes
$docentes = [];
$resultado_docentes = $conn->query($sql_docentes);
if ($resultado_docentes->num_rows > 0) {
    while ($docente = $resultado_docentes->fetch_assoc()) {
        $docentes[] = $docente;
    }
}

// Generar formulario
if ($resultado->num_rows > 0) {
    echo '<div class="container mt-4">';
    echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">';

    while ($row = $resultado->fetch_assoc()) {
        // Buscar el nombre del docente actual (opcional, solo si quieres mostrarlo)
        $current_docente_nombre = '';
        foreach ($docentes as $d) {
            if ($d['username'] == $row["docente_actual"]) {
                $current_docente_nombre = $d['nombre'];
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
                        <label class="form-label">Buscar Docente</label>
                        <!-- Select con opciones de docentes -->
                        <select class="form-select docente-select"
                                data-idcurso="' . htmlspecialchars($row["id"]) . '"
                                data-tipocampo="' . htmlspecialchars($row["tipocampo"]) . '">
                            <option value="">Seleccione un docente</option>';
                            
                            // Rellenar las opciones con la lista de docentes
                            foreach ($docentes as $docente) {
                                // Marcar como "selected" si coincide con el docente_actual
                                $selected = ($docente['username'] == $row['docente_actual']) ? 'selected' : '';
                                echo '<option value="' . $docente['username'] . '" ' . $selected . '>' 
                                     . htmlspecialchars($docente['nombre']) . '</option>';
                            }

        echo '          </select>
                        <button class="btn bg-indigo-dark text-white w-100 mt-2 actualizar-docente">Actualizar</button>
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
    height: 38px; /* similar a la altura de un form-control de Bootstrap */
  }
</style>

<!-- Scripts: jQuery, Select2 y SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Inicializa Select2 en todos los <select> con la clase .docente-select
    $('.docente-select').select2({
        width: '100%' // se adapta al contenedor
    });

    // Manejo del botón "Actualizar"
    document.querySelectorAll('.actualizar-docente').forEach(btn => {
        btn.addEventListener('click', function() {
            const cardBody = btn.closest('.card-body');
            const select = cardBody.querySelector('.docente-select');
            const docenteUsername = select.value; // valor seleccionado
            const idCurso = select.dataset.idcurso;
            const tipoCampo = select.dataset.tipocampo;

            if (!docenteUsername) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Seleccione un docente primero'
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

            // Realizar la petición a tu archivo update_teacher.php
            fetch("components/setTeachers/update_teacher.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: new URLSearchParams({
                    id_curso: idCurso,
                    docente: docenteUsername,
                    tipo_campo: tipoCampo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Docente actualizado correctamente',
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