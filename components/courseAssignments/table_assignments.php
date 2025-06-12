<?php
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$sql = "SELECT 
            ca.*,
            ur.first_name,
            ur.second_name,
            ur.first_last,
            ur.second_last,
            ur.email,
            ur.first_phone,
            ur.statusAdmin,
            ur.department,
            ur.municipality,
            ur.program,
            ur.level,
            ur.lote,
            ur.mode,
            d.departamento AS department_name,
            m.municipio AS municipality_name,
            u.nombre AS assigned_by_name  
        FROM 
            course_assignments ca
        JOIN 
            user_register ur ON ca.student_id = ur.number_id
        LEFT JOIN 
            departamentos d ON ur.department = d.id_departamento
        LEFT JOIN 
            municipios m ON ur.municipality = m.id_municipio AND m.departamento_id = d.id_departamento
        LEFT JOIN 
            users u ON ca.assigned_by = u.username
        ORDER BY 
            ca.assigned_date DESC";

$result = $conn->query($sql);
$assignments = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
}

// Consulta para obtener los cursos únicos para los filtros
$bootcampSql = "SELECT DISTINCT bootcamp_id, bootcamp_name FROM course_assignments ORDER BY bootcamp_name";
$englishSql = "SELECT DISTINCT leveling_english_id, leveling_english_name FROM course_assignments ORDER BY leveling_english_name";
$englishCodeSql = "SELECT DISTINCT english_code_id, english_code_name FROM course_assignments ORDER BY english_code_name";
$skillsSql = "SELECT DISTINCT skills_id, skills_name FROM course_assignments ORDER BY skills_name";
$loteSql = "SELECT DISTINCT ur.lote FROM course_assignments ca JOIN user_register ur ON ca.student_id = ur.number_id WHERE ur.lote IS NOT NULL AND ur.lote != '' ORDER BY ur.lote";

$bootcampResult = $conn->query($bootcampSql);
$englishResult = $conn->query($englishSql);
$englishCodeResult = $conn->query($englishCodeSql);
$skillsResult = $conn->query($skillsSql);
$loteResult = $conn->query($loteSql);

// Mapeo de códigos de estado a etiquetas descriptivas
$statusLabels = [
    '0' => 'Pendiente',
    '1' => 'Beneficiario',
    '2' => 'Rechazado',
    '3' => 'Matriculado',
    '4' => 'Sin contacto',
    '5' => 'En proceso',
    '6' => 'Culminó proceso',
    '7' => 'Inactivo',
    '8' => 'Beneficiario contrapartida'
];

// Mapeo de códigos de estado a clases CSS
$statusClasses = [
    '0' => 'bg-secondary',
    '1' => 'bg-success',
    '2' => 'bg-danger',
    '3' => 'bg-lime-dark',
    '4' => 'bg-warning',
    '5' => 'bg-info',
    '6' => 'bg-primary',
    '7' => 'bg-dark',
    '8' => 'bg-teal-dark'
];
?>

