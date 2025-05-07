<?php
// Conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Procesar peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'count') {
        $count = contarEstudiantesConCertificacion();
        echo json_encode(['count' => $count]);
        exit;
    }
}

// Función para contar estudiantes actuales que tienen certificación anterior
function contarEstudiantesConCertificacion()
{
    global $conn;
    $sql = "SELECT COUNT(*) as total 
            FROM user_register u
            JOIN participantes p ON u.number_id = p.numero_documento
            WHERE u.status = '1'";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

// Obtener conteo inicial
$totalConCertificacion = contarEstudiantesConCertificacion();
?>

<div class="previous-students-container">
    <button id="previousStudentsBtn" type="button" class="btn" style="background-color: #efbf04; position: relative;" 
            data-bs-toggle="popover" 
            data-bs-placement="top" 
            data-bs-content="Descargar listado de estudiantes con certificación previa"
            data-bs-trigger="hover">
        <i class="fa-solid fa-user-graduate"></i>
        <span id="previousStudentsCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-magenta-dark">
            <?php echo $totalConCertificacion; ?>
        </span>
    </button>
</div>

<style>
    .previous-students-container {
        position: relative;
        margin-right: 20px;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const previousStudentsBtn = document.getElementById("previousStudentsBtn");
        let lastCount = <?php echo $totalConCertificacion; ?>;

        // Al hacer clic en el botón, iniciar la exportación
        previousStudentsBtn.addEventListener("click", function(e) {
            exportPreviousStudents();
        });

        // Función para actualizar el contador
        function updatePreviousStudentsCounter() {
            fetch("components/registrationsContact/previous_students_button.php?action=count")
                .then(response => response.json())
                .then(data => {
                    const counter = document.getElementById("previousStudentsCounter");
                    counter.textContent = data.count;
                })
                .catch(error => {
                    console.error("Error al actualizar contador:", error);
                });
        }

        // Actualizar cada 30 segundos
        setInterval(updatePreviousStudentsCounter, 30000);

        // Función para exportar los estudiantes
        function exportPreviousStudents() {
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

            fetch("components/registrationsContact/export_previous_students.php", {
                method: 'POST'
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'listado_estudiantes_certificados.xlsx';
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
        }
    });

    // Inicializar popovers de Bootstrap
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
</script>