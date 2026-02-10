<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Incluir la conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';


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
// function callMoodleAPI($function, $params = [])
// {
//     global $api_url, $token, $format;
//     $params['wstoken'] = $token;
//     $params['wsfunction'] = $function;
//     $params['moodlewsrestformat'] = $format;
//     $url = $api_url . '?' . http_build_query($params);
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     $response = curl_exec($ch);
//     if (curl_errno($ch)) {
//         echo 'Error en la solicitud cURL: ' . curl_error($ch);
//     }
//     curl_close($ch);
//     return json_decode($response, true);
// }

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

$username = $_SESSION['username'] ?? null;

// Obtener cursos donde el usuario es mentor
function getCoursesByMentor($conn, $username)
{
    $courses = [];
    if ($username) {
        $sql = "SELECT * FROM courses WHERE mentor = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    return $courses;
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCoursesByMentor($conn, $username);

// Función para obtener códigos QR pendientes únicamente
function getPendingQRCodes($conn)
{
    global $rol, $username;
    if ($rol === 'Mentor' && $username) {
        // Solo los QR pendientes creados por el mentor actual
        $sql = "SELECT qr.*, u.username, u.nombre FROM qr_mentorias qr LEFT JOIN users u ON qr.created_by = u.username 
                WHERE qr.created_by = ? AND (qr.authorized_by = 0 OR qr.authorized = 0)
                ORDER BY qr.created_at DESC";
        $stmt = $conn->prepare($sql);
        $username_int = (int)$username; // Crear variable temporal
        $stmt->bind_param("i", $username_int);
        $stmt->execute();
        $result = $stmt->get_result();
        $qrs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $qrs;
    } else {
        // Todos los QR pendientes para otros roles
        $sql = "SELECT qr.*, u.username, u.nombre FROM qr_mentorias qr LEFT JOIN users u ON qr.created_by = u.username 
                WHERE (qr.authorized_by = 0 OR qr.authorized = 0)
                ORDER BY qr.created_at DESC";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Obtener solo los códigos QR pendientes
$qrCodes = getPendingQRCodes($conn);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

<!-- Navtabs para QR Codes -->
<div class="d-flex justify-content-center mb-4">
    <ul class="nav nav-tabs" id="qrTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="qr-list-tab" data-bs-toggle="tab" data-bs-target="#qr-list" type="button" role="tab" aria-controls="qr-list" aria-selected="true">
                <i class="bi bi-qr-code"></i> Códigos QR
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="qr-test-tab" data-bs-toggle="tab" data-bs-target="#qr-test" type="button" role="tab" aria-controls="qr-test" aria-selected="false">
                <i class="bi bi-clock"></i> Tabla de nivelacion
            </button>
        </li>
    </ul>
</div>

<div class="tab-content" id="qrTabContent">
    <!-- Tab de Códigos QR -->
    <div class="tab-pane fade show active" id="qr-list" role="tabpanel" aria-labelledby="qr-list-tab">
        <!-- Botón para agregar nuevo código QR y controles en la misma línea -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn bg-magenta-dark text-white" data-bs-toggle="modal" data-bs-target="#addQRModal">
                        <i class="bi bi-plus-circle"></i> Nuevo Código QR
                    </button>
                    <?php if ($rol !== 'Mentor'): ?>
                        <button type="button" class="btn bg-success text-white" id="exportExcelBtn">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($rol !== 'Mentor'): ?>
                    <div class="d-flex align-items-center gap-2">
                        <label for="filterStatus" class="form-label mb-0">Filtrar:</label>
                        <select id="filterStatus" class="form-select" style="width: auto;">
                            <option value="pending">Pendientes</option>
                            <option value="authorized">Autorizadas</option>
                            <option value="all">Todas</option>
                        </select>
                    </div>
                    <div class="input-group" style="max-width: 320px;">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar por cédula del mentor...">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Loading spinner -->
        <div id="loadingSpinner" class="text-center d-none">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <!-- Grid de cards para códigos QR -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="qrCardsContainer">
            <?php foreach ($qrCodes as $qr): ?>
                <?php
                $isPending = ($qr['authorized_by'] == 0 || $qr['authorized'] == 0);
                $headerClass = $isPending ? 'bg-warning text-dark' : 'bg-teal-dark text-white';
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header <?php echo $headerClass; ?>">
                            <h5 class="card-title mb-0 text-center">
                                <?php echo htmlspecialchars($qr['title']); ?>
                                <?php if ($isPending): ?>
                                    <span class="badge bg-danger ms-2">Pendiente</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center">
                            <div class="qr-image mb-3">
                                <img src="img/qrcodes/<?php echo htmlspecialchars($qr['image_filename']); ?>"
                                    class="img-fluid"
                                    alt="Código QR"
                                    style="max-width: 200px;">
                            </div>
                            <div class="separator w-100 border-bottom my-2"></div>
                            <div class="qr-info w-100">
                                <p class="card-text small text-muted mb-2">
                                    <button type="button" class="btn btn-sm ms-2 copy-url-btn" title="Copiar URL" data-url="<?php echo htmlspecialchars($qr['url']); ?>">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                    <span class="qr-url-text"><?php echo htmlspecialchars($qr['url']); ?></span>
                                </p>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        document.querySelectorAll('.copy-url-btn').forEach(function(btn) {
                                            btn.addEventListener('click', function() {
                                                const url = btn.getAttribute('data-url');
                                                navigator.clipboard.writeText(url).then(function() {
                                                    btn.innerHTML = '<i class="bi bi-clipboard-check"></i>';
                                                    setTimeout(function() {
                                                        btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                                                    }, 1200);
                                                });
                                            });
                                        });
                                    });
                                </script>
                                <div class="separator w-100 border-bottom my-2"></div>
                                <p class="card-text small mb-0">
                                    <i class="bi bi-calendar3"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($qr['created_at'])); ?>
                                </p>
                                <?php if ($rol !== 'Mentor'): ?>
                                    <p class="card-text small mb-0">
                                        <i class="bi bi-person"></i> Creado por: <br><?php echo htmlspecialchars($qr['username'] ?? ''); ?>
                                        (<?php echo htmlspecialchars($qr['nombre'] ?? ''); ?>)
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between gap-2">
                                <button class="btn bg-teal-dark text-white btn-sm flex-grow-1 download-qr"
                                    data-filename="<?php echo htmlspecialchars($qr['image_filename']); ?>">
                                    <i class="bi bi-download"></i> Descargar QR
                                </button>
                                <?php if ($rol === 'Control maestro'): ?>
                                    <button class="btn btn-danger btn-sm delete-qr"
                                        data-id="<?php echo $qr['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($qr['title']); ?>"
                                        data-filename="<?php echo htmlspecialchars($qr['image_filename']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                                <div class="d-flex justify-content-center mt-2">
                                    <button class="btn btn-warning btn-sm edit-qr w-100"
                                        data-id="<?php echo $qr['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($qr['title']); ?>"
                                        data-url="<?php echo htmlspecialchars($qr['url']); ?>"
                                        data-clases="<?php echo (int)$qr['clases_equivalentes']; ?>"
                                        data-filename="<?php echo htmlspecialchars($qr['image_filename']); ?>">
                                        <i class="bi bi-pencil"></i> Asignar clases
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Navegación de páginas">
                <ul class="pagination" id="pagination">
                    <!-- Se llenará por JavaScript -->
                </ul>
            </nav>
        </div>

        <!-- Info de paginación -->
        <div class="text-center mt-2">
            <small class="text-muted" id="paginationInfo">
                <!-- Se llenará por JavaScript -->
            </small>
        </div>
    </div>
    <!-- Tab de Prueba -->
    <div class="tab-pane fade" id="qr-test" role="tabpanel" aria-labelledby="qr-test-tab">
        <div class="mt-4">
            <?php
            // Obtener cursos con masterclass y asistencias registradas
            $sql = "SELECT DISTINCT am.code, c.name
                    FROM asistencias_mentorias am
                    INNER JOIN courses c ON am.code = c.code
                    ORDER BY c.name";
            $result = $conn->query($sql);
            ?>
            <div class="d-flex justify-content-center mb-3">
                <div style="min-width: 500px; max-width: 600px;">
                    <label for="cursoMasterclass" class="form-label">Curso:</label>
                    <select id="cursoMasterclass" class="form-select">
                        <option value="">Seleccione un curso</option>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <option value="<?php echo $row['code']; ?>">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <table id="listaMasterclass" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Identificación</th>
                        <th>Nombre completo</th>
                        <th>Programa</th>
                        <th>Modalidad</th>
                        <th>Correo</th>
                        <th>Correo institucional</th>
                        <th>Bootcamp</th>
                        <th>Fecha Master Class</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará por AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Agregar estilos CSS -->
<style>
    .card {
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        border-bottom: none;
    }

    .card-footer {
        border-top: none;
    }

    .qr-info {
        text-align: center;
    }

    .separator {
        opacity: 0.2;
    }

    .card-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn {
        transition: all 0.3s;
    }

    .btn:hover {
        transform: scale(1.05);
    }

    .nav-tabs .nav-link.active {
        background-color: #fff !important;
        color: #30336b !important;
        font-weight: bold !important;
    }

    .nav-tabs .nav-link {
        color: #000 !important;
        font-weight: normal !important;
        background-color: #fff !important;
    }
</style>

<!-- Modal para agregar nuevo código QR -->
<div class="modal fade" id="addQRModal" tabindex="-1" aria-labelledby="addQRModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQRModalLabel">Nuevo Código QR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addQRForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="title" name="title" disabled required>
                    </div>
                    <div class="mb-3">
                        <select id="bootcamp" name="bootcamp" class="form-select course-select" required>
                            <?php foreach ($courses_data as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['code']); ?>"
                                    data-fullname="<?php echo htmlspecialchars($course['name']); ?>">
                                    <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="hora" class="form-label">Hora</label>
                        <input type="time" class="form-control" id="hora" name="hora" required>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" disabled required>
                    </div>
                    <div class="mb-3">
                        <label for="clases_equivalentes" class="form-label">
                            Clases equivalentes
                            <i class="bi bi-info-circle" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="La cantidad de clases equivalentes debe ser asignada por el líder de seguimiento"></i>
                        </label>
                        <input type="number" class="form-control" id="clases_equivalentes" name="clases_equivalentes" value="0" disabled>
                    </div>
                    <input type="hidden" id="course_name" name="course_name">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn bg-indigo-dark text-white">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver código QR -->
<div class="modal fade" id="viewQRModal" tabindex="-1" aria-labelledby="viewQRModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewQRModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImage" src="" alt="Código QR" class="img-fluid mb-3">
                <p id="qrUrl" class="mb-3"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="downloadQR" href="#" class="btn bg-indigo-dark">
                    <i class="bi bi-download"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar clases equivalentes -->
<div class="modal fade" id="editQRModal" tabindex="-1" aria-labelledby="editQRModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editQRForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editQRModalLabel">Editar clases equivalentes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_qr_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control" id="edit_title" name="title" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="text" class="form-control" id="edit_url" name="url" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_clases_equivalentes" class="form-label">
                            Clases equivalentes
                            <i class="bi bi-info-circle" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Solo el líder de seguimiento puede modificar este valor"></i>
                        </label>
                        <input type="number" class="form-control" id="edit_clases_equivalentes" name="clases_equivalentes" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // Manejar el envío del formulario de nuevo QR
        $('#addQRForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'components/qrMentorias/save_qr.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'El código QR ha sido generado correctamente y esta a espera de autorización.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#30336b'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#addQRModal').modal('hide');
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.error || 'Error desconocido al guardar el código QR',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', status, error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error en la solicitud: ' + error,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        $('.download-qr').on('click', function() {
            const filename = $(this).data('filename');
            const qrUrl = `img/qrcodes/${filename}`;

            // Crear elemento de descarga
            const link = document.createElement('a');
            link.href = qrUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Manejar eliminación de QR
        $('.delete-qr').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const filename = $(this).data('filename');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el código QR "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#30336b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Segunda confirmación
                    Swal.fire({
                        title: 'Confirmar eliminación',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#30336b',
                        confirmButtonText: 'Sí, eliminar definitivamente',
                        cancelButtonText: 'Cancelar'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            // Proceder con la eliminación
                            $.ajax({
                                url: 'components/qrMentorias/delete_qr.php',
                                method: 'POST',
                                data: {
                                    id: id,
                                    filename: filename
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: '¡Eliminado!',
                                            text: 'El código QR ha sido eliminado correctamente',
                                            icon: 'success',
                                            confirmButtonColor: '#30336b'
                                        }).then(() => {
                                            // Recargar la vista actual
                                            document.getElementById('filterStatus').dispatchEvent(new Event('change'));
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: response.error || 'Error al eliminar el código QR',
                                            icon: 'error',
                                            confirmButtonColor: '#d33'
                                        });
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
    });

    $(document).on('click', '.edit-qr', function() {
        $('#edit_qr_id').val($(this).data('id'));
        $('#edit_title').val($(this).data('title'));
        $('#edit_url').val($(this).data('url'));
        $('#edit_clases_equivalentes').val($(this).data('clases'));
        $('#editQRModal').modal('show');
    });

    $('#editQRForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'components/qrMentorias/update_qr.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'Las clases equivalentes han sido actualizadas.',
                        icon: 'success',
                        confirmButtonColor: '#30336b'
                    }).then(() => {
                        $('#editQRModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.error || 'Error al actualizar.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        });
    });

    // Función para actualizar título y URL automáticamente
    function updateTitleAndUrl() {
        const bootcamp = document.getElementById('bootcamp');
        const fecha = document.getElementById('fecha').value;
        const hora = document.getElementById('hora').value;
        const selected = bootcamp.options[bootcamp.selectedIndex];
        const courseName = selected.getAttribute('data-fullname');
        document.getElementById('course_name').value = courseName;

        if (courseName && fecha && hora) {
            // Formato fecha para título y URL
            const fechaObj = new Date(fecha);
            const d = String(fechaObj.getDate()).padStart(2, '0');
            const m = String(fechaObj.getMonth() + 1).padStart(2, '0');
            const y = fechaObj.getFullYear();
            const fechaTitulo = `${d}/${m}/${y}`;
            const fechaUrl = `${y}-${m}-${d}`;
            
            // Formatear hora
            const horaFormateada = hora.substring(0, 5); // HH:MM

            // Actualizar campos
            document.getElementById('title').value = `Mentoria ${courseName}, ${fechaTitulo} ${horaFormateada}`;
            document.getElementById('url').value = `https://dashboard.utinnova.co/asistenciaMentorias.php?fecha=${fechaUrl}&curso=${bootcamp.value}&hora=${hora}`;
        } else {
            document.getElementById('title').value = '';
            document.getElementById('url').value = '';
        }
    }

    document.getElementById('bootcamp').addEventListener('change', updateTitleAndUrl);
    document.getElementById('fecha').addEventListener('change', updateTitleAndUrl);
    document.getElementById('hora').addEventListener('change', updateTitleAndUrl);

    // Manejar exportación a Excel
    document.getElementById('exportExcelBtn').addEventListener('click', function() {
        // Mostrar loading
        Swal.fire({
            title: 'Generando Excel...',
            text: 'Por favor espere mientras se genera el archivo.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Crear formulario para envío POST y descarga directa
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'components/qrMentorias/export_excel.php';
        form.style.display = 'none';

        // Agregar campos de datos (aunque no se usen por los cambios realizados)
        const statusInput = document.createElement('input');
        statusInput.name = 'status';
        statusInput.value = currentStatus;
        form.appendChild(statusInput);

        const searchInput = document.createElement('input');
        searchInput.name = 'search';
        searchInput.value = currentSearch;
        form.appendChild(searchInput);

        // Agregar el formulario al body y enviarlo
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        // Cerrar el loading después de un breve momento
        setTimeout(() => {
            Swal.close();
            Swal.fire({
                title: '¡Descarga iniciada!',
                text: 'El archivo Excel se está descargando automáticamente.',
                icon: 'success',
                confirmButtonColor: '#30336b',
                timer: 3000,
                showConfirmButton: false
            });
        }, 1000);
    });

    // Variables globales para paginación y búsqueda
    let currentPage = 1;
    let currentSearch = '';
    let currentStatus = 'pending';
    const itemsPerPage = 16;
    let searchTimeout = null;

    // Función para cargar datos con paginación y búsqueda
    function loadQRData(page = 1, search = '', status = 'pending') {
        const container = document.getElementById('qrCardsContainer');
        const loading = document.getElementById('loadingSpinner');

        // Mostrar spinner
        loading.classList.remove('d-none');
        container.style.opacity = '0.5';

        $.ajax({
            url: 'components/qrMentorias/get_qr_codes.php',
            method: 'POST',
            data: {
                status: status,
                page: page,
                search: search,
                limit: itemsPerPage
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    container.innerHTML = response.html;
                    updatePagination(response.pagination);

                    // Reinitializar eventos para los nuevos elementos
                    initializeQREvents();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.error || 'Error al cargar los códigos QR',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Error en la solicitud: ' + error,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            },
            complete: function() {
                // Ocultar spinner
                loading.classList.add('d-none');
                container.style.opacity = '1';
            }
        });
    }

    // Función para actualizar la paginación
    function updatePagination(pagination) {
        const paginationContainer = document.getElementById('pagination');
        const paginationInfo = document.getElementById('paginationInfo');

        // Actualizar información
        paginationInfo.textContent = `Mostrando ${pagination.from}-${pagination.to} de ${pagination.total} resultados`;

        // Limpiar paginación
        paginationContainer.innerHTML = '';

        if (pagination.total_pages <= 1) {
            return;
        }

        // Botón anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${pagination.current_page <= 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${pagination.current_page - 1}">Anterior</a>`;
        paginationContainer.appendChild(prevLi);

        // Páginas
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

        if (startPage > 1) {
            const firstLi = document.createElement('li');
            firstLi.className = 'page-item';
            firstLi.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
            paginationContainer.appendChild(firstLi);

            if (startPage > 2) {
                const dotsLi = document.createElement('li');
                dotsLi.className = 'page-item disabled';
                dotsLi.innerHTML = `<span class="page-link">...</span>`;
                paginationContainer.appendChild(dotsLi);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationContainer.appendChild(pageLi);
        }

        if (endPage < pagination.total_pages) {
            if (endPage < pagination.total_pages - 1) {
                const dotsLi = document.createElement('li');
                dotsLi.className = 'page-item disabled';
                dotsLi.innerHTML = `<span class="page-link">...</span>`;
                paginationContainer.appendChild(dotsLi);
            }

            const lastLi = document.createElement('li');
            lastLi.className = 'page-item';
            lastLi.innerHTML = `<a class="page-link" href="#" data-page="${pagination.total_pages}">${pagination.total_pages}</a>`;
            paginationContainer.appendChild(lastLi);
        }

        // Botón siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${pagination.current_page + 1}">Siguiente</a>`;
        paginationContainer.appendChild(nextLi);

        // Agregar eventos a los botones de paginación
        paginationContainer.addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.classList.contains('page-link') && e.target.dataset.page) {
                const page = parseInt(e.target.dataset.page);
                if (page !== pagination.current_page && page >= 1 && page <= pagination.total_pages) {
                    currentPage = page;
                    loadQRData(currentPage, currentSearch, currentStatus);
                }
            }
        });
    }

    // Manejar cambio de filtro de estado
    document.getElementById('filterStatus').addEventListener('change', function() {
        currentStatus = this.value;
        currentPage = 1;
        loadQRData(currentPage, currentSearch, currentStatus);
    });

    // Manejar búsqueda en tiempo real
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.trim();

        // Cancelar timeout anterior
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Establecer nuevo timeout
        searchTimeout = setTimeout(function() {
            currentSearch = searchValue;
            currentPage = 1;
            loadQRData(currentPage, currentSearch, currentStatus);
        }, 300); // 300ms de delay
    });

    // Botón limpiar búsqueda
    document.getElementById('clearSearch').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        currentSearch = '';
        currentPage = 1;
        loadQRData(currentPage, currentSearch, currentStatus);
    });

    // Función para reinitializar eventos después de cargar contenido AJAX
    function initializeQREvents() {
        // Reinitializar eventos de copy URL
        document.querySelectorAll('.copy-url-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const url = btn.getAttribute('data-url');
                navigator.clipboard.writeText(url).then(function() {
                    btn.innerHTML = '<i class="bi bi-clipboard-check"></i>';
                    setTimeout(function() {
                        btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                    }, 1200);
                });
            });
        });

        // Reinitializar eventos de descarga
        $('.download-qr').off('click').on('click', function() {
            const filename = $(this).data('filename');
            const qrUrl = `img/qrcodes/${filename}`;

            const link = document.createElement('a');
            link.href = qrUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Reinitializar eventos de eliminación
        $('.delete-qr').off('click').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const filename = $(this).data('filename');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el código QR "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#30336b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Segunda confirmación
                    Swal.fire({
                        title: 'Confirmar eliminación',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#30336b',
                        confirmButtonText: 'Sí, eliminar definitivamente',
                        cancelButtonText: 'Cancelar'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            // Proceder con la eliminación
                            $.ajax({
                                url: 'components/qrMentorias/delete_qr.php',
                                method: 'POST',
                                data: {
                                    id: id,
                                    filename: filename
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: '¡Eliminado!',
                                            text: 'El código QR ha sido eliminado correctamente',
                                            icon: 'success',
                                            confirmButtonColor: '#30336b'
                                        }).then(() => {
                                            // Recargar la vista actual
                                            loadQRData(currentPage, currentSearch, currentStatus);
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: response.error || 'Error al eliminar el código QR',
                                            icon: 'error',
                                            confirmButtonColor: '#d33'
                                        });
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });

        // Reinitializar eventos de edición
        $('.edit-qr').off('click').on('click', function() {
            $('#edit_qr_id').val($(this).data('id'));
            $('#edit_title').val($(this).data('title'));
            $('#edit_url').val($(this).data('url'));
            $('#edit_clases_equivalentes').val($(this).data('clases'));
            $('#editQRModal').modal('show');
        });
    }

    // Cargar paginación inicial
    $(document).ready(function() {
        // Inicializar con datos de la primera carga
        const initialCards = document.querySelectorAll('#qrCardsContainer .col').length;
        updatePagination({
            current_page: 1,
            total_pages: Math.ceil(initialCards / itemsPerPage),
            total: initialCards,
            from: 1,
            to: Math.min(itemsPerPage, initialCards)
        });
    });

    $(document).ready(function() {
        var tabla = $('#listaMasterclass').DataTable({
            responsive: true,
            language: {
                url: "controller/datatable_esp.json"
            },
            pagingType: "simple"
        });

        $('#cursoMasterclass').on('change', function() {
            var curso_id = $(this).val();
            tabla.clear().draw();
            if (curso_id) {
                $.ajax({
                    url: 'components/qrMentorias/get_asistentes_mentoria.php',
                    method: 'POST',
                    data: {
                        curso_id: curso_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        data.forEach(function(row) {
                            tabla.row.add([
                                row.number_id,
                                row.full_name,
                                row.program,
                                row.mode,
                                row.email,
                                row.institutional_email,
                                row.bootcamp_name,
                                row.fecha
                            ]).draw(false);
                        });
                    }
                });
            }
        });
    });
</script>