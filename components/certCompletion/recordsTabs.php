<style>
    .nav-tabs .nav-link.active {
        font-weight: bold;
        color: #30336b !important;
        background-color: #f8f9fa;
    }

    .nav-tabs .nav-link {
        color: #000 !important;
        font-weight: normal;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    /* Estilos personalizados para checkboxes */
    .custom-checkbox {
        width: 25px;
        height: 25px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        border: 2px solid #30336b;
        border-radius: 4px;
        background-color: #fff;
        position: relative;
        transition: all 0.3s ease;
        margin: 0;
    }

    .custom-checkbox:hover {
        border-color: #30336b;
        box-shadow: 0 0 5px rgba(48, 51, 107, 0.2);
    }

    .custom-checkbox:checked {
        background-color: #30336b;
        border-color: #30336b;
        box-shadow: 0 0 8px rgba(48, 51, 107, 0.3);
    }

    .custom-checkbox:checked::before {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
        font-weight: bold;
        line-height: 1;
    }

    .custom-checkbox:indeterminate {
        background-color: #30336b;
        border-color: #30336b;
        box-shadow: 0 0 8px rgba(48, 51, 107, 0.3);
    }

    .custom-checkbox:indeterminate::before {
        content: '−';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 16px;
        font-weight: bold;
        line-height: 1;
    }

    .custom-checkbox:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(48, 51, 107, 0.25);
    }

    /* Estilos para el checkbox principal (Seleccionar todos) */
    #selectAllStudents {
        width: 22px;
        height: 22px;
    }

    /* Layout principal */
    .main-container {
        display: flex;
        height: calc(100vh - 100px);
        gap: 20px;
    }

    /* Panel izquierdo de filtros */
    .filters-panel {
        flex: 0 0 350px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
    }

    .filters-panel h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    /* Select2 estilos mínimos */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 10px;
    }

    .select2-dropdown {
        border-radius: 0.375rem;
        border-color: #ced4da;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 8px 12px;
    }

    .select2-results__options {
        max-height: 300px;
        overflow-y: auto;
    }

    /* Panel derecho de tabla */
    .table-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-content {
        flex: 1;
        overflow: auto;
        padding: 0;
    }

    /* Estilos para la tabla */
    .lista-estudiantes {
        width: 100%;
        min-width: 1400px;
        margin: 0;
        table-layout: auto;
    }

    .lista-estudiantes th,
    .lista-estudiantes td {
        vertical-align: middle;
        white-space: nowrap;
        padding: 8px 12px;
        min-width: 100px;
    }

    /* Permitir texto largo solo en nombre y correo */
    .lista-estudiantes td:nth-child(3),
    .lista-estudiantes td:nth-child(4) {
        white-space: normal !important;
        min-width: 180px;
        max-width: 250px;
        word-wrap: break-word;
    }

    /* Thead fijo */
    .lista-estudiantes thead {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .lista-estudiantes thead th {
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        background: #f8f9fa;
        font-weight: 600;
    }

    .badge-success {
        background-color: #66cc00;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-warning {
        background-color: #ffc107;
    }

    .percentage-bar {
        height: 20px;
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .percentage-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
        background-color: #ec008c !important;
    }

    .percentage-fill.success {
        background-color: #ec008c !important;
    }

    .percentage-fill.warning {
        background-color: #ec008c !important;
    }

    .percentage-fill.danger {
        background-color: #ec008c !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
            height: auto;
        }

        .filters-panel {
            flex: none;
        }

        .table-panel {
            min-height: 500px;
        }
    }

    /* Estilos para checkboxes deshabilitados */
    .custom-checkbox:disabled {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        cursor: not-allowed !important;
        opacity: 0.6;
    }

    .custom-checkbox:disabled:hover {
        border-color: #dee2e6 !important;
        box-shadow: none !important;
    }

    /* Estilo para filas con constancia existente */
    tr[data-has-certificate="true"] {
        background-color: #f8f9fa;
    }

    /* Estilos adicionales para la tabla de constancias */
    .tabla-constancias {
        width: 100%;
        min-width: 1600px;
        margin: 0;
        table-layout: auto;
    }

    .tabla-constancias th,
    .tabla-constancias td {
        vertical-align: middle;
        white-space: nowrap;
        padding: 8px 12px;
        min-width: 100px;
    }

    /* Permitir texto largo en nombre, email y bootcamp */
    .tabla-constancias td:nth-child(2),
    .tabla-constancias td:nth-child(5),
    .tabla-constancias td:nth-child(9) {
        white-space: normal !important;
        min-width: 180px;
        max-width: 250px;
        word-wrap: break-word;
    }

    /* Badge para serie de constancia */
    .tabla-constancias .badge {
        font-size: 0.8em;
        padding: 0.4em 0.8em;
    }

    /* Botones en tabla */
    .tabla-constancias .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Estilos para grupo de botones en tabla de constancias */
    .tabla-constancias .btn-group .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        margin: 0 1px;
    }

    .tabla-constancias .btn-group {
        display: flex;
        gap: 2px;
    }

    /* Hover effects para botones de eliminar */
    .eliminar-constancia-btn:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
        transform: scale(1.05);
        transition: all 0.2s ease;
    }

    /* Animación para filas que se eliminan */
    .tabla-constancias tr.removing {
        background-color: #f8d7da !important;
        transition: all 0.3s ease;
    }

    /* Fin de estilos adicionales */
