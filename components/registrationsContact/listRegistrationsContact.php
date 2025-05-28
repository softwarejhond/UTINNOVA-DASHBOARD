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
$limit = 30; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Parámetro de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modifica la consulta SQL existente
$sql = "SELECT 
            user_register.*, 
            EXISTS(
                SELECT 1 
                FROM participantes p 
                WHERE p.numero_documento = user_register.number_id
            ) AS tiene_certificado
        FROM user_register
        WHERE user_register.status = '1'
          AND (
              user_register.first_name LIKE CONCAT(?, '%') 
              OR user_register.number_id LIKE CONCAT(?, '%')
          )
        ORDER BY user_register.first_name ASC  
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);


$sqlContactLog = "SELECT cl.*, a.name AS advisor_name
                  FROM contact_log cl
                  LEFT JOIN advisors a ON cl.idAdvisor = a.id
                  WHERE cl.number_id = ?";


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

function obtenerSedes($conn)
{
    $sql = "SELECT DISTINCT name FROM headquarters ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $sedes = array();
    while ($row = $result->fetch_assoc()) {
        $sedes[] = $row['name'];
    }

    return $sedes;
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
            <div class="row mb-4 sticky-top bg-white shadow-sm py-3">
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
                                <option value="6">Certificado</option>
                                <option value="7">Inactivo</option>
                                <option value="8">Beneficiario contrapartida</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="w-100" style="overflow-x: auto;">
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
                    <th>Part. Ant.</th>
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
                            <b></b>
                        </td>

                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo strtoupper(str_replace(
                                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
                                ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
                                htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['second_name']) . ' ' . htmlspecialchars($row['first_last']) . ' ' . htmlspecialchars($row['second_last'])
                            )); ?></td>
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
                            $departamento = htmlspecialchars($row['department']);
                            if ($departamento === '11') {
                                echo "<button class='btn bg-lime-light w-100'><b>BOGOTÁ D.C.</b></button>"; // Botón verde para CUNDINAMARCA
                            }
                            ?>
                        </td>

                        <td>
                            <?php
                            $municipio = htmlspecialchars($row['municipality']);
                            if ($municipio === '11001') {
                                echo "<button class='btn bg-indigo-light w-100'><b>BOGOTÁ</b></button>"; // Botón verde para Bogotá
                            } else {
                                echo "<button class='btn bg-gray-light w-100'><b>" . $municipio . "</b></button>"; // Botón gris para otros municipios
                            }
                            ?>
                        </td>
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
                            $isAccepted = true;
                            if ($row['mode'] === 'Presencial') {
                                if (
                                    $row['typeID'] === 'CC' && $row['age'] > 17 &&
                                    (strtoupper($row['department']) === '11')
                                ) {
                                    $isAccepted = true;
                                }
                            } elseif ($row['mode'] === 'Virtual') {
                                if (
                                    $row['typeID'] === 'CC' && $row['age'] > 17 &&
                                    (strtoupper($row['department']) === '11') &&
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

                        <td class="text-center">
                            <?php if ($row['tiene_certificado']): ?>
                                <button class="btn text-white" style="background-color: #ffbf00;"
                                    onclick="mostrarCertificacionSwal('<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['first_last']); ?>')"
                                    data-bs-toggle="popover"
                                    data-bs-trigger="hover"
                                    data-bs-placement="top"
                                    data-bs-content="El estudiante cuenta con una certificación">
                                    <i class="fa-solid fa-graduation-cap fa-beat text-black"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary"
                                    data-bs-toggle="popover"
                                    data-bs-trigger="hover"
                                    data-bs-placement="top"
                                    data-bs-content="Sin información a destacar">
                                    <i class="bi bi-x"></i>
                                </button>
                            <?php endif; ?>
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
                            } elseif ($row['statusAdmin'] == '8') {
                                echo '<button class="btn bg-amber-dark text-dark" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="BENEFICIARIO CONTRAPARTIDA"><i class="bi bi-check-circle-fill"></i></button>';
                            } elseif ($row['statusAdmin'] == '9') {
                                echo '<button class="btn bg-magenta-dark text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="PENDIENTE MINTIC"><i class="bi bi-hourglass-split"></i></button>';
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

        // Primero verificamos si el estudiante tiene certificación anterior
        fetch(`components/registrationsContact/verificar_participante.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                // Preparar las opciones según si existe o no en participantes
                let opcionBeneficiario = '';

                if (data.existe) {
                    // Si existe en participantes, mostrar opción de Beneficiario para contrapartida
                    opcionBeneficiario = `<option value="8">Beneficiario para contrapartida</option>
                                          <option value="9">Pendiente MINTIC</option>`;
                } else {
                    // Si no existe, mostrar opción regular de Beneficiario
                    opcionBeneficiario = '<option value="1">Beneficiario</option>';
                }

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
                                            ${opcionBeneficiario}
                                            <option value="0">Sin estado</option>
                                            <option value="2">Rechazado</option>
                                            <option value="3">Matriculado</option>
                                            <option value="4">Pendiente</option>
                                            <option value="5">En proceso</option>
                                            <option value="6">Certificado</option>
                                            <option value="7">Inactivo</option>
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

                    const nuevoEstado = $('#nuevoEstado_' + id).val();

                    // Si el nuevo estado es Beneficiario (1), verificar si ya tiene cursos asignados
                    if (nuevoEstado === '1' || nuevoEstado === '8') {
                        // Mostrar cargando mientras verificamos
                        Swal.fire({
                            title: 'Verificando asignaciones',
                            text: 'Comprobando si el estudiante ya tiene cursos asignados...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Verificar si ya tiene cursos asignados
                        fetch(`components/registrationsContact/verificar_cursos_asignados.php?student_id=${id}`)
                            .then(response => response.json())
                            .then(data => {
                                Swal.close();

                                if (data.success && data.tiene_cursos) {
                                    // Si ya tiene cursos, preguntar si quiere actualizar los cursos existentes
                                    Swal.fire({
                                        title: 'El estudiante ya tiene cursos asignados',
                                        text: '¿Desea mantener la asignación actual o realizar una nueva?',
                                        icon: 'question',
                                        showDenyButton: true,
                                        confirmButtonText: 'Mantener actual',
                                        denyButtonText: 'Nueva asignación',
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Mantener la asignación actual, solo actualizar el estado
                                            actualizarEstadoAdmision(id, nuevoEstado);
                                            $('#modalActualizarAdmision_' + id).modal('hide');
                                        } else if (result.isDenied) {
                                            // Realizar nueva asignación
                                            $('#modalActualizarAdmision_' + id).modal('hide');
                                            mostrarModalSeleccionCursos(id, nuevoEstado);
                                        }
                                    });
                                } else {
                                    // Si no tiene cursos, mostrar modal para asignar cursos
                                    $('#modalActualizarAdmision_' + id).modal('hide');
                                    mostrarModalSeleccionCursos(id, nuevoEstado);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo verificar la información de cursos'
                                });
                            });
                    } else {
                        // Si no es Beneficiario, confirmar la actualización normalmente
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
                                actualizarEstadoAdmision(id, nuevoEstado);
                                $('#modalActualizarAdmision_' + id).modal('hide');
                            }
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error al verificar participante:', error);
                // Si hay error, mostrar modal con opciones predeterminadas
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo verificar la información del participante'
                });
            });
    }

    function autoSelectRelatedCourses(id, courseId) {
        // Obtener el texto de la opción seleccionada
        const selectedOption = $(`#bootcamp_${id} option:selected`).text();

        // Extraer el código del curso (formato C1L1-G1V)
        const courseCodeMatch = selectedOption.match(/C\d+L\d+-G\d+[A-Z]?/);
        if (!courseCodeMatch) return;

        const courseCode = courseCodeMatch[0];
        console.log("Curso seleccionado:", selectedOption);
        console.log("Código extraído:", courseCode);

        // Para cada tipo de curso, buscar el que tenga el mismo código
        const courseTypes = ['english', 'english_code', 'skills'];

        courseTypes.forEach(type => {
            const select = $(`#${type}_${id}`);
            let found = false;

            // Buscar en todas las opciones
            select.find('option').each(function() {
                const optionText = $(this).text();
                if (optionText.includes(courseCode)) {
                    select.val($(this).val());
                    found = true;
                    return false; // Salir del bucle each
                }
            });

            // Si no se encontró, dejar la selección como está
            if (!found) {
                console.log(`No se encontró curso ${type} con código ${courseCode}`);
            }
        });

        // Verificar cupos para todos los cursos automáticamente
        agregarVerificacionCupos(id);
    }

    function mostrarModalSeleccionCursos(id, nuevoEstado) {
        // Mostrar SweetAlert de carga
        Swal.fire({
            title: 'Cargando cursos',
            text: 'Obteniendo información de cursos disponibles...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Cargar los cursos desde la API de Moodle
        fetch('components/registrationsContact/get_moodle_courses.php')
            .then(response => response.json())
            .then(courses => {
                // Cerrar el SweetAlert de carga
                Swal.close();

                // Crear las opciones para cada tipo de curso
                let bootcampOptions = '<option value="">Seleccionar Bootcamp</option>';
                let englishOptions = '<option value="">Seleccionar Inglés Nivelatorio</option>';
                let englishCodeOptions = '<option value="">Seleccionar English Code</option>';
                let skillsOptions = '<option value="">Seleccionar Habilidades</option>';

                // Organizar los cursos por categoría
                courses.forEach(course => {
                    const courseOption = `<option value="${course.id}">${course.id} - ${course.fullname}</option>`;

                    // Filtrar por categoría
                    switch (parseInt(course.categoryid)) {
                        // Bootcamp (categorías específicas)
                        case 19:
                        case 21:
                        case 24:
                        case 26:
                        case 27:
                        case 35:
                        case 20:
                        case 22:
                        case 23:
                        case 25:
                        case 28:
                        case 35:
                            bootcampOptions += courseOption;
                            break;

                            // Inglés nivelatorio
                        case 18:
                        case 17:
                            englishOptions += courseOption;
                            break;

                            // English Code
                        case 31:
                        case 30:
                            englishCodeOptions += courseOption;
                            break;

                            // Habilidades
                        case 32:
                        case 33:
                            skillsOptions += courseOption;
                            break;
                    }
                });

                // Remover cualquier modal previo que pueda estar en el DOM
                $('#modalSeleccionCursos_' + id).remove();

                // Crear el modal de selección de cursos
                const modalHtml = `
                    <div id="modalSeleccionCursos_${id}" class="modal fade" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-indigo-dark">
                                    <h5 class="modal-title text-white">
                                        <i class="bi bi-book"></i> Asignación de cursos
                                    </h5>
                                    <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Seleccione los cursos para el estudiante. Recuerde verificar la disponibilidad de cupos antes de asignar.
                                    </div>
                                    
                                    <form id="formAsignarCursos_${id}">
                                        <div class="form-group mb-3">
                                            <label for="bootcamp_${id}" class="form-label">Bootcamp:</label>
                                            <select class="form-select" id="bootcamp_${id}" required>
                                                ${bootcampOptions}
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="english_${id}" class="form-label">Inglés Nivelatorio:</label>
                                            <select class="form-select" id="english_${id}" required>
                                                ${englishOptions}
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="english_code_${id}" class="form-label">English Code:</label>
                                            <select class="form-select" id="english_code_${id}" required>
                                                ${englishCodeOptions}
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="skills_${id}" class="form-label">Habilidades:</label>
                                            <select class="form-select" id="skills_${id}" required>
                                                ${skillsOptions}
                                            </select>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn bg-indigo-dark text-white">
                                                <i class="bi bi-check-circle"></i> Guardar asignación
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Añadir el modal al DOM 
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                $(`#bootcamp_${id}`).on('change', function() {
                    const bootcampId = $(this).val();
                    if (bootcampId) {
                        autoSelectRelatedCourses(id, bootcampId);
                    }
                });
                
                // Asegurar que el modal se muestre después de que el SweetAlert esté completamente cerrado
                setTimeout(() => {
                    // Mostrar el modal usando Bootstrap
                    const modalElement = document.getElementById(`modalSeleccionCursos_${id}`);
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();

                    // Agregar verificación de cupos para todos los cursos
                    agregarVerificacionCupos(id);

                    // Modificar el envío del formulario para incluir la verificación final
                    $(`#formAsignarCursos_${id}`).on('submit', async function(e) {
                        e.preventDefault();

                        const cursos = ['bootcamp', 'english', 'english_code', 'skills'];
                        let todosCursosTienenCupo = true;

                        // Verificar cupos para todos los cursos
                        for (const tipo of cursos) {
                            const cursoId = $(`#${tipo}_${id}`).val();
                            if (!cursoId) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: `Debe seleccionar un curso para ${tipo}`
                                });
                                return;
                            }

                            const respuestaVerificacion = await verificarCuposDisponibles(cursoId, tipo);
                            if (!respuestaVerificacion) {
                                todosCursosTienenCupo = false;
                                break;
                            }
                        }

                        if (todosCursosTienenCupo) {
                            // Crear objeto con datos para enviar al servidor
                            const formData = {
                                student_id: id,
                                estado: nuevoEstado,
                                bootcamp: {
                                    id: $(`#bootcamp_${id}`).val(),
                                    name: $(`#bootcamp_${id} option:selected`).text().substring(
                                        $(`#bootcamp_${id} option:selected`).text().indexOf('-') + 1
                                    ).trim()
                                },
                                english: {
                                    id: $(`#english_${id}`).val(),
                                    name: $(`#english_${id} option:selected`).text().substring(
                                        $(`#english_${id} option:selected`).text().indexOf('-') + 1
                                    ).trim()
                                },
                                english_code: {
                                    id: $(`#english_code_${id}`).val(),
                                    name: $(`#english_code_${id} option:selected`).text().substring(
                                        $(`#english_code_${id} option:selected`).text().indexOf('-') + 1
                                    ).trim()
                                },
                                skills: {
                                    id: $(`#skills_${id}`).val(),
                                    name: $(`#skills_${id} option:selected`).text().substring(
                                        $(`#skills_${id} option:selected`).text().indexOf('-') + 1
                                    ).trim()
                                }
                            };

                            guardarAsignacionCursos(id, formData);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Uno o más cursos no tienen cupos disponibles. Por favor, seleccione otros cursos.'
                            });
                        }
                    });
                }, 300); // Pequeño retraso para asegurar que el SweetAlert esté cerrado
            })
            .catch(error => {
                console.error('Error al cargar los cursos:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los cursos desde Moodle'
                });
            });
    }

    function verificarCuposDisponibles(courseId, courseType) {
        return fetch(`components/registrationsContact/verificar_cupo_curso.php?course_id=${courseId}&course_type=${courseType}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (!data.tiene_cupo) {
                        return Swal.fire({
                            icon: 'error',
                            title: 'No hay cupos disponibles',
                            text: `El curso seleccionado para ${courseType} no tiene cupos disponibles.`,
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            return false;
                        });
                    }

                    // Mostrar información de cupos disponibles
                    return Swal.fire({
                        icon: 'info',
                        title: `Información de cupos - ${courseType === 'bootcamp' ? 'Bootcamp' : 
                                courseType === 'skills' ? 'Habilidades' : 
                                courseType === 'english' ? 'Inglés Nivelatorio' : 'English Code'}`,
                        html: `
                            <div class="alert alert-info">
                                <p><strong>Cupos ocupados:</strong> ${data.total_asignaciones} de ${data.cupo_maximo}</p>
                                <p><strong>Cupos disponibles:</strong> ${data.cupos_disponibles}</p>
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#2B5BAC'
                    }).then(() => {
                        return true;
                    });
                } else {
                    // Si data.success es false, mostrar mensaje de error con data.message
                    return Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al verificar cupos disponibles',
                    }).then(() => {
                        return false;
                    });
                }
            })
            .catch(error => {
                console.error('Error al verificar cupos:', error);
                return Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ha ocurrido un error al verificar los cupos disponibles',
                }).then(() => {
                    return false;
                });
            });
    }

    function agregarVerificacionCupos(id) {
        const cursos = {
            'bootcamp': 'Bootcamp',
            'english': 'Inglés Nivelatorio',
            'english_code': 'English Code',
            'skills': 'Habilidades'
        };

        Object.entries(cursos).forEach(([tipo, nombre]) => {
            $(`#${tipo}_${id}`).on('change', async function() {
                const cursoId = $(this).val();
                if (!cursoId) return;

                // Mostrar loading mientras verifica
                Swal.fire({
                    title: 'Verificando cupo',
                    text: `Comprobando disponibilidad en ${nombre}...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(`components/registrationsContact/verificar_cupo_curso.php?course_id=${cursoId}&course_type=${tipo}`);
                    const data = await response.json();

                    Swal.close();

                    if (data.success) {
                        if (!data.tiene_cupo) {
                            await Swal.fire({
                                icon: 'error',
                                title: 'No hay cupos disponibles',
                                text: `El curso seleccionado para ${nombre} no tiene cupos disponibles.`,
                                confirmButtonColor: '#d33'
                            });
                            $(this).val(''); // Limpiar la selección
                            return false;
                        } else {
                            // Mostrar información sobre cupos disponibles
                            await Swal.fire({
                                icon: 'success',
                                title: 'Cupos disponibles',
                                html: `
                                    <div class="alert alert-success">
                                        <p><b>Curso:</b> ${nombre}</p>
                                        <p><b>Cupos ocupados:</b> ${data.total_asignaciones} de ${data.cupo_maximo}</p>
                                        <p><b>Cupos disponibles:</b> ${data.cupos_disponibles}</p>
                                    </div>
                                `,
                                confirmButtonText: 'Continuar',
                                confirmButtonColor: '#2B5BAC'
                            });
                            return true;
                        }
                    } else {
                        throw new Error('Error al verificar cupos');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo verificar la disponibilidad del curso'
                    });
                    $(this).val(''); // Limpiar la selección en caso de error
                    return false;
                }
            });
        });
    }

    function guardarAsignacionCursos(id, formData) {
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Guardando asignación',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar datos al servidor
        fetch('components/registrationsContact/save_course_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Se ha actualizado el estado y asignado los cursos correctamente',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Hubo un problema al guardar la asignación'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema al comunicarse con el servidor'
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
                                <select class="form-control" id="nuevoSede_${id}" name="nuevoNivel">
                                    <option value="">Seleccionar sede</option>
                                    <?php
                                    $sedes = obtenerSedes($conn);
                                    foreach ($sedes as $sede): ?>
                                        <option value="<?php echo htmlspecialchars($sede); ?>">
                                            <?php echo htmlspecialchars($sede); ?>
                                        </option>
                                    <?php endforeach; ?>
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

    function mostrarDetallesParticipante(numeroDocumento, nombreEstudiante) {
        // Crear y mostrar modal
        const modalHtml = `
            <div class="modal fade" id="modalDetallesParticipante" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-indigo-dark">
                            <h5 class="modal-title text-white">
                                <i class="fa-solid fa-user-graduate"></i> Inscripción Anterior
                            </h5>
                            <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="loaderParticipante" class="text-center mb-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            <div id="detallesParticipanteContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Agregar modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalDetallesParticipante'));
        modal.show();

        // Cargar datos
        fetch(`components/registrationsContact/obtener_detalles_participante.php?numero_documento=${numeroDocumento}`)
            .then(response => response.json())
            .then(data => {
                // Ocultar el loader
                document.getElementById('loaderParticipante').style.display = 'none';

                const contenido = `
                    <h2 class="text-center mb-2 text-magenta-dark">${nombreEstudiante.toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')}</h2>
    
                    <p class="text-center mb-4"><h4><b>${data.numero_documento}</b></h4></p>
                <small class="text-danger fw-bold mb-3 d-block text-center">Tenga en cuenta que este campista ya cuenta con una formación en otra región o una certificación.</small>
                
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tbody>
                                <tr class="text-start">
                                    <th style="width: 50%">
                                        <i class="bi bi-geo-alt-fill"></i> Departamento:
                                    </th>  
                                    <td>${data.departamento || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-pin-map-fill"></i> Municipio:
                                    </th>
                                    <td>${data.municipio || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-map"></i> Región:
                                    </th>
                                    <td>${data.region || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-person-lines-fill"></i> Cohorte:
                                    </th>
                                    <td>${data.cohorte || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-book-half"></i> Programa:
                                    </th>
                                    <td>${data.eje_final || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-check-circle-fill"></i> Matriculado:
                                    </th>
                                    <td>${data.matriculado || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-journal-check"></i> Estado formación:
                                    </th>
                                    <td>${data.estado || 'No especificado'}</td>
                                </tr>
                                <tr class="text-start">
                                    <th>
                                        <i class="bi bi-star-fill"></i> Origen:
                                    </th>
                                    <td>${data.origen || 'No especificado'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
    
                    <div class="modal-footer">
                        <div class="text-center w-100">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Información cargada en tiempo real - 
                                <span id="relojModal"></span>
                            </small>
                        </div>
                    </div>
                `;

                document.getElementById('detallesParticipanteContent').innerHTML = contenido;

                // Inicializar el reloj después de cargar el contenido
                let relojInterval;

                function actualizarReloj() {
                    const ahora = new Date();
                    let horas = ahora.getHours();
                    const minutos = ahora.getMinutes().toString().padStart(2, '0');
                    const segundos = ahora.getSeconds().toString().padStart(2, '0');
                    const ampm = horas >= 12 ? 'PM' : 'AM';

                    // Convertir a formato 12 horas
                    horas = horas % 12;
                    horas = horas ? horas : 12; // la hora '0' debe ser '12'
                    const horasFormateadas = horas.toString().padStart(2, '0');

                    const relojElement = document.getElementById('relojModal');
                    if (relojElement) {
                        relojElement.textContent = `${horasFormateadas}:${minutos}:${segundos} ${ampm}`;
                    }
                }

                actualizarReloj(); // Ejecutar inmediatamente
                relojInterval = setInterval(actualizarReloj, 1000); // Actualizar cada segundo

                // Limpiar el intervalo cuando se cierre el modal
                document.getElementById('modalDetallesParticipante').addEventListener('hidden.bs.modal', function() {
                    clearInterval(relojInterval); // Detener el reloj
                    this.remove(); // Eliminar el modal del DOM
                });
            })
            .catch(error => {
                // Ocultar el loader
                document.getElementById('loaderParticipante').style.display = 'none';

                document.getElementById('detallesParticipanteContent').innerHTML = `
                    <div class="alert alert-danger">
                        Error al cargar los datos: ${error.message}
                    </div>
                `;
            });
    }

    function mostrarCertificacionSwal(nombreEstudiante) {
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
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>