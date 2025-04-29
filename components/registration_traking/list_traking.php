<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!--<script>
    let timerInterval;
    Swal.fire({
        title: "Cargando información...",
        html: "Por favor espera mientras obtenemos los datos.",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
</script>-->
<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Parámetros de paginación
$limit = 60; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Parámetro de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modificar la consulta SQL principal
$sql = "SELECT user_register.*, 
        municipios.municipio, 
        departamentos.departamento,
        g.creation_date as matriculation_date
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    LEFT JOIN groups g ON user_register.number_id = g.number_id
    WHERE departamentos.id_departamento = 11
    AND user_register.status = '1'
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)
    ORDER BY user_register.first_name ASC
    LIMIT ? OFFSET ?";

$sqlContactLog = "SELECT cl.*, a.name AS advisor_name,
                  MIN(cl.contact_date) as first_contact_date,
                  MAX(cl.contact_date) as last_contact_date
                  FROM contact_log cl
                  LEFT JOIN advisors a ON cl.idAdvisor = a.id
                  WHERE cl.number_id = ?
                  GROUP BY cl.number_id";

$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param('ssii', $searchParam, $searchParam, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$data = [];


// Función para obtener los niveles de los usuarios 
function obtenerNivelesUsuarios($conn)
{
    $sql = "SELECT cedula, nivel FROM usuarios";
    $result = $conn->query($sql);

    $niveles = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $niveles[$row['cedula']] = $row['nivel'];
        }
    }

    return $niveles;
}


// Obtener los niveles de usuarios
$nivelesUsuarios = obtenerNivelesUsuarios($conn);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Obtener los datos de contact_log para el número de ID actual
        $stmtContactLog = $conn->prepare($sqlContactLog);
        $stmtContactLog->bind_param('i', $row['number_id']);
        $stmtContactLog->execute();
        $resultContactLog = $stmtContactLog->get_result();
        $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

        // Si hay registros, asignar los valores
        if (!empty($contactLogs)) {
            // Agregar fechas de contacto
            $row['first_contact_date'] = $contactLogs[0]['first_contact_date'] ?? 'Sin contacto';
            $row['last_contact_date'] = $contactLogs[0]['last_contact_date'] ?? 'Sin contacto';
            
            // Crear un array para almacenar todos los registros de contact_log
            $row['contact_logs'] = [];

            foreach ($contactLogs as $log) {
                $row['contact_logs'][] = [
                    'idAdvisor' => $log['idAdvisor'],
                    'advisor_name' => $log['advisor_name'],
                    'details' => $log['details'],
                    'contact_established' => $log['contact_established'],
                    'continues_interested' => $log['continues_interested'],
                    'observation' => $log['observation']
                ];
            }

            // Asignar el último registro como valores por defecto
            $lastLog = end($contactLogs);
            $row['idAdvisor'] = $lastLog['idAdvisor'];
            $row['advisor_name'] = $lastLog['advisor_name'];
            $row['details'] = $lastLog['details'];
            $row['contact_established'] = $lastLog['contact_established'];
            $row['continues_interested'] = $lastLog['continues_interested'];
            $row['observation'] = $lastLog['observation'];
        } else {
            // Si no hay registros, asignar valores por defecto
            $row['first_contact_date'] = 'Sin contacto';
            $row['last_contact_date'] = 'Sin contacto';
            
            $row['idAdvisor'] = 'No registrado';
            $row['advisor_name'] = 'Sin asignar';
            $row['details'] = 'Sin detalles';
            $row['contact_established'] = 0; // Cambiado a 0
            $row['continues_interested'] = 0; // Cambiado a 0
            $row['observation'] = 'Sin observaciones';
            $row['contact_logs'] = []; // Array vacío para contact_logs
        }

        // Calcular edad
        $birthday = new DateTime($row['birthdate']);
        $now = new DateTime();
        $age = $now->diff($birthday)->y;
        $row['age'] = $age;

        $data[] = $row;
    }
} else {
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}


$sedes = []; // Agregar array para sedes

foreach ($data as $row) {
    $sede = $row['headquarters'];

    // Obtener sedes únicas
    if (!in_array($sede, $sedes) && !empty($sede)) {
        $sedes[] = $sede;
    }
}
// Obtener el total de registros para la paginación
$totalSql = "SELECT COUNT(*) as total FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    WHERE departamentos.id_departamento = 11
    AND user_register.status = '1' AND user_register.statusAdmin = '' AND user_register.department=15 OR user_register.department=25
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)";
$stmtTotal = $conn->prepare($totalSql);
$stmtTotal->bind_param('ss', $searchParam, $searchParam);
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Agregar esta consulta al inicio del archivo donde están las otras consultas
function obtenerHorarios($conn, $mode, $headquarters, $program)
{
    $sql = "SELECT schedule FROM schedules 
            WHERE mode = ? 
            AND (headquarters = ? OR mode = 'Virtual')
            AND program = ?
            ORDER BY schedule ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $mode, $headquarters, $program);
    $stmt->execute();
    $result = $stmt->get_result();

    $horarios = array();
    while ($row = $result->fetch_assoc()) {
        $horarios[] = $row['schedule'];
    }

    return $horarios;
}
?>