</style>

<!-- CDN de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<ul class="nav nav-tabs justify-content-center" id="demoTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1"
            type="button" role="tab" aria-controls="tab1" aria-selected="true">
            Estudiantes Aprobados
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2"
            type="button" role="tab" aria-controls="tab2" aria-selected="false">
            Constancias Generadas
        </button>
    </li>
</ul>

<div class="tab-content text-center mt-4" id="demoTabsContent">
    <!-- Tabla de aprobados -->
    <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
        <div class="container-fluid">
            <div class="main-container">
                <!-- Panel izquierdo de filtros -->
                <div class="filters-panel">
                    <h5><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>

                    <!-- Selección de Curso -->
                    <div class="form-group">
                        <label class="form-label">Curso</label>
                        <select id="bootcampAprobados" class="form-select course-select">
                            <option value="">Seleccione un curso</option>
                            <?php
                            require_once __DIR__ . '/../../conexion.php';

                            // Obtener los códigos de curso únicos aprobados
                            $sql = "SELECT DISTINCT course_code FROM course_approvals";
                            $result = mysqli_query($conn, $sql);

                            // Crear array para almacenar los códigos
                            $course_codes = [];
                            while ($row = mysqli_fetch_assoc($result)) {
                                $course_codes[] = $row['course_code'];
                            }

                            // Si hay códigos, obtener los nombres de los cursos
                            if (!empty($course_codes)) {
                                // Preparar los códigos para la consulta IN
                                $codes_in = "'" . implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($course_codes), $conn), $course_codes)) . "'";
                                $sql_courses = "SELECT id, code, name FROM courses WHERE code IN ($codes_in)";
                                $result_courses = mysqli_query($conn, $sql_courses);

                                // Crear array asociativo code => name
                                $courses = [];
                                while ($row = mysqli_fetch_assoc($result_courses)) {
                                    $courses[$row['code']] = $row['name'];
                                }

                                // Mostrar opciones
                                foreach ($course_codes as $code) {
                                    $name = isset($courses[$code]) ? $courses[$code] : 'Nombre no encontrado';
                                    echo '<option value="' . htmlspecialchars($code) . '">' . htmlspecialchars($code . ' - ' . $name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Información del Curso Seleccionado -->
                    <div id="courseInfoContainerAprobados" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Tipo de Curso</label>
                            <input type="text" id="courseTypeAprobados" class="form-control" readonly value="Técnico">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Modalidad</label>
                            <input type="text" id="courseModeAprobados" class="form-control" readonly placeholder="Modalidad del curso">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sede</label>
                            <input type="text" id="courseSedeAprobados" class="form-control" readonly placeholder="Sede del curso">
                        </div>
                    </div>
                </div>

                <!-- Panel derecho de tabla -->
                <div class="table-panel">
                    <!-- Header de la tabla -->
                    <div class="table-header">
                        <h5 class="mb-0"><i class="fa fa-graduation-cap"></i> Estudiantes Aprobados</h5>
                        <div class="d-flex gap-2">
                            <button id="btnEnvioMasivo" class="btn bg-indigo-dark text-white" style="display: none;">
                                <i class="fa fa-paper-plane"></i> Envío Masivo de Constancias
                            </button>
                        </div>
                    </div>

                    <!-- Contenido de la tabla scrolleable -->
                    <div class="table-content">
                        <table id="listaEstudiantesAprobados" class="table table-hover table-bordered lista-estudiantes">
                            <thead>
                                <tr class="text-center">
                                    <th>
                                        <input type="checkbox" id="selectAllStudents" class="custom-checkbox" title="Seleccionar todos">
                                    </th>
                                    <th>ID</th>
                                    <th>Número de ID</th>
                                    <th>Nombre completo</th>
                                    <th>Correo institucional</th>
                                    <th>Programa</th>
                                    <th>Modalidad</th>
                                    <th>Sede</th>
                                    <th>% Asistencia</th>
                                    <th>Nota Final</th>
                                    <th>Estado</th>
                                    <!-- Eliminada la columna "Acción" -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <i class="fa fa-search fa-2x text-muted mb-3"></i><br>
                                        Seleccione un curso para cargar los estudiantes aprobados
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de constancias generadas -->
    <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
        <div class="container-fluid">
            <div class="table-panel">
                <!-- Header de la tabla -->
                <div class="table-header">
                    <h5 class="mb-0"><i class="fa fa-certificate"></i> Constancias Emitidas</h5>
                    <div class="d-flex gap-2">
                        <button id="btnRefreshConstancias" class="btn btn-outline-secondary">
                            <i class="fa fa-refresh"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Contenido de la tabla scrolleable -->
                <div class="table-content">
                    <div class="table-responsive">
                        <table id="tablaConstanciasEmitidas" class="table table-hover table-bordered tabla-constancias">
                            <thead>
                                <tr class="text-center">
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Tipo ID</th>
                                    <th>Número ID</th>
                                    <th>Email</th>
                                    <th>Programa</th>
                                    <th>Modalidad</th>
                                    <th>Sede</th>
                                    <th>Bootcamp</th>
                                    <th>Serie Constancia</th>
                                    <th>Emitido por</th>
                                    <th>Fecha Emisión</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                // Consulta para obtener todas las constancias emitidas con datos completos
                                $sql = "SELECT 
                                                ce.id,
                                                ce.number_id, 
                                                ce.serie_constancia, 
                                                ce.emitido_por, 
                                                ce.fecha_emision,
                                                g.full_name, 
                                                g.type_id, 
                                                g.email,
                                                g.institutional_email, 
                                                g.program, 
                                                g.mode, 
                                                g.headquarters,
                                                g.bootcamp_name,
                                                u.nombre AS nombre_emisor
                                            FROM constancias_emitidas ce
                                            LEFT JOIN groups g ON ce.number_id = g.number_id
                                            LEFT JOIN users u ON ce.emitido_por = u.username
                                            ORDER BY ce.fecha_emision DESC";

                                $result = mysqli_query($conn, $sql);

                                if (mysqli_num_rows($result) > 0):
                                    $contador = 1;
                                    while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                        <tr id="row-constancia-<?= $row['id'] ?>">
                                            <td class="text-center"><?= $contador ?></td>
                                            <td><?= htmlspecialchars($row['full_name'] ?? 'N/A') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['type_id'] ?? 'N/A') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['number_id'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['institutional_email'] ?? $row['email'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['program'] ?? 'N/A') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['mode'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['headquarters'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['bootcamp_name'] ?? 'N/A') ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-success text-white">
                                                    <?= htmlspecialchars($row['serie_constancia'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['nombre_emisor'] ?? 'Sistema') ?></td>
                                            <td class="text-center">
                                                <?= $row['fecha_emision'] ? date('d/m/Y H:i', strtotime($row['fecha_emision'])) : 'N/A' ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn bg-teal-dark text-white btn-sm ver-constancia-btn"
                                                        data-serie="<?= htmlspecialchars($row['serie_constancia'] ?? '') ?>"
                                                        data-nombre="<?= htmlspecialchars($row['full_name'] ?? 'Constancia') ?>"
                                                        title="Ver constancia">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm eliminar-constancia-btn"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-serie="<?= htmlspecialchars($row['serie_constancia'] ?? '') ?>"
                                                        data-nombre="<?= htmlspecialchars($row['full_name'] ?? '') ?>"
                                                        title="Eliminar constancia">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                        $contador++;
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <?php for ($i = 0; $i < 13; $i++): ?>
                                            <?php if ($i == 0): ?>
                                                <td colspan="13" class="text-center py-5">
                                                    <i class="fa fa-certificate fa-2x text-muted mb-3"></i><br>
                                                    No se han emitido constancias aún
                                                </td>
                                            <?php else: ?>
                                                
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<!-- Modal para mostrar la constancia PDF -->
<div class="modal fade" id="modalConstancia" tabindex="-1" aria-labelledby="modalConstanciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="modalConstanciaLabel">
                    <i class="fa fa-certificate"></i> Constancia de Participación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="iframeConstancia" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
                <button type="button" id="btnDescargarConstancia" class="btn bg-indigo-dark text-white">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 para el selector de cursos aprobados
        $('#bootcampAprobados').select2({
            placeholder: "Buscar y seleccionar un curso...",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron cursos";
                },
                searching: function() {
                    return "Buscando...";
                },
                loadingMore: function() {
                    return "Cargando más resultados...";
                }
            }
        });

        // Función para actualizar la visibilidad de los botones
        function actualizarBotonesAprobados() {
            const $btnExcel = $('#btnExportarExcelAprobados');
            const $btnMasivo = $('#btnEnvioMasivo');
            const hayEstudiantes = $('#listaEstudiantesAprobados tbody tr[data-student-id]').length > 0;

            if (hayEstudiantes) {
                $btnExcel.show();
                $btnMasivo.show();
            } else {
                $btnExcel.hide();
                $btnMasivo.hide();
            }
        }

        // Función para exportar a Excel estudiantes aprobados
        $('#btnExportarExcelAprobados').click(function() {
            const bootcamp = $('#bootcampAprobados').val();

            if (!bootcamp) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'Por favor, seleccione un curso antes de exportar',
                    icon: 'warning'
                });
                return;
            }

            // Mostrar loader con progreso
            Swal.fire({
                title: 'Generando archivo Excel...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mb-2">Procesando datos de estudiantes aprobados...</p>
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" id="exportProgressAprobados">
                            </div>
                        </div>
                        <small class="text-muted">Por favor espere, esto puede tomar unos segundos</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '400px'
            });

            // Simular progreso
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                }
                $('#exportProgressAprobados').css('width', progress + '%');
            }, 200);

            // Realizar la exportación usando AJAX con responseType blob
            $.ajax({
                url: 'components/certCompletion/export_excel_aprobados.php',
                type: 'POST',
                data: {
                    bootcamp: bootcamp
                },
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.responseType = 'blob';
                    return xhr;
                },
                success: function(data, status, xhr) {
                    clearInterval(progressInterval);
                    $('#exportProgressAprobados').css('width', '100%');

                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });

                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;

                    const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'estudiantes_aprobados_certificacion.xlsx';

                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }

                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    Swal.fire({
                        title: '¡Descarga completada!',
                        text: 'El archivo Excel se ha descargado correctamente',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                },
                error: function(xhr, status, error) {
                    clearInterval(progressInterval);

                    console.error('Error en la exportación:', error);

                    Swal.fire({
                        title: 'Error en la exportación',
                        text: 'Hubo un problema al generar el archivo Excel. Por favor, inténtelo nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // Función para cargar los estudiantes aprobados
        const cargarEstudiantesAprobados = () => {
            const bootcamp = $('#bootcampAprobados').val();

            if (!bootcamp) {
                console.log('Por favor, seleccione un curso');
                return;
            }

            // Mostrar loading
            $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Cargando estudiantes aprobados...</td></tr>');
            actualizarBotonExportacionAprobados();

            $.ajax({
                url: 'components/certCompletion/buscar_aprobados.php',
                type: 'POST',
                data: {
                    bootcamp: bootcamp
                },
                dataType: 'json',
                success: (response) => {
                    if (response && response.html) {
                        $('#listaEstudiantesAprobados tbody').html(response.html);

                        // Actualizar información del curso
                        if (response.courseInfo) {
                            $('#courseModeAprobados').val(response.courseInfo.mode || 'No disponible');
                            $('#courseSedeAprobados').val(response.courseInfo.headquarters || 'No disponible');
                            $('#courseInfoContainerAprobados').show();
                        } else {
                            $('#courseInfoContainerAprobados').hide();
                        }

                        // Actualizar botón de exportación
                        actualizarBotonExportacionAprobados();
                    } else {
                        $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-exclamation-triangle fa-2x text-warning"></i><br><br>No se encontraron estudiantes aprobados para este curso</td></tr>');
                        $('#courseInfoContainerAprobados').hide();
                        actualizarBotonExportacionAprobados();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error en la solicitud:', error);
                    $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-times-circle fa-2x text-danger"></i><br><br>Error al cargar los datos</td></tr>');
                    $('#courseInfoContainerAprobados').hide();
                    actualizarBotonExportacionAprobados();
                }
            });
        };

        // Event listener para cambio de curso
        $('#bootcampAprobados').change(function() {
            const bootcamp = $(this).val();

            if (bootcamp) {
                cargarEstudiantesAprobados();
            } else {
                $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-search fa-2x text-muted"></i><br><br>Seleccione un curso para cargar los estudiantes aprobados</td></tr>');
                $('#courseInfoContainerAprobados').hide();
                actualizarBotonExportacionAprobados();
            }
        });

        // Función para manejar selección de todos los estudiantes (solo activos)
        $('#selectAllStudents').change(function() {
            const isChecked = $(this).is(':checked');
            $('.student-checkbox:not(:disabled)').prop('checked', isChecked);
            actualizarContadorSeleccionados();
        });

        // Función para actualizar contador de seleccionados (solo activos)
        function actualizarContadorSeleccionados() {
            const totalSeleccionados = $('.student-checkbox:checked:not(:disabled)').length;
            const totalEstudiantesActivos = $('.student-checkbox:not(:disabled)').length;

            if (totalSeleccionados > 0) {
                $('#btnEnvioMasivo').html(`<i class="fa fa-paper-plane"></i> Enviar Constancias (${totalSeleccionados})`);
            } else {
                $('#btnEnvioMasivo').html(`<i class="fa fa-paper-plane"></i> Envío de Constancias`);
            }

            // Actualizar estado del checkbox principal (solo considera checkboxes activos)
            if (totalSeleccionados === 0) {
                $('#selectAllStudents').prop('indeterminate', false).prop('checked', false);
            } else if (totalSeleccionados === totalEstudiantesActivos) {
                $('#selectAllStudents').prop('indeterminate', false).prop('checked', true);
            } else {
                $('#selectAllStudents').prop('indeterminate', true);
            }
        }

        // Event listener para checkboxes individuales (solo activos)
        $(document).on('change', '.student-checkbox:not(:disabled)', function() {
            actualizarContadorSeleccionados();
        });

        // Función para envío masivo de constancias (solo estudiantes activos)
        $('#btnEnvioMasivo').click(function() {
            const estudiantesSeleccionados = [];
            const bootcamp = $('#bootcampAprobados').val();
            const bootcampText = $('#bootcampAprobados option:selected').text();

            if (!bootcamp) {
                Swal.fire({
                    title: 'Error',
                    text: 'Seleccione un curso antes de enviar constancias',
                    icon: 'warning'
                });
                return;
            }

            // Recopilar solo estudiantes seleccionados y activos (sin constancia previa)
            $('.student-checkbox:checked:not(:disabled)').each(function() {
                const $row = $(this).closest('tr');
                estudiantesSeleccionados.push({
                    id: $row.data('student-id'),
                    cedula: $row.data('student-cedula'),
                    nombre: $row.data('student-name'),
                    email: $row.data('student-email'),
                    modalidad: $row.data('student-modalidad') || 'presencial',
                    fecha_inicio: $row.data('student-start-date') || '',
                    fecha_fin: $row.data('student-end-date') || ''
                });
            });

            if (estudiantesSeleccionados.length === 0) {
                Swal.fire({
                    title: 'Sin selección',
                    text: 'Seleccione al menos un estudiante sin constancia previa para enviar',
                    icon: 'warning'
                });
                return;
            }

            // Confirmar envío masivo
            Swal.fire({
                title: '¿Confirmar envío masivo?',
                html: `
                    <div class="text-start">
                        <p><strong>Curso:</strong> ${bootcampText}</p>
                        <p><strong>Estudiantes seleccionados:</strong> ${estudiantesSeleccionados.length}</p>
                        <p class="text-muted small">Se generarán y enviarán constancias por correo electrónico a todos los estudiantes seleccionados que aún no tienen constancia.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar constancias',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#066aab'
            }).then((result) => {
                if (result.isConfirmed) {
                    enviarConstanciasMasivo(estudiantesSeleccionados, bootcampText);
                }
            });
        });

        // Función para procesar envío masivo
        function enviarConstanciasMasivo(estudiantes, nombreBootcamp) {
            let procesados = 0;
            let exitosos = 0;
            let errores = 0;
            const total = estudiantes.length;
            const resultados = [];

            // Mostrar modal de progreso
            Swal.fire({
                title: 'Enviando Constancias',
                html: `
                    <div class="text-center">
                        <div class="progress mb-3">
                            <div id="progressBarMasivo" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <p id="progressTextMasivo">Iniciando envío...</p>
                        <div id="progressDetailsMasivo" class="mt-3 text-start" style="max-height: 200px; overflow-y: auto;">
                            <!-- Detalles del progreso -->
                        </div>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '600px'
            });

            // Procesar estudiantes secuencialmente
            function procesarSiguienteEstudiante(index = 0) {
                if (index >= estudiantes.length) {
                    // Completado
                    mostrarResultadoFinal();
                    return;
                }

                const estudiante = estudiantes[index];
                const progreso = ((index + 1) / total * 100).toFixed(1);

                // Actualizar progreso
                $('#progressBarMasivo').css('width', progreso + '%');
                $('#progressTextMasivo').text(`Procesando ${index + 1} de ${total}: ${estudiante.nombre}`);

                // Enviar solicitud AJAX
                $.ajax({
                    url: 'components/certCompletion/export_complet.php',
                    type: 'POST',
                    data: {
                        nombre_estudiante: estudiante.nombre,
                        cedula: estudiante.cedula,
                        nombre_bootcamp: nombreBootcamp,
                        fecha_inicio: estudiante.fecha_inicio,
                        fecha_fin: estudiante.fecha_fin,
                        modalidad: estudiante.modalidad,
                        schedules: 'N/A',
                        email: estudiante.email
                    },
                    dataType: 'json',
                    timeout: 30000, // 30 segundos timeout
                    success: function(response) {
                        procesados++;
                        if (response.success) {
                            exitosos++;
                            agregarDetalleProgreso(estudiante.nombre, 'success', 'Constancia enviada correctamente');
                        } else {
                            errores++;
                            agregarDetalleProgreso(estudiante.nombre, 'error', response.message || 'Error desconocido');
                        }

                        // Procesar siguiente estudiante
                        setTimeout(() => procesarSiguienteEstudiante(index + 1), 500);
                    },
                    error: function(xhr, status, error) {
                        procesados++;
                        errores++;
                        agregarDetalleProgreso(estudiante.nombre, 'error', `Error de conexión: ${error}`);

                        // Procesar siguiente estudiante
                        setTimeout(() => procesarSiguienteEstudiante(index + 1), 500);
                    }
                });
            }

            // Agregar detalle de progreso
            function agregarDetalleProgreso(nombre, tipo, mensaje) {
                const iconClass = tipo === 'success' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                const detalleHtml = `
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa ${iconClass} me-2"></i>
                        <small><strong>${nombre}:</strong> ${mensaje}</small>
                    </div>
                `;
                $('#progressDetailsMasivo').append(detalleHtml);

                // Scroll automático al final
                const container = document.getElementById('progressDetailsMasivo');
                container.scrollTop = container.scrollHeight;
            }

            // Mostrar resultado final
            function mostrarResultadoFinal() {
                let iconType = 'success';
                let title = '¡Envío completado!';

                if (errores === total) {
                    iconType = 'error';
                    title = 'Envío fallido';
                } else if (errores > 0) {
                    iconType = 'warning';
                    title = 'Envío completado con advertencias';
                }

                Swal.fire({
                    title: title,
                    html: `
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="text-success">
                                            <i class="fa fa-check-circle fa-2x"></i>
                                            <br><strong>${exitosos}</strong>
                                            <br><small>Exitosos</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-danger">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                            <br><strong>${errores}</strong>
                                            <br><small>Errores</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-info">
                                            <i class="fa fa-users fa-2x"></i>
                                            <br><strong>${total}</strong>
                                            <br><small>Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    icon: iconType,
                    confirmButtonText: 'Entendido',
                    width: '500px'
                });

                // Desseleccionar todos los checkboxes
                $('.student-checkbox').prop('checked', false);
                $('#selectAllStudents').prop('checked', false);
                actualizarContadorSeleccionados();
            }

            // Iniciar el procesamiento
            procesarSiguienteEstudiante();
        }

        // Actualizar función existente
        function actualizarBotonExportacionAprobados() {
            actualizarBotonesAprobados();
        }

        // =================== FUNCIONALIDAD PARA CONSTANCIAS EMITIDAS ===================

        // Evento para el botón "Ver Constancia"
        $(document).on('click', '.ver-constancia-btn', function() {
            const serie = $(this).data('serie');
            const nombre = $(this).data('nombre');

            if (!serie) {
                Swal.fire({
                    title: 'Error',
                    text: 'No se encontró la serie de la constancia',
                    icon: 'error'
                });
                return;
            }

            // Ruta simplificada de la constancia
            const pdfUrl = `constancias/${serie}.pdf`;

            // Mostrar modal con la constancia
            $('#modalConstanciaLabel').html(`<i class="fa fa-certificate"></i> Constancia - ${nombre}`);
            $('#iframeConstancia').attr('src', pdfUrl);
            $('#modalConstancia').modal('show');

            // Configurar botón de descarga
            $('#btnDescargarConstancia').data('url', pdfUrl).data('filename', `constancia_${serie}.pdf`);
        });

        // Limpiar iframe al cerrar modal
        $('#modalConstancia').on('hidden.bs.modal', function() {
            $('#iframeConstancia').attr('src', '');
            $('#modalConstanciaLabel').html('<i class="fa fa-certificate"></i> Constancia de Participación');
        });

        // Funcionalidad del botón descargar
        $('#btnDescargarConstancia').click(function() {
            const url = $(this).data('url');
            const filename = $(this).data('filename');

            if (url) {
                // Crear enlace temporal para descarga
                const a = document.createElement('a');
                a.href = url;
                a.download = filename || 'constancia.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                // Mostrar mensaje de confirmación
                Swal.fire({
                    title: 'Descarga iniciada',
                    text: 'La constancia se está descargando',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        // Funcionalidad del botón actualizar
        $('#btnRefreshConstancias').click(function() {
            // Mostrar loading
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.html('<i class="fa fa-spinner fa-spin"></i> Actualizando...').prop('disabled', true);

            // Recargar la página después de un breve delay
            setTimeout(function() {
                location.reload();
            }, 1000);
        });

        // Funcionalidad para eliminar constancia
        $(document).on('click', '.eliminar-constancia-btn', function() {
            const id = $(this).data('id');
            const serie = $(this).data('serie');
            const nombre = $(this).data('nombre');
            const $row = $(this).closest('tr');

            if (!id || !serie) {
                Swal.fire({
                    title: 'Error',
                    text: 'No se encontraron los datos de la constancia',
                    icon: 'error'
                });
                return;
            }

            // Confirmación de eliminación
            Swal.fire({
                title: '¿Eliminar constancia?',
                html: `
                    <div class="text-start">
                        <p><strong>Estudiante:</strong> ${nombre}</p>
                        <p><strong>Serie:</strong> ${serie}</p>
                        <div class="alert alert-warning mt-3">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>¡Atención!</strong> Esta acción:
                            <ul class="mt-2 mb-0">
                                <li>Eliminará el archivo PDF de la constancia</li>
                                <li>Eliminará el registro de la base de datos</li>
                                <li><strong>No se puede deshacer</strong></li>
                            </ul>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarConstancia(id, serie, nombre, $row);
                }
            });
        });

        // Función para procesar la eliminación
        function eliminarConstancia(id, serie, nombre, $row) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando constancia...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-danger mb-3" role="status">
                            <span class="visually-hidden">Eliminando...</span>
                        </div>
                        <p>Eliminando constancia de <strong>${nombre}</strong></p>
                        <small class="text-muted">Por favor espere...</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Realizar petición AJAX
            $.ajax({
                url: 'components/certCompletion/eliminar_constancia.php',
                type: 'POST',
                data: {
                    id_constancia: id,
                    serie_constancia: serie
                },
                dataType: 'json',
                timeout: 15000, // 15 segundos timeout
                success: function(response) {
                    if (response.success) {
                        // Eliminar la fila de la tabla con animación
                        $row.fadeOut(400, function() {
                            $(this).remove();

                            // Verificar si quedaron filas
                            const filasRestantes = $('#tablaConstanciasEmitidas tbody tr[id^="row-constancia-"]').length;
                            if (filasRestantes === 0) {
                                $('#tablaConstanciasEmitidas tbody').html(`
                                    <tr>
                                        <td colspan="13" class="text-center py-5">
                                            <i class="fa fa-certificate fa-2x text-muted mb-3"></i><br>
                                            No se han emitido constancias aún
                                        </td>
                                    </tr>
                                `);
                            }
                        });

                        // Mostrar mensaje de éxito
                        Swal.fire({
                            title: '¡Eliminada correctamente!',
                            text: response.message,
                            icon: 'success',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });

                    } else {
                        // Mostrar error
                        Swal.fire({
                            title: 'Error al eliminar',
                            text: response.message || 'No se pudo eliminar la constancia',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en eliminación:', error);

                    let mensajeError = 'Error de conexión al eliminar la constancia';
                    if (status === 'timeout') {
                        mensajeError = 'La operación tardó demasiado tiempo. Verifique si la constancia fue eliminada.';
                    }

                    Swal.fire({
                        title: 'Error de conexión',
                        text: mensajeError,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        }
        // Inicializar DataTable solo cuando el DOM esté completamente cargado
        // y después de que la tabla haya sido renderizada con PHP
        $('#tab2-tab').on('shown.bs.tab', function(e) {
            // Inicializar DataTable de forma simple
            if (!$.fn.DataTable.isDataTable('#tablaConstanciasEmitidas')) {
                $('#tablaConstanciasEmitidas').DataTable({
                    responsive: true,
                    language: {
                        "search": "Buscar:",
                        "lengthMenu": "Mostrar _MENU_ registros",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    },
                    pageLength: 10,
                    order: [
                        [11, 'desc']
                    ],
                    columnDefs: [{
                        targets: [12],
                        orderable: false
                    },
                    { "targets": "_all", "defaultContent": "" }
                ]
                });
            }
        });

        // También inicializar cuando el documento esté listo, pero solo si la tab2 está activa
        if ($('#tab2-tab').hasClass('active')) {
            if (!$.fn.DataTable.isDataTable('#tablaConstanciasEmitidas')) {
                $('#tablaConstanciasEmitidas').DataTable({
                    responsive: true,
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay constancias emitidas disponibles",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ constancias",
                        "infoEmpty": "Mostrando 0 a 0 de 0 constancias",
                        "infoFiltered": "(filtrado de _MAX_ constancias totales)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrar _MENU_ constancias",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "No se encontraron constancias coincidentes",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        },
                        "aria": {
                            "sortAscending": ": activar para ordenar ascendentemente",
                            "sortDescending": ": activar para ordenar descendentemente"
                        }
                    },
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Todas"]
                    ],
                    order: [
                        [11, 'desc']
                    ],
                    columnDefs: [{
                            targets: [12],
                            orderable: false,
                            searchable: false
                        },
                        {
                            targets: [0, 2, 3, 6, 9, 11, 12],
                            className: 'text-center'
                        }
                    ],
                    scrollX: true,
                    autoWidth: false
                });
            }
        }

    });
</script>