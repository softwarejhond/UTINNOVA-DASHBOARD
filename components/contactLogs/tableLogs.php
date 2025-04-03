<?php

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
    WHERE departamentos.id_departamento IN (15, 25)
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
    WHERE departamentos.id_departamento IN (15, 25)
    AND user_register.status = '1' AND user_register.statusAdmin = '' AND user_register.department=15 OR user_register.department=25
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)";
$stmtTotal = $conn->prepare($totalSql);
$stmtTotal->bind_param('ss', $searchParam, $searchParam);
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>

<div class="table-responsive">
    <button id="exportarExcel" class="btn btn-success mb-3"
        onclick="window.location.href='components/contactLogs/allLogs.php?action=export'">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
    </button>
    <div class=" mt-4">
        <div class="row align-items-center">

            <!-- Formulario de búsqueda -->
            <div class="col-md-4 col-sm-12 mb-3 text-center">
                <p class="mb-2">Buscar usuarios</p>
                <form method="GET" action="" class="d-flex">

                    <input type="text" class="form-control me-2 text-center" name="search" placeholder="Buscar por nombre o ID" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-sm bg-indigo-dark text-white w-100"><i class="bi bi-search"></i> Buscar</button>
                </form>
            </div>

            <!-- Indicador y paginación -->
            <div class="col-md-4 col-sm-12">
                <p class="h6 pb-2 mb-2 text-indigo-dark"><b>Navega entre páginas para ver más registros.</b></p>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Botón Anterior -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link btn-sm" href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo htmlspecialchars($search); ?>">
                                &laquo; Anterior
                            </a>
                        </li>

                        <!-- Primera página -->
                        <?php if ($page > 2): ?>
                            <li class="page-item"><a class="page-link btn-sm" href="?page=1&search=<?php echo htmlspecialchars($search); ?>">1</a></li>
                            <?php if ($page > 3): ?>
                                <li class="page-item disabled"><span class="page-link btn-sm">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Páginas visibles -->
                        <?php for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link btn-sm" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Última página -->
                        <?php if ($page < $totalPages - 1): ?>
                            <?php if ($page < $totalPages - 2): ?>
                                <li class="page-item disabled"><span class="page-link btn-sm">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link btn-sm" href="?page=<?php echo $totalPages; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $totalPages; ?></a></li>
                        <?php endif; ?>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link btn-sm" href="?page=<?php echo min($totalPages, $page + 1); ?>&search=<?php echo htmlspecialchars($search); ?>">
                                Siguiente &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>


        </div>
    </div>

    <table id="listaInscritos" class="table table-hover table-bordered">
        <thead class="thead-dark text-center">
            <tr class="text-center">
                <th>Tipo ID</th>
                <th>Número</th>
                <th>Nombre </th>
                <th>Edad</th>
                <th>Correo</th>
                <th>Teléfono 1</th>
                <th>Teléfono 2</th>
                <th>Departamento</th>
                <th>Municipio</th>
                <th>Sede</th>
                <th>Modalidad</th>
                <th>Programa</th>
                <th>Nivel</th>
                <th>Admisión</th>
                <th>Ver registros</th>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>

                    <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['second_name']) . ' ' . htmlspecialchars($row['first_last']) . ' ' . htmlspecialchars($row['second_last']); ?>
                    </td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['first_phone']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['second_phone']); ?></td>

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
                    <td><?php echo htmlspecialchars($row['headquarters']); ?></td>
                    <td><?php echo htmlspecialchars($row['mode']); ?></td>

                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['program']); ?></td>
                    <td style="width: 200px; min-width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['level']); ?></td>

                    <td>
                        <?php
                        if ($row['statusAdmin'] == '1') {
                            echo '<a class="btn bg-teal-dark w-100" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="BENEFICIARIO"><i class="bi bi-check-circle"></i></a>';
                        } elseif ($row['statusAdmin'] == '0') {
                            echo '<a class="btn bg-silver text-white w-100" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="SIN ESTADO"><i class="bi bi-question-circle"></i></a>';
                        } elseif ($row['statusAdmin'] == '2') {
                            echo '<a class="btn bg-danger w-100" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="RECHAZADO"><i class="bi bi-x-circle"></i></a>';
                        } elseif ($row['statusAdmin'] == '3') {
                            echo '<a class="btn bg-success w-100 text-white" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="MATRICULADO"><i class="fa-solid fa-pencil"></i></a>';
                        } elseif ($row['statusAdmin'] == '4') {
                            echo '<a class="btn bg-secondary w-100 text-white" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="PENDIENTE"><i class="bi bi-telephone-x"></a>';
                        } elseif ($row['statusAdmin'] == '5') {
                            echo '<a class="btn bg-warning w-100 text-white" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="EN PROCESO"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden"></span></div></a>';
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <a href="studentContacts.php?id=<?php echo $row['number_id']; ?>" class="btn bg-indigo-dark text-white" data-toggle="tooltip" title="Ver historial de llamadas">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        if (popoverTriggerList.length > 0) {
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>