<div class="container-fluid mb-5">
    <!-- Filtros de cursos -->
    <div class="row mb-4 mt-4">
        <div class="col-md-3">
            <label for="filter-bootcamp" class="form-label fw-bold">Filtrar por Bootcamp</label>
            <select id="filter-bootcamp" class="form-select">
                <option value="">Todos los Bootcamps</option>
                <?php
                if ($bootcampResult && $bootcampResult->num_rows > 0) {
                    while ($course = $bootcampResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($course['bootcamp_id']) . '">' .
                            htmlspecialchars($course['bootcamp_name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter-english" class="form-label fw-bold">Filtrar por Inglés Nivelador</label>
            <select id="filter-english" class="form-select">
                <option value="">Todos los cursos de Inglés</option>
                <?php
                if ($englishResult && $englishResult->num_rows > 0) {
                    while ($course = $englishResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($course['leveling_english_id']) . '">' .
                            htmlspecialchars($course['leveling_english_name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter-english-code" class="form-label fw-bold">Filtrar por English Code</label>
            <select id="filter-english-code" class="form-select">
                <option value="">Todos los English Code</option>
                <?php
                if ($englishCodeResult && $englishCodeResult->num_rows > 0) {
                    while ($course = $englishCodeResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($course['english_code_id']) . '">' .
                            htmlspecialchars($course['english_code_name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter-skills" class="form-label fw-bold">Filtrar por Habilidades</label>
            <select id="filter-skills" class="form-select">
                <option value="">Todas las Habilidades</option>
                <?php
                if ($skillsResult && $skillsResult->num_rows > 0) {
                    while ($course = $skillsResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($course['skills_id']) . '">' .
                            htmlspecialchars($course['skills_name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="row mb-2 mt-3">
            <div class="col-md-3 offset-md-4">
                <label for="filter-lote" class="form-label fw-bold text-center">Filtrar por Lote</label>
                <select id="filter-lote" class="form-select">
                    <option value="">Todos los Lotes</option>
                    <?php
                    if ($loteResult && $loteResult->num_rows > 0) {
                        while ($lote = $loteResult->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($lote['lote']) . '">' .
                                htmlspecialchars($lote['lote']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col">
            <button id="btnExportExcel" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
            </button>
        </div>
    </div>

    <!-- Tabla de asignaciones -->
    <div class="table-responsive">
        <table id="courseAssignmentsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th style="min-width: 300px;">Nombre Completo</th>
                    <th>Departamento</th>
                    <th>Ciudad/Municipio</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Programa</th>
                    <th>Nivel</th>
                    <th>Modalidad</th>
                    <th>Lote</th>
                    <th>Estado</th>
                    <th>Bootcamp</th>
                    <th>Inglés Nivelador</th>
                    <th>English Code</th>
                    <th>Habilidades</th>
                    <th>Fecha Asignación</th>
                    <th>Asignado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $row): ?>
                    <tr data-bootcamp-id="<?= htmlspecialchars($row['bootcamp_id']) ?>"
                        data-english-id="<?= htmlspecialchars($row['leveling_english_id']) ?>"
                        data-english-code-id="<?= htmlspecialchars($row['english_code_id']) ?>"
                        data-skills-id="<?= htmlspecialchars($row['skills_id']) ?>"
                        data-lote="<?= htmlspecialchars($row['lote']) ?>">

                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td>
                            <?= htmlspecialchars(string: $row['first_name'] . ' ' . $row['second_name'] . ' ' . $row['first_last'] . ' ' . $row['second_last']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                        <td><?= htmlspecialchars($row['municipality_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['first_phone']) ?></td>
                        <td><?= htmlspecialchars($row['program']) ?></td>
                        <td><?= htmlspecialchars($row['level']) ?></td>
                        <td><?= htmlspecialchars($row['mode']) ?></td>
                        <td><?= htmlspecialchars($row['lote']) ?></td>
                        <td>
                            <span class="badge <?= $statusClasses[$row['statusAdmin']] ?? 'bg-secondary' ?>">
                                <?= $statusLabels[$row['statusAdmin']] ?? 'Desconocido' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['bootcamp_name']) ?></td>
                        <td><?= htmlspecialchars($row['leveling_english_name']) ?></td>
                        <td><?= htmlspecialchars($row['english_code_name']) ?></td>
                        <td><?= htmlspecialchars($row['skills_name']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['assigned_date']))) ?></td>
                        <td><?= htmlspecialchars($row['assigned_by_name']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-danger btn-eliminar-asignacion"
                                data-student-id="<?= htmlspecialchars($row['student_id']) ?>"
                                data-student-name="<?= htmlspecialchars($row['first_name'] . ' ' . $row['first_last']) ?>">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script para filtros y eliminación -->
<script>
    // Función global para copiar el código
    function copiarCodigo(id) {
        const codigoInput = document.getElementById('securityCode' + id);
        navigator.clipboard.writeText(codigoInput.value).then(() => {
            const copyBtn = document.getElementById('copyBtn' + id);
            const originalContent = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
            copyBtn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(function() {
                copyBtn.innerHTML = originalContent;
                copyBtn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 1500);
        }).catch(err => {
            console.error('Error al copiar: ', err);
        });
    }
</script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        const table = $('#courseAssignmentsTable').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            order: [
                [15, 'desc']
            ], // Ordenar por fecha de asignación descendente
            pageLength: 25
        });

        // Función para aplicar filtros
        function applyFilters() {
            // Elimina cualquier filtro personalizado anterior para evitar acumulaciones
            $.fn.dataTable.ext.search.pop();

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const $row = $(table.row(dataIndex).node()); // Obtener el nodo de la fila

                const bootcampFilter = $('#filter-bootcamp').val();
                const englishFilter = $('#filter-english').val();
                const englishCodeFilter = $('#filter-english-code').val();
                const skillsFilter = $('#filter-skills').val();
                const loteFilter = $('#filter-lote').val();

                // Para cada filtro, si está activo, verificar si la fila coincide.
                // Si un filtro no está activo, se considera que la fila pasa ese filtro.

                let showRow = true; // Asumir que la fila se mostrará

                if (bootcampFilter) { // Si el filtro de Bootcamp está activo
                    if (String($row.data('bootcamp-id')) !== bootcampFilter) {
                        showRow = false; // No coincide, no mostrar
                    }
                }

                if (englishFilter) { // Si el filtro de Inglés está activo
                    if (String($row.data('english-id')) !== englishFilter) {
                        showRow = false; // No coincide, no mostrar
                    }
                }

                if (englishCodeFilter) { // Si el filtro de English Code está activo
                    if (String($row.data('english-code-id')) !== englishCodeFilter) {
                        showRow = false; // No coincide, no mostrar
                    }
                }

                if (skillsFilter) { // Si el filtro de Habilidades está activo
                    if (String($row.data('skills-id')) !== skillsFilter) {
                        showRow = false; // No coincide, no mostrar
                    }
                }

                if (loteFilter) { // Si el filtro de Lote está activo
                    if (String($row.data('lote')) !== loteFilter) {
                        showRow = false; // No coincide, no mostrar
                    }
                }

                return showRow; // Devolver true si la fila pasa todos los filtros activos
            });

            // Redibujar la tabla con los filtros aplicados
            table.draw();
        }

        // Eventos para los filtros
        $('#filter-bootcamp, #filter-english, #filter-english-code, #filter-skills, #filter-lote').on('change', function() {
            applyFilters();
        });

        // Reemplazar la función de eliminación actual con esta nueva versión
        $('#courseAssignmentsTable').on('click', '.btn-eliminar-asignacion', function() {
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');

            // Generar modal con código de seguridad
            const modalHtml = `
            <div class="modal fade" id="deleteModal${studentId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-circle"></i> ATENCIÓN
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h5>Nombre: <strong>${studentName}</strong></h5>
                            <h5>Número ID: <strong>${studentId}</strong></h5>
                            
                            <hr>
                            <div class="card shadow-lg p-3 mb-5 bg-body-tertiary rounded">
                                <p class="mb-1">Para confirmar la eliminación, ingresa el código de seguridad:</p>
                                <div class="code-container mt-2 mb-3 p-2 bg-light">
                                    <div class="input-group">
                                        <input type="text" 
                                            class="form-control security-code" 
                                            id="securityCode${studentId}"
                                            readonly 
                                            value="" 
                                            onclick="this.select();"
                                            style="font-family: monospace; letter-spacing: 3px; text-align: center; font-weight: bold; cursor: pointer;"
                                        >
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" 
                                                id="copyBtn${studentId}"
                                                onclick="copiarCodigo('${studentId}')">
                                                <i class="bi bi-clipboard"></i> Copiar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted text-center d-block mt-1">
                                        Este código cambiará en <span id="codeTimer${studentId}">15</span> segundos
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="confirmCode${studentId}">Ingresa el código</label>
                                    <input type="text" class="form-control-lg text-center w-100" 
                                        id="confirmCode${studentId}" placeholder="Código">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="deleteBtn${studentId}" disabled>
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Remover modal anterior si existe
            $(`#deleteModal${studentId}`).remove();

            // Agregar nuevo modal al DOM
            $('body').append(modalHtml);

            // Inicializar el modal
            const modal = new bootstrap.Modal(document.getElementById(`deleteModal${studentId}`));

            // Variables para el código de seguridad
            let securityCode = '';
            let timer = 15;
            let interval;

            // Función para generar código aleatorio
            function generateCode() {
                const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                let result = '';
                for (let i = 0; i < 6; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            }

            // Función para actualizar el temporizador
            function updateTimer() {
                timer--;
                $(`#codeTimer${studentId}`).text(timer);

                if (timer <= 0) {
                    timer = 15;
                    securityCode = generateCode();
                    $(`#securityCode${studentId}`).val(securityCode);
                }
            }

            // Eventos del modal
            $(`#deleteModal${studentId}`).on('shown.bs.modal', function() {
                securityCode = generateCode();
                $(`#securityCode${studentId}`).val(securityCode);
                timer = 15;
                $(`#codeTimer${studentId}`).text(timer);
                clearInterval(interval);
                interval = setInterval(updateTimer, 1000);
            });

            $(`#deleteModal${studentId}`).on('hidden.bs.modal', function() {
                clearInterval(interval);
                $(`#confirmCode${studentId}`).val('');
                $(`#deleteBtn${studentId}`).prop('disabled', true);
            });

            // Verificar código ingresado
            $(`#confirmCode${studentId}`).on('input', function() {
                const inputCode = $(this).val();
                $(`#deleteBtn${studentId}`).prop('disabled', inputCode !== securityCode);
            });

            // Evento del botón eliminar
            $(`#deleteBtn${studentId}`).on('click', function() {
                modal.hide();

                Swal.fire({
                    title: 'Eliminando asignación',
                    text: 'Por favor espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('components/courseAssignments/delete_assignment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            student_id: studentId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: 'La asignación ha sido eliminada correctamente.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Hubo un problema al eliminar la asignación.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema de conexión con el servidor.'
                        });
                    });
            });

            // Mostrar el modal
            modal.show();
        });

        // Exportar a Excel
        $('#btnExportExcel').on('click', function() {
            // Mostrar SweetAlert de carga
            Swal.fire({
                title: 'Generando Excel',
                html: 'Por favor espere mientras se genera el archivo...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar la petición al archivo export_excel.php
            window.location.href = 'components/courseAssignments/export_excel.php';

            // Cerrar el SweetAlert después de un tiempo prudente
            setTimeout(() => {
                Swal.close();
            }, 3000);
        });
    });
</script>