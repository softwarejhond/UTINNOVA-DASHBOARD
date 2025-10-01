<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

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

// Consulta para obtener los períodos de bootcamp
$sql = "SELECT * FROM course_periods WHERE bootcamp_code IS NOT NULL ORDER BY created_at DESC";
$result = $conn->query($sql);
$periods = [];

// Llenar array con los resultados de la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $periods[] = $row;
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <?php if ($rol === 'Administrador' || $rol === 'Control maestro') { ?>
        <button class="btn bg-magenta-dark text-white mb-4" data-bs-toggle="modal" data-bs-target="#addPeriodModal">

            <i class="bi bi-plus-circle"></i> Nuevo Período
        </button>
    <?php } ?>

    <div class="table-responsive">
        <table id="periodsTable" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <th class="text-center">Período</th>
                    <th class="text-center">Cohorte</th>
                    <th class="text-center"># Pago</th>
                    <th class="text-center">Código Bootcamp</th>
                    <th class="text-center">Nombre Bootcamp</th>
                    <th class="text-center">Fecha Inicio</th>
                    <th class="text-center">Fecha Fin</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Detalles</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($periods)) { ?>
                    <tr>
                        <td colspan="10" class="text-center">
                            <i class="bi bi-inbox"></i> No hay períodos registrados
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($periods as $period) { ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($period['period_name']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($period['cohort']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-magenta-dark text-white">
                                    <?php echo htmlspecialchars($period['payment_number']); ?>
                                </span>
                            </td>
                            <td class="text-center"><?php echo htmlspecialchars($period['bootcamp_code']); ?></td>
                            <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($period['bootcamp_name']); ?>
                            </td>
                            <td class="text-center"><?php echo date('d/m/Y', strtotime($period['start_date'])); ?></td>
                            <td class="text-center"><?php echo date('d/m/Y', strtotime($period['end_date'])); ?></td>
                            <td class="text-center">
                                <?php if ($period['status'] == 1) { ?>
                                    <span class="badge bg-success text-white">Activo</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger text-white">Inactivo</span>
                                <?php } ?>
                            </td>
                            <!-- Botón Detalles -->
                            <td class="text-center">
                                <button class="btn btn-sm bg-indigo-dark text-white"
                                    onclick="showPeriodDetails(<?php echo $period['id']; ?>)"
                                    title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                            <!-- Botón Editar -->
                            <td class="text-center">
                                <?php if ($rol === 'Administrador' || $rol === 'Control maestro') { ?>
                                    <button class="btn btn-warning btn-sm text-black"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal<?php echo $period['id']; ?>"
                                        title="Editar período">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                <?php } ?>
                            </td>
                            <!-- Botón Eliminar -->
                            <td class="text-center">
                                <?php if ($rol === 'Administrador' || $rol === 'Control maestro') { ?>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="deletePeriod(<?php echo $period['id']; ?>)"
                                        title="Eliminar período">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Crear Nuevo Período -->
<div class="modal fade" id="addPeriodModal" tabindex="-1" aria-labelledby="addPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="addPeriodModalLabel">
                    <i class="bi bi-plus-circle"></i> Crear Nuevo Período
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPeriodForm">
                    <!-- Información General del Período -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="period_name_create" class="form-label">Nombre del Período <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="period_name_create" name="period_name"
                                placeholder="Se generará automáticamente" readonly required>
                            <small class="form-text text-muted">El nombre se generará automáticamente basado en el código del curso seleccionado</small>
                        </div>
                        <div class="col-md-6">
                            <label for="cohort_create" class="form-label">Cohorte <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cohort_create" name="cohort"
                                placeholder="Ej: 12" min="1" max="99" required>
                        </div>
                    </div>

                    <!-- Fechas del Período -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date_create" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date_create" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date_create" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date_create" name="end_date" required>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3"><i class="bi bi-laptop"></i> Cursos del Período</h6>

                    <!-- Bootcamp (Técnico) -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-gear"></i> Bootcamp (Técnico)</h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select" id="bootcamp_course_select_create" name="bootcamp_course">
                                <option value="">Seleccione un curso técnico</option>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course):
                                        $categoryAllowed = in_array($course['categoryid'], [20, 22, 23, 25, 28, 34, 19, 21, 24, 26, 27, 35]);
                                        if ($categoryAllowed): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Nivelador de Inglés -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-translate"></i> Inglés Nivelador</h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select" id="leveling_english_course_select_create" name="leveling_english_course">
                                <option value="">Seleccione un curso nivelador</option>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course):
                                        if ($course['categoryid'] == 17 || $course['categoryid'] == 18): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- English Code -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-code-slash"></i> English Code</h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select" id="english_code_course_select_create" name="english_code_course">
                                <option value="">Seleccione un english code</option>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course):
                                        if ($course['categoryid'] == 30 || $course['categoryid'] == 31): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Habilidades -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Habilidades</h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select" id="skills_course_select_create" name="skills_course">
                                <option value="">Seleccione un curso de habilidades</option>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course):
                                        if ($course['categoryid'] == 32 || $course['categoryid'] == 33): ?>
                                            <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Número de Pago y Estado (alineados en una sola línea) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_number_create" class="form-label">Número de Pago</label>
                            <input type="number" class="form-control" id="payment_number_create" name="payment_number"
                                placeholder="Ej: 1" min="0" max="99">
                        </div>
                        <div class="col-md-6">
                            <label for="status_create" class="form-label">Estado</label>
                            <select class="form-select" id="status_create" name="status">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Los campos marcados con <span class="text-danger">*</span> son obligatorios. Los cursos son opcionales y pueden agregarse después.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="createPeriodBtn">
                    <i class="bi bi-save"></i> Crear Período
                </button>
            </div>
        </div>
    </div>
</div>

<?php foreach ($periods as $period) { ?>
    <!-- Modal para Editar Período -->
    <div class="modal fade" id="editModal<?php echo $period['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $period['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editModalLabel<?php echo $period['id']; ?>">
                        <i class="bi bi-pencil-square"></i> Editar Período: <?php echo htmlspecialchars($period['period_name']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPeriodForm<?php echo $period['id']; ?>">
                        <input type="hidden" name="period_id" value="<?php echo $period['id']; ?>">

                        <!-- Información No Editable (Solo Lectura) -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Información del Período</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Nombre:</strong> <?php echo htmlspecialchars($period['period_name']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Bootcamp:</strong> <?php echo htmlspecialchars($period['bootcamp_name'] ?? 'No asignado'); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Campos Editables -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cohort_edit_<?php echo $period['id']; ?>" class="form-label">
                                    Cohorte <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                    class="form-control"
                                    id="cohort_edit_<?php echo $period['id']; ?>"
                                    name="cohort"
                                    value="<?php echo htmlspecialchars($period['cohort']); ?>"
                                    min="1" max="99" required>
                            </div>

                            <div class="col-md-6">
                                <label for="payment_number_edit_<?php echo $period['id']; ?>" class="form-label">Número de Pago</label>
                                <input type="number"
                                    class="form-control"
                                    id="payment_number_edit_<?php echo $period['id']; ?>"
                                    name="payment_number"
                                    value="<?php echo htmlspecialchars($period['payment_number'] ?? 0); ?>"
                                    min="0" max="99">
                            </div>

                            <div class="col-12">
                                <label for="status_edit_<?php echo $period['id']; ?>" class="form-label">Estado</label>
                                <select class="form-select" id="status_edit_<?php echo $period['id']; ?>" name="status">
                                    <option value="1" <?php echo $period['status'] == 1 ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo $period['status'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date_edit_<?php echo $period['id']; ?>" class="form-label">
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                    class="form-control"
                                    id="start_date_edit_<?php echo $period['id']; ?>"
                                    name="start_date"
                                    value="<?php echo htmlspecialchars($period['start_date']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date_edit_<?php echo $period['id']; ?>" class="form-label">
                                    Fecha de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                    class="form-control"
                                    id="end_date_edit_<?php echo $period['id']; ?>"
                                    name="end_date"
                                    value="<?php echo htmlspecialchars($period['end_date']); ?>" required>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Nota:</strong> Solo se pueden editar las fechas, cohorte, número de pago y estado del período.
                            Los cursos asignados no se pueden modificar desde aquí.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="updatePeriod(<?php echo $period['id']; ?>)">
                        <i class="bi bi-save"></i> Actualizar Período
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    $(document).ready(function() {
        // Función para extraer código de grupo del nombre del curso
        function extractGroupCode(fullname) {
            if (!fullname) return null;

            // Buscar patrones como G41-1P, G20-2V, G12-1P, G5-2V, etc.
            var matches = fullname.match(/G\d+-\d+[A-Z]/g);
            if (matches && matches.length > 0) {
                return matches[0];
            }

            // Fallback: buscar otros patrones más específicos
            var altMatches = fullname.match(/C\d+L\d+-?G\d+(?:-\d+)?[A-Z]/g);
            if (altMatches && altMatches.length > 0) {
                return altMatches[0];
            }

            // Patrón adicional para casos especiales
            var specialMatches = fullname.match(/[A-Z]\d+[A-Z]\d+-[A-Z]\d+[A-Z]/g);
            if (specialMatches && specialMatches.length > 0) {
                return specialMatches[0];
            }

            // NUEVO: patrón sugerido por el usuario
            var userPattern = fullname.match(/G\d+[A-Z]?/g);
            if (userPattern && userPattern.length > 0) {
                return userPattern[0];
            }

            return null;
        }

        // Función para autoseleccionar cursos relacionados
        function autoSelectRelatedCourse(selectId, groupCode) {
            console.log("Buscando cursos con código", groupCode, "en", selectId);
            var found = false;

            // Extraer la parte base del código (sin el último carácter P/V para búsqueda más flexible)
            var baseCode = groupCode.replace(/[PV]$/, '');
            console.log("Código base para búsqueda:", baseCode);

            $(selectId + ' option').each(function() {
                var optionText = $(this).text();
                var optionValue = $(this).val();

                if (!optionValue) return;

                // Buscar coincidencia exacta primero
                if (optionText.indexOf(groupCode) > -1) {
                    console.log("¡Coincidencia exacta encontrada! Seleccionando:", optionText);
                    $(selectId).val(optionValue).trigger('change');
                    found = true;
                    return false;
                }

                // Buscar coincidencia por código base (más flexible)
                if (optionText.indexOf(baseCode) > -1) {
                    console.log("¡Coincidencia por código base encontrada! Seleccionando:", optionText);
                    $(selectId).val(optionValue).trigger('change');
                    found = true;
                    return false;
                }
            });

            if (!found) {
                console.log("No se encontraron cursos relacionados para", groupCode);
            }

            return found;
        }

        // Función para actualizar el nombre del período
        function updatePeriodName(groupCode) {
            if (groupCode) {
                $('#period_name_create').val(groupCode);
            }
        }

        // Evento para autoselección cuando cambie el bootcamp
        $('#bootcamp_course_select_create').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var fullname = selectedOption.data('fullname') || selectedOption.text();

            console.log("Bootcamp seleccionado:", fullname);

            if (fullname && $(this).val()) {
                var groupCode = extractGroupCode(fullname);

                if (groupCode) {
                    console.log("Código de grupo encontrado:", groupCode);

                    // Actualizar el nombre del período
                    updatePeriodName(groupCode);

                    // Autoseleccionar cursos relacionados
                    autoSelectRelatedCourse('#leveling_english_course_select_create', groupCode);
                    autoSelectRelatedCourse('#english_code_course_select_create', groupCode);
                    autoSelectRelatedCourse('#skills_course_select_create', groupCode);

                    // Mostrar notificación
                    Swal.fire({
                        icon: 'info',
                        title: 'Autoselección completada',
                        text: `Se han seleccionado automáticamente los cursos relacionados con el código ${groupCode}`,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    console.log("No se pudo extraer el código de grupo de:", fullname);

                    // Intentar extraer manualmente patrones adicionales
                    var manualMatch = fullname.match(/[A-Z]\d+[A-Z]\d+-[A-Z]\d+[A-Z]|G\d+[A-Z]|C\d+L\d+[A-Z]/);
                    if (manualMatch) {
                        var manualCode = manualMatch[0];
                        console.log("Código extraído manualmente:", manualCode);
                        updatePeriodName(manualCode);
                    }
                }
            } else {
                // Si se deselecciona, limpiar campos
                $('#period_name_create').val('');
                $('#leveling_english_course_select_create, #english_code_course_select_create, #skills_course_select_create').val('').trigger('change');
            }
        });

        // Evento para crear período
        $('#createPeriodBtn').on('click', function() {
            var form = $('#addPeriodForm')[0];

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var submitButton = $(this);
            var originalHtml = submitButton.html();

            submitButton.html('<i class="bi bi-hourglass-split"></i> Creando...').prop('disabled', true);

            // Recopilar datos del formulario
            var formData = new FormData();
            formData.append('period_name', $('#period_name_create').val());
            formData.append('cohort', $('#cohort_create').val());
            formData.append('start_date', $('#start_date_create').val());
            formData.append('end_date', $('#end_date_create').val());
            formData.append('status', $('#status_create').val());
            formData.append('created_by', '<?php echo $_SESSION['username'] ?? ''; ?>');

            // Bootcamp
            var bootcampCourse = $('#bootcamp_course_select_create').val();
            if (bootcampCourse) {
                formData.append('bootcamp_code', bootcampCourse);
                formData.append('bootcamp_name', $('#bootcamp_course_select_create option:selected').data('fullname') || $('#bootcamp_course_select_create option:selected').text());
            }

            // Inglés Nivelador
            var levelingCourse = $('#leveling_english_course_select_create').val();
            if (levelingCourse) {
                formData.append('leveling_english_code', levelingCourse);
                formData.append('leveling_english_name', $('#leveling_english_course_select_create option:selected').data('fullname') || $('#leveling_english_course_select_create option:selected').text());
            }

            // English Code
            var englishCodeCourse = $('#english_code_course_select_create').val();
            if (englishCodeCourse) {
                formData.append('english_code_code', englishCodeCourse);
                formData.append('english_code_name', $('#english_code_course_select_create option:selected').data('fullname') || $('#english_code_course_select_create option:selected').text());
            }

            // Habilidades
            var skillsCourse = $('#skills_course_select_create').val();
            if (skillsCourse) {
                formData.append('skills_code', skillsCourse);
                formData.append('skills_name', $('#skills_course_select_create option:selected').data('fullname') || $('#skills_course_select_create option:selected').text());
            }

            // Enviar datos
            $.ajax({
                url: 'components/bootcampPeriods/create_period.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Período creado exitosamente!',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al crear período',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de comunicación',
                        text: 'No se pudo crear el período. Intente nuevamente.',
                        confirmButtonText: 'Aceptar'
                    });
                },
                complete: function() {
                    submitButton.html(originalHtml).prop('disabled', false);
                }
            });
        });

        // Función para eliminar período
        window.deletePeriod = function(periodId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'components/bootcampPeriods/delete_period.php',
                        type: 'POST',
                        data: {
                            period_id: periodId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message,
                                    confirmButtonText: 'Aceptar'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo eliminar el período',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        };

        window.updatePeriod = function(periodId) {
            var form = $('#editPeriodForm' + periodId)[0];
        
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
        
            // Validar fechas antes de enviar
            var startDate = $('#start_date_edit_' + periodId).val();
            var endDate = $('#end_date_edit_' + periodId).val();
        
            if (new Date(endDate) <= new Date(startDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'La fecha de fin debe ser posterior a la fecha de inicio',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
        
            Swal.fire({
                title: '¿Confirmar actualización?',
                text: 'Se actualizarán las fechas, cohorte y estado del período',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle"></i> Sí, actualizar',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var formData = new FormData();
                    formData.append('period_id', periodId);
                    formData.append('cohort', $('#cohort_edit_' + periodId).val());
                    formData.append('payment_number', $('#payment_number_edit_' + periodId).val()); // <-- AGREGAR ESTO
                    formData.append('start_date', startDate);
                    formData.append('end_date', endDate);
                    formData.append('status', $('#status_edit_' + periodId).val());
        
                    $.ajax({
                        url: 'components/bootcampPeriods/update_period.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Actualizando...',
                                text: 'Por favor espere',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Período actualizado!',
                                    text: response.message,
                                    confirmButtonText: 'Aceptar'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al actualizar',
                                    text: response.message,
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de comunicación',
                                text: 'No se pudo actualizar el período. Intente nuevamente.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        };

        // Función para mostrar detalles del período con SweetAlert
        window.showPeriodDetails = function(periodId) {
            // Buscar los datos del período
            $.ajax({
                url: 'components/bootcampPeriods/get_period_details.php',
                type: 'POST',
                data: {
                    period_id: periodId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const period = response.data;

                        // Construir el HTML del contenido
                        const detailsHtml = `
                            <div class="period-details-container">
                                <style>
                                    .period-details-container {
                                        text-align: left;
                                        max-width: 650px;
                                        margin: 0 auto;
                                    }
                                    .detail-section {
                                        background: #f8f9fa;
                                        border-radius: 8px;
                                        padding: 15px;
                                        margin-bottom: 15px;
                                        border-left: 4px solid #007bff;
                                    }
                                    .detail-section.general {
                                        border-left-color: #28a745;
                                    }
                                    .detail-section.bootcamp {
                                        border-left-color: #30336b;
                                    }
                                    .detail-section.english {
                                        border-left-color: #ec008c;
                                    }
                                    .detail-section.english-code {
                                        border-left-color: #006d68;
                                    }
                                    .detail-section.skills {
                                        border-left-color: #5d3fd3;
                                    }
                                    .detail-row {
                                        display: flex;
                                        justify-content: space-between;
                                        margin-bottom: 8px;
                                        padding: 5px 0;
                                        border-bottom: 1px solid #e9ecef;
                                    }
                                    .detail-row:last-child {
                                        border-bottom: none;
                                        margin-bottom: 0;
                                    }
                                    .detail-label {
                                        font-weight: bold;
                                        color: #495057;
                                        flex: 0 0 40%;
                                    }
                                    .detail-value {
                                        color: #212529;
                                        flex: 1;
                                        text-align: right;
                                    }
                                    .section-title {
                                        font-size: 16px;
                                        font-weight: bold;
                                        margin-bottom: 10px;
                                        color: #343a40;
                                        display: flex;
                                        align-items: center;
                                    }
                                    .section-title i {
                                        margin-right: 8px;
                                    }
                                    .badge-custom {
                                        padding: 4px 8px;
                                        border-radius: 4px;
                                        font-size: 12px;
                                        font-weight: bold;
                                    }
                                    .badge-active {
                                        background-color: #28a745;
                                        color: white;
                                    }
                                    .badge-inactive {
                                        background-color: #dc3545;
                                        color: white;
                                    }
                                    .no-course {
                                        color: #6c757d;
                                        font-style: italic;
                                    }
                                </style>
                                
                                <!-- Información General -->
                                <div class="detail-section general">
                                    <div class="section-title">
                                        <i class="bi bi-info-circle"></i>
                                        Información General
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nombre del Período:</span>
                                        <span class="detail-value">${period.period_name}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Cohorte:</span>
                                        <span class="detail-value">${period.cohort}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Fecha de Inicio:</span>
                                        <span class="detail-value">${formatDate(period.start_date)}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Fecha de Fin:</span>
                                        <span class="detail-value">${formatDate(period.end_date)}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Estado:</span>
                                        <span class="detail-value">
                                            <span class="badge-custom ${period.status == 1 ? 'badge-active' : 'badge-inactive'}">
                                                ${period.status == 1 ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Creado por:</span>
                                        <span class="detail-value">${period.created_by}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Fecha de Creación:</span>
                                        <span class="detail-value">${formatDateTime(period.created_at)}</span>
                                    </div>
                                </div>
                                
                                <!-- Bootcamp -->
                                <div class="detail-section bootcamp">
                                    <div class="section-title">
                                        <i class="bi bi-gear"></i>
                                        Bootcamp (Técnico)
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Código:</span>
                                        <span class="detail-value">${period.bootcamp_code || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nombre:</span>
                                        <span class="detail-value">${period.bootcamp_name || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                </div>
                                
                                <!-- Inglés Nivelador -->
                                <div class="detail-section english">
                                    <div class="section-title">
                                        <i class="bi bi-translate"></i>
                                        Inglés Nivelador
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Código:</span>
                                        <span class="detail-value">${period.leveling_english_code || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nombre:</span>
                                        <span class="detail-value">${period.leveling_english_name || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                </div>
                                
                                <!-- English Code -->
                                <div class="detail-section english-code">
                                    <div class="section-title">
                                        <i class="bi bi-code-slash"></i>
                                        English Code
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Código:</span>
                                        <span class="detail-value">${period.english_code_code || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nombre:</span>
                                        <span class="detail-value">${period.english_code_name || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                </div>
                                
                                <!-- Habilidades -->
                                <div class="detail-section skills">
                                    <div class="section-title">
                                        <i class="bi bi-lightbulb"></i>
                                        Habilidades
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Código:</span>
                                        <span class="detail-value">${period.skills_code || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nombre:</span>
                                        <span class="detail-value">${period.skills_name || '<span class="no-course">No asignado</span>'}</span>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Mostrar SweetAlert con los detalles
                        Swal.fire({
                            title: `<strong><i class="bi bi-calendar-event"></i> Detalles del Período</strong>`,
                            html: detailsHtml,
                            width: '55%',
                            maxWidth: '550px',
                            showCloseButton: true,
                            showConfirmButton: true,
                            confirmButtonText: '<i class="bi bi-check-circle"></i> Cerrar',
                            confirmButtonColor: '#007bff',
                            customClass: {
                                popup: 'period-details-popup',
                                title: 'period-details-title'
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudieron cargar los detalles del período',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de comunicación',
                        text: 'No se pudieron cargar los detalles del período',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        };

        // Funciones auxiliares para formatear fechas
        function formatDate(dateString) {
            if (!dateString) return 'No definida';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'No definida';
            const date = new Date(dateTimeString);
            return date.toLocaleString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Hacer el campo de nombre del período de solo lectura
        $('#period_name_create').prop('readonly', true).css('background-color', '#f8f9fa');
    });

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('modal') === 'addPeriod') {
            // Autocompletar campos
            const codigoGrupo = urlParams.get('codigo_grupo') || '';
            const cohort = urlParams.get('cohort') || '';
            const nombre = urlParams.get('nombre') || '';

            document.getElementById('period_name_create').value = codigoGrupo;
            document.getElementById('cohort_create').value = cohort;

            // Bootcamp técnico
            const bootcampSelect = document.getElementById('bootcamp_course_select_create');
            if (bootcampSelect && nombre) {
                for (let opt of bootcampSelect.options) {
                    if (opt.text.includes(nombre) || opt.text.includes(codigoGrupo)) {
                        bootcampSelect.value = opt.value;
                        break;
                    }
                }
            }

            // Inglés nivelador
            const englishLevelSelect = document.getElementById('leveling_english_course_select_create');
            if (englishLevelSelect) {
                for (let opt of englishLevelSelect.options) {
                    if (opt.text.includes(codigoGrupo)) {
                        englishLevelSelect.value = opt.value;
                        break;
                    }
                }
            }

            // English code
            const englishCodeSelect = document.getElementById('english_code_course_select_create');
            if (englishCodeSelect) {
                for (let opt of englishCodeSelect.options) {
                    if (opt.text.includes(codigoGrupo)) {
                        englishCodeSelect.value = opt.value;
                        break;
                    }
                }
            }

            // Habilidades
            const skillsSelect = document.getElementById('skills_course_select_create');
            if (skillsSelect) {
                for (let opt of skillsSelect.options) {
                    if (opt.text.includes(codigoGrupo)) {
                        skillsSelect.value = opt.value;
                        break;
                    }
                }
            }

            // Abre el modal
            const modal = new bootstrap.Modal(document.getElementById('addPeriodModal'));
            modal.show();
        }
    });
</script>