<?php
// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Verificar conexión a la base de datos
require __DIR__ . '../../../controller/conexion.php';
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión a la base de datos: " .
        (isset($conn) ? $conn->connect_error : "No se pudo establecer la conexión"));
}

// Función para llamar a la API de Moodle
function callMoodleAPI($function, $params = [])
{
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();

// Consulta para obtener usuarios
$sql = "SELECT ur.*, d.departamento, ca.*
    FROM user_register ur
    INNER JOIN departamentos d ON ur.department = d.id_departamento 
    LEFT JOIN course_assignments ca ON ur.number_id = ca.student_id
    WHERE d.id_departamento = 11
      AND ur.status = '1' 
    AND ur.statusAdmin IN ('1', '8')";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}

// Obtener datos únicos para los filtros
$departamentos = ['BOGOTÁ, D.C.'];
$programas = [];
$modalidades = [];
$sedes = []; // Agregar array para sedes
$niveles = ['Explorador', 'Integrador', 'Innovador'];
$horarios = [];

foreach ($data as $row) {
    $depto = $row['departamento'];
    $sede = $row['headquarters'];


    // Obtener sedes únicas
    if (!in_array($sede, $sedes) && !empty($sede)) {
        $sedes[] = $sede;
    }

    // Obtener programas únicas
    if (!in_array($row['program'], $programas)) {
        $programas[] = $row['program'];
    }

    // Obtener modalidades únicas
    if (!in_array($row['mode'], $modalidades)) {
        $modalidades[] = $row['mode'];
    }

    // Obtener horarios únicos
    if (!in_array($row['schedules'], $horarios)) {
        $horarios[] = $row['schedules'];
    }
}

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Estilos para la tabla con scroll */
    /* Estilos para la tabla con encabezados fijos */
    .table-container {
        max-height: 60vh;
        overflow: hidden;
        border: 1px solidrgb(49, 28, 75);
        border-radius: 4px;
        padding: 5px;
    }

    .table-wrapper {
        overflow-y: auto;
        max-height: inherit;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        border: 1px solid transparent;
        position: sticky;
        top: 0;
        z-index: 3;
    }

    /* Evitar bordes automáticos */
    .table-bordered th {
        border: 1px solid transparent !important;
    }

    .table td {
        white-space: nowrap;
        padding: 8px;
    }

    /* Ajuste para el scroll horizontal */
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1rem;
    }

    /* Asegurar que el encabezado se mantenga visible al hacer scroll horizontal */
    .table thead {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    /* Estilo para el fondo del encabezado */
    .thead-dark {
        background-color: #343a40;
        color: white;
    }

    .table-container {
        max-height: 60vh;
        overflow: hidden;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 20px;
    }

    .table-wrapper {
        overflow-y: auto;
        max-height: inherit;
    }

    .table {
        width: 100%;
        margin-bottom: 0;
        background-color: white;
    }

    /* Asegurarse que la tabla sea visible cuando se muestre */
    .table.table-bordered {
        display: table !important;
        visibility: visible !important;
    }
</style>
<div class="container-fluid px-2">
    <div class="table-responsive">

        <div class="row">
            <div class="col-md-3">
                <b class="text-left mb-1"><i class="bi bi-card-checklist"></i> Seleccionar cursos</b>

                <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="course-title text-indigo-dark "><i class="bi bi-laptop"></i> Bootcamp</div>
                    <div class="card course-card card-bootcamp" data-icon="💻">
                        <div class="card-body">
                            <select id="bootcamp" class="form-select course-select">
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php
                                        $categoryAllowed = in_array($course['categoryid'], [20, 22, 23, 25, 28, 35, 19, 21, 24, 26, 27, 34, 35]);
                                        if ($categoryAllowed):
                                        ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Agregar después del selector de bootcamp -->
                <div class="col-md-12 mt-2" id="activeFilterInfo" style="display: none;">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Filtro activo por código: <strong id="activeCodeFilter"></strong>
                        <button class="btn btn-sm btn-outline-secondary ms-2" id="clearFilter">
                            <i class="bi bi-x-circle"></i> Limpiar filtro
                        </button>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="course-title text-indigo-dark"><i class="bi bi-translate"></i> Inglés nivelatorio</div>
                    <div class="card course-card card-ingles" data-icon="🌍">
                        <div class="card-body">
                            <select id="ingles" class="form-select course-select" readonly>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php if (in_array($course['categoryid'], [17, 18])): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                                <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="course-title text-indigo-dark"><i class="bi bi-code-slash"></i> English Code</div>
                    <div class="card course-card card-english-code" data-icon="👨‍💻">
                        <div class="card-body">
                            <select id="english_code" class="form-select course-select" readonly>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php if (in_array($course['categoryid'], [30, 31])): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                                <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="course-title text-indigo-dark"><i class="bi bi-lightbulb"></i> Habilidades</div>
                    <div class="card course-card card-skills" data-icon="💡">
                        <div class="card-body">
                            <select id="skills" class="form-select course-select" readonly>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php if (in_array($course['categoryid'], [32, 33])): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                                <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex gap-2 mb-3">

                    <!-- Button to open filter modal -->
                    <!-- <button type="button" class="btn bg-indigo-dark text-white" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-filter-circle"></i> Filtros personalizados
                    </button> -->

                    <!-- Botón para exportar a Excel -->
                    <button id="exportarExcel" class="btn btn-success"
                        onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
                        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                    </button>

                    <!-- Botón para mostrar usuarios seleccionados -->
                    <button class="btn bg-magenta-dark text-white d-flex align-items-center gap-2"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#selectedUsersList">
                        <i class="bi bi-list-check"></i>
                        <span>Gestionar seleccionados (<span id="floatingSelectedCount">0</span>)</span>
                    </button>

                </div>
                <div class="table-container">
                    <div class="table-wrapper">

                        <table class="table table-hover table-bordered" style="display: none;">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Tipo ID</th>
                                    <th>Número</th>
                                    <th>Nombre</th>
                                    <th>Programa</th>
                                    <th>Preferencia</th>
                                    <th>Modalidad</th>
                                    <th class="text-center">
                                        <input type="checkbox" id="selectAllCheckbox" class="form-check-input"
                                            style="width: 25px; height: 25px; appearance: none; background-color: white; border: 2px solid #ec008c; cursor: pointer; position: relative;">
                                    </th>
                                    <th>Email</th>
                                    <th>Nuevo Email</th>
                                    <th>Departamento</th>
                                    <th>Sede</th>
                                    <th>Bootcamp pre-asignado</th>
                                    <th>Ingles nivelador pre-asignado</th>
                                    <th>English code pre-asignado</th>
                                    <th>Habilidades de poder pre-asignado</th>
                                    <th>Horario</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="studentsTableBody">
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                            <div id="initialMessage" class="text-center p-5">
                                <i class="bi bi-arrow-left-circle fs-1 text-muted"></i>
                                <h4 class="mt-3">Seleccione un bootcamp para cargar los estudiantes</h4>
                                <p class="text-muted">Los datos se cargarán automáticamente al seleccionar un curso</p>
                            </div>
                        </table>
                    </div>
                </div>



                <!-- Filter Modal -->
                <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filterModalLabel">
                                    <i class="bi bi-filter-circle"></i> Filtros de Búsqueda
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <div class="filter-title"><i class="bi bi-map"></i> Departamento</div>
                                        <div class="card filter-card card-department" data-icon="📍">
                                            <div class="card-body">
                                                <select id="filterDepartment" class="form-select">
                                                    <option value="">Todos los departamentos</option>
                                                    <option value="BOYACÁ">BOYACÁ</option>
                                                    <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
                                        <div class="card filter-card card-headquarters" data-icon="🏫">
                                            <div class="card-body">
                                                <select id="filterHeadquarters" class="form-select">
                                                    <option value="">Todas las sedes</option>
                                                    <?php foreach ($sedes as $sede): ?>
                                                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <div class="filter-title"><i class="bi bi-mortarboard"></i> Programa</div>
                                        <div class="card filter-card card-program" data-icon="🎓">
                                            <div class="card-body">
                                                <select id="filterProgram" class="form-select">
                                                    <option value="">Todos los programas</option>
                                                    <?php foreach ($programas as $programa): ?>
                                                        <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <div class="filter-title"><i class="bi bi-laptop"></i> Modalidad</div>
                                        <div class="card filter-card card-mode" data-icon="💻">
                                            <div class="card-body">
                                                <select id="filterMode" class="form-select">
                                                    <option value="">Todas las modalidades</option>
                                                    <option value="Virtual">Virtual</option>
                                                    <option value="Presencial">Presencial</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <div class="filter-title"><i class="bi bi-layers"></i> Preferencia</div>
                                        <div class="card filter-card card-level" data-icon="⭐">
                                            <div class="card-body">
                                                <select id="filterLevel" class="form-select">
                                                    <option value="">Todos los niveles</option>
                                                    <option value="Explorador">Explorador</option>
                                                    <option value="Integrador">Integrador</option>
                                                    <option value="Innovador">Innovador</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <div class="filter-title"><i class="bi bi-clock"></i> Horario</div>
                                        <div class="card filter-card card-schedule" data-icon="⏰">
                                            <div class="card-body">
                                                <select id="filterSchedule" class="form-select">
                                                    <option value="">Todos los horarios</option>
                                                    <?php foreach ($horarios as $horario): ?>
                                                        <option value="<?= htmlspecialchars($horario) ?>"><?= htmlspecialchars($horario) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-magenta-dark text-white" data-bs-dismiss="modal">
                                    <i class="bi bi-check2-circle"></i> Ver resultados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Agregar después de la tabla -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="selectedUsersList" aria-labelledby="selectedUsersListLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="selectedUsersListLabel">
                            <i class="bi bi-person-check"></i> Beneficiarios seleccionados (<span id="selectedCount">0</span>)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>

                    </div>


                    <div class="offcanvas-body">
                        <div class="m-3">
                            <button id="enrollSelectedUsers" class="btn bg-magenta-dark text-white w-100">
                                <i class="bi bi-patch-check-fill"></i> Matricular Seleccionados
                            </button>
                        </div>
                        <div id="selectedUsersContainer"></div>

                    </div>
                </div>


            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // 1. Definir las variables globales
                const bootcampSelect = document.getElementById('bootcamp');
                const tableContainer = document.querySelector('.table');
                const initialMessage = document.getElementById('initialMessage');
                let rowCounter = 1; // Agregar variable contador fuera de la función
                const selectedUsers = new Map(); // selectedUsers se define aquí

                // 2. Función para crear el spinner
                function createLoadingSpinner() {
                    const spinner = document.createElement('div');
                    spinner.innerHTML = `
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <h5 class="mt-3">Cargando estudiantes...</h5>
                        </div>
                    `;
                    return spinner;
                }

                // 3. Función para obtener el código del curso
                function getCourseCode(courseName) {
                    if (courseName && courseName.length >= 6) {
                        const match = courseName.match(/C\d+L\d+-G\d+[A-Z]?/);
                        return match ? match[0] : courseName.slice(-6);
                    }
                    return null;
                }

                // 4. Función para cargar los datos
                async function loadStudentsData(courseCode) {
                    rowCounter = 1; // Resetear contador al cargar nuevos datos
                    const loadingSpinner = createLoadingSpinner();

                    try {
                        // Mostrar spinner y ocultar mensaje inicial
                        initialMessage.style.display = 'none';
                        document.querySelector('.table-wrapper').insertBefore(loadingSpinner, tableContainer);

                        console.log('Cargando datos para el código:', courseCode); // Debug

                        const response = await fetch(`components/registerMoodle/load_students.php?courseCode=${courseCode}`);
                        const data = await response.json();

                        console.log('Datos recibidos:', data); // Debug

                        if (data.success) {
                            const tbody = document.getElementById('studentsTableBody');
                            tbody.innerHTML = ''; // Limpiar tabla existente

                            data.data.forEach(row => {
                                const tr = createStudentRow(row);
                                tbody.appendChild(tr);
                            });

                            // Mostrar tabla y ocultar spinner
                            loadingSpinner.remove();
                            tableContainer.style.display = 'table';
                            initialMessage.style.display = 'none';

                            // Asegurarnos de que la tabla sea visible
                            const table = document.querySelector('.table');
                            if (table) {
                                table.style.display = 'table';
                            }

                            updateVisibleRowsCount();
                            if (courseCode) {
                                filterStudentsByCode(courseCode);
                            }
                        } else {
                            throw new Error(data.message || 'No se pudieron cargar los datos');
                        }
                    } catch (error) {
                        console.error('Error cargando datos:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar los datos: ' + error.message
                        });

                        loadingSpinner.remove();
                        initialMessage.style.display = 'block';
                    }
                }

                // 5. Agregar el evento change al selector de bootcamp
                if (bootcampSelect) {
                    bootcampSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const selectedText = selectedOption.text || '';
                        const courseCode = getCourseCode(selectedText);

                        console.log('Bootcamp seleccionado:', selectedText);
                        console.log('Código extraído:', courseCode);

                        if (courseCode) {
                            try {
                                loadStudentsData(courseCode);
                                autoSelectRelatedCourses(courseCode);
                            } catch (error) {
                                console.error('Error al cargar datos:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error al cargar los datos: ' + error.message
                                });
                            }
                        }
                    });
                }

                // 6. Función para actualizar el contador de filas visibles
                function updateVisibleRowsCount() {
                    const visibleRows = document.querySelectorAll('table tbody tr[style="display: ;"], table tbody tr:not([style*="display"])').length;
                    const totalRows = document.querySelectorAll('table tbody tr').length;

                    let countElement = document.getElementById('rowsCounter');
                    if (!countElement) {
                        countElement = document.createElement('div');
                        countElement.id = 'rowsCounter';
                        countElement.className = 'alert alert-info mt-2';
                        const tableContainer = document.querySelector('.table-container');
                        if (tableContainer) {
                            tableContainer.parentNode.insertBefore(countElement, tableContainer);
                        }
                    }

                    countElement.innerHTML = `Mostrando <b>${visibleRows}</b> de <b>${totalRows}</b> estudiantes`;
                }

                // 7. Función para crear la fila de estudiante
                function createStudentRow(rowData) {
                    const tr = document.createElement('tr');

                    // Construir el nombre completo
                    const fullName = `${rowData.first_name || ''} ${rowData.second_name || ''} ${rowData.first_last || ''} ${rowData.second_last || ''}`.trim();

                    // Generar el correo institucional siguiendo el mismo patrón
                    const nuevoCorreo = (
                        (rowData.first_name ? rowData.first_name.substring(0, 1).toLowerCase() : '') +
                        (rowData.second_name ? rowData.second_name.substring(0, 1).toLowerCase() : '') +
                        (rowData.number_id ? rowData.number_id.slice(-4) : '') +
                        (rowData.first_last ? rowData.first_last.substring(0, 1).toLowerCase() : '') +
                        (rowData.second_last ? rowData.second_last.substring(0, 1).toLowerCase() : '') +
                        '.ut@cendi.edu.co'
                    );

                    // Configurar datasets
                    tr.dataset.typeId = rowData.typeID;
                    tr.dataset.numberId = rowData.number_id;
                    tr.dataset.fullName = fullName;
                    tr.dataset.program = rowData.program;
                    tr.dataset.level = rowData.level;
                    tr.dataset.mode = rowData.mode;
                    tr.dataset.email = rowData.email;
                    tr.dataset.institutionalEmail = nuevoCorreo;  // Agregar el nuevo correo al dataset
                    tr.dataset.department = rowData.departamento;
                    tr.dataset.headquarters = rowData.headquarters;
                    tr.dataset.schedule = rowData.schedules;

                    // Modificar la construcción del HTML de la fila para incluir el nuevo correo
                    tr.innerHTML = `
                        <td><span class="badge bg-magenta-dark"></span></td>
                        <td>${rowData.typeID || ''}</td>
                        <td>${rowData.number_id || ''}</td>
                        <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${fullName}
                        </td>
                        <td>${rowData.program || ''}</td>
                        <td>${rowData.level || ''}</td>
                        <td>${rowData.mode || ''}</td>
                        <td class="text-center">
                            <input type="checkbox" 
                                   class="form-check-input student-checkbox" 
                                   data-student-id="${rowData.number_id}"
                                   data-student-name="${fullName}"
                                   data-student-email="${rowData.email}"
                                   style="width: 25px; height: 25px; appearance: none; background-color: white; border: 2px solid #ec008c; cursor: pointer;">
                        </td>
                        <td>${rowData.email || ''}</td>
                        <td>${nuevoCorreo}</td>
                        <td>${rowData.departamento || ''}</td>
                        <td>${rowData.headquarters || ''}</td>
                        <td>${rowData.bootcamp_pre_asignado || 'Sin asignar'}</td>
                        <td>${rowData.ingles_pre_asignado || 'Sin asignar'}</td>
                        <td>${rowData.english_code_pre_asignado || 'Sin asignar'}</td>
                        <td>${rowData.habilidades_pre_asignado || 'Sin asignar'}</td>
                        <td class="text-center">
                            <a class="btn bg-indigo-light" 
                               tabindex="0" 
                               role="button" 
                               data-bs-toggle="popover" 
                               data-bs-trigger="focus" 
                               data-bs-placement="top"
                               title="${rowData.schedules || 'Sin horario asignado'}">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </td>
                    `;

                    // Inicializar los popovers
                    const popoverTriggerList = [].slice.call(tr.querySelectorAll('[data-bs-toggle="popover"]'));
                    popoverTriggerList.forEach(popoverTriggerEl => {
                        new bootstrap.Popover(popoverTriggerEl);
                    });

                    return tr;
                }

                // 8. Función para filtrar estudiantes por código
                function filterStudentsByCode(courseCode) {
                    let visibleCounter = 1; // Contador solo para filas visibles

                    if (!courseCode) {
                        document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                            row.style.display = '';
                            // Actualizar el número de la fila visible
                            row.querySelector('td:first-child .badge').textContent = visibleCounter++;
                        });
                        updateVisibleRowsCount();
                        return;
                    }

                    document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                        const bootcampCell = row.querySelector('td:nth-child(13)');
                        const inglesCell = row.querySelector('td:nth-child(14)');
                        const englishCodeCell = row.querySelector('td:nth-child(15)');
                        const skillsCell = row.querySelector('td:nth-child(16)');

                        const bootcampText = bootcampCell?.textContent || '';
                        const inglesText = inglesCell?.textContent || '';
                        const englishCodeText = englishCodeCell?.textContent || '';
                        const skillsText = skillsCell?.textContent || '';

                        if (bootcampText.includes(courseCode) ||
                            inglesText.includes(courseCode) ||
                            englishCodeText.includes(courseCode) ||
                            skillsText.includes(courseCode)) {
                            row.style.display = '';
                            // Actualizar el número solo para las filas visibles
                            row.querySelector('td:first-child .badge').textContent = visibleCounter++;
                        } else {
                            row.style.display = 'none';
                            // Limpiar el número de las filas ocultas
                            row.querySelector('td:first-child .badge').textContent = '';
                        }
                    });

                    updateVisibleRowsCount();
                }

                // 9. Función para autoseleccionar cursos relacionados
                function autoSelectRelatedCourses(courseCode) {
                    if (!courseCode) return;

                    const selectors = ['ingles', 'english_code', 'skills'];

                    selectors.forEach(selector => {
                        const selectElement = document.getElementById(selector);
                        if (!selectElement) return;

                        // Deshabilitar temporalmente el selector
                        selectElement.disabled = true;

                        // Buscar opción con el mismo código
                        const options = Array.from(selectElement.options);
                        const matchingOption = options.find(option =>
                            option.text.includes(courseCode)
                        );

                        if (matchingOption) {
                            selectElement.value = matchingOption.value;
                        } else {
                            // Si no hay coincidencia, habilitar el selector
                            selectElement.disabled = false;
                            selectElement.selectedIndex = 0;
                        }
                    });

                    // Actualizar información del filtro activo
                    const filterInfo = document.getElementById('activeFilterInfo');
                    const activeCodeFilter = document.getElementById('activeCodeFilter');

                    if (filterInfo && activeCodeFilter) {
                        activeCodeFilter.textContent = courseCode;
                        filterInfo.style.display = 'block';
                    }
                }

                // 10. Manejar el botón de limpiar filtro
                const clearFilterBtn = document.getElementById('clearFilter');
                if (clearFilterBtn) {
                    clearFilterBtn.addEventListener('click', function() {
                        // Limpiar selectores
                        ['ingles', 'english_code', 'skills'].forEach(selector => {
                            const selectElement = document.getElementById(selector);
                            if (selectElement) {
                                selectElement.disabled = false;
                                selectElement.selectedIndex = 0;
                            }
                        });

                        // Mostrar todas las filas
                        document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                            row.style.display = '';
                        });

                        // Ocultar info del filtro
                        const filterInfo = document.getElementById('activeFilterInfo');
                        if (filterInfo) {
                            filterInfo.style.display = 'none';
                        }

                        updateVisibleRowsCount();
                    });
                }

                // 11. Modificar el manejo del checkbox "Seleccionar todos"
                const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        // IMPORTANTE: Solo seleccionar checkboxes de filas VISIBLES
                        const visibleCheckboxes = Array.from(document.querySelectorAll('.student-checkbox'))
                            .filter(checkbox => checkbox.closest('tr').style.display !== 'none');

                        visibleCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                            checkbox.style.backgroundColor = this.checked ? '#ec008c' : 'white';

                            // Obtener la fila y actualizar la selección
                            const row = checkbox.closest('tr');
                            if (row) {
                                toggleUserSelection(checkbox, row);
                            }
                        });
                    });
                }

                // 12. Asegurar que toggleUserSelection funcione correctamente
                function toggleUserSelection(checkbox, row) {
                    const numberId = row.dataset.numberId;

                    if (checkbox.checked) {
                        // Obtener todos los datos necesarios de la fila
                        selectedUsers.set(numberId, {
                            type_id: row.dataset.typeId,
                            number_id: numberId,
                            full_name: row.dataset.fullName,
                            email: row.dataset.email,
                            institutional_email: row.dataset.institutionalEmail,
                            department: row.dataset.department,
                            headquarters: row.dataset.headquarters,
                            program: row.dataset.program,
                            mode: row.dataset.mode,
                            level: row.dataset.level,
                            schedule: row.dataset.schedule,
                            password: numberId
                        });
                    } else {
                        selectedUsers.delete(numberId);
                    }

                    // Actualizar la lista y el contador
                    updateSelectedUsersList();
                    updateSelectedCount();
                }

                // 13. Función mejorada para actualizar la lista de usuarios en el offcanvas
                function updateSelectedUsersList() {
                    const container = document.getElementById('selectedUsersContainer');
                    if (!container) return;

                    container.innerHTML = '';
                    let index = 1;

                    selectedUsers.forEach((userData, numberId) => {
                        const userCard = document.createElement('div');
                        userCard.className = 'card mb-2';
                        userCard.innerHTML = `
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-magenta-dark">${index++}</span>
                                        <strong class="ms-2">${userData.full_name}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-person-vcard"></i> ${userData.number_id}
                                            <br>
                                            <i class="bi bi-envelope"></i> ${userData.email}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-sm border-0" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                            <span role="status">En espera</span>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm remove-selected" data-number-id="${numberId}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Agregar evento al botón de eliminar
                        const removeButton = userCard.querySelector('.remove-selected');
                        removeButton.addEventListener('click', function() {
                            removeSelectedUser(numberId);
                        });

                        container.appendChild(userCard);
                    });
                }

                // 14. Función para eliminar usuario seleccionado desde el panel
                function removeSelectedUser(numberId) {
                    // Desmarcar el checkbox en la tabla
                    const checkbox = document.querySelector(`.student-checkbox[data-student-id="${numberId}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                        checkbox.style.backgroundColor = 'white';
                    }

                    // Eliminar del Map de usuarios seleccionados
                    selectedUsers.delete(numberId);

                    // Actualizar la lista y el contador
                    updateSelectedUsersList();
                    updateSelectedCount();

                    // Actualizar estado del checkbox "Seleccionar todos"
                    updateSelectAllCheckboxState();
                }

                // 15. Agregar listener para los checkbox individuales en filas de la tabla
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('student-checkbox')) {
                        const row = e.target.closest('tr');
                        if (row) {
                            toggleUserSelection(e.target, row);
                            e.target.style.backgroundColor = e.target.checked ? '#ec008c' : 'white';
                        }

                        // Actualizar estado del checkbox "Seleccionar todos"
                        updateSelectAllCheckboxState();
                    }
                });

                // 16. Modificar la función updateSelectedCount
                function updateSelectedCount() {
                    const selectedCountElement = document.getElementById('selectedCount');
                    const floatingSelectedCountElement = document.getElementById('floatingSelectedCount');
                    const selectedCount = selectedUsers.size;

                    if (selectedCountElement) {
                        selectedCountElement.textContent = selectedCount;
                    }
                    if (floatingSelectedCountElement) {
                        floatingSelectedCountElement.textContent = selectedCount;
                    }
                }

                // 17. Modificar la función updateSelectAllCheckboxState
                function updateSelectAllCheckboxState() {
                    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                    if (!selectAllCheckbox) return;

                    const visibleCheckboxes = Array.from(document.querySelectorAll('.student-checkbox'))
                        .filter(checkbox => checkbox.closest('tr').style.display !== 'none');

                    const allChecked = visibleCheckboxes.every(checkbox => checkbox.checked);
                    const someChecked = visibleCheckboxes.some(checkbox => checkbox.checked);

                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && someChecked;
                }

                // 18. Inicialización inicial
                updateVisibleRowsCount();

                // Modificar el evento del botón de matriculación
                document.getElementById('enrollSelectedUsers').addEventListener('click', function() {
                    if (selectedUsers.size === 0) { // Se intenta acceder a selectedUsers aquí
                        Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
                        return;
                    }

                    try {
                        const usersToEnroll = [];
                        
                        // Procesamiento asíncrono de usuarios seleccionados
                        const processUsers = async () => {
                            for (const userData of selectedUsers.values()) {
                                try {
                                    const formData = await getFormDataFromRow(userData);
                                    usersToEnroll.push(formData);
                                } catch (error) {
                                    console.error('Error procesando usuario:', error);
                                    throw new Error(`Error al preparar datos para ${userData.full_name}: ${error.message}`);
                                }
                            }
                            confirmBulkEnrollment(usersToEnroll);
                        };

                        processUsers().catch(error => {
                            Swal.fire('Error', error.message, 'error');
                        });
                    } catch (error) {
                        Swal.fire('Error', error.message, 'error');
                    }
                });
            });

            // Función para confirmar la matrícula masiva
            function confirmBulkEnrollment(usersToEnroll) {
                if (usersToEnroll.length === 0) {
                    Swal.fire('Error', 'Por favor seleccione al menos un estudiante', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Confirmar matrícula',
                    text: `¿Está seguro que desea matricular a ${usersToEnroll.length} estudiantes seleccionados?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, matricular',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const result = await processEnrollments(usersToEnroll);

                            Swal.fire({
                                title: 'Resultado del proceso',
                                html: result.message,
                                icon: result.icon,
                                confirmButtonText: 'Entendido'
                            }).then(() => {
                                updateSelectedUsersList();
                                window.location.reload();
                            });
                        } catch (error) {
                            console.error("Error en el proceso de matrícula:", error);
                            Swal.fire({
                                title: 'Error inesperado',
                                html: `Ha ocurrido un error durante el proceso:<br>${error.message}`,
                                icon: 'error',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    }
                });
            }

            // Función para procesar las matrículas
            async function processEnrollments(usersToEnroll) {
                const errors = [];
                let successes = 0;
                let processed = 0;
                let emailSuccesses = 0;

                // Iniciar SweetAlert con el contador de progreso
                Swal.fire({
                    title: 'Procesando matrículas',
                    html: `<div id="enrollmentProgress">Procesando: <b>0</b> de ${usersToEnroll.length}<br>Por favor espere...</div>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                for (const formData of usersToEnroll) {
                    try {
                        // Matrícula
                        const enrollResponse = await fetch('components/registerMoodle/enroll_user_multiple.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData)
                        });

                        let enrollData;
                        try {
                            enrollData = await enrollResponse.json();
                        } catch (jsonError) {
                            throw new Error(`Error al procesar respuesta JSON: ${jsonError.message}\nCódigo HTTP: ${enrollResponse.status}\nEstado: ${enrollResponse.statusText}`);
                        }

                        // Verificar si la respuesta HTTP no es exitosa
                        if (!enrollResponse.ok) {
                            throw new Error(
                                `Error HTTP ${enrollResponse.status}: ${enrollResponse.statusText}\n` +
                                `Detalles: ${enrollData?.message || 'No hay detalles disponibles'}\n` +
                                `Usuario: ${formData.full_name} (${formData.number_id})`
                            );
                        }

                        processed++;

                        // Actualizar la interfaz con el progreso
                        const swalContent = document.getElementById('enrollmentProgress');
                        if (swalContent) {
                            swalContent.innerHTML = `
                                Procesando: <b>${processed}</b> de ${usersToEnroll.length}<br>
                                Éxitos: <b>${successes}</b><br>
                                Errores: <b>${errors.length}</b><br>
                                Por favor espere...
                            `;
                        }

                        // Si la matrícula fue exitosa
                        if (enrollData.success) {
                            successes++;
                            let carnetFilePath = null;
                            try {
                                // Intentar generar/obtener el carnet
                                // Llamamos a generate_carnet.php sin el parámetro 'download=1' para obtener la ruta del archivo
                                const carnetResponse = await fetch(`components/listCredentials/generate_carnet.php?generate_carnet=1&number_id=${formData.number_id}&username=${encodeURIComponent(formData.full_name)}`); // Asumimos que el nombre de usuario para el registro del carnet puede ser el nombre completo. Ajustar si es necesario.
                                if (carnetResponse.ok) {
                                    const carnetData = await carnetResponse.json();
                                    if (carnetData.success && carnetData.file_path) {
                                        carnetFilePath = carnetData.file_path;
                                    } else {
                                        console.warn(`Advertencia al generar/obtener carnet para ${formData.number_id}: ${carnetData.message || 'Respuesta no exitosa o sin ruta de archivo.'}`);
                                        errors.push({
                                            student: formData.number_id,
                                            message: `Matrícula exitosa, pero advertencia al generar/obtener carnet: ${carnetData.message || 'Error desconocido en carnet'}`,
                                            type: 'carnet_warning'
                                        });
                                    }
                                } else {
                                    const errorText = await carnetResponse.text();
                                    console.error(`Error HTTP al generar/obtener carnet para ${formData.number_id}: ${carnetResponse.status} - ${errorText}`);
                                    errors.push({
                                        student: formData.number_id,
                                        message: `Matrícula exitosa, pero error HTTP (${carnetResponse.status}) al generar/obtener carnet.`,
                                        type: 'carnet_http_error'
                                    });
                                }
                            } catch (carnetError) {
                                console.error(`Error en la llamada para generar/obtener carnet para ${formData.number_id}:`, carnetError);
                                errors.push({
                                    student: formData.number_id,
                                    message: `Matrícula exitosa, pero error al procesar generación/obtención de carnet: ${carnetError.message}`,
                                    type: 'carnet_fetch_error'
                                });
                            }

                            try {
                                const emailResponse = await sendEnrollmentEmail(formData, carnetFilePath); // Pasar la ruta del carnet
                                if (emailResponse && emailResponse.success) {
                                    emailSuccesses++;
                                } else {
                                    errors.push({
                                        student: formData.number_id,
                                        message: `Matrícula exitosa, pero error al enviar correo: ${emailResponse?.message || 'Error desconocido'}\nDetalles: ${JSON.stringify(emailResponse)}`,
                                        type: 'email'
                                    });
                                }
                            } catch (emailError) {
                                errors.push({
                                    student: formData.number_id,
                                    message: `Matrícula exitosa, pero error al enviar correo: ${emailError.message}\nDetalles: ${emailError.stack || 'No disponible'}`,
                                    type: 'email'
                                });
                            }
                        } else {
                            errors.push({
                                student: formData.number_id,
                                message: `Error en la matrícula: ${enrollData.message || 'Error desconocido'}\nDetalles: ${JSON.stringify(enrollData)}`,
                                type: 'enroll'
                            });
                        }
                    } catch (error) {
                        processed++;
                        errors.push({
                            student: formData.number_id,
                            message: `Error: ${error.message}\nDetalles: ${error.stack || 'No disponible'}\nDatos enviados: ${JSON.stringify(formData)}`,
                            type: 'server'
                        });

                        // Actualizar progreso incluso en caso de error
                        const swalContent = document.getElementById('enrollmentProgress');
                        if (swalContent) {
                            swalContent.innerHTML = `
                                Procesando: <b>${processed}</b> de ${usersToEnroll.length}<br>
                                Éxitos: <b>${successes}</b><br>
                                Errores: <b>${errors.length}</b><br>
                                Por favor espere...
                            `;
                        }
                    }
                }

                // Generar mensaje de resumen detallado
                let message = `<h4>Resumen de matrícula</h4>`;
                message += `<p>Total procesados: <b>${processed}</b></p>`;
                message += `<p>Matrículas exitosas: <b>${successes}</b></p>`;
                message += `<p>Correos enviados: <b>${emailSuccesses}</b></p>`;
                message += `<p>Errores totales: <b>${errors.length}</b></p>`;

                if (errors.length > 0) {
                    message += '<hr><h5>Detalles de errores:</h5>';
                    message += '<div style="max-height: 200px; overflow-y: auto; text-align: left;">';
                    errors.forEach((err, index) => {
                        message += `
                            <p><b>${index + 1}. Usuario:</b> ${err.student}</p>
                            <p><b>Tipo de error:</b> ${err.type}</p>
                            <p><b>Mensaje:</b> ${err.message}</p>
                            <hr>
                        `;
                    });
                    message += '</div>';
                }

                return {
                    message,
                    icon: errors.length === 0 ? 'success' : (successes > 0 ? 'warning' : 'error')
                };
            }

            // Función para obtener datos del formulario para la matrícula
            async function getFormDataFromRow(userData) {
                const getCourseData = (prefix) => {
                    const select = document.getElementById(prefix);
                    if (!select) {
                        console.error(`Select no encontrado: ${prefix}`);
                        return {
                            id: '0',
                            name: 'No seleccionado'
                        };
                    }
                    const option = select.options[select.selectedIndex];
                    const fullText = option.text;
                    const id = option.value;
                    const name = fullText.split(' - ').slice(1).join(' - ').trim();

                    return {
                        id,
                        name
                    };
                };

                const bootcamp = getCourseData('bootcamp');
                const ingles = getCourseData('ingles');
                const englishCode = getCourseData('english_code');
                const skills = getCourseData('skills');

                const formData = {
                    type_id: userData.type_id,
                    number_id: userData.number_id,
                    full_name: userData.full_name,
                    email: userData.email,
                    institutional_email: userData.institutional_email,
                    department: userData.department,
                    headquarters: userData.headquarters,
                    program: userData.program,
                    mode: userData.mode,
                    password: userData.number_id, // Usar la misma contraseña
                    id_bootcamp: bootcamp.id,
                    bootcamp_name: bootcamp.name,
                    id_leveling_english: ingles.id,
                    leveling_english_name: ingles.name,
                    id_english_code: englishCode.id,
                    english_code_name: englishCode.name,
                    id_skills: skills.id,
                    skills_name: skills.name
                };

                return formData;
            }

            // Función para enviar el correo electrónico
            async function sendEnrollmentEmail(userData) {
                try {
                    const response = await fetch('components/registerMoodle/send_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: userData.email,
                            program: userData.program,
                            first_name: userData.full_name.split(' ')[0], // Toma el primer nombre
                            usuario: userData.number_id,
                            password: userData.password
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }

                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error enviando email:', error);
                    return {
                        success: false,
                        message: 'Error enviando el correo electrónico: ' + error.message
                    };
                }
            }
        </script>
        </body>

        </html>