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

// Obtener los datos con paginación y búsqueda
$sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    WHERE departamentos.id_departamento= 11
    AND user_register.status = '1'
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)
    ORDER BY user_register.first_name ASC  
    LIMIT ? OFFSET ?";

$sqlContactLog = "SELECT cl.*, a.name AS advisor_name
                  FROM contact_log cl
                  LEFT JOIN advisors a ON cl.idAdvisor = a.id
                  WHERE cl.number_id = ?";

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
    AND user_register.status = 1 AND user_register.statusAdmin = '' AND user_register.department = '11'
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)";
$stmtTotal = $conn->prepare($totalSql);
$stmtTotal->bind_param('ss', $searchParam, $searchParam);
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Agregar esta consulta al inicio del archivo donde están las otras consultas
function obtenerHorarios($conn, $mode)
{
    $sql = "SELECT DISTINCT schedule 
            FROM schedules 
            WHERE mode = ?
            ORDER BY schedule ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mode);
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
                <th>Tipo ID</th>
                <th>Número</th>
                <th>Foto de CC</th>
                <th>Nombre </th>
                <th>Edad</th>
                <th>Correo</th>
                <th>Teléfono 1</th>
                <th>Teléfono 2</th>
                <th>Medio de contacto</th>
                <th>Contacto de emergencia</th>
                <th>Teléfono del contacto</th>
                <th>Nacionalidad</th>
                <th>Departamento</th>
                <th>Municipio</th>
                <th>Ocupación</th>
                <th>Campesino</th>
                <th>Tiempo de obligaciones</th>
                <th>Sede de elección</th>
                <th>Modalidad</th>
                <th>Actualizar modalidad</th>
                <th>Programa de interés</th>
                <th>Nivel de preferencia</th>
                <th>Lote</th>
                <th>Actualizar programa, nivel, sede y lote</th>
                <th>Horario</th>
                <th>Horario alternativo</th>
                <th>Cambiar Horarios</th>
                <th>Dispositivo</th>
                <th>Internet</th>
                <th>Estado</th>
                <th>Estado de admision</th>
                <th>Actualizar medio de contacto</th>
                <th>Puntaje de prueba</th>
                <th>Nivel obtenido</th>
                <th>Actualizar contacto</th>
                <th>Actualizar admision</th>
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
                                                        src="../files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
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
                                                        src="../files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
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
                        <?php
                        // Asigna la clase y el ícono según el valor de 'contactMedium'
                        $btnClass = '';
                        $btnText = htmlspecialchars($row['contactMedium']); // El texto que aparecerá en la tooltip
                        $icon = ''; // Ícono correspondiente

                        if ($row['contactMedium'] === 'WhatsApp') {
                            $btnClass = 'btn bg-lime-dark text-white'; // Verde para WhatsApp
                            $icon = '<i class="bi bi-whatsapp"></i>'; // Ícono de WhatsApp
                        } elseif ($row['contactMedium'] === 'Teléfono') {
                            $btnClass = 'btn bg-teal-dark text-white'; // Azul para Teléfono
                            $icon = '<i class="bi bi-telephone"></i>'; // Ícono de Teléfono
                        } elseif ($row['contactMedium'] === 'Correo') {
                            $btnClass = 'btn bg-orange-light'; // Amarillo para Correo
                            $icon = '<i class="bi bi-envelope"></i>'; // Ícono de Correo
                        } else {
                            $btnClass = 'btn btn-secondary'; // Clase genérica si no coincide
                            $icon = '<i class="bi bi-question-circle"></i>'; // Ícono genérico
                            $btnText = 'Desconocido'; // Texto genérico
                        }

                        // Mostrar el botón con la clase, ícono y tooltip correspondientes
                        echo '<a tabindex="0" role="button" class="' . $btnClass . '" data-toggle="popover" data-trigger="focus" data-placement="top" title="' . $btnText . '">'
                            . $icon .
                            '</a>';
                        ?>
                    </td>

                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['emergency_contact_name']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['emergency_contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['nationality']); ?></td>
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

                    <td><b class="text-center"><?php echo htmlspecialchars($row['municipio']); ?></b></td>
                    <td><?php echo htmlspecialchars($row['occupation']); ?></td>
                    <td><?php echo !empty($row['country_person']) ? htmlspecialchars($row['country_person']) : 'Sin especificar'; ?></td>
                    <td><?php echo htmlspecialchars($row['time_obligations']); ?></td>
                    <td><?php echo htmlspecialchars($row['headquarters']); ?></td>
                    <td><?php echo htmlspecialchars($row['mode']); ?></td>
                    <td>
                        <button class="btn text-white" style="background-color: #fc4b08;" onclick="modalActualizarModalidad(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar modalidad">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                    </td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['program']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['level']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['lote']); ?></td>

                    <td>
                        <button class="btn btn-warning" onclick="mostrarModalActualizarPrograma(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar programa y nivel">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                    </td>


                    <td class="text-center">
                        <a class="btn bg-indigo-light"
                            tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top"
                            title="<?php echo empty($row['schedules']) ? 'Sin horario asignado' : htmlspecialchars($row['schedules']); ?>">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <a class="btn bg-teal-light"
                            tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top"
                            title="<?php echo empty($row['schedules_alternative']) ? 'Sin horario asignado' : htmlspecialchars($row['schedules']); ?>">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    </td>
                    <td>
                        <button class="btn text-white" style="background-color: #b624d5;" onclick="mostrarModalActualizarHorario(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar horario">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                    </td>
                    <?php
                    // Asigna la clase, ícono y texto del tooltip según el valor de 'technologies'
                    $btnClass = '';
                    $btnText = htmlspecialchars($row['technologies']); // El texto que aparecerá en la tooltip
                    $icon = ''; // Ícono correspondiente

                    if ($row['technologies'] === 'computador') {
                        $btnClass = 'bg-indigo-dark text-white'; // Clase para computador
                        $icon = '<i class="bi bi-laptop"></i>'; // Ícono de computador
                    } elseif ($row['technologies'] === 'smartphone') {
                        $btnClass = 'bg-teal-dark text-white'; // Clase para smartphone
                        $icon = '<i class="bi bi-phone"></i>'; // Ícono de smartphone
                    } elseif ($row['technologies'] === 'tablet') {
                        $btnClass = 'bg-amber-light text-white'; // Clase para tablet
                        $icon = '<i class="bi bi-tablet"></i>'; // Ícono de tablet
                    } else {
                        $btnClass = 'btn-secondary'; // Clase genérica si no coincide
                        $icon = '<i class="bi bi-question-circle"></i>'; // Ícono genérico
                    }

                    // Mostrar el botón con la clase, ícono y tooltip correspondientes
                    echo '<td class="text-center">
                                <a class="btn ' . $btnClass . '" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" 
                                title="' . $btnText . '">
                                    ' . $icon . '
                                </a>
                            </td>';
                    ?>

                    <?php
                    $btnClass = '';
                    $btnText = htmlspecialchars($row['internet']); // El texto que aparecerá en la tooltip
                    $icon = ''; // Ícono correspondiente

                    // Mostrar el estado internet
                    if ($row['internet'] === 'Sí') {
                        $btnClass = 'bg-indigo-dark text-white'; // Clase para internet
                        $icon = '<i class="bi bi-router-fill"></i>'; // Ícono de internet
                    } elseif ($row['internet'] === 'No') {
                        $btnClass = 'bg-red-dark text-white'; // Clase para smartphone
                        $icon = '<i class="bi bi-wifi-off"></i>'; // Ícono de wifi off
                    }
                    // Mostrar el botón con la clase, ícono y tooltip correspondientes
                    echo '<td class="text-center">
                    <a class="btn ' . $btnClass . '" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" 
                        title="' . $btnText . '">
                        ' . $icon . '
                    </a>
                  </td>'
                    ?>

                    <td>
                        <?php
                        // Verificar condiciones para cada registro
                        $isAccepted = false;
                        if ($row['mode'] === 'Presencial') {
                            if (
                                $row['typeID'] === 'CC' && $row['age'] > 17 &&
                                (strtoupper($row['departamento']) === 'BOGOTÁ, D.C.')
                            ) {
                                $isAccepted = true;
                            }
                        } elseif ($row['mode'] === 'Virtual') {
                            if (
                                $row['typeID'] === 'CC' && $row['age'] > 17 &&
                                (strtoupper($row['departamento']) === 'BOGOTÁ, D.C.') &&
                                $row['internet'] === 'Sí'
                            ) {
                                $isAccepted = true;
                            }
                        }

                        if ($isAccepted) {
                            echo '<a class="btn bg-teal-dark w-100" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" title="CUMPLE"><i class="bi bi-check-circle"></i></a>';
                        } else {
                            echo '<a class="btn bg-danger text-white w-100" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" title="NO CUMPLE"><i class="bi bi-x-circle"></i></a>';
                        }
                        ?>
                    </td>

                    <td>
                        <?php
                        if ($row['statusAdmin'] == '1') {
                            echo '<button class="btn bg-teal-dark" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="BENEFICIARIO"><i class="bi bi-check-circle"></i></button>';
                        } elseif ($row['statusAdmin'] == '0') {
                            echo '<button class="btn bg-indigo-dark text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="SIN ESTADO"><i class="bi bi-question-circle"></i></button>';
                        } elseif ($row['statusAdmin'] == '2') {
                            echo '<button class="btn bg-danger" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="RECHAZADO"><i class="bi bi-x-circle"></i></button>';
                        } elseif ($row['statusAdmin'] == '3') {
                            echo '<button class="btn bg-success text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="MATRICULADO"><i class="fa-solid fa-pencil"></i></button>';
                        } elseif ($row['statusAdmin'] == '4') {
                            echo '<button class="btn bg-secondary text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="PENDIENTE"><i class="bi bi-telephone-x"></i></button>';
                        } elseif ($row['statusAdmin'] == '5') {
                            echo '<button class="btn bg-warning text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="EN PROCESO"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden"></span></div></button>';
                        } elseif ($row['statusAdmin'] == '6') {
                            echo '<button class="btn bg-orange-dark text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="CERTIFICADO" data-status="6"><i class="bi bi-patch-check-fill"></i></button>';
                        } elseif ($row['statusAdmin'] == '7') {
                            echo '<button class="btn bg-silver text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="INACTIVO"><i class="bi bi-person-x"></i></button>';
                        }
                        ?>
                    </td>
                    <td>
                        <button class="btn bg-magenta-dark text-white" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar medio de contacto">
                            <i class="bi bi-arrow-left-right"></i></button>
                    </td>
                    <td><?php
                        if (isset($nivelesUsuarios[$row['number_id']])) {
                            $puntaje = $nivelesUsuarios[$row['number_id']];
                            if ($puntaje >= 0 && $puntaje <= 5) {
                                echo '<button class="btn bg-magenta-dark w-100" role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                            } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                echo '<button class="btn bg-orange-dark w-100" role="alert"role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                            } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                echo '<button class="btn bg-teal-dark w-100" role="alert" role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                            }
                        } else {
                            echo '<a class="btn bg-silver w-100" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" title="No ha presentado la prueba" >
                            <i class="bi bi-ban"></i>
                            </a>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (isset($nivelesUsuarios[$row['number_id']])) {
                            $puntaje = $nivelesUsuarios[$row['number_id']];
                            if ($puntaje >= 0 && $puntaje <= 5) {
                                echo '<button class="btn bg-magenta-dark w-100" role="alert">Básico</div>';
                            } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                echo '<button class="btn bg-orange-dark w-100" role="alert"role="alert">Intermedio</div>';
                            } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                echo '<button class="btn bg-teal-dark w-100" role="alert" role="alert">Avanzado</div>';
                            }
                        } else {
                            echo '<a class="btn bg-silver w-100" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" title="No ha presentado la prueba" >
                            <i class="bi bi-ban"></i>
                            </a>';
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>">
                            <i class="bi bi-telephone"></i>
                        </button>
                    </td>

                    <td>
                        <button class="btn bg-indigo-dark text-white" onclick="mostrarModalActualizarAdmision(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar estado de admisión">
                            <i class="bi bi-arrow-left-right"></i></button>
                    </td>
                </tr>



                <!-- Modal -->
                <div class="modal fade" id="modalLlamada_<?php echo $row['number_id']; ?>" tabindex="-1" aria-labelledby="modalLlamadaLabel_<?php echo $row['number_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-indigo-dark">
                                <h5 class="modal-title" id="modalLlamadaLabel_<?php echo $row['number_id']; ?>">
                                    <i class="bi bi-telephone"></i> Información de Llamada
                                </h5>
                                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formActualizarLlamada_<?php echo $row['number_id']; ?>" method="POST" onsubmit="return actualizarLlamada(<?php echo $row['number_id']; ?>)">
                                <div class="modal-body">
                                    <!-- Contenedor para asesor actual y anterior -->
                                    <div class="row">
                                        <!-- Columna para el asesor actual -->
                                        <div class="col-md-6">
                                            <div class="mb-3"><u><strong>Asesor actual:</strong></u></div>
                                            <hr class="hr" />
                                            <div class="mb-3">
                                                <label class="form-label"><strong>ID de asesor:</strong></label>
                                                <input type="text" class="form-control" name="idAdvisor" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Nombre:</strong></label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php
                                                            // Consulta para obtener todos los asesores
                                                            $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
                                                            $resultAsesores = $conn->query($sqlAsesores);

                                                            // Buscar y mostrar el nombre del asesor correspondiente
                                                            if ($resultAsesores && $resultAsesores->num_rows > 0) {
                                                                while ($asesor = $resultAsesores->fetch_assoc()) {
                                                                    if ($asesor['idAdvisor'] == $_SESSION['username']) {
                                                                        echo htmlspecialchars($asesor['name']);
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            ?>">
                                            </div>
                                        </div>

                                        <!-- Columna para el asesor anterior -->
                                        <div class="col-md-6">
                                            <div class="mb-3"><u><strong>Asesor anterior:</strong></u></div>
                                            <hr class="hr" />
                                            <div class="mb-3">
                                                <label class="form-label"><strong>ID de asesor:</strong></label>
                                                <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($row['idAdvisor']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Nombre:</strong></label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php
                                                            // Consulta para obtener todos los asesores
                                                            $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
                                                            $resultAsesores = $conn->query($sqlAsesores);

                                                            // Buscar y mostrar el nombre del asesor correspondiente
                                                            if ($resultAsesores && $resultAsesores->num_rows > 0) {
                                                                while ($asesor = $resultAsesores->fetch_assoc()) {
                                                                    if ($asesor['idAdvisor'] == $row['idAdvisor']) {
                                                                        echo htmlspecialchars($asesor['name']);
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Resto del formulario -->
                                    <hr class="hr" />
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Detalle:</strong></label>
                                        <select class="form-control" name="details">
                                            <option value="Sin detalles" <?php if ($row['details'] == 'Sin detalles') echo 'selected'; ?>>Sin detalles</option>
                                            <option value="Número equivocado" <?php if ($row['details'] == 'Número equivocado') echo 'selected'; ?>>Número equivocado</option>
                                            <option value="Teléfono apagado" <?php if ($row['details'] == 'Teléfono apagado') echo 'selected'; ?>>Teléfono apagado</option>
                                            <option value="Teléfono desconectado" <?php if ($row['details'] == 'Teléfono desconectado') echo 'selected'; ?>>Teléfono desconectado</option>
                                            <option value="Sin señal" <?php if ($row['details'] == 'Sin señal') echo 'selected'; ?>>Sin señal</option>
                                            <option value="No contestan" <?php if ($row['details'] == 'No contestan') echo 'selected'; ?>>No contestan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Estableció Contacto:</strong></label>
                                        <select class="form-control" name="contact_established">
                                            <option value="0" <?php if ($row['contact_established'] == 0) echo 'selected'; ?>>No</option>
                                            <option value="1" <?php if ($row['contact_established'] == 1) echo 'selected'; ?>>Sí</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Aún Interesado:</strong></label>
                                        <select class="form-control" name="continues_interested">
                                            <option value="0" <?php if ($row['continues_interested'] == 0) echo 'selected'; ?>>No</option>
                                            <option value="1" <?php if ($row['continues_interested'] == 1) echo 'selected'; ?>>Sí</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Observación:</strong></label>
                                        <textarea rows="3" class="form-control" name="observation"><?php echo htmlspecialchars($row['observation']); ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer position-relative d-flex justify-content-center">
                                    <button type="submit" class="btn bg-indigo-dark text-white">Actualizar Información</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                                <!-- Modal para actualizar horario -->
                <div id="modalActualizarHorario_<?php echo $row['number_id']; ?>" class="modal fade" aria-hidden="true" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-indigo-dark">
                                <h5 class="modal-title text-white">
                                    <i class="bi bi-clock"></i> Actualizar Horarios
                                </h5>
                                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formActualizarHorario_<?php echo $row['number_id']; ?>">
                                    <!-- Horario Principal -->
                                    <div class="form-group mb-3">
                                        <label>Horario Principal actual:</label>
                                        <input type="text" class="form-control" value="<?php echo !empty($row['schedules']) ? htmlspecialchars($row['schedules']) : 'Sin horario asignado'; ?>" readonly>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="nuevoHorario_<?php echo $row['number_id']; ?>">Seleccionar nuevo horario principal:</label>
                                        <select class="form-control" id="nuevoHorario_<?php echo $row['number_id']; ?>" name="nuevoHorario">
                                            <option value="">Seleccionar horario</option>
                                            <?php
                                            $horarios = obtenerHorarios($conn, $row['mode']);
                                            foreach ($horarios as $horario):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($horario); ?>">
                                                    <?php echo htmlspecialchars($horario); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                
                                    <!-- Horario Alternativo -->
                                    <div class="form-group mb-3">
                                        <label>Horario Alternativo actual:</label>
                                        <input type="text" class="form-control" value="<?php echo !empty($row['schedules_alternative']) ? htmlspecialchars($row['schedules_alternative']) : 'Sin horario alternativo'; ?>" readonly>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="nuevoHorarioAlt_<?php echo $row['number_id']; ?>">Seleccionar nuevo horario alternativo:</label>
                                        <select class="form-control" id="nuevoHorarioAlt_<?php echo $row['number_id']; ?>" name="nuevoHorarioAlternativo">
                                            <option value="">Seleccionar horario</option>
                                            <?php foreach ($horarios as $horario): ?>
                                                <option value="<?php echo htmlspecialchars($horario); ?>">
                                                    <?php echo htmlspecialchars($horario); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                
                                    <button type="submit" class="btn bg-indigo-dark text-white w-100">Actualizar Horarios</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function mostrarModalActualizar(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizar_' + id).remove();

        // Crear el modal dinámicamente con un identificador único
        const modalHtml = `
    <div id="modalActualizar_${id}" class="modal fade"  aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-indigo-dark">
                    <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Medio de Contacto</h5>
                      <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            
                </div>
                <div class="modal-body">
                    <form id="formActualizarMedio_${id}">
                        <div class="form-group">
                            <label for="nuevoMedio_${id}">Seleccionar nuevo medio de contacto:</label>
                            <select class="form-control" id="nuevoMedio_${id}" name="nuevoMedio" required>
                                <option value="Correo">Correo</option>
                                <option value="Teléfono">Teléfono</option>
                                <option value="WhatsApp">WhatsApp</option>
                            </select>
                        </div>
                        <br>
                        <input type="hidden" name="id" value="${id}">
                        <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    `;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar el modal
        $('#modalActualizar_' + id).modal('show');

        // Manejar el envío del formulario con confirmación
        $('#formActualizarMedio_' + id).on('submit', function(e) {
            e.preventDefault();

            if (confirm("¿Está seguro de que desea actualizar el medio de contacto?")) {
                const nuevoMedio = $('#nuevoMedio_' + id).val();
                actualizarMedioContacto(id, nuevoMedio);
                $('#modalActualizar_' + id).modal('hide');
            } else {
                toastr.info("La actualización ha sido cancelada.");
            }
        });
    }

    function actualizarMedioContacto(id, nuevoMedio) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_medio_contacto.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const response = xhr.responseText;
                console.log("Respuesta del servidor: " + response);

                if (response == "success") {
                    const result = getBtnClass(nuevoMedio);
                    const botonHtml = `<button class="btn ${result.btnClass}">${result.icon} ${nuevoMedio}</button>`;

                    // Actualizar solo el botón específico
                    document.querySelector("#medioContacto_" + id).innerHTML = botonHtml;

                    toastr.success("El medio de contacto se actualizó correctamente.");
                } else {
                    toastr.error("Hubo un error al actualizar el medio de contacto.");
                }
            }
        };
        xhr.send("id=" + id + "&nuevoMedio=" + encodeURIComponent(nuevoMedio));
    }

    // Función para obtener la clase del botón según el medio de contacto
    function getBtnClass(medio) {
        let btnClass = '';
        let icon = '';

        if (medio == 'WhatsApp') {
            btnClass = 'bg-lime-dark w-100';
            icon = '<i class="bi bi-whatsapp"></i>';
        } else if (medio == 'Teléfono') {
            btnClass = 'bg-teal-dark w-100';
            icon = '<i class="bi bi-telephone"></i>';
        } else if (medio == 'Correo') {
            btnClass = 'bg-amber-light w-100';
            icon = '<i class="bi bi-envelope"></i>';
        }

        return {
            btnClass,
            icon
        };
    }

    function actualizarLlamada(id) {
        const form = document.getElementById('formActualizarLlamada_' + id);
        if (!form) {
            console.error('Formulario no encontrado');
            return false;
        }

        const formData = new FormData(form);
        formData.append('number_id', id);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_llamada.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();

                    // Verificar si es una respuesta de duplicado con temporizador
                    if (response.startsWith("duplicate:")) {
                        const timeRemaining = parseInt(response.split(':')[1]);

                        // Mostrar SweetAlert con temporizador
                        let timerInterval;
                        Swal.fire({
                            title: '¡Registro duplicado!',
                            html: `Ya existe un registro reciente para este estudiante.<br>
                               Podrá registrar un nuevo contacto en 1 minuto`,
                            icon: 'warning',
                            timer: timeRemaining * 1000,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const timer = Swal.getPopup().querySelector('b');
                                timerInterval = setInterval(() => {
                                    timer.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        });

                    } else if (response === "success") {
                        // Cerrar el modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalLlamada_' + id));
                        modal.hide();

                        Swal.fire({
                            title: '¡Exitoso! 🎉',
                            text: 'La información se ha guardado correctamente.',
                            toast: true,
                            position: 'center',
                        });
                    } else {
                        // Mostrar notificación de error
                        Swal.fire({
                            title: 'Error! ❌',
                            text: 'Hubo un problema al guardar la información: ' + response,
                            toast: true,
                            position: 'center',
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 4000,
                        });
                    }
                } else {
                    console.error("Error en la conexión con el servidor");
                }
            }
        };

        xhr.onerror = function() {
            Swal.fire({
                title: 'Error! ❌',
                text: 'No se pudo conectar con el servidor.',
                toast: true,
                position: 'center',
                icon: 'error',
                showConfirmButton: false,
                timer: 4000,
            });
        };

        xhr.send(formData);
        return false;
    }

    function mostrarModalActualizarAdmision(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarAdmision_' + id).remove();

        // Crear el modal dinámicamente con un identificador único
        const modalHtml = `
        <div id="modalActualizarAdmision_${id}" class="modal fade" aria-hidden="true" aria-labelledby="modalActualizarAdmisionLabel" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark">
                        <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Estado de Admisión</h5>
                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formActualizarAdmision_${id}">
                            <div class="form-group">
                                <label for="nuevoEstado_${id}">Seleccionar nuevo estado:</label>
                                <select class="form-control" id="nuevoEstado_${id}" name="nuevoEstado" required>
                                   <option value="">Seleccionar</option>
                                    <option value="1">Beneficiario</option>
                                    <option value="2">Rechazado</option>
                                    <?php if ($rol == 'Administrador'): ?>
                                    <option value="3">Matriculado</option>
                                    <?php endif; ?>
                                    <option value="4">Sin contacto</option>
                                    <option value="5">En proceso</option>
                                </select>
                            </div>
                            <br>
                            <input type="hidden" name="id" value="${id}">
                            <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar el modal
        $('#modalActualizarAdmision_' + id).modal('show');

        // Manejar el envío del formulario
        $('#formActualizarAdmision_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar el estado de admisión?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const nuevoEstado = $('#nuevoEstado_' + id).val();
                    actualizarEstadoAdmision(id, nuevoEstado);
                    $('#modalActualizarAdmision_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarEstadoAdmision(id, nuevoEstado) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_admision.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'El estado de admisión se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar el estado de admisión.'
                        });
                    }
                }
            }
        };

        xhr.send("id=" + id + "&nuevoEstado=" + encodeURIComponent(nuevoEstado));
    }

    function actualizarModalidad(id, nuevaModalidad) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_modalidad.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La modalidad se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la modalidad.'
                        });
                    }
                }
            }
        };

        xhr.send("id=" + id + "&nuevaModalidad=" + encodeURIComponent(nuevaModalidad));
    }

    function modalActualizarModalidad(id) {
        $('#modalActualizarModalidad_' + id).remove();

        const modalHtml = `
            <div id="modalActualizarModalidad_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-indigo-dark">
                            <h5 class="modal-title text-center">
                                <i class="bi bi-arrow-left-right"></i> Actualizar Modalidad
                            </h5>
                            <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formActualizarModalidad_${id}">
                                <div class="form-group">
                                    <label for="nuevaModalidad_${id}">Seleccionar nueva modalidad:</label>
                                    <select class="form-control" id="nuevaModalidad_${id}" name="nuevaModalidad" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Presencial">Presencial</option>
                                        <option value="Virtual">Virtual</option>
                                    </select>
                                </div>
                                <br>
                                <input type="hidden" name="id" value="${id}">
                                <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#modalActualizarModalidad_' + id).modal('show');

        $('#formActualizarModalidad_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar la modalidad?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const nuevaModalidad = $('#nuevaModalidad_' + id).val();
                    actualizarModalidad(id, nuevaModalidad);
                    $('#modalActualizarModalidad_' + id).modal('hide');
                }
            });
        });
    }

    function mostrarModalActualizarHorario(id) {
        $('#modalActualizarHorario_' + id).modal('show');

        $('#formActualizarHorario_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar el horario?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const nuevoHorario = $('#nuevoHorario_' + id).val();
                    const nuevoHorarioAlt = $('#nuevoHorarioAlt_' + id).val();
                    actualizarHorario(id, nuevoHorario, nuevoHorarioAlt);
                    $('#modalActualizarHorario_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarHorario(id, nuevoHorario, nuevoHorarioAlt) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('nuevoHorario', nuevoHorario);
        formData.append('nuevoHorarioAlternativo', nuevoHorarioAlt);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_Horario.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'Los horarios se han actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar los horarios.'
                        });
                    }
                }
            }
        };

        xhr.send(formData);
    }

    function mostrarModalActualizarPrograma(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarPrograma_' + id).remove();

        // Crear el modal dinámicamente con un identificador único
        const modalHtml = `
        <div id="modalActualizarPrograma_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark">
                        <h5 class="modal-title text-center">
                            <i class="bi bi-arrow-left-right"></i> Actualizar Programa, Nivel, Sede y Lote
                        </h5>
                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formActualizarPrograma_${id}">
                            <div class="form-group mb-3">
                                <label for="nuevoPrograma_${id}">Seleccionar nuevo programa:</label>
                                <select class="form-control" id="nuevoPrograma_${id}" name="nuevoPrograma">
                                    <option value="">Seleccionar programa</option>
                                    <option value="Programación">Programación</option>
                                    <option value="Ciberseguridad">Ciberseguridad</option>
                                    <option value="Arquitectura en la Nube">Arquitectura en la Nube</option>
                                    <option value="Análisis de datos">Análisis de datos</option>
                                    <option value="Inteligencia Artificial">Inteligencia Artificial</option>
                                    <option value="Blockchain">Blockchain</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="nuevoNivel_${id}">Seleccionar nuevo nivel:</label>
                                <select class="form-control" id="nuevoNivel_${id}" name="nuevoNivel" >
                                    <option value="">Seleccionar nivel</option>
                                    <option value="Explorador">Explorador</option>
                                    <option value="Innovador">Innovador</option>
                                    <option value="Integrador">Integrador</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="nuevoSede_${id}">Seleccionar nueva sede:</label>
                                <select class="form-control" id="nuevoSede_${id}" name="nuevoNivel" >
                                    <option value="">Seleccionar sede</option>
                                    <option value="Cota">Cota</option>
                                    <option value="Tunja">Tunja</option>
                                    <option value="Sogamoso">Sogamoso</option>
                                    <option value="Soacha">Soacha</option>
                                    <option value="Ubate">Ubate</option>
                                    <option value="Giradot">Giradot</option>
                                    <option value="Chía">Chía</option>
                                    <option value="Cajica">Cajica</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="nuevoLote_${id}">Seleccionar lote:</label>
                                <select class="form-control" id="nuevoLote_${id}" name="nuevoLote">
                                    <option value="">Seleccionar lote</option>
                                    <option value="1">Lote 1</option>
                                    <option value="2">Lote 2</option>
                                </select>
                            </div>
                            <input type="hidden" name="id" value="${id}">
                            <button type="submit" class="btn bg-indigo-dark text-white w-100">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#modalActualizarPrograma_' + id).modal('show');

        // Manejar el envío del formulario
        $('#formActualizarPrograma_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar la información?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Solo obtener valores si fueron seleccionados
                    const nuevoPrograma = $('#nuevoPrograma_' + id).val() || null;
                    const nuevoNivel = $('#nuevoNivel_' + id).val() || null;
                    const nuevoSede = $('#nuevoSede_' + id).val() || null;
                    const nuevoLote = $('#nuevoLote_' + id).val() || null;

                    actualizarProgramaNivel(id, nuevoPrograma, nuevoNivel, nuevoSede, nuevoLote);
                    $('#modalActualizarPrograma_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarProgramaNivel(id, nuevoPrograma, nuevoNivel, nuevoSede, nuevoLote) {
        const formData = new FormData();
        formData.append('id', id);

        // Solo agregar los campos que tienen valor
        if (nuevoPrograma) formData.append('nuevoPrograma', nuevoPrograma);
        if (nuevoNivel) formData.append('nuevoNivel', nuevoNivel);
        if (nuevoSede) formData.append('nuevoSede', nuevoSede);
        if (nuevoLote) formData.append('nuevoLote', nuevoLote);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_programa.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'Se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la información.'
                        });
                    }
                }
            }
        };

        xhr.send(formData);
    }
    // Muestra una notificación de actualización con SweetAlert2
    Swal.fire({
        icon: 'info',
        title: 'Actualizando información...',
        text: 'Por favor, espere un momento.',
        position: 'center',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    })

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover({
            placement: 'top',
            trigger: 'focus',
            html: true
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        if (popoverTriggerList.length > 0) {
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    });

    // Manejar el botón de exportar a Excel
    document.getElementById('exportarExcel').addEventListener('click', function() {
        // Mostrar SweetAlert mientras se genera el archivo
        Swal.fire({
            title: 'Generando archivo Excel',
            html: '<div class="d-flex flex-column align-items-center">' +
                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                '<p>Procesando datos para exportación...</p>' +
                '<p class="text-muted small">Este proceso puede tardar varios minutos para archivos grandes.</p>' +
                '</div>',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        // Realizar la solicitud AJAX para generar el archivo
        fetch('components/registrationsContact/export_to_excel.php?action=export')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la generación del archivo');
                }
                return response.blob();
            })
            .then(blob => {
                // Crear URL del blob
                const url = window.URL.createObjectURL(blob);

                // Actualizar SweetAlert antes de iniciar la descarga
                Swal.update({
                    title: 'Archivo generado correctamente',
                    html: 'Iniciando descarga...',
                    icon: 'success'
                });

                // Crear elemento para la descarga
                const a = document.createElement('a');
                a.href = url;
                a.download = 'datos_inscritos.xlsx'; // Nombre del archivo
                document.body.appendChild(a);

                // Iniciar descarga y luego cerrar el SweetAlert
                a.click();
                window.URL.revokeObjectURL(url);

                // Cerrar SweetAlert después de un breve momento
                setTimeout(() => {
                    Swal.close();
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ha ocurrido un error al generar el archivo. Por favor, inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>