<div class="table-responsive">
    <button id="exportarExcel" class="btn btn-success mb-3">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
    </button>
    <div class=" mt-4">
        <div class="row align-items-center">

            <!-- Row para búsqueda y paginación -->

            <div class="row mb-4">
                <!-- Formulario de búsqueda -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <form method="GET" action="">
                                <h6 class="card-title mb-3 text-indigo-dark text-center">
                                    <i class="bi bi-search"></i> Buscar en lista de registros
                                </h6>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        name="search"
                                        placeholder="Buscar por nombre o ID"
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit"
                                        class="btn bg-indigo-dark text-white">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3 text-indigo-dark text-center">
                                <i class="bi bi-book"></i> Navegación de páginas
                            </h6>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <!-- Botón Anterior -->
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo htmlspecialchars($search); ?>">
                                            &laquo; Anterior
                                        </a>
                                    </li>

                                    <!-- Primera página -->
                                    <?php if ($page > 2): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1&search=<?php echo htmlspecialchars($search); ?>">1</a>
                                        </li>
                                        <?php if ($page > 3): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Páginas visibles -->
                                    <?php for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++): ?>
                                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Última página -->
                                    <?php if ($page < $totalPages - 1): ?>
                                        <?php if ($page < $totalPages - 2): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $totalPages; ?></a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Botón Siguiente -->
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>&search=<?php echo htmlspecialchars($search); ?>">
                                            Siguiente &raquo;
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row para filtros -->
            <div class="row mb-4">
                <!-- Filtro por sede -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-building"></i> Filtrar por sede
                            </h6>
                            <select id="filterHeadquarters" class="form-select">
                                <option value="">Todas las sedes</option>
                                <?php foreach ($sedes as $sede): ?>
                                    <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Filtro por estado de admisión -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-person-check"></i> Filtrar por estado de admisión
                            </h6>
                            <select id="filterAdmissionStatus" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="1">Beneficiario</option>
                                <option value="0">Sin estado</option>
                                <option value="2">Rechazado</option>
                                <option value="3">Matriculado</option>
                                <option value="4">Sin contacto</option>
                                <option value="5">En proceso</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>


    <table id="listaInscritos" class="table table-hover table-bordered">
        <thead class="thead-dark text-center">
            <tr class="text-center">
                <th class="text-center">Tipo ID</th>
                <th class="text-center">Número</th>
                <th class="text-center">Foto de CC</th>
                <th class="text-center">Nombre </th>
                <th class="text-center">Edad</th>
                <th class="text-center">Correo</th>
                <th class="text-center">Teléfono 1</th>
                <th class="text-center">Teléfono 2</th>
                <th class="text-center">Medio de contacto</th>
                <th class="text-center">Contacto de emergencia</th>
                <th class="text-center">Teléfono del contacto</th>
                <th class="text-center">Nacionalidad</th>
                <th class="text-center">Departamento</th>
                <th class="text-center">Municipio</th>
                <th class="text-center">Ocupación</th>
                <th class="text-center">Tiempo de obligaciones</th>
                <th class="text-center">Sede de elección</th>
                <th class="text-center">Modalidad</th>
                <th class="text-center">Programa de interés</th>
                <th class="text-center">Nivel de preferencia</th>
                <th class="text-center">Dispositivo</th>
                <th class="text-center">Internet</th>
                <th class="text-center">Estado de admision</th>
                <th class="text-center">Fecha de registro</th>
                <th class="text-center">Fecha primer contacto</th>
                <th class="text-center">Fecha ultimo contacto</th>
                <th class="text-center">Fecha de matricula o rechazo</th>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalID_<?php echo $row['number_id']; ?>">
                            <i class="bi bi-card-image"></i>
                        </button>

                        <!-- Modal para mostrar las imágenes -->
                        <div class="modal fade" id="modalID_<?php echo $row['number_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-indigo-dark">
                                        <h5 class="modal-title">Imágenes de Identificación</h5>
                                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body position-relative" style="overflow: visible;">
                                        <div class="row">
                                            <!-- Frente del documento -->
                                            <div class="col-12 mb-4 text-center">
                                                <h6>Frente del documento</h6>
                                                <div class="position-relative overflow-visible">
                                                    <img id="idImageFront_<?php echo $row['number_id']; ?>"
                                                        src="https://dashboard.uttalento.co/files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
                                                        class="img-fluid w-100 zoomable"
                                                        style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                        alt="Frente ID"
                                                        onclick="toggleZoom('idImageFront_<?php echo $row['number_id']; ?>')">
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                </div>
                                            </div>

                                            <!-- Reverso del documento -->
                                            <div class="col-12 text-center">
                                                <h6>Reverso del documento</h6>
                                                <div class="position-relative overflow-visible">
                                                    <img id="idImageBack_<?php echo $row['number_id']; ?>"
                                                        src="https://dashboard.uttalento.co/files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
                                                        class="img-fluid w-100 zoomable"
                                                        style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                        alt="Reverso ID"
                                                        onclick="toggleZoom('idImageBack_<?php echo $row['number_id']; ?>')">
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Verificar si la variable ya existe en el ámbito global
                            if (typeof window.imageTransforms === 'undefined') {
                                window.imageTransforms = {};
                            }

                            function rotateImage(imageId, degrees) {
                                if (!window.imageTransforms[imageId]) {
                                    window.imageTransforms[imageId] = {
                                        rotation: 0,
                                        scale: 1
                                    };
                                }
                                window.imageTransforms[imageId].rotation += degrees;
                                applyTransform(imageId);
                            }

                            function toggleZoom(imageId) {
                                if (!window.imageTransforms[imageId]) {
                                    window.imageTransforms[imageId] = {
                                        rotation: 0,
                                        scale: 1
                                    };
                                }
                                window.imageTransforms[imageId].scale = window.imageTransforms[imageId].scale === 1 ? 2 : 1;
                                applyTransform(imageId);
                            }

                            function applyTransform(imageId) {
                                let imgElement = document.getElementById(imageId);
                                if (imgElement) {
                                    let {
                                        rotation,
                                        scale
                                    } = window.imageTransforms[imageId];
                                    imgElement.style.transform = `rotate(${rotation}deg) scale(${scale})`;
                                }
                            }
                        </script>

                    </td>

                    <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['second_name']) . ' ' . htmlspecialchars($row['first_last']) . ' ' . htmlspecialchars($row['second_last']); ?>
                    </td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['first_phone']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['second_phone']); ?></td>
                    <td id="medioContacto_<?php echo $row['number_id']; ?>">
                        <?php echo htmlspecialchars($row['contactMedium']); ?>
                    </td>

                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['emergency_contact_name']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['emergency_contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['nationality']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($row['departamento']); ?>
                    </td>

                    <td><b class="text-center"><?php echo htmlspecialchars($row['municipio']); ?></b></td>
                    <td><?php echo htmlspecialchars($row['occupation']); ?></td>
                    <td><?php echo htmlspecialchars($row['time_obligations']); ?></td>
                    <td><?php echo htmlspecialchars($row['headquarters']); ?></td>
                    <td><?php echo htmlspecialchars($row['mode']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['program']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['level']); ?></td>
                    

                    <td><?php echo htmlspecialchars($row['technologies']); ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['internet']); ?>
                    </td>


                    <td>
                        <?php
                        if ($row['statusAdmin'] == '1') {
                            echo 'Beneficiario';
                        } elseif ($row['statusAdmin'] == '0') {
                            echo 'Sin estado';
                        } elseif ($row['statusAdmin'] == '2') {
                            echo 'Rechazado';
                        } elseif ($row['statusAdmin'] == '3') {
                            echo 'Matriculado';
                        } elseif ($row['statusAdmin'] == '4') {
                            echo 'Sin contacto';
                        } elseif ($row['statusAdmin'] == '5') {
                            echo 'En proceso';
                        }
                        ?>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars($row['creationDate']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['first_contact_date']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['last_contact_date']); ?></td>
                    <td class="text-center">
                        <?php 
                            if ($row['statusAdmin'] == '2') {
                                echo !empty($row['last_contact_date']) ? htmlspecialchars($row['last_contact_date']) : 'Pendiente';
                            } else {
                                echo !empty($row['matriculation_date']) ? htmlspecialchars($row['matriculation_date']) : 'Pendiente';
                            }
                        ?>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Función para obtener la clase del botón según el medio de contacto
    function getBtnClass(medio) {
        let btnClass = '';
        let icon = '';

        if (medio == 'WhatsApp') {
            btnClass = 'btn-secondary';
            icon = '<i class="bi bi-whatsapp"></i>';
        } else if (medio == 'Teléfono') { 
            btnClass = 'btn-secondary';
            icon = '<i class="bi bi-telephone"></i>';
        } else if (medio == 'Correo') {
            btnClass = 'btn-secondary'; 
            icon = '<i class="bi bi-envelope"></i>';
        }

        return {
            btnClass,
            icon
        };
    }

    // Muestra una notificación de actualización con SweetAlert2
        document.getElementById('exportarExcel').addEventListener('click', function() {
        Swal.fire({
            title: 'Generando archivo Excel',
            html: '<div class="d-flex flex-column align-items-center">' +
                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                '<p>Procesando datos para exportación...</p>' +
                '<p class="text-muted small">Este proceso puede tardar varios minutos.</p>' +
                '</div>',
            allowOutsideClick: false,
            showConfirmButton: false
        });
    
        fetch('components/registration_traking/export_list_tracking.php?action=export')
            .then(response => {
                if (!response.ok) throw new Error('Error en la generación del archivo');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                Swal.update({
                    title: 'Archivo generado correctamente',
                    html: 'Iniciando descarga...',
                    icon: 'success'
                });
    
                const a = document.createElement('a');
                a.href = url;
                a.download = 'lista_seguimiento.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
    
                setTimeout(() => Swal.close(), 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al generar el archivo. Por favor, inténtalo de nuevo.',
                    icon: 'error'
                });
            });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>