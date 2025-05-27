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
$sql = "SELECT user_register.*, departamentos.departamento,
    EXISTS(
        SELECT 1 
        FROM participantes p 
        WHERE p.numero_documento = user_register.number_id
    ) AS tiene_certificado
    FROM user_register
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    WHERE departamentos.id_departamento = 11
      AND user_register.status = '1' 
      AND user_register.statusAdmin IN ('1', '8')
    ORDER BY user_register.first_name ASC";

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

    // Obtener programas únicos
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }

    /* Ajuste para el ancho de las columnas */
    .table th,
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
                                        $categoryAllowed = in_array($course['categoryid'], [20, 22, 23, 25, 27, 28, 35]);
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

                <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="course-title text-indigo-dark"><i class="bi bi-translate"></i> Inglés nivelatorio</div>
                    <div class="card course-card card-ingles" data-icon="🌍">
                        <div class="card-body">
                            <select id="ingles" class="form-select course-select">
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php if ($course['categoryid'] == 17 || $course['categoryid'] == 18): ?>
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
                            <select id="english_code" class="form-select course-select">
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php if ($course['categoryid'] == 30 || $course['categoryid'] == 31): ?>
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
                            <select id="skills" class="form-select course-select">
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <?php if ($course['categoryid'] == 32 || $course['categoryid'] == 33): ?>
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
                    <!-- Botón para cambiar lote -->
                    <button id="cambiarLoteBtn" class="btn bg-teal-dark text-white">
                        <i class="bi bi-arrow-repeat"></i> Cambiar Lote
                    </button>

                    <!-- Button to open filter modal -->
                    <button type="button" class="btn bg-indigo-dark text-white" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-filter-circle"></i> Filtros personalizados
                    </button>

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
                        <table id="listaInscritos" class="table table-hover table-bordered">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>Tipo ID</th>
                                    <th>Número</th>
                                    <th>Nombre</th>
                                    <th>Lote</th>
                                    <th>Programa</th>
                                    <th>Preferencia</th>
                                    <th>Modalidad</th>
                                    <th class="text-center">
                                        <i class="bi bi-patch-check-fill"></i>
                                    </th>
                                    <th>Email</th>
                                    <th>Nuevo Email</th>
                                    <th>Departamento</th>
                                    <th>Sede</th>
                                    <th>Horario</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php foreach ($data as $row):
                                    // Procesar datos del usuario
                                    $firstName   = ucwords(strtolower(trim($row['first_name'])));
                                    $secondName  = ucwords(strtolower(trim($row['second_name'])));
                                    $firstLast   = ucwords(strtolower(trim($row['first_last'])));
                                    $secondLast  = ucwords(strtolower(trim($row['second_last'])));
                                    $fullName = $firstName . " " . $secondName . " " . $firstLast . " " . $secondLast;
                                    $nuevoCorreo = strtolower(substr(trim($row['first_name']), 0, 1))
                                        . strtolower(substr(trim($row['second_name']), 0, 1))
                                        . substr(trim($row['number_id']), -4)
                                        . strtolower(substr(trim($row['first_last']), 0, 1))
                                        . strtolower(substr(trim($row['second_last']), 0, 1))
                                        . '.ut@cendi.edu.co';
                                ?>
                                    <tr data-type-id="<?php echo htmlspecialchars($row['typeID']); ?>"
                                        data-number-id="<?php echo htmlspecialchars($row['number_id']); ?>"
                                        data-full-name="<?php echo htmlspecialchars($fullName); ?>"
                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                        data-institutional-email="<?php echo htmlspecialchars($nuevoCorreo); ?>"
                                        data-department="<?= htmlspecialchars($row['departamento']) ?>"
                                        data-headquarters="<?= htmlspecialchars($row['headquarters']) ?>"
                                        data-program="<?= htmlspecialchars($row['program']) ?>"
                                        data-level="<?= htmlspecialchars($row['level']) ?>"
                                        data-schedule="<?= htmlspecialchars($row['schedules']) ?>"
                                        data-mode="<?= htmlspecialchars($row['mode']) ?>">
                                        <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                                        <td style="width: 350px; min-width: 350px; max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($fullName); ?>
                                            <?php if ($row['tiene_certificado']): ?>
                                                <button class="btn text-white ms-2" style="background-color: #ffbf00;"
                                                    onclick="mostrarCertificacionAlert('<?php echo htmlspecialchars($fullName); ?>')"
                                                    data-bs-toggle="popover"
                                                    data-bs-trigger="hover"
                                                    data-bs-placement="top"
                                                    data-bs-content="El estudiante cuenta con una certificación">
                                                    <i class="fa-solid fa-graduation-cap fa-beat text-black"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['lote']); ?></td>
                                        <td><?php echo htmlspecialchars($row['program']); ?></td>
                                        <td><?php echo htmlspecialchars($row['level']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                                        <td>
                                            <input type="checkbox" class="form-check-input usuario-checkbox"
                                                style="width: 25px; height: 25px; appearance: none; background-color: white; border: 2px solid #ec008c; cursor: pointer; position: relative;"
                                                onclick="this.style.backgroundColor = this.checked ? 'magenta' : 'white'">
                                        </td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($nuevoCorreo); ?></td>

                                        <td>
                                            <?php
                                            $departamento = htmlspecialchars($row['departamento']);
                                            if ($departamento === 'CUNDINAMARCA') {
                                                echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>"; // Botón verde para CUNDINAMARCA
                                            } elseif ($departamento === 'BOYACÁ') {
                                                echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>"; // Botón azul para BOYACÁ
                                            } else {
                                                echo "<span>{$departamento}</span>"; // Texto normal para otros valores
                                            }
                                            ?>
                                        </td>
                                        <td><b class="text-center"><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                                        <td class="text-center">
                                            <a class="btn bg-indigo-light"
                                                tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top"
                                                title="<?php echo empty($row['schedules']) ? 'Sin horario asignado' : htmlspecialchars($row['schedules']); ?>">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
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
                                                    <option value="BOGOTÁ, D.C.">BOGOTÁ, D.C.</option>
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
    </div>
    <script>
        const selectedUsers = new Map();

        document.addEventListener("DOMContentLoaded", function() {
            // Modal de selección de lote al inicio
            Swal.fire({
                title: 'Selección de Lote',
                text: 'Seleccione el lote de estudiantes para matrícula en Moodle:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Lote 1',
                cancelButtonText: 'Lote 2',
                confirmButtonColor: '#ec008c',
                cancelButtonColor: '#311c4b',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                const loteSeleccionado = result.isConfirmed ? 1 : 2;
                sessionStorage.setItem('loteSeleccionado', loteSeleccionado);
                filtrarPorLote(loteSeleccionado);
            });

            // Resto del código existente para los checkboxes
            const checkboxes = document.querySelectorAll(".usuario-checkbox");

            function actualizarContador() {
                // Cuenta los checkboxes que están marcados
                const seleccionados = document.querySelectorAll(".usuario-checkbox:checked").length;
                // Actualizar solo los contadores que existen
                const selectedCount = document.getElementById('selectedCount');
                const floatingSelectedCount = document.getElementById('floatingSelectedCount');

                if (selectedCount) selectedCount.textContent = seleccionados;
                if (floatingSelectedCount) floatingSelectedCount.textContent = seleccionados;
            }

            // Agrega un evento a cada checkbox para actualizar el contador
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    const row = this.closest('tr');
                    toggleUserSelection(this, row);
                    actualizarContador();
                });
            });
        });

        // No necesitamos este evento ya que está duplicado más abajo con el ID correcto 'enrollSelectedUsers'

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
                        try {
                            const emailResponse = await sendEnrollmentEmail(formData);
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

        // Modificar la función getFormDataFromRow en multipleRegistrations.php
        async function getFormDataFromRow(row) {
            // Obtener datos del usuario desde el Map de usuarios seleccionados
            const numberId = row.dataset.numberId;
            const userData = selectedUsers.get(numberId) || {};

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

            const formData = {
                type_id: userData.type_id || row.dataset.typeId,
                number_id: numberId,
                full_name: userData.full_name || row.dataset.fullName,
                email: userData.email || row.dataset.email,
                institutional_email: userData.institutional_email || row.dataset.institutionalEmail,
                department: userData.department || row.dataset.department,
                headquarters: userData.headquarters || row.dataset.headquarters,
                program: userData.program || row.dataset.program,
                mode: userData.mode || row.dataset.mode,
                password: userData.password || 'UTt@2025!',
                id_bootcamp: getCourseData('bootcamp').id,
                bootcamp_name: getCourseData('bootcamp').name,
                id_leveling_english: getCourseData('ingles').id,
                leveling_english_name: getCourseData('ingles').name,
                id_english_code: getCourseData('english_code').id,
                english_code_name: getCourseData('english_code').name,
                id_skills: getCourseData('skills').id,
                skills_name: getCourseData('skills').name
            };

            return formData;
        }

        // Modificar el evento de matrícula
        document.getElementById('enrollSelectedUsers').addEventListener('click', function() {
            if (selectedUsers.size === 0) {
                Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
                return;
            }

            try {
                const usersToEnroll = Array.from(selectedUsers.values()).map(userData => {
                    const row = document.querySelector(`tr[data-number-id="${userData.number_id}"]`);
                    if (!row) {
                        throw new Error(`No se encontraron los datos completos para el usuario ${userData.full_name}`);
                    }
                    return getFormDataFromRow(row);
                });

                confirmBulkEnrollment(usersToEnroll);
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        });

        // Modificar la función toggleUserSelection para guardar todos los campos necesarios
        function toggleUserSelection(checkbox, row) {
            const numberId = row.dataset.numberId;

            if (checkbox.checked) {
                // Obtener datos de los cursos seleccionados
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
                    return {
                        id: option.value,
                        name: option.text.split(' - ').slice(1).join(' - ').trim()
                    };
                };

                const bootcamp = getCourseData('bootcamp');
                const ingles = getCourseData('ingles');
                const englishCode = getCourseData('english_code');
                const skills = getCourseData('skills');

                // Agregar usuario a la lista con todos los campos requeridos
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
                    password: 'UTt@2025!',
                    id_bootcamp: bootcamp.id,
                    bootcamp_name: bootcamp.name,
                    id_leveling_english: ingles.id,
                    leveling_english_name: ingles.name,
                    id_english_code: englishCode.id,
                    english_code_name: englishCode.name,
                    id_skills: skills.id,
                    skills_name: skills.name
                });
            } else {
                selectedUsers.delete(numberId);
            }

            updateSelectedUsersList();
        }

        function updateSelectedUsersList() {
            const container = document.getElementById('selectedUsersContainer');
            const selectedCount = document.getElementById('selectedCount');
            const floatingSelectedCount = document.getElementById('floatingSelectedCount');

            container.innerHTML = '';
            selectedUsers.forEach((userData, numberId) => {
                const userCard = document.createElement('div');
                userCard.className = 'card mb-2';
                userCard.innerHTML = `
            <div class="card-body d-flex flex-column text-center">
                <h6 class="card-title mb-2"><b>${userData.full_name}</b></h6>
                <p class="card-text mb-1">
                    <strong>ID:</strong> ${numberId}
                </p>
                <p class="card-text mb-1">
                    <strong>Email:</strong> ${userData.institutional_email}
                </p>
                <button class="btn border-0" type="button" disabled>
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span role="status">En espera para matricular</span>
                </button>
                <button class="btn btn-danger btn-sm mt-auto delete-selection" data-number-id="${numberId}">
                    <i class="bi bi-trash"></i> Eliminar selección
                </button>
            </div>
        `;
                container.appendChild(userCard);

                // Agregar el evento click al botón de eliminar
                const deleteButton = userCard.querySelector('.delete-selection');
                deleteButton.addEventListener('click', function() {
                    const numberId = this.getAttribute('data-number-id');
                    removeSelectedUser(numberId);
                });
            });

            const count = selectedUsers.size;
            if (selectedCount) selectedCount.textContent = count;
            if (floatingSelectedCount) floatingSelectedCount.textContent = count;
        }

        document.getElementById('enrollSelectedUsers').addEventListener('click', function() {
            if (selectedUsers.size === 0) {
                Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
                return;
            }

            // Convertir el Map a un array de usuarios para procesar
            const usersToEnroll = Array.from(selectedUsers.values());
            confirmBulkEnrollment(usersToEnroll);
        });

        function removeSelectedUser(numberId) {
            selectedUsers.delete(numberId);
            // Desmarcar el checkbox en la tabla si está visible
            const checkbox = document.querySelector(`tr[data-number-id="${numberId}"] input[type="checkbox"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
            updateSelectedUsersList();
        }

        // Agregar esta nueva función para enviar el correo
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

        $(document).ready(function() {
            $('[data-toggle="popover"]').popover({
                placement: 'top',
                trigger: 'focus',
                html: true
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const tableWrapper = document.querySelector('.table-wrapper');

            // Mantener la posición del scroll al filtrar
            function saveScrollPosition() {
                sessionStorage.setItem('tableScrollPosition', tableWrapper.scrollTop);
            }

            function restoreScrollPosition() {
                const scrollPosition = sessionStorage.getItem('tableScrollPosition');
                if (scrollPosition) {
                    tableWrapper.scrollTop = parseInt(scrollPosition);
                }
            }

            // Guardar posición del scroll cuando el usuario hace scroll
            tableWrapper.addEventListener('scroll', saveScrollPosition);

            // Restaurar posición del scroll después de filtrar
            const filterSelects = document.querySelectorAll('select[id^="filter"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    setTimeout(restoreScrollPosition, 100);
                });
            });
        });

        // Nueva función para filtrar por lote
        function filtrarPorLote(lote) {
            // // Mostrar indicador de lote activo
            // const loteIndicator = document.createElement('div');
            // loteIndicator.className = 'alert ' + (lote === 1 ? 'bg-magenta-dark' : 'bg-indigo-dark') + ' text-white';
            // loteIndicator.innerHTML = `<i class="bi bi-filter-circle-fill"></i> <b>Lote ${lote} seleccionado</b>`;
            // document.querySelector('.container-fluid').prepend(loteIndicator);

            // 1. Filtrar la tabla de estudiantes
            const filas = document.querySelectorAll('#listaInscritos tbody tr');
            let contadorVisibles = 0;

            filas.forEach(fila => {
                const loteFila = fila.querySelector('td:nth-child(4)').textContent.trim();
                if (loteFila == lote) {
                    fila.style.display = '';
                    contadorVisibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // 2. Filtrar las categorías de los cursos según el lote
            const categoriasPorLote = {
                1: {
                    bootcamp: [20, 22, 23, 25, 28, 35],
                    ingles: [17],
                    english_code: [30],
                    skills: [33]
                },
                2: {
                    bootcamp: [19, 21, 24, 26, 27, 35],
                    ingles: [18],
                    english_code: [31],
                    skills: [32]
                }
            };

            // Función para filtrar opciones de select por categorías
            function filtrarSelectPorCategoria(selectId, categorias) {
                const select = document.getElementById(selectId);
                if (!select) return;

                Array.from(select.options).forEach(option => {
                    if (!option.value) return; // Mantener la opción vacía

                    // Obtener el courseId de la descripción del curso (formato: "ID - Nombre")
                    const courseData = option.text.split(' - ');
                    const courseId = parseInt(courseData[0]);

                    // Obtener categoryId de los datos originales de cursos
                    const course = window.coursesData?.find(c => c.id == option.value);
                    const categoryId = course ? parseInt(course.categoryid) : 0;

                    const visible = categorias.includes(categoryId);
                    option.style.display = visible ? '' : 'none';

                    // Si la opción actual está oculta y seleccionada, seleccionar primera visible
                    if (!visible && option.selected && select.options.length > 0) {
                        for (let i = 0; i < select.options.length; i++) {
                            if (select.options[i].style.display !== 'none') {
                                select.options[i].selected = true;
                                break;
                            }
                        }
                    }
                });
            }

            // Exponer datos de cursos a nivel global
            window.coursesData = <?php echo json_encode($courses_data); ?>;

            // Filtrar cada select por las categorías correspondientes
            filtrarSelectPorCategoria('bootcamp', categoriasPorLote[lote].bootcamp);
            filtrarSelectPorCategoria('ingles', categoriasPorLote[lote].ingles);
            filtrarSelectPorCategoria('english_code', categoriasPorLote[lote].english_code);
            filtrarSelectPorCategoria('skills', categoriasPorLote[lote].skills);

            // Mostrar información sobre registros filtrados
            Swal.fire({
                title: `Lote ${lote} seleccionado`,
                html: `Se han filtrado <b>${contadorVisibles}</b> estudiantes correspondientes al Lote ${lote}.<br>
                       Las categorías de los cursos también han sido filtradas según el lote.`,
                icon: 'info',
                confirmButtonColor: '#ec008c'
            });
        }

        // Evento para cambiar lote después de selección inicial
        document.getElementById('cambiarLoteBtn').addEventListener('click', function() {
            const loteActual = parseInt(sessionStorage.getItem('loteSeleccionado') || '1');
            const nuevoLote = loteActual === 1 ? 2 : 1;

            Swal.fire({
                title: `¿Cambiar a Lote ${nuevoLote}?`,
                text: 'Se limpiarán las selecciones actuales y se filtrarán los estudiantes según el nuevo lote.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ec008c'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Limpiar selecciones
                    selectedUsers.clear();
                    updateSelectedUsersList();

                    // Desmarcar todos los checkboxes
                    document.querySelectorAll('.usuario-checkbox').forEach(cb => {
                        cb.checked = false;
                        cb.style.backgroundColor = 'white';
                    });

                    // Actualizar lote y aplicar filtros
                    sessionStorage.setItem('loteSeleccionado', nuevoLote);
                    filtrarPorLote(nuevoLote);
                }
            });
        });

        // Verificar si hay lote seleccionado previamente
        const loteGuardado = parseInt(sessionStorage.getItem('loteSeleccionado') || '0');
        if (loteGuardado > 0) {
            filtrarPorLote(loteGuardado);
        }

        // Agregar esta función al final del archivo, dentro de las etiquetas <script>
        function mostrarCertificacionAlert(nombreEstudiante) {
            Swal.fire({
                icon: 'warning',
                title: 'Estudiante con certificación previa',
                html: `
                    <div class="alert alert-warning">
                        <p><strong>${nombreEstudiante.toUpperCase()}</strong> ya tiene registrada una certificación en otro lote o región.</p>
                        <p>Tenga esto en cuenta antes de continuar con el proceso de asignación.</p>
                    </div>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ffbf00',
                allowOutsideClick: true
            });
        }

        // Asegúrate de que los popovers estén inicializados
        document.addEventListener('DOMContentLoaded', function() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            if (popoverTriggerList.length > 0) {
                [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
            }
        });
    </script>
    </body>

    </html>