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


// Función para obtener todos los códigos QR
function getQRCodes($conn)
{
    $sql = "SELECT * FROM qr_masterclass ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener los códigos QR
$qrCodes = getQRCodes($conn);
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
        <!-- Botón para agregar nuevo código QR -->
        <div class="mb-4">
            <button type="button" class="btn bg-magenta-dark text-white" data-bs-toggle="modal" data-bs-target="#addQRModal">
                <i class="bi bi-plus-circle"></i> Nuevo Código QR
            </button>
        </div>

        <!-- Grid de cards para códigos QR -->
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($qrCodes as $qr): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-teal-dark text-white">
                            <h5 class="card-title mb-0 text-center">
                                <?php echo htmlspecialchars($qr['title']); ?>
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
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Tab de Prueba -->
    <div class="tab-pane fade" id="qr-test" role="tabpanel" aria-labelledby="qr-test-tab">
        <div class="mt-4">
            <?php
            // Obtener cursos con masterclass y asistencias registradas
            $sql = "SELECT DISTINCT am.code, c.name
                    FROM asistencias_masterclass am
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
                                <?php if (in_array($course['categoryid'], [20, 22, 23, 25, 28, 35, 19, 21, 24, 26, 27, 34, 35])): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                        data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                        <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" disabled required>
                    </div>
                    <div class="mb-3">
                        <label for="clases_equivalentes" class="form-label">
                            Clases equivalentes
                            <i class="bi bi-info-circle" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Cantidad de clases a las que equivale esta masterclass"></i>
                        </label>
                        <input type="number" class="form-control" id="clases_equivalentes" name="clases_equivalentes" min="1" value="1" required>
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

<script>
    $(document).ready(function() {

        // Manejar el envío del formulario de nuevo QR
        $('#addQRForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'components/qrNivelaciones/save_qr.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'El código QR ha sido generado correctamente',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3085d6'
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
                cancelButtonColor: '#3085d6',
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
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar definitivamente',
                        cancelButtonText: 'Cancelar'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            // Proceder con la eliminación
                            $.ajax({
                                url: 'components/qrNivelaciones/delete_qr.php',
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
                                            confirmButtonColor: '#3085d6'
                                        }).then(() => {
                                            location.reload();
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

    // Función para actualizar título y URL automáticamente
    function updateTitleAndUrl() {
        const bootcamp = document.getElementById('bootcamp');
        const fecha = document.getElementById('fecha').value;
        const selected = bootcamp.options[bootcamp.selectedIndex];
        const courseName = selected.getAttribute('data-fullname');
        document.getElementById('course_name').value = courseName;

        if (courseName && fecha) {
            // Formato fecha para título y URL
            const fechaObj = new Date(fecha);
            const d = String(fechaObj.getDate()).padStart(2, '0');
            const m = String(fechaObj.getMonth() + 1).padStart(2, '0');
            const y = fechaObj.getFullYear();
            const fechaTitulo = `${d}/${m}/${y}`;
            const fechaUrl = `${y}-${m}-${d}`;

            // Actualizar campos
            document.getElementById('title').value = `Masterclass ${courseName}, ${fechaTitulo}`;
            document.getElementById('url').value = `https://dashboard.utinnova.co/asistenciaMasterClass.php?fecha=${fechaUrl}&curso=${bootcamp.value}`;
        } else {
            document.getElementById('title').value = '';
            document.getElementById('url').value = '';
        }
    }

    document.getElementById('bootcamp').addEventListener('change', updateTitleAndUrl);
    document.getElementById('fecha').addEventListener('change', updateTitleAndUrl);

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
                    url: 'components/qrNivelaciones/get_asistentes_masterclass.php',
                    method: 'POST',
                    data: { curso_id: curso_id },
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