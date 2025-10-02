<div class="p-3">
    <div class="row">
        <div class="col col-lg-12 col-md-12 col-sm-12 px-2 mt-1 mx-auto">
            <div class="card text-center">
                <div class="card-header bg-indigo-dark text-white">
                    <i class="bi bi-person-badge"></i> BUSCAR ESTUDIANTE <i class="bi bi-person-badge"></i>
                </div>
                <br>
                <!-- Mostrar imagen solo si no hay búsqueda -->
                <form action="" method="GET" class="mx-3">
                    <div class="input-group  mb-3">
                        <input type="number" name="search" required
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                            class="form-control text-center"
                            placeholder="IDENTIFICACIÓN DEL USUARIO" style="font-size: 1.5rem;">
                        <button type="submit" class="btn bg-indigo-dark text-white" title="Buscar estudiante">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['search'])): ?>
            <div class="col col-lg-12 col-md-12 col-sm-12 px-2 mt-1 ">
                <?php
                $filtervalues = $_GET['search'];
                $query = "SELECT ur.*, m.municipio, d.departamento, 
                        TIMESTAMPDIFF(YEAR, ur.birthdate, CURDATE()) as age,
                        CASE 
                            WHEN p.numero_documento IS NOT NULL THEN 1
                            ELSE 0
                        END as tiene_certificado,
                        EXISTS(
                            SELECT 1 
                            FROM course_assignments ca 
                            WHERE ca.student_id = ur.number_id
                        ) AS tiene_preasignacion
                        FROM user_register ur
                        LEFT JOIN municipios m ON ur.municipality = m.id_municipio 
                        LEFT JOIN departamentos d ON ur.department = d.id_departamento
                        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
                        WHERE ur.number_id LIKE ? LIMIT 1";
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

                $stmt = $conn->prepare($query);
                $searchParam = "%$filtervalues%";
                $stmt->bind_param("s", $searchParam);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $number_id = $row['number_id'];
                    $nombre = htmlspecialchars($row['first_name'] . ' ' . $row['second_name'] . ' ' .
                        $row['first_last'] . ' ' . $row['second_last']);

                    // Consulta para obtener historial de contactos
                    $sqlContactLog = "SELECT cl.*, a.name AS advisor_name 
                                    FROM contact_log cl
                                    LEFT JOIN advisors a ON cl.idAdvisor = a.idAdvisor 
                                    WHERE cl.number_id = ?";

                    // Preparar y ejecutar consulta de contact_log
                    $stmtContactLog = $conn->prepare($sqlContactLog);
                    $stmtContactLog->bind_param('s', $number_id);
                    $stmtContactLog->execute();
                    $resultContactLog = $stmtContactLog->get_result();
                    $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

                    // Si hay registros de contact_log, asignar valores
                    if (!empty($contactLogs)) {
                        // Almacenar todo el historial
                        $row['contact_logs'] = [];
                        foreach ($contactLogs as $log) {
                            $row['contact_logs'][] = [
                                'idAdvisor' => $log['idAdvisor'],
                                'advisor_name' => $log['advisor_name'],
                                'details' => $log['details'],
                                'contact_established' => $log['contact_established'],
                                'continues_interested' => $log['continues_interested'],
                                'observation' => $log['observation'],
                                'contact_date' => $log['contact_date']
                            ];
                        }

                        // Asignar último registro como valores actuales
                        $lastLog = end($contactLogs);
                        $row['idAdvisor'] = $lastLog['idAdvisor'];
                        $row['advisor_name'] = $lastLog['advisor_name'];
                        $row['details'] = $lastLog['details'];
                        $row['contact_established'] = $lastLog['contact_established'];
                        $row['continues_interested'] = $lastLog['continues_interested'];
                        $row['observation'] = $lastLog['observation'];
                    } else {
                        // Valores por defecto si no hay registros
                        $row['idAdvisor'] = 'No registrado';
                        $row['advisor_name'] = 'Sin asignar';
                        $row['details'] = 'Sin detalles';
                        $row['contact_established'] = 0;
                        $row['continues_interested'] = 0;
                        $row['observation'] = 'Sin observaciones';
                        $row['contact_logs'] = [];
                    }

                    function obtenerHorarios($conn, $mode, $headquarters = null)
                    {
                        $sql = "SELECT DISTINCT schedule 
                        FROM schedules 
                        WHERE mode = ?";
                        $params = [$mode];
                        $types = "s";

                        if ($headquarters !== null) {
                            $sql .= " AND headquarters = ?";
                            $params[] = $headquarters;
                            $types .= "s";
                        }

                        $sql .= " ORDER BY schedule ASC";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $horarios = array();
                        while ($row = $result->fetch_assoc()) {
                            $horarios[] = $row['schedule'];
                        }

                        return $horarios;
                    }

                    function obtenerSedes($conn, $mode)
                    {
                        $sql = "SELECT DISTINCT name 
                                FROM headquarters 
                                WHERE mode = ?
                                ORDER BY name ASC";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $mode);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $sedes = array();
                        while ($row = $result->fetch_assoc()) {
                            $sedes[] = $row['name'];
                        }

                        return $sedes;
                    }

                    // Después de obtener los datos del usuario y antes del cálculo de $isAccepted
                    $verificacion = [
                        'name_verified' => 0,
                        'document_number_verified' => 0,
                        'birth_date_verified' => 0,
                        'document_type_verified' => 0,
                        'notes' => ''
                    ];
                    $stmt = $conn->prepare("SELECT name_verified, document_number_verified, birth_date_verified, document_type_verified, notes FROM document_verifications WHERE number_id = ? ORDER BY verification_date DESC LIMIT 1");
                    $stmt->bind_param("s", $row['number_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $verificacion = $result->fetch_assoc();
                    }
                    $stmt->close();

                    $isAccepted = true;
                    $razonIncumplimiento = '';

                    // Validación de nombre: vocales con tilde o último carácter espacio
                    $nombreCompleto = $row['first_name'];
                    if (!empty($row['second_name'])) {
                        $nombreCompleto .= ' ' . $row['second_name'];
                    }
                    $nombreCompleto .= ' ' . $row['first_last'];
                    if (!empty($row['second_last'])) {
                        $nombreCompleto .= ' ' . $row['second_last'];
                    }
                    $tieneTilde = preg_match('/[áéíóúÁÉÍÓÚ]/u', $nombreCompleto);
                    $ultimoCaracterEspacio = substr(trim($nombreCompleto), -1) === ' ' || substr($nombreCompleto, -1) === ' ';

                    if ($tieneTilde) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'Nombre contiene tildes. ';
                    }
                    if ($ultimoCaracterEspacio) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'Nombre termina en espacio. ';
                    }

                    // Validar disponibilidad de compromiso
                    if ($row['availability'] !== 'Sí') {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'No acepta compromiso. ';
                    }

                    // NUEVO: Validar si tiene prueba realizada
                    if (!isset($nivelesUsuarios[$row['number_id']])) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'No tiene prueba realizada. ';
                    }

                    // NUEVO: Validar lote asignado
                    if (empty($row['lote']) || intval($row['lote']) === 0) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'No tiene lote asignado válido. ';
                    }

                    // NUEVO: Validar verificación de documento
                    if ($verificacion['name_verified'] != 1) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'Nombre no verificado en documento. ';
                    }
                    if ($verificacion['document_number_verified'] != 1) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'Número de documento no verificado. ';
                    }
                    if ($verificacion['birth_date_verified'] != 1) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'Fecha de nacimiento no verificada en documento. ';
                    }
                    if ($verificacion['document_type_verified'] != 1) {
                        $isAccepted = false;
                        $razonIncumplimiento .= 'Tipo de documento no verificado. ';
                    }

                    // Continuar con las validaciones existentes (modalidad, edad, etc.)
                    if ($isAccepted) {
                        if ($row['mode'] === 'Presencial') {
                            if (
                                $row['typeID'] === 'CC' && $row['age'] > 17 &&
                                in_array(strtoupper($row['department']), ['11'])
                            ) {
                                $isAccepted = true;
                            } else {
                                $isAccepted = false;
                                if ($row['typeID'] !== 'CC') $razonIncumplimiento .= 'Tipo de documento no es CC';
                                if ($row['age'] <= 17) $razonIncumplimiento .= 'Edad menor o igual a 17. ';
                                if (!in_array(strtoupper($row['department']), ['11'])) $razonIncumplimiento .= 'Departamento diferente de BOGOTÁ.';
                            }
                        } elseif ($row['mode'] === 'Virtual') {
                            if (
                                $row['typeID'] === 'CC' && $row['age'] > 17 &&
                                (in_array(strtoupper($row['department']), ['11'])) &&
                                $row['internet'] === 'Sí'
                            ) {
                                $isAccepted = true;
                            } else {
                                $isAccepted = false;
                                if ($row['typeID'] !== 'CC') $razonIncumplimiento .= 'Tipo de documento no es CC. ';
                                if ($row['age'] <= 17) $razonIncumplimiento .= 'Edad menor o igual a 17. ';
                                if (!in_array(strtoupper($row['department']), ['11'])) $razonIncumplimiento .= 'Departamento diferente de BOGOTÁ.';
                                if ($row['internet'] !== 'Sí') $razonIncumplimiento .= 'No tiene internet. ';
                            }
                        }
                    }

                    function badgeCumplimiento($isAccepted, $razonIncumplimiento = '')
                    {
                        if ($isAccepted) {
                            return '<span class="badge bg-teal-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Cumple con todos los requisitos"><i class="bi bi-check-circle"></i> CUMPLE</span>';
                        } else {
                            $tooltip = 'No cumple con los requisitos mínimos';
                            if ($razonIncumplimiento) $tooltip .= ': ' . trim($razonIncumplimiento);
                            return '<span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' . htmlspecialchars($tooltip) . '"><i class="bi bi-x-circle"></i> NO CUMPLE</span>';
                        }
                    }
                ?>
            </div>

            <form method="POST">
                <div class="container-fluid py-2">
                    <div class="row g-2">
                        <!-- Información Personal -->
                        <div class="col-12 col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-person card-icon me-2"></i> Información Personal
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarNombre(<?= $row['number_id'] ?>)">Actualizar Nombre</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarNacimiento(<?= $row['number_id'] ?>)">Actualizar Fecha de Nacimiento</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarDocumento(<?= $row['number_id'] ?>)">Actualizar Documento</a></li>

                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Corrección aquí -->
                                    <div class="d-flex flex-column gap-2 w-100">
                                        <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2">
                                            <span class="text-muted">Nombre:</span>
                                            <span class="fw-bold text-end"><?= htmlspecialchars($nombre) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Tipo de documento:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['typeID']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">ID:</span>
                                            <span class="text-end"><?= htmlspecialchars($number_id) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Ver documento:</span>
                                            <span class="text-end"><?php include 'showPictureId.php'; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Documento verificado:</span>
                                            <span class="text-end">
                                                <?php
                                                $todosVerificados = $verificacion['name_verified'] &&
                                                    $verificacion['document_number_verified'] &&
                                                    $verificacion['birth_date_verified'] &&
                                                    $verificacion['document_type_verified'];

                                                if ($todosVerificados): ?>
                                                    <span class="badge bg-success"><i class="bi bi-shield-check"></i> Verificado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><i class="bi bi-shield-exclamation"></i> Pendiente</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Edad:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['age']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Fecha de nacimiento:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['birthdate'] ? date('d/m/Y', strtotime($row['birthdate'])) : 'No especificada') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Contacto -->
                        <div class="col-12 col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi-telephone card-icon me-2" style="color: #198754;"></i>
                                        Información de Contacto
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)">Actualizar Medio de Contacto</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarContactoCompleto(<?php echo $row['number_id']; ?>)">Actualizar Información Completa</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column gap-2 w-100">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span class="text-muted">Telfefono 1:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['first_phone']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Telefono 2:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['second_phone']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Email:</span>
                                            <span class="text-primary text-end"><?= htmlspecialchars($row['email']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Medio de contacto:</span>
                                            <span class="text-primary text-end"><?php include 'contactMedium.php'; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 border-top pt-3">
                                            <span class="text-muted">Contacto de emergencia:</span>
                                            <span class="fw-medium text-end"><?= htmlspecialchars($row['emergency_contact_name']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Numero de contacto:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['emergency_contact_number']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="col-12 col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi-geo-alt card-icon me-2" style="color: #dc3545;"></i>
                                        Registro y Ubicación
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarUbicacion(<?php echo $row['number_id']; ?>)">Actualizar Ubicación</a></li>

                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column gap-2 w-100">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span class="text-muted">Nacionalidad:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['nationality']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Departamento:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['departamento']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Municipio:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['municipio']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Dirección:</span>
                                            <span class="text-end"><?= htmlspecialchars($row['address']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Académica -->
                        <div class="col-12 col-lg-8">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi-mortarboard card-icon me-2" style="color: #6f42c1;"></i>
                                        Información académica registrada
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarPrograma(<?php echo $row['number_id']; ?>)">Cambiar Programa, Nivel, Sede y Lote</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarHorario(<?php echo $row['number_id']; ?>)">Actualizar Horarios</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="modalActualizarModalidad(<?php echo $row['number_id']; ?>)">Cambiar Modalidad</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarModalActualizarFechaInscripcion(<?= $row['number_id'] ?>, '<?= !empty($row['creationDate']) ? date('Y-m-d', strtotime($row['creationDate'])) : '' ?>')">Actualizar Fecha de Inscripción</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row w-100">
                                        <!-- Primera columna -->
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column gap-2 w-100">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="text-muted">Ocupación:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['occupation']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Tiempo para obligaciones:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['time_obligations']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Sede:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['headquarters']) ?></span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Lote:</span>
                                                    <span class="text-end">
                                                        <?php
                                                        if (empty($row['lote']) || intval($row['lote']) === 0) {
                                                            // Mostrar alerta con SweetAlert si el lote es 0 o vacío
                                                            echo '<span class="badge bg-danger" id="loteAlerta_' . $row['number_id'] . '" style="cursor:pointer;">No asignado</span>';
                                                            echo "
                                                            <script>
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    var loteBadge = document.getElementById('loteAlerta_" . $row['number_id'] . "');
                                                                    if (loteBadge) {
                                                                        loteBadge.addEventListener('click', function() {
                                                                            Swal.fire({
                                                                                icon: 'warning',
                                                                                title: 'Lote no asignado',
                                                                                html: '<div class=\"alert alert-warning\">Este estudiante <b>NO tiene lote asignado válido</b>.<br>Debe asignar un lote válido para continuar con el proceso.</div>',
                                                                                confirmButtonText: 'Entendido',
                                                                                confirmButtonColor: '#fd7e14'
                                                                            });
                                                                        });
                                                                    }
                                                                });
                                                            </script>
                                                            ";
                                                        } else {
                                                            echo htmlspecialchars($row['lote']);
                                                        }
                                                        ?>
                                                    </span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="text-muted">Programa:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['program']) ?></span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="text-muted">Fecha de inscripción:</span>
                                                    <span class="text-end">
                                                        <?= !empty($row['creationDate']) ? date('d/m/Y', strtotime($row['creationDate'])) : 'No especificada' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Segunda columna -->
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column gap-2 w-100">

                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Nivel de preferencia:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['level']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Modalidad:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['mode']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Horarios (Principal y alternativo):</span>
                                                    <span class="text-end">
                                                        <button type="button" class="btn btn-sm badge bg-cyan-dark text-white"
                                                            data-bs-toggle="popover"
                                                            data-bs-trigger="click"
                                                            data-bs-placement="top"
                                                            data-bs-html="true"
                                                            data-bs-content="
                                                                <strong>Horario Principal:</strong><br>
                                                                <?= htmlspecialchars($row['schedules']) ?><br><br>
                                                                <strong>Horario Alternativo:</strong><br>
                                                                <?= htmlspecialchars($row['schedules_alternative'] ?: 'No especificado') ?>
                                                            "
                                                            title="Horarios seleccionados">
                                                            <i class="bi bi-clock"></i> Ver horarios
                                                        </button>
                                                    </span>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Registró otra certificación:</span>
                                                    <span class="text-end">
                                                        <?php
                                                        // Consulta para obtener datos de certificación previa
                                                        $certQuery = "SELECT has_certification, program_certified, anio_certificacion FROM certification_previous WHERE number_id = ?";
                                                        $stmtCert = $conn->prepare($certQuery);
                                                        $stmtCert->bind_param("s", $row['number_id']);
                                                        $stmtCert->execute();
                                                        $resultCert = $stmtCert->get_result();
                                                        $certData = $resultCert->fetch_assoc();

                                                        // Badge para has_certification
                                                        if ($certData && $certData['has_certification'] == 'SI') {
                                                            echo '<span class="badge bg-teal-dark me-2">Sí</span>';
                                                        } else {
                                                            echo '<span class="badge bg-secondary me-2">No</span>';
                                                        }

                                                        // Badge para program_certified
                                                        if ($certData && !empty($certData['program_certified'])) {
                                                            echo '<span class="badge bg-orange-dark text-black">' . htmlspecialchars($certData['program_certified']) . '</span>';
                                                        } else {
                                                            echo '<span class="badge bg-secondary">Sin programa</span>';
                                                        }

                                                        // Badge para anio_certificacion
                                                        if ($certData && !empty($certData['anio_certificacion'])) {
                                                            echo '<span class="badge bg-teal-dark text-white">' . htmlspecialchars($certData['anio_certificacion']) . '</span>';
                                                        } else {
                                                            echo '<span class="badge bg-secondary">Sin especificar</span>';
                                                        }
                                                        ?>


                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Seguimiento -->
                        <div class="col-12 col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi-check-circle card-icon me-2" style="color: #fd7e14;"></i>
                                        Estado y Nivel de prueba
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#" onclick="mostrarModalActualizarAdmision(<?= $row['number_id'] ?>, <?= $isAccepted ? 'true' : 'false' ?>)">Cambiar Estado Admisión</a>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column gap-2 w-100">

                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Estado:</span>
                                            <span class="text-end">
                                                <?= badgeCumplimiento($isAccepted, $razonIncumplimiento) ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span class="text-muted">Estado de admisión:</span>
                                            <span class="text-end">
                                                <?php
                                                if ($row['statusAdmin'] == '1') {
                                                    // Verificar si tiene preasignación
                                                    if ($row['tiene_preasignacion']) {
                                                        echo '<span class="badge bg-lime-dark text-dark"><i class="bi bi-check-circle-fill"></i> BENEFICIARIO CON CURSOS ASIGNADOS</span>';
                                                    } else {
                                                        echo '<span class="badge bg-teal-dark"><i class="bi bi-check-circle"></i> BENEFICIARIO</span>';
                                                    }
                                                } elseif ($row['statusAdmin'] == '0') {
                                                    echo '<span class="badge bg-indigo-dark text-white"><i class="bi bi-question-circle"></i> SIN ESTADO</span>';
                                                } elseif ($row['statusAdmin'] == '2') {
                                                    echo '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> RECHAZADO</span>';
                                                } elseif ($row['statusAdmin'] == '3') {
                                                    echo '<span class="badge bg-success text-white"><i class="fa-solid fa-pencil"></i> MATRICULADO</span>';
                                                } elseif ($row['statusAdmin'] == '4') {
                                                    echo '<span class="badge bg-secondary text-white"><i class="bi bi-telephone-x"></i> SIN CONTACTO</span>';
                                                } elseif ($row['statusAdmin'] == '5') {
                                                    echo '<span class="badge bg-warning text-white"><i class="spinner-border spinner-border-sm" role="status"></i> EN PROCESO</span>';
                                                } elseif ($row['statusAdmin'] == '6') {
                                                    echo '<span class="badge bg-orange-dark text-white"><i class="bi bi-patch-check-fill"></i> CERTIFICADO</span>';
                                                } elseif ($row['statusAdmin'] == '7') {
                                                    echo '<span class="badge bg-silver text-white"><i class="bi bi-person-x"></i> INACTIVO</span>';
                                                } elseif ($row['statusAdmin'] == '8') {
                                                    // Verificar si tiene preasignación para contrapartida también
                                                    if ($row['tiene_preasignacion']) {
                                                        echo '<span class="badge bg-lime-dark text-dark"><i class="bi bi-check-circle-fill"></i> BENEFICIARIO CONTRAPARTIDA CON CURSOS ASIGNADOS</span>';
                                                    } else {
                                                        echo '<span class="badge bg-amber-dark text-dark"><i class="bi bi-check-circle-fill"></i> BENEFICIARIO CONTRAPARTIDA</span>';
                                                    }
                                                } elseif ($row['statusAdmin'] == '9') {
                                                    echo '<span class="badge bg-magenta-dark text-white"><i class="bi bi-hourglass-split"></i> APLAZADO</span>';
                                                } elseif ($row['statusAdmin'] == '10') {
                                                    echo '<span class="badge bg-cyan-dark text-white"><i class="bi bi-mortarboard-fill"></i> FORMADO</span>';
                                                } elseif ($row['statusAdmin'] == '11') {
                                                    echo '<span class="badge bg-red-dark text-white"><i class="bi bi-exclamation-triangle"></i> NO VÁLIDO</span>';
                                                } elseif ($row['statusAdmin'] == '12') {
                                                    echo '<span class="badge bg-danger text-dark"><i class="bi bi-x-circle"></i> NO APROBADO</span>';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Certificación anterior:</span>
                                            <span class="text-end">
                                                <?php if ($row['tiene_certificado']): ?>
                                                    <span class="badge bg-success"
                                                        onclick="mostrarCertificacionAlert('<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['first_last']); ?>')"
                                                        style="cursor: pointer;"
                                                        data-bs-toggle="popover"
                                                        data-bs-trigger="hover"
                                                        data-bs-placement="top"
                                                        data-bs-content="El estudiante cuenta con una certificación">
                                                        Sí
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"
                                                        data-bs-toggle="popover"
                                                        data-bs-trigger="hover"
                                                        data-bs-placement="top"
                                                        data-bs-content="Sin certificación previa">
                                                        No
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Puntaje de prueba:</span>
                                            <span class="text-end">
                                                <?php
                                                if (isset($nivelesUsuarios[$row['number_id']])) {
                                                    $puntaje = $nivelesUsuarios[$row['number_id']];
                                                    if ($puntaje >= 0 && $puntaje <= 5) {
                                                        echo '<span class="badge bg-magenta-dark">' . htmlspecialchars($puntaje) . '</span>';
                                                    } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                                        echo '<span class="badge bg-orange-dark">' . htmlspecialchars($puntaje) . '</span>';
                                                    } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                                        echo '<span class="badge bg-teal-dark">' . htmlspecialchars($puntaje) . '</span>';
                                                    }
                                                } else {
                                                    echo '<span class="badge bg-silver" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="No ha presentado la prueba"><i class="bi bi-ban"></i></span>';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Nivel de prueba:</span>
                                            <span class="text-end">
                                                <?php
                                                if (isset($nivelesUsuarios[$row['number_id']])) {
                                                    $puntaje = $nivelesUsuarios[$row['number_id']];
                                                    if ($puntaje >= 0 && $puntaje <= 5) {
                                                        echo '<span class="badge bg-magenta-dark">Básico</span>';
                                                    } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                                        echo '<span class="badge bg-orange-dark">Intermedio</span>';
                                                    } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                                        echo '<span class="badge bg-teal-dark">Avanzado</span>';
                                                    }
                                                } else {
                                                    echo '<span class="badge bg-silver" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="No ha presentado la prueba"><i class="bi bi-ban"></i></span>';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tecnología y requisitos-->
                        <div class="col-12 col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi-laptop card-icon me-2" style="color: #0dcaf0;"></i>
                                        Tecnología y requisitos
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#" onclick="mostrarModalActualizarAvailability('<?= $row['number_id'] ?>', '<?= $row['availability'] ?>', '<?= $row['internet'] ?>')">
                                                Actualizar disponibilidad de compromiso e internet
                                            </a>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column gap-2 w-100">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span class="text-muted">Dispositivo:</span>
                                            <span class="text-end">
                                                <?php
                                                // Asigna la clase, ícono y texto del badge según el valor de 'technologies'
                                                $btnClass = '';
                                                $btnText = htmlspecialchars($row['technologies']); // El texto que aparecerá en el badge
                                                $icon = ''; // Ícono correspondiente

                                                if ($row['technologies'] === 'computador') {
                                                    $btnClass = 'bg-indigo-dark text-white'; // Clase para computador
                                                    $icon = '<i class="bi bi-laptop"></i>'; // Ícono de computador
                                                } elseif ($row['technologies'] === 'smartphone') {
                                                    $btnClass = 'bg-teal-dark text-white'; // Clase para smartphone
                                                    $icon = '<i class="bi bi-phone"></i>'; // Ícono de smartphone
                                                } elseif ($row['technologies'] === 'tablet') {
                                                    $btnClass = 'bg-amber-light text-black'; // Clase para tablet
                                                    $icon = '<i class="bi bi-tablet"></i>'; // Ícono de tablet
                                                } else {
                                                    $btnClass = 'badge-secondary'; // Clase genérica si no coincide
                                                    $icon = '<i class="bi bi-question-circle"></i>'; // Ícono genérico
                                                }

                                                // Mostrar el badge con la clase, ícono y texto correspondientes
                                                echo '<span class="badge ' . $btnClass . '">' . $icon . ' ' . $btnText . '</span>';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Internet:</span>
                                            <span class="text-end">
                                                <?php
                                                $btnClass = '';
                                                $btnText = htmlspecialchars($row['internet']); // El texto que aparecerá en el badge
                                                $icon = ''; // Ícono correspondiente

                                                // Mostrar el estado internet
                                                if ($row['internet'] === 'Sí') {
                                                    $btnClass = 'bg-indigo-dark text-white'; // Clase para internet
                                                    $icon = '<i class="bi bi-router-fill"></i>'; // Ícono de internet
                                                } elseif ($row['internet'] === 'No') {
                                                    $btnClass = 'bg-red-dark text-white'; // Clase para wifi off
                                                    $icon = '<i class="bi bi-wifi-off"></i>'; // Ícono de wifi off
                                                }
                                                // Mostrar el badge con la clase, ícono y texto correspondientes
                                                echo '<span class="badge ' . $btnClass . '">' . $icon . ' ' . $btnText . '</span>';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Acepta compromiso:</span>
                                            <span class="text-end">
                                                <?php if ($row['availability'] === 'Sí'): ?>
                                                    <span class="badge bg-success">Sí</span>
                                                <?php elseif ($row['availability'] === 'No'): ?>
                                                    <span class="badge bg-danger">No</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin respuesta</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Acepta requerimientos:</span>
                                            <span class="text-end">
                                                <?php if ($row['accept_requirements'] === 'Sí'): ?>
                                                    <span class="badge bg-success">Sí</span>
                                                <?php elseif ($row['accept_requirements'] === 'No'): ?>
                                                    <span class="badge bg-danger">No</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin respuesta</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Acepta Talento Tech:</span>
                                            <span class="text-end">
                                                <?php if ($row['accepts_tech_talent'] === 'Sí'): ?>
                                                    <span class="badge bg-success">Sí</span>
                                                <?php elseif ($row['accepts_tech_talent'] === 'No'): ?>
                                                    <span class="badge bg-danger">No</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin respuesta</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                            <span class="text-muted">Acepta política de datos:</span>
                                            <span class="text-end">
                                                <?php if ($row['accept_data_policies'] === 'Sí'): ?>
                                                    <span class="badge bg-success">Sí</span>
                                                <?php elseif ($row['accept_data_policies'] === 'No'): ?>
                                                    <span class="badge bg-danger">No</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin respuesta</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registro de contacto -->
                        <div class="col-12 col-lg-8">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi-chat-dots card-icon me-2" style="color: #6610f2;"></i>
                                        Último contacto
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="border: none; background: none;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>">Registrar nuevo contacto</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row w-100">
                                        <!-- Primera columna -->
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column gap-2 w-100">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="text-muted">Asesor Asignado:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['advisor_name']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">ID Asesor:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['idAdvisor']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Contacto Establecido:</span>
                                                    <span class="text-end">
                                                        <?php if ($row['contact_established'] == 1): ?>
                                                            <span class="badge bg-success">Sí</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">No</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Continúa Interesado:</span>
                                                    <span class="text-end">
                                                        <?php if ($row['continues_interested'] == 1): ?>
                                                            <span class="badge bg-success">Sí</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">No</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Segunda columna -->
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column gap-2 w-100">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="text-muted">Detalles de Contacto:</span>
                                                    <span class="text-end"><?= htmlspecialchars($row['details']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Total de contactos:</span>
                                                    <span class="text-end">
                                                        <?php if (!empty($row['contact_logs'])): ?>
                                                            <?= count($row['contact_logs']) ?>
                                                        <?php else: ?>
                                                            0
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                                    <span class="text-muted">Observaciones:</span>
                                                    <span class="text-end">
                                                        <?php if (!empty($row['observation']) && $row['observation'] !== 'Sin observaciones'): ?>
                                                            <button type="button" class="btn btn-sm badge bg-indigo-dark text-white"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalObservaciones_<?php echo $row['number_id']; ?>"
                                                                style="cursor: pointer;">
                                                                <i class="bi bi-eye"></i> Ver observaciones
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Sin observaciones</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info de bootcamp matriculado -->
                        <div class="col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-mortarboard card-icon me-2" style="color: #efb810;"></i>
                                        Información de matrícula
                                    </h5>
                                </div>
                                <div class="card-body w-100">
                                    <?php
                                    // Consultar información de matrícula
                                    $matriculaQuery = "SELECT g.*,
                                                cp.period_name,
                                                cp.cohort AS periodo_cohort,
                                                cp.start_date,
                                                cp.end_date,
                                                DATE_FORMAT(g.creation_date, '%d/%m/%Y') as matricula_fecha
                                        FROM groups g
                                        LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code
                                        WHERE g.number_id = ?";

                                    $stmtMatricula = $conn->prepare($matriculaQuery);
                                    $stmtMatricula->bind_param("s", $number_id);
                                    $stmtMatricula->execute();
                                    $matriculaResult = $stmtMatricula->get_result();

                                    if ($matriculaResult && $matriculaResult->num_rows > 0):
                                        $matricula = $matriculaResult->fetch_assoc();
                                    ?>
                                        <div class="row w-100">
                                            <!-- Columna 1: Información general y fechas -->
                                            <div class="col-md-6">
                                                <div class="d-flex flex-column gap-2 w-100">
                                                    <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2">
                                                        <span class="text-muted">Fecha de matrícula:</span>
                                                        <span class="fw-medium text-end"><?= htmlspecialchars($matricula['matricula_fecha']) ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Modalidad:</span>
                                                        <span class="badge bg-indigo-dark"><?= htmlspecialchars($matricula['mode']) ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Sede:</span>
                                                        <span class="text-end"><?= htmlspecialchars($matricula['headquarters']) ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Cohorte:</span>
                                                        <span class="badge bg-teal-dark"><?= htmlspecialchars($matricula['cohort']) ?></span>
                                                    </div>

                                                    <!-- Programa -->
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2 border-top pt-3">
                                                        <span class="text-muted">Programa:</span>
                                                        <span class="fw-medium text-end"><?= htmlspecialchars($matricula['program']) ?></span>
                                                    </div>

                                                </div>
                                            </div>

                                            <!-- Columna 2: Información de cursos -->
                                            <div class="col-md-6">
                                                <div class="d-flex flex-column gap-2 w-100">

                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Periodo:</span>
                                                        <span class="text-end"><?= htmlspecialchars($matricula['period_name'] ?? 'No especificado') ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Fecha de inicio:</span>
                                                        <span class="text-end">
                                                            <?= !empty($matricula['start_date']) ? htmlspecialchars(date('d/m/Y', strtotime($matricula['start_date']))) : 'No definida' ?>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Fecha de finalización:</span>
                                                        <span class="text-end">
                                                            <?= !empty($matricula['end_date']) ? htmlspecialchars(date('d/m/Y', strtotime($matricula['end_date']))) : 'No definida' ?>
                                                        </span>
                                                    </div>

                                                    <!-- Correo institucional -->
                                                    <div class="d-flex justify-content-between align-items-center w-100 mt-2">
                                                        <span class="text-muted">Email institucional:</span>
                                                        <span class="text-primary text-end">
                                                            <?= !empty($matricula['institutional_email']) ? htmlspecialchars($matricula['institutional_email']) : 'No asignado' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            No se encontró información de matrícula para este estudiante.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Info de cursos asignados -->
                        <div class="col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-journal-bookmark card-icon me-2" style="color: #20c997;"></i>
                                        Cursos asignados y equipo docente
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Consultar información detallada de cursos con el equipo docente
                                    $cursosQuery = "SELECT 
                                                g.*,
                                                -- Bootcamp
                                                c1.name as bootcamp_course_name,
                                                c1.real_hours as bootcamp_real_hours,
                                                u1.nombre as bootcamp_teacher_name,
                                                u2.nombre as bootcamp_mentor_name,
                                                u3.nombre as bootcamp_monitor_name,
                                                -- Leveling English
                                                c2.name as leveling_course_name,
                                                c2.real_hours as leveling_real_hours,
                                                u4.nombre as leveling_teacher_name,
                                                u5.nombre as leveling_mentor_name,
                                                u6.nombre as leveling_monitor_name,
                                                -- English Code
                                                c3.name as english_code_course_name,
                                                c3.real_hours as english_code_real_hours,
                                                u7.nombre as english_code_teacher_name,
                                                u8.nombre as english_code_mentor_name,
                                                u9.nombre as english_code_monitor_name,
                                                -- Skills
                                                c4.name as skills_course_name,
                                                c4.real_hours as skills_real_hours,
                                                u10.nombre as skills_teacher_name,
                                                u11.nombre as skills_mentor_name,
                                                u12.nombre as skills_monitor_name
                                            FROM groups g
                                            -- Bootcamp joins
                                            LEFT JOIN courses c1 ON g.id_bootcamp = c1.code
                                            LEFT JOIN users u1 ON c1.teacher = u1.username
                                            LEFT JOIN users u2 ON c1.mentor = u2.username
                                            LEFT JOIN users u3 ON c1.monitor = u3.username
                                            -- Leveling English joins
                                            LEFT JOIN courses c2 ON g.id_leveling_english = c2.code
                                            LEFT JOIN users u4 ON c2.teacher = u4.username
                                            LEFT JOIN users u5 ON c2.mentor = u5.username
                                            LEFT JOIN users u6 ON c2.monitor = u6.username
                                            -- English Code joins
                                            LEFT JOIN courses c3 ON g.id_english_code = c3.code
                                            LEFT JOIN users u7 ON c3.teacher = u7.username
                                            LEFT JOIN users u8 ON c3.mentor = u8.username
                                            LEFT JOIN users u9 ON c3.monitor = u9.username
                                            -- Skills joins
                                            LEFT JOIN courses c4 ON g.id_skills = c4.code
                                            LEFT JOIN users u10 ON c4.teacher = u10.username
                                            LEFT JOIN users u11 ON c4.mentor = u11.username
                                            LEFT JOIN users u12 ON c4.monitor = u12.username
                                            WHERE g.number_id = ?";

                                    $stmtCursos = $conn->prepare($cursosQuery);
                                    $stmtCursos->bind_param("s", $number_id);
                                    $stmtCursos->execute();
                                    $resultCursos = $stmtCursos->get_result();

                                    if ($resultCursos && $resultCursos->num_rows > 0):
                                        $cursosData = $resultCursos->fetch_assoc();
                                    ?>
                                        <div class="row">
                                            <!-- Bootcamp -->
                                            <?php if (!empty($cursosData['id_bootcamp'])): ?>
                                                <div class="col-lg-6 col-md-12 mb-4">
                                                    <div class="card border-indigo-dark h-100">
                                                        <div class="card-header bg-indigo-dark text-white">
                                                            <h6 class="card-title mb-0 d-flex align-items-center">
                                                                <i class="bi bi-laptop me-2"></i>
                                                                <?= htmlspecialchars($cursosData['bootcamp_course_name'] ?? $cursosData['bootcamp_name'] ?? 'Sin asignar') ?>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-2">
                                                                <small class="text-muted">Código:</small>
                                                                <span class="badge bg-indigo-dark ms-1"><?= htmlspecialchars($cursosData['id_bootcamp']) ?></span>
                                                            </div>

                                                            <div class="team-info">
                                                                <div class="row text-center">
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-gear text-indigo-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Profesor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['bootcamp_teacher_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-check text-lime-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Mentor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['bootcamp_mentor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-up text-cyan-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Monitor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['bootcamp_monitor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Inglés Nivelatorio -->
                                            <?php if (!empty($cursosData['id_leveling_english'])): ?>
                                                <div class="col-lg-6 col-md-12 mb-4">
                                                    <div class="card border-lime-dark h-100">
                                                        <div class="card-header bg-lime-dark text-white">
                                                            <h6 class="card-title mb-0 d-flex align-items-center">
                                                                <i class="bi bi-translate me-2"></i>
                                                                <?= htmlspecialchars($cursosData['leveling_course_name'] ?? $cursosData['leveling_english_name'] ?? 'Sin asignar') ?>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-2">
                                                                <small class="text-muted">Código:</small>
                                                                <span class="badge bg-lime-dark ms-1"><?= htmlspecialchars($cursosData['id_leveling_english']) ?></span>
                                                            </div>

                                                            <div class="team-info">
                                                                <div class="row text-center">
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-gear text-indigo-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Profesor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['leveling_teacher_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-check text-lime-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Mentor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['leveling_mentor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-up text-cyan-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Monitor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['leveling_monitor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- English Code -->
                                            <?php if (!empty($cursosData['id_english_code'])): ?>
                                                <div class="col-lg-6 col-md-12 mb-4">
                                                    <div class="card border-orange-dark h-100">
                                                        <div class="card-header bg-orange-dark text-dark">
                                                            <h6 class="card-title mb-0 d-flex align-items-center">
                                                                <i class="bi bi-code-slash me-2"></i>
                                                                <?= htmlspecialchars($cursosData['english_code_course_name'] ?? $cursosData['english_code_name'] ?? 'Sin asignar') ?>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-2">
                                                                <small class="text-muted">Código:</small>
                                                                <span class="badge bg-orange-dark text-dark ms-1"><?= htmlspecialchars($cursosData['id_english_code']) ?></span>
                                                            </div>

                                                            <div class="team-info">
                                                                <div class="row text-center">
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-gear text-indigo-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Profesor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['english_code_teacher_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-check text-lime-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Mentor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['english_code_mentor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-up text-cyan-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Monitor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['english_code_monitor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Habilidades -->
                                            <?php if (!empty($cursosData['id_skills'])): ?>
                                                <div class="col-lg-6 col-md-12 mb-4">
                                                    <div class="card border-purple-dark h-100">
                                                        <div class="card-header bg-purple-dark text-white">
                                                            <h6 class="card-title mb-0 d-flex align-items-center">
                                                                <i class="bi bi-lightbulb me-2"></i>
                                                                <?= htmlspecialchars($cursosData['skills_course_name'] ?? $cursosData['skills_name'] ?? 'Sin asignar') ?>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-2">
                                                                <small class="text-muted">Código:</small>
                                                                <span class="badge bg-purple-dark ms-1"><?= htmlspecialchars($cursosData['id_skills']) ?></span>
                                                            </div>

                                                            <div class="team-info">
                                                                <div class="row text-center">
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-gear text-indigo-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Profesor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['skills_teacher_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-check text-lime-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Mentor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['skills_mentor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="team-member">
                                                                            <i class="bi bi-person-fill-up text-cyan-dark fs-4"></i>
                                                                            <p class="mb-1"><small class="text-muted">Monitor</small></p>
                                                                            <p class="fw-medium small mb-0"><?= htmlspecialchars($cursosData['skills_monitor_name'] ?? 'Sin asignar') ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php
                                        // Verificar si no hay cursos asignados
                                        $hayCursos = !empty($cursosData['id_bootcamp']) || !empty($cursosData['id_leveling_english']) ||
                                            !empty($cursosData['id_english_code']) || !empty($cursosData['id_skills']);

                                        if (!$hayCursos): ?>
                                            <div class="alert alert-info text-center">
                                                <i class="bi bi-info-circle me-2"></i>
                                                No hay cursos asignados para este estudiante.
                                            </div>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <div class="alert alert-info text-center">
                                            <i class="bi bi-info-circle me-2"></i>
                                            No se encontró información de cursos para este estudiante.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta de Cumplimiento de Asistencias -->
                        <div class="col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between pb-3">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        <i class="bi bi-clock-history card-icon me-2" style="color: #6f42c1;"></i>
                                        Cumplimiento de asistencias y notas de curso tecnico
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Obtener datos de asistencia desde las tablas
                                    $queryAsistencia = "SELECT 
                                        g.id_bootcamp AS bootcamp_code, g.bootcamp_name,
                                        g.id_leveling_english AS leveling_code, g.leveling_english_name,
                                        g.id_english_code AS english_code, g.english_code_name,
                                        g.id_skills AS skills_code, g.skills_name,
                                        b.real_hours AS bootcamp_hours, 
                                        l.real_hours AS leveling_hours, 
                                        e.real_hours AS english_hours, 
                                        s.real_hours AS skills_hours
                                        FROM groups g
                                        LEFT JOIN courses b ON g.id_bootcamp = b.code 
                                        LEFT JOIN courses l ON g.id_leveling_english = l.code 
                                        LEFT JOIN courses e ON g.id_english_code = e.code
                                        LEFT JOIN courses s ON g.id_skills = s.code
                                        WHERE g.number_id = ?";

                                    $stmtAsistencia = $conn->prepare($queryAsistencia);
                                    $stmtAsistencia->bind_param("s", $number_id);
                                    $stmtAsistencia->execute();
                                    $resultAsistencia = $stmtAsistencia->get_result();

                                    if ($resultAsistencia && $resultAsistencia->num_rows > 0):
                                        $asistenciaData = $resultAsistencia->fetch_assoc();

                                        // Definir horas totales para cada curso
                                        $totalTecnico = 120;
                                        $totalNivelador = 20;
                                        $totalIngles = 24;
                                        $totalHabilidades = 15;

                                        function calcularHorasAsistencia($conn, $studentId, $courseId, $horasMaximas = null)
                                        {
                                            if (empty($courseId)) return 0;

                                            $sql = "SELECT ar.class_date, 
                                                        CASE 
                                                            WHEN ar.attendance_status = 'presente' THEN 
                                                                CASE DAYOFWEEK(ar.class_date)
                                                                    WHEN 2 THEN c.monday_hours    -- Lunes
                                                                    WHEN 3 THEN c.tuesday_hours   -- Martes
                                                                    WHEN 4 THEN c.wednesday_hours -- Miércoles
                                                                    WHEN 5 THEN c.thursday_hours  -- Jueves
                                                                    WHEN 6 THEN c.friday_hours    -- Viernes
                                                                    WHEN 7 THEN c.saturday_hours  -- Sábado
                                                                    WHEN 1 THEN c.sunday_hours    -- Domingo
                                                                    ELSE 0
                                                                END
                                                            WHEN ar.attendance_status = 'tarde' THEN ar.recorded_hours
                                                            ELSE 0 
                                                        END as horas,
                                                        ar.attendance_status
                                                    FROM attendance_records ar
                                                    JOIN courses c ON ar.course_id = c.code
                                                    WHERE ar.student_id = ? 
                                                    AND ar.course_id = ?
                                                    ORDER BY ar.class_date, FIELD(ar.attendance_status, 'presente', 'tarde')";

                                            $stmt = $conn->prepare($sql);
                                            if (!$stmt) return 0;

                                            $stmt->bind_param("si", $studentId, $courseId);
                                            if (!$stmt->execute()) return 0;

                                            $result = $stmt->get_result();

                                            $fechasContadas = [];
                                            $totalHoras = 0;

                                            while ($asistencia = $result->fetch_assoc()) {
                                                $fecha = $asistencia['class_date'];

                                                if (!in_array($fecha, $fechasContadas)) {
                                                    $totalHoras += $asistencia['horas'];
                                                    $fechasContadas[] = $fecha;
                                                }
                                            }

                                            $stmt->close();

                                            if ($horasMaximas !== null && $totalHoras > $horasMaximas) {
                                                return $horasMaximas;
                                            }

                                            return $totalHoras;
                                        }

                                        // Calcular horas por tipo de curso
                                        $horasTecnico = isset($asistenciaData['bootcamp_hours']) ? intval($asistenciaData['bootcamp_hours']) : 0;
                                        $horasNivelador = isset($asistenciaData['leveling_hours']) ? intval($asistenciaData['leveling_hours']) : 0;
                                        $horasIngles = isset($asistenciaData['english_hours']) ? intval($asistenciaData['english_hours']) : 0;
                                        $horasHabilidades = isset($asistenciaData['skills_hours']) ? intval($asistenciaData['skills_hours']) : 0;

                                        // Calcular horas actuales con límite
                                        $horasActualesTecnico = isset($asistenciaData['bootcamp_code']) && !empty($asistenciaData['bootcamp_code']) ?
                                            calcularHorasAsistencia($conn, $number_id, $asistenciaData['bootcamp_code'], $horasTecnico) : 0;

                                        $horasActualesNivelador = isset($asistenciaData['leveling_code']) && !empty($asistenciaData['leveling_code']) ?
                                            calcularHorasAsistencia($conn, $number_id, $asistenciaData['leveling_code'], $horasNivelador) : 0;

                                        $horasActualesIngles = isset($asistenciaData['english_code']) && !empty($asistenciaData['english_code']) ?
                                            calcularHorasAsistencia($conn, $number_id, $asistenciaData['english_code'], $horasIngles) : 0;

                                        $horasActualesHabilidades = isset($asistenciaData['skills_code']) && !empty($asistenciaData['skills_code']) ?
                                            calcularHorasAsistencia($conn, $number_id, $asistenciaData['skills_code'], $horasHabilidades) : 0;

                                        // Aplicar límites adicionales
                                        $horasActualesTecnico = min($horasActualesTecnico, $horasTecnico);
                                        $horasActualesNivelador = min($horasActualesNivelador, $horasNivelador);
                                        $horasActualesIngles = min($horasActualesIngles, $horasIngles);
                                        $horasActualesHabilidades = min($horasActualesHabilidades, $horasHabilidades);

                                        // Calcular porcentajes
                                        $porcentajeTecnico = $totalTecnico > 0 ? min(100, round(($horasActualesTecnico / $totalTecnico) * 100)) : 0;
                                        $porcentajeNivelador = $totalNivelador > 0 ? min(100, round(($horasActualesNivelador / $totalNivelador) * 100)) : 0;
                                        $porcentajeIngles = $totalIngles > 0 ? min(100, round(($horasActualesIngles / $totalIngles) * 100)) : 0;
                                        $porcentajeHabilidades = $totalHabilidades > 0 ? min(100, round(($horasActualesHabilidades / $totalHabilidades) * 100)) : 0;

                                        // MODIFICADO: Calcular total general SIN incluir inglés nivelatorio
                                        $horasTotalActual = $horasActualesTecnico + $horasActualesIngles + $horasActualesHabilidades;
                                        $horasTotalRequerido = $totalTecnico + $totalIngles + $totalHabilidades;
                                        $porcentajeTotal = $horasTotalRequerido > 0 ? min(100, round(($horasTotalActual / $horasTotalRequerido) * 100)) : 0;
                                    ?>
                                        <div class="w-100">
                                            <div class="text-center mb-4">
                                                <h6 class="fw-bold">Avance Total: <?= $porcentajeTotal ?>%</h6>
                                                <p class="text-muted small mb-2">* No incluye horas de Inglés Nivelatorio</p>
                                                <div class="progress" style="height: 12px;">
                                                    <div class="progress-bar bg-indigo-dark" role="progressbar"
                                                        style="width: <?= $porcentajeTotal ?>%;"
                                                        aria-valuenow="<?= $porcentajeTotal ?>" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="mt-1 d-block text-muted"><?= $horasTotalActual ?>/<?= $horasTotalRequerido ?> horas</span>
                                            </div>

                                            <!-- MODIFICADO: Alineación horizontal de los círculos -->
                                            <div class="row text-center justify-content-center">
                                                <!-- Técnico / Bootcamp -->
                                                <div class="col-lg-2 col-md-3 col-6 mb-3 mt-4">
                                                    <div class="progress-circle mx-auto" data-value="<?= $porcentajeTecnico / 100 ?>">
                                                        <span class="progress-left">
                                                            <span class="progress-bar border-indigo-dark"></span>
                                                        </span>
                                                        <span class="progress-right">
                                                            <span class="progress-bar border-indigo-dark"></span>
                                                        </span>
                                                        <div class="progress-value w-100 h-100 rounded-circle d-flex align-items-center justify-content-center">
                                                            <div>
                                                                <small class="fw-bold text-indigo-dark"><?= $horasActualesTecnico ?>/<?= $totalTecnico ?></small><br>
                                                                <span class="text-muted small">horas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h6 class="mt-2 fw-bold">Técnico</h6>
                                                    <span class="badge bg-indigo-dark"><?= $porcentajeTecnico ?>%</span>
                                                    <div class="text-muted small text-truncate mt-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($asistenciaData['bootcamp_name'] ?? 'Sin asignar') ?>">
                                                        <?= htmlspecialchars($asistenciaData['bootcamp_name'] ?? 'Sin asignar') ?>
                                                    </div>
                                                </div>

                                                <!-- Inglés Nivelatorio -->
                                                <div class="col-lg-2 col-md-3 col-6 mb-3 mt-4">
                                                    <div class="progress-circle mx-auto" data-value="<?= $porcentajeNivelador / 100 ?>">
                                                        <span class="progress-left">
                                                            <span class="progress-bar border-lime-dark"></span>
                                                        </span>
                                                        <span class="progress-right">
                                                            <span class="progress-bar border-lime-dark"></span>
                                                        </span>
                                                        <div class="progress-value w-100 h-100 rounded-circle d-flex align-items-center justify-content-center">
                                                            <div>
                                                                <small class="fw-bold text-lime-dark"><?= $horasActualesNivelador ?>/<?= $totalNivelador ?></small><br>
                                                                <span class="text-muted small">horas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h6 class="mt-2 fw-bold">Nivelatorio</h6>
                                                    <span class="badge bg-lime-dark"><?= $porcentajeNivelador ?>%</span>
                                                    <div class="text-muted small text-truncate mt-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($asistenciaData['leveling_english_name'] ?? 'Sin asignado') ?>">
                                                        <?= htmlspecialchars($asistenciaData['leveling_english_name'] ?? 'Sin asignado') ?>
                                                    </div>
                                                    <div class="mt-1">
                                                        <small class="text-info">
                                                            <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="No cuenta para el total general"></i>
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- English Code -->
                                                <div class="col-lg-2 col-md-3 col-6 mb-3 mt-4">
                                                    <div class="progress-circle mx-auto" data-value="<?= $porcentajeIngles / 100 ?>">
                                                        <span class="progress-left">
                                                            <span class="progress-bar border-orange-dark"></span>
                                                        </span>
                                                        <span class="progress-right">
                                                            <span class="progress-bar border-orange-dark"></span>
                                                        </span>
                                                        <div class="progress-value w-100 h-100 rounded-circle d-flex align-items-center justify-content-center">
                                                            <div>
                                                                <small class="fw-bold text-orange-dark"><?= $horasActualesIngles ?>/<?= $totalIngles ?></small><br>
                                                                <span class="text-muted small">horas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h6 class="mt-2 fw-bold">English Code</h6>
                                                    <span class="badge bg-orange-dark text-dark"><?= $porcentajeIngles ?>%</span>
                                                    <div class="text-muted small text-truncate mt-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($asistenciaData['english_code_name'] ?? 'Sin asignado') ?>">
                                                        <?= htmlspecialchars($asistenciaData['english_code_name'] ?? 'Sin asignado') ?>
                                                    </div>
                                                </div>

                                                <!-- Habilidades -->
                                                <div class="col-lg-2 col-md-3 col-6 mb-2 mt-4">
                                                    <div class="progress-circle mx-auto" data-value="<?= $porcentajeHabilidades / 100 ?>">
                                                        <span class="progress-left">
                                                            <span class="progress-bar border-purple-dark"></span>
                                                        </span>
                                                        <span class="progress-right">
                                                            <span class="progress-bar border-purple-dark"></span>
                                                        </span>
                                                        <div class="progress-value w-100 h-100 rounded-circle d-flex align-items-center justify-content-center">
                                                            <div>
                                                                <small class="fw-bold text-purple-dark"><?= $horasActualesHabilidades ?>/<?= $totalHabilidades ?></small><br>
                                                                <span class="text-muted small">horas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h6 class="mt-2 fw-bold">Habilidades</h6>
                                                    <span class="badge bg-purple-dark"><?= $porcentajeHabilidades ?>%</span>
                                                    <div class="text-muted small text-truncate mt-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($asistenciaData['skills_name'] ?? 'Sin asignado') ?>">
                                                        <?= htmlspecialchars($asistenciaData['skills_name'] ?? 'Sin asignado') ?>
                                                    </div>
                                                </div>

                                                <!-- Notas del Curso Técnico -->
                                                <div class="col-lg-4 col-md-12 col-6 mb-4">
                                                    <div class="card border-success h-100">
                                                        <div class="card-header bg-success text-white text-center">
                                                            <h6 class="card-title mb-0 fw-bold">
                                                                <i class="bi bi-star-fill me-1"></i>Notas Técnico
                                                            </h6>
                                                        </div>
                                                        <div class="card-body text-center w-100">
                                                            <?php
                                                            // Función para obtener notas de Moodle
                                                            function obtenerNotasMoodle($username, $courseid)
                                                            {
                                                                $apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
                                                                $token = '3f158134506350615397c83d861c2104';
                                                                $format = 'json';

                                                                // Obtener userid a partir del username
                                                                $functionGetUser = 'core_user_get_users_by_field';
                                                                $paramsUser = [
                                                                    'field' => 'username',
                                                                    'values[0]' => $username
                                                                ];

                                                                $postdataUser = http_build_query([
                                                                    'wstoken' => $token,
                                                                    'wsfunction' => $functionGetUser,
                                                                    'moodlewsrestformat' => $format
                                                                ] + $paramsUser);

                                                                $ch = curl_init();
                                                                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                                curl_setopt($ch, CURLOPT_POST, true);
                                                                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdataUser);
                                                                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                                                                $responseUser = curl_exec($ch);

                                                                if ($responseUser === false) {
                                                                    curl_close($ch);
                                                                    return ['error' => 'Error de conexión'];
                                                                }

                                                                $userData = json_decode($responseUser, true);

                                                                if (empty($userData)) {
                                                                    curl_close($ch);
                                                                    return ['error' => 'Usuario no encontrado'];
                                                                }

                                                                $userid = $userData[0]['id'];

                                                                // Obtener notas
                                                                $function = 'gradereport_user_get_grade_items';
                                                                $params = [
                                                                    'courseid' => $courseid,
                                                                    'userid' => $userid
                                                                ];

                                                                $postdata = http_build_query([
                                                                    'wstoken' => $token,
                                                                    'wsfunction' => $function,
                                                                    'moodlewsrestformat' => $format
                                                                ] + $params);

                                                                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                                                                $response = curl_exec($ch);
                                                                curl_close($ch);

                                                                if ($response === false) {
                                                                    return ['error' => 'Error al obtener notas'];
                                                                }

                                                                $data = json_decode($response, true);

                                                                if ($data === null || !isset($data['usergrades'][0])) {
                                                                    return ['error' => 'No hay notas disponibles'];
                                                                }

                                                                $usergrade = $data['usergrades'][0];
                                                                $notas = [];

                                                                if (isset($usergrade['gradeitems'])) {
                                                                    foreach ($usergrade['gradeitems'] as $item) {
                                                                        if (
                                                                            (isset($item['itemtype']) && $item['itemtype'] === 'course') ||
                                                                            (isset($item['graderaw']) && $item['graderaw'] !== null)
                                                                        ) {
                                                                            $notaRaw = isset($item['graderaw']) ? $item['graderaw'] : 0;
                                                                            $grademax = isset($item['grademax']) ? $item['grademax'] : 5.0;
                                                                            $itemname = isset($item['itemname']) ? $item['itemname'] : 'Nota';

                                                                            if ($grademax > 0) {
                                                                                $notaFinal = round($notaRaw, 2);
                                                                                $notas[] = [
                                                                                    'nombre' => $itemname,
                                                                                    'nota' => $notaFinal,
                                                                                    'raw' => $notaRaw,
                                                                                    'max' => $grademax
                                                                                ];
                                                                            }
                                                                        }
                                                                        if (count($notas) == 2) break;
                                                                    }
                                                                }

                                                                return ['notas' => $notas];
                                                            }

                                                            // Obtener notas del estudiante para el curso técnico
                                                            $notasData = ['notas' => []];
                                                            if (!empty($asistenciaData['bootcamp_code'])) {
                                                                $notasData = obtenerNotasMoodle($number_id, $asistenciaData['bootcamp_code']);
                                                            }

                                                            $nota1 = 0.0;
                                                            $nota2 = 0.0;
                                                            $promedio = 0.0;

                                                            if (!isset($notasData['error']) && !empty($notasData['notas'])) {
                                                                $notas = $notasData['notas'];
                                                                $nota1 = isset($notas[0]) ? $notas[0]['nota'] : 0.0;
                                                                $nota2 = isset($notas[1]) ? $notas[1]['nota'] : 0.0;
                                                                $promedio = count($notas) > 0 ? ($nota1 + $nota2) / 2 : 0.0;
                                                            }
                                                            ?>

                                                            <div class="d-flex flex-column gap-2 w-100">
                                                                <!-- Nota 1 -->
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-muted small">Nota 1:</span>
                                                                    <span class="badge <?= $nota1 >= 3.0 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                                                        <?= number_format($nota1, 1) ?>
                                                                    </span>
                                                                </div>

                                                                <!-- Nota 2 -->
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-muted small">Nota 2:</span>
                                                                    <span class="badge <?= $nota2 >= 3.0 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                                                        <?= number_format($nota2, 1) ?>
                                                                    </span>
                                                                </div>

                                                                <!-- Línea divisoria -->
                                                                <hr class="my-2">

                                                                <!-- Promedio -->
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="fw-bold text-dark">Promedio:</span>
                                                                    <span class="badge <?= $promedio >= 3.0 ? 'bg-success' : 'bg-danger' ?> fs-5 px-3 py-2">
                                                                        <i class="bi bi-calculator me-1"></i>
                                                                        <?= number_format($promedio, 1) ?>
                                                                    </span>
                                                                </div>

                                                                <!-- Estado académico -->
                                                                <div class="mt-2">
                                                                    <?php if ($promedio >= 3.0): ?>
                                                                        <span class="badge bg-success w-100 py-2">
                                                                            <i class="bi bi-check-circle me-1"></i>
                                                                            APROBADO
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger w-100 py-2">
                                                                            <i class="bi bi-x-circle me-1"></i>
                                                                            EN RIESGO
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <!-- Estado de aprobación final -->
                                                                <div class="mt-2">
                                                                    <?php if ($row['statusAdmin'] == '10'): ?>
                                                                        <span class="badge bg-success w-100 py-2">
                                                                            <i class="bi bi-check-circle-fill me-1"></i>
                                                                            APROBADO
                                                                        </span>
                                                                    <?php elseif ($porcentajeTotal >= 75 && $promedio >= 3.0): ?>
                                                                        <span class="badge bg-warning text-dark w-100 py-2" style="background: linear-gradient(45deg, #ffd700, #ffed4e) !important;">
                                                                            <i class="bi bi-star-fill me-1"></i>
                                                                            APTO PARA APROBACIÓN
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary w-100 py-2">
                                                                            <i class="bi bi-hourglass-half me-1"></i>
                                                                            AÚN NO ES APTO
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <!-- Información adicional -->
                                                                <small class="text-muted mt-2">
                                                                    <i class="bi bi-info-circle"></i>
                                                                    Curso: <?= htmlspecialchars($asistenciaData['bootcamp_name'] ?? 'No asignado') ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            No se encontró información de asistencia para este estudiante.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </form>
    </div>
</div>
<br><br>
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
            <form id="formActualizarLlamada_<?php echo $row['number_id']; ?>" method="POST" onsubmit="event.preventDefault(); return actualizarLlamada(<?php echo $row['number_id']; ?>)">
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
                            $horarios = obtenerHorarios($conn, $row['mode'], $row['headquarters']);
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

<!-- Modal para actualizar ubicación -->
<div id="modalActualizarUbicacion" class="modal fade" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark">
                <h5 class="modal-title text-center">
                    <i class="bi bi-geo-alt"></i> Actualizar Ubicación
                </h5>
                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formActualizarUbicacion">
                    <div class="form-group mb-3">
                        <label>Departamento:</label>
                        <select class="form-control" name="department" id="department" required>
                            <option value="">Seleccione un departamento</option>
                            <?php
                            $query = "SELECT * FROM departamentos ORDER BY departamento";
                            $result = $conn->query($query);
                            while ($depRow = $result->fetch_assoc()) {
                                echo "<option value='" . $depRow['id_departamento'] . "'>" . $depRow['departamento'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Municipio:</label>
                        <select class="form-control" name="municipality" id="municipality" required>
                            <option value="">Seleccione primero un departamento</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Dirección:</label>
                        <textarea class="form-control" name="address" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="id" id="ubicacion_id">
                    <button type="submit" class="btn bg-indigo-dark text-white">Actualizar Ubicación</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para actualizar información de contacto completa -->
<div id="modalActualizarContactoCompleto" class="modal fade" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px; width: 90%;">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark">
                <h5 class="modal-title text-center">
                    <i class="bi bi-telephone"></i> Actualizar Información de Contacto
                </h5>
                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formActualizarContactoCompleto">
                    <div class="row">
                        <!-- Columna izquierda - Teléfonos -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-telephone"></i> Información Telefónica
                            </h6>

                            <div class="form-group mb-3">
                                <label for="telefono1">Teléfono 1: <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefono1" name="telefono1"
                                    value="<?= htmlspecialchars($row['first_phone']) ?>"
                                    pattern="[0-9]{10}" maxlength="15" required>
                                <small class="form-text text-muted">Formato: 10 dígitos</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="telefono2">Teléfono 2:</label>
                                <input type="tel" class="form-control" id="telefono2" name="telefono2"
                                    value="<?= htmlspecialchars($row['second_phone']) ?>"
                                    pattern="[0-9]{10}" maxlength="15">
                                <small class="form-text text-muted">Opcional - Formato: 10 dígitos</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="medioContacto">Medio de Contacto Preferido:</label>
                                <select class="form-control" id="medioContacto" name="medioContacto">
                                    <option value="Correo" <?= ($row['contactMedium'] == 'Correo') ? 'selected' : '' ?>>Correo</option>
                                    <option value="Teléfono" <?= ($row['contactMedium'] == 'Teléfono') ? 'selected' : '' ?>>Teléfono</option>
                                    <option value="WhatsApp" <?= ($row['contactMedium'] == 'WhatsApp') ? 'selected' : '' ?>>WhatsApp</option>
                                </select>
                            </div>
                        </div>

                        <!-- Columna derecha - Email y Contacto de emergencia -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-envelope"></i> Email y Contacto de Emergencia
                            </h6>

                            <div class="form-group mb-3">
                                <label for="email">Correo Electrónico: <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($row['email']) ?>" required>
                                <small class="form-text text-muted">Formato válido de email</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="contactoEmergencia">Nombre Contacto de Emergencia: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactoEmergencia" name="contactoEmergencia"
                                    value="<?= htmlspecialchars($row['emergency_contact_name']) ?>"
                                    maxlength="150" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="numeroEmergencia">Número Contacto de Emergencia: <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="numeroEmergencia" name="numeroEmergencia"
                                    value="<?= htmlspecialchars($row['emergency_contact_number']) ?>"
                                    pattern="[0-9]{10}" maxlength="15" required>
                                <small class="form-text text-muted">Formato: 10 dígitos</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </div>

                    <input type="hidden" name="id" id="contacto_completo_id">
                    <div class="d-grid">
                        <button type="submit" class="btn bg-indigo-dark text-white">
                            <i class="bi bi-check-circle"></i> Actualizar Información de Contacto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal para mostrar observaciones en carrusel -->
<div class="modal fade" id="modalObservaciones_<?php echo $row['number_id']; ?>" tabindex="-1" aria-labelledby="modalObservacionesLabel_<?php echo $row['number_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="modalObservacionesLabel_<?php echo $row['number_id']; ?>">
                    <i class="bi bi-chat-square-text"></i> Observaciones del Estudiante
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="row align-items-center justify-content-center">
                            <div class="col-md-8 text-center">
                                <h3 class="card-title mb-0 fw-bold">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($nombre) ?>
                                    <span class="badge bg-indigo-dark ms-2"><?= htmlspecialchars($row['number_id']) ?></span>
                                </h3>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php if (!empty($row['contact_logs']) && count($row['contact_logs']) > 0): ?>
                                    <span class="badge bg-indigo-dark fs-6">
                                        <i class="bi bi-clock-history"></i> <?= count($row['contact_logs']) ?> contacto(s)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0" style="height: 500px; overflow: hidden;">
                        <?php if (!empty($row['contact_logs']) && count($row['contact_logs']) > 0): ?>
                            <!-- Carrusel de observaciones -->
                            <div id="carouselObservaciones_<?php echo $row['number_id']; ?>" class="carousel slide h-100" data-bs-ride="false" data-bs-interval="false">
                                <div class="carousel-inner h-100">
                                    <?php foreach (array_reverse($row['contact_logs']) as $index => $log): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> h-100">
                                            <div class="d-flex flex-column h-100 p-4" style="min-height: 500px;">
                                                <!-- Header del contacto -->
                                                <div class="card border-start border-indigo-dark border-4 mb-3" style="flex-shrink: 0;">
                                                    <div class="card-header bg-light">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center">
                                                                    <span class="badge bg-indigo-dark rounded-pill me-2"><?= $index + 1 ?></span>
                                                                    <div>
                                                                        <strong class="text-primary">
                                                                            <i class="bi bi-person"></i>
                                                                            <?= htmlspecialchars($log['advisor_name'] ?? 'No registrado') ?>
                                                                        </strong>
                                                                        <br>
                                                                        <small class="text-muted">Asesor</small>
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            <i class="bi bi-calendar"></i>
                                                                            <?= date('d/m/Y H:i', strtotime($log['contact_date'])) ?>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 text-end">
                                                                <div class="d-flex flex-column align-items-end">
                                                                    <?php if ($log['contact_established'] == 1): ?>
                                                                        <span class="badge bg-success mb-1">
                                                                            <i class="bi bi-telephone-check"></i> Contactado
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger mb-1">
                                                                            <i class="bi bi-telephone-x"></i> Sin contacto
                                                                        </span>
                                                                    <?php endif; ?>

                                                                    <?php if ($log['continues_interested'] == 1): ?>
                                                                        <span class="badge bg-success">
                                                                            <i class="bi bi-heart-fill"></i> Sí interesado
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning">
                                                                            <i class="bi bi-heart"></i> No interesado
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Contenido principal -->
                                                <div class="row flex-grow-1" style="min-height: 300px; min-width: 1080px;">
                                                    <!-- Columna izquierda - Detalles -->
                                                    <div class="col-md-4">
                                                        <div class="card h-100 border-indigo-dark">
                                                            <div class="card-header bg-indigo-dark text-white">
                                                                <h6 class="mb-0">
                                                                    <i class="bi bi-info-circle"></i> Detalles del Contacto
                                                                </h6>
                                                            </div>
                                                            <div class="card-body d-flex align-items-center justify-content-center">
                                                                <div class="text-center">
                                                                    <div class="mb-3">
                                                                        <?php
                                                                        $iconClass = '';
                                                                        $bgClass = '';
                                                                        switch ($log['details'] ?? 'Sin detalles') {
                                                                            case 'Número equivocado':
                                                                                $iconClass = 'bi-telephone-x';
                                                                                $bgClass = 'bg-warning';
                                                                                break;
                                                                            case 'Teléfono apagado':
                                                                                $iconClass = 'bi-power';
                                                                                $bgClass = 'bg-secondary';
                                                                                break;
                                                                            case 'Teléfono desconectado':
                                                                                $iconClass = 'bi-telephone-minus';
                                                                                $bgClass = 'bg-danger';
                                                                                break;
                                                                            case 'Sin señal':
                                                                                $iconClass = 'bi-signal';
                                                                                $bgClass = 'bg-warning';
                                                                                break;
                                                                            case 'No contestan':
                                                                                $iconClass = 'bi-telephone-forward';
                                                                                $bgClass = 'bg-info';
                                                                                break;
                                                                            default:
                                                                                $iconClass = 'bi-question-circle';
                                                                                $bgClass = 'bg-secondary';
                                                                        }
                                                                        ?>
                                                                        <div class="icon-container mb-3">
                                                                            <i class="<?= $iconClass ?> <?= $bgClass ?> text-white p-3 rounded-circle" style="font-size: 2rem;"></i>
                                                                        </div>
                                                                    </div>
                                                                    <h6 class="text-primary"><?= htmlspecialchars($log['details'] ?? 'Sin detalles') ?></h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Columna derecha - Observaciones -->
                                                    <div class="col-md-8">
                                                        <div class="card h-100 border-indigo-dark">
                                                            <div class="card-header bg-indigo-dark text-white">
                                                                <h6 class="mb-0">
                                                                    <i class="bi bi-chat-quote"></i> Observación Registrada
                                                                </h6>
                                                            </div>
                                                            <div class="card-body d-flex flex-column" style="min-height: 250px;">
                                                                <?php if (!empty($log['observation']) && $log['observation'] !== 'Sin observaciones'): ?>
                                                                    <div class="observation-content flex-grow-1 d-flex flex-column">
                                                                        <div class="quote-icon mb-3 text-center">
                                                                            <i class="bi bi-quote text-black" style="font-size: 3rem; opacity: 0.3;"></i>
                                                                        </div>
                                                                        <div class="observation-text flex-grow-1 d-flex align-items-center">
                                                                            <p class="lead text-dark mb-0" style="line-height: 1.6;">
                                                                                <?= nl2br(htmlspecialchars($log['observation'])) ?>
                                                                            </p>
                                                                        </div>
                                                                        <div class="mt-auto pt-3">
                                                                            <small class="text-muted fst-italic">
                                                                                <i class="bi bi-person-check"></i>
                                                                                Registrado por: <?= htmlspecialchars($log['advisor_name'] ?? 'No especificado') ?>
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="d-flex align-items-center justify-content-center h-100">
                                                                        <div class="text-center text-muted">
                                                                            <i class="bi bi-chat-square-dots" style="font-size: 4rem; opacity: 0.3;"></i>
                                                                            <h5 class="mt-3">Sin observaciones</h5>
                                                                            <p>No se registraron observaciones para este contacto</p>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Indicador de progreso en la parte inferior -->
                                                <div class="mt-3" style="flex-shrink: 0;">
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-indigo-dark" role="progressbar"
                                                            style="width: <?= round((($index + 1) / count($row['contact_logs'])) * 100) ?>%">
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-2">
                                                        <small class="text-muted">
                                                            Contacto <?= $index + 1 ?> de <?= count($row['contact_logs']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Controles de navegación -->
                                <?php if (count($row['contact_logs']) > 1): ?>
                                    <button class="carousel-control-prev" type="button"
                                        data-bs-target="#carouselObservaciones_<?php echo $row['number_id']; ?>"
                                        data-bs-slide="prev">
                                        <div class="carousel-control-icon bg-indigo-dark rounded-circle p-2">
                                            <i class="bi bi-chevron-left text-white"></i>
                                        </div>
                                        <span class="visually-hidden">Anterior</span>
                                    </button>
                                    <button class="carousel-control-next" type="button"
                                        data-bs-target="#carouselObservaciones_<?php echo $row['number_id']; ?>"
                                        data-bs-slide="next">
                                        <div class="carousel-control-icon bg-indigo-dark rounded-circle p-2">
                                            <i class="bi bi-chevron-right text-white"></i>
                                        </div>
                                        <span class="visually-hidden">Siguiente</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-chat-square-text" style="font-size: 5rem; opacity: 0.3;"></i>
                                    <h4 class="mt-4">Sin observaciones registradas</h4>
                                    <p class="lead">No hay historial de contactos para este estudiante.</p>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-info btn-lg"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>"
                                            data-bs-dismiss="modal">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Registrar primer contacto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div>
                        <?php if (!empty($row['contact_logs']) && count($row['contact_logs']) > 1): ?>
                            <small class="text-muted">
                                <i class="bi bi-arrow-left-right"></i>
                                Usa las flechas o indicadores para navegar
                            </small>
                        <?php endif; ?>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cerrar
                        </button>
                        <button type="button" class="btn bg-indigo-dark text-white"
                            data-bs-toggle="modal"
                            data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>"
                            data-bs-dismiss="modal">
                            <i class="bi bi-plus-circle"></i> Agregar nueva observación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para actualizar fecha de inscripción -->
<div id="modalActualizarFechaInscripcion" class="modal fade" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark">
                <h5 class="modal-title text-center">
                    <i class="bi bi-calendar-plus"></i> Actualizar Fecha de Inscripción
                </h5>
                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formActualizarFechaInscripcion">
                    <div class="form-group mb-3">
                        <label>Nueva fecha de inscripción:</label>
                        <input type="date" class="form-control" name="nuevaFechaInscripcion" id="nuevaFechaInscripcion" required>
                    </div>
                    <input type="hidden" name="id" id="fechaInscripcion_id">
                    <button type="submit" class="btn bg-indigo-dark text-white">Actualizar Fecha</button>
                </form>
            </div>
        </div>
    </div>
</div>


</div>
</div>
</form>
</tr>

<?php } else { ?>
    <div class="alert alert-danger mt-3">
        No se encontró ningún estudiante con el código <?= htmlspecialchars($filtervalues) ?>
    </div>
<?php } ?>
</div>
<?php endif; ?>
</div>
</div>

<style>
    /* Estilos para los círculos de progreso */
    .progress-circle {
        position: relative;
        width: 100px;
        height: 100px;
        background: none;
        border-radius: 50%;
    }

    .progress-circle::after {
        content: '';
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 6px solid #f2f2f2;
        position: absolute;
        top: 0;
        left: 0;
    }

    .progress-circle>span {
        position: absolute;
        width: 50%;
        height: 100%;
        overflow: hidden;
        top: 0;
        z-index: 1;
    }

    .progress-circle .progress-left {
        left: 0;
    }

    .progress-circle .progress-right {
        right: 0;
    }

    .progress-circle .progress-bar {
        width: 100%;
        height: 100%;
        position: absolute;
        border-width: 6px;
        border-style: solid;
        border-color: transparent;
        top: 0;
    }

    .progress-circle .progress-left .progress-bar {
        left: 100%;
        border-top-right-radius: 80px;
        border-bottom-right-radius: 80px;
        border-left: 0;
        transform-origin: center left;
    }

    .progress-circle .progress-right .progress-bar {
        left: -100%;
        border-top-left-radius: 80px;
        border-bottom-left-radius: 80px;
        border-right: 0;
        transform-origin: center right;
    }

    .progress-circle .progress-value {
        position: absolute;
        top: 0;
        left: 0;
    }

    .carousel-control-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 10%;
        opacity: 0.8;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1;
    }

    .carousel-indicators {
        bottom: -50px;
    }

    .carousel-indicators [data-bs-target] {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #17a2b8;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .carousel-indicators .active {
        background-color: #0d6efd;
        transform: scale(1.2);
    }

    /* Estilos para las observaciones */
    .observation-content {
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .observation-text {
        flex-grow: 1;
        display: flex;
        align-items: center;
        padding: 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        border-left: 4px solid #30336b;
    }

    .observation-text p {
        margin: 0;
        font-size: 1.1rem;
        color: #495057;
    }

    .quote-icon {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1;
    }

    /* Efectos hover para las cards */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 95%;
        }

        .card-body {
            height: 400px !important;
        }

        .observation-text p {
            font-size: 0.95rem;
        }
    }

    /* Animaciones suaves */
    .carousel-item {
        transition: transform 0.6s ease-in-out;
    }

    .progress-bar {
        transition: width 0.6s ease;
    }

    /* Estilos para los badges */
    .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }

    /* Icon containers */
    .icon-container i {
        transition: transform 0.3s ease;
    }

    .icon-container:hover i {
        transform: scale(1.1);
    }

    /* Estilos adicionales para el carrusel de observaciones */
    .carousel-item {
        transition: transform 0.3s ease-in-out;
    }

    .carousel-inner {
        overflow: hidden;
    }

    .carousel-item.active,
    .carousel-item-next,
    .carousel-item-prev {
        display: flex !important;
    }

    .carousel-item-next:not(.carousel-item-start),
    .carousel-item-prev:not(.carousel-item-end) {
        transform: translateX(0);
    }

    .carousel-item-next.carousel-item-start,
    .carousel-item-prev.carousel-item-end {
        transform: translateX(0);
    }

    .carousel-item-next,
    .carousel-item-prev,
    .carousel-item.active {
        min-height: 500px;
    }

    /* Prevenir el cambio de tamaño durante la transición */
    .carousel-inner>.carousel-item {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .carousel-inner>.carousel-item>div {
        width: 100%;
        height: 100%;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            btnClass = 'bg-lime-dark ';
            icon = '<i class="bi bi-whatsapp"></i>';
        } else if (medio == 'Teléfono') {
            btnClass = 'bg-teal-dark ';
            icon = '<i class="bi bi-telephone"></i>';
        } else if (medio == 'Correo') {
            btnClass = 'bg-amber-light ';
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
                console.log("Respuesta:", xhr.responseText);

                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();

                    if (response === "success") {
                        // Cerrar el modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalLlamada_' + id));
                        modal.hide();


                        Swal.fire({
                            title: '¡Exitoso! 🎉',
                            text: 'La información se ha guardado correctamente.',
                            toast: true,
                            position: 'center',
                        }).then(() => {
                            // Recargar la página después de 2 segundos
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
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

    function mostrarModalActualizarAdmision(id, isAccepted) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarAdmision_' + id).remove();

        // Verificar si el estudiante tiene certificación anterior
        fetch(`components/registrationsContact/verificar_participante.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                // Preparar las opciones según si existe o no en participantes y si cumple los requisitos
                let opcionesBeneficiario = '';
                let opcionesEstado = '';

                // Si isAccepted es false, mostrar solo estas opciones
                if (!isAccepted) {
                    opcionesEstado = `
                        <option value="0">Sin estado</option>
                        <option value="5">En proceso</option>
                        <option value="9">Aplazado</option>
                        <option value="4">Sin contacto</option>
                        <option value="2">Rechazado</option>
                        <option value="11">No válido</option>
                    `;
                } else {
                    // Si cumple con los requisitos, mostrar todas las opciones
                    if (data.existe) {
                        // Si existe en participantes, mostrar opción de Beneficiario para contrapartida
                        opcionesBeneficiario = `<option value="8">Beneficiario para contrapartida</option>`;
                    } else {
                        // Si no existe, mostrar opción regular de Beneficiario
                        opcionesBeneficiario = '<option value="1">Beneficiario</option>';
                    }

                    opcionesEstado = `
                        <option value="0">Sin estado</option>
                        ${opcionesBeneficiario}
                        <option value="2">Rechazado</option>
                        <option value="5">En proceso</option>
                        <option value="3">Matriculado</option>
                        <option value="4">Sin contacto</option>
                        <option value="6">Certificado</option>
                        <option value="7">Inactivo</option>
                        <option value="9">Aplazado</option>
                        <option value="10">Formado</option>
                        <option value="11">No válido</option>
                        <option value="12">No aprobado</option>
                    `;
                }

                // Crear el modal dinámicamente con las opciones filtradas
                const modalHtml = `
                <div id="modalActualizarAdmision_${id}" class="modal fade" aria-hidden="true" aria-labelledby="modalActualizarAdmisionLabel" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-indigo-dark">
                                <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Estado de Admisión</h5>
                                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${!isAccepted ? 
                                    `<div class="alert alert-warning mb-3">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Atención:</strong> Este estudiante no cumple con los requisitos. Solo puede seleccionar los estados: Sin estado, Rechazado, Sin contacto o No válido.
                                    </div>` : ''
                                }
                                <form id="formActualizarAdmision_${id}">
                                    <div class="form-group">
                                        <label for="nuevoEstado_${id}">Seleccionar nuevo estado:</label>
                                        <select class="form-control" id="nuevoEstado_${id}" name="nuevoEstado" required>
                                            <option value="">Seleccionar estado...</option>
                                            ${opcionesEstado}
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
                                        html: `
                                            <div class="alert alert-info">
                                                <h6><strong>Cursos actualmente asignados:</strong></h6>
                                                <ul class="list-group mt-3">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><strong>Bootcamp:</strong></span>
                                                        <span class="badge bg-primary rounded-pill">${data.cursos.bootcamp.name || 'No asignado'}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><strong>Inglés Nivelatorio:</strong></span>
                                                        <span class="badge bg-success rounded-pill">${data.cursos.english.name || 'No asignado'}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><strong>English Code:</strong></span>
                                                        <span class="badge bg-warning rounded-pill">${data.cursos.english_code.name || 'No asignado'}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span><strong>Habilidades:</strong></span>
                                                        <span class="badge bg-info rounded-pill">${data.cursos.skills.name || 'No asignado'}</span>
                                                    </li>
                                                </ul>
                                                <p class="mt-3 mb-0"><strong>¿Desea mantener la asignación actual o realizar una nueva?</strong></p>
                                            </div>
                                        `,
                                        icon: 'question',
                                        showDenyButton: true,
                                        confirmButtonText: 'Mantener actual',
                                        denyButtonText: 'Nueva asignación',
                                        confirmButtonColor: '#28a745',
                                        denyButtonColor: '#007bff',
                                        width: '600px'
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

        // Extraer el código del curso (formato C1L1-G1V o G100P/G202P/G301V)
        // Busca primero el formato C{num}L{num}-G{num}{letra}, si no lo encuentra busca G{num}{letra}
        let courseCodeMatch = selectedOption.match(/C\d+L\d+-G\d+[A-Z]?/);
        if (!courseCodeMatch) {
            courseCodeMatch = selectedOption.match(/G\d+[A-Z]?/);
        }
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
                        case 34:
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
                    } else if (response === "desmatricular_primero") {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se puede cambiar el estado',
                            text: 'El estudiante ya está matriculado en un grupo. Debe desmatricularlo antes de cambiar el estado de admisión.'
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
                text: "¿Desea actualizar los horarios?",
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

        // Mostrar indicador de carga
        Swal.fire({
            title: 'Cargando información',
            text: 'Obteniendo datos actuales...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Obtener datos actuales del usuario
        $.ajax({
            url: 'components/individualSearch/get_programa_info.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id: id
            },
            success: function(response) {
                Swal.close();

                if (response.success) {
                    const data = response.data;

                    // Crear el modal dinámicamente con los datos actuales
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
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Información actual:</strong> ${data.program || 'No asignado'} - ${data.level || 'No asignado'} - ${data.headquarters || 'No asignado'} - Lote ${data.lote || 'No asignado'}
                                    </div>
                                    
                                    <form id="formActualizarPrograma_${id}">
                                        <div class="form-group mb-3">
                                            <label for="nuevoPrograma_${id}">Programa actual: <strong>${data.program || 'No asignado'}</strong></label>
                                            <label for="nuevoPrograma_${id}">Seleccionar nuevo programa:</label>
                                            <select class="form-control" id="nuevoPrograma_${id}" name="nuevoPrograma">
                                                <option value="">Mantener actual (${data.program || 'No asignado'})</option>
                                                <option value="Programación" ${data.program === 'Programación' ? 'selected' : ''}>Programación</option>
                                                <option value="Ciberseguridad" ${data.program === 'Ciberseguridad' ? 'selected' : ''}>Ciberseguridad</option>
                                                <option value="Arquitectura en la Nube" ${data.program === 'Arquitectura en la Nube' ? 'selected' : ''}>Arquitectura en la Nube</option>
                                                <option value="Análisis de datos" ${data.program === 'Análisis de datos' ? 'selected' : ''}>Análisis de datos</option>
                                                <option value="Inteligencia Artificial" ${data.program === 'Inteligencia Artificial' ? 'selected' : ''}>Inteligencia Artificial</option>
                                                <option value="Blockchain" ${data.program === 'Blockchain' ? 'selected' : ''}>Blockchain</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="nuevoNivel_${id}">Nivel actual: <strong>${data.level || 'No asignado'}</strong></label>
                                            <label for="nuevoNivel_${id}">Seleccionar nuevo nivel:</label>
                                            <select class="form-control" id="nuevoNivel_${id}" name="nuevoNivel">
                                                <option value="">Mantener actual (${data.level || 'No asignado'})</option>
                                                <option value="Explorador" ${data.level === 'Explorador' ? 'selected' : ''}>Explorador</option>
                                                <option value="Innovador" ${data.level === 'Innovador' ? 'selected' : ''}>Innovador</option>
                                                <option value="Integrador" ${data.level === 'Integrador' ? 'selected' : ''}>Integrador</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="nuevoSede_${id}">Sede actual: <strong>${data.headquarters || 'No asignado'}</strong></label>
                                            <label for="nuevoSede_${id}">Seleccionar nueva sede:</label>
                                            <select class="form-control" id="nuevoSede_${id}" name="nuevoSede">
                                                <option value="">Mantener actual (${data.headquarters || 'No asignado'})</option>
                                                <option value="">Cargando sedes...</option>
                                            </select>
                                        </div>
    
                                        <div id="horariosContainer_${id}" style="display: none;">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i>
                                                <strong>Al cambiar la sede o programa, debe seleccionar nuevos horarios disponibles.</strong>
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label for="nuevoHorarioPrincipal_${id}">Nuevo Horario Principal: <span class="text-danger">*</span></label>
                                                <select class="form-control" id="nuevoHorarioPrincipal_${id}" name="nuevoHorarioPrincipal">
                                                    <option value="">Seleccionar horario principal</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label for="nuevoHorarioAlternativo_${id}">Nuevo Horario Alternativo:</label>
                                                <select class="form-control" id="nuevoHorarioAlternativo_${id}" name="nuevoHorarioAlternativo">
                                                    <option value="">Seleccionar horario alternativo (opcional)</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="nuevoLote_${id}">Lote actual: <strong>${data.lote || 'No asignado'}</strong></label>
                                            <label for="nuevoLote_${id}">Seleccionar lote:</label>
                                            <select class="form-control" id="nuevoLote_${id}" name="nuevoLote">
                                                <option value="">Mantener actual (${data.lote || 'No asignado'})</option>
                                                <option value="1" ${data.lote === '1' ? 'selected' : ''}>Lote 1</option>
                                                <option value="2" ${data.lote === '2' ? 'selected' : ''}>Lote 2</option>
                                            </select>
                                        </div>
                                        
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Nota:</strong> Si no selecciona una nueva opción, se mantendrá la información actual.
                                        </div>
                                        
                                        <input type="hidden" name="id" value="${id}">
                                        <button type="submit" class="btn bg-indigo-dark text-white w-100">
                                            <i class="bi bi-check-circle"></i> Actualizar Información
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    // Añadir el modal al DOM
                    document.body.insertAdjacentHTML('beforeend', modalHtml);

                    // Mostrar el modal
                    $('#modalActualizarPrograma_' + id).modal('show');

                    // Cargar las sedes después de mostrar el modal
                    cargarSedesParaModal(id, data.mode, data.headquarters);

                    // NUEVO: Función para actualizar horarios según programa y sede seleccionados
                    function actualizarHorariosSegunSeleccion() {
                        const programaSeleccionado = $(`#nuevoPrograma_${id}`).val() || data.program;
                        const sedeSeleccionada = $(`#nuevoSede_${id}`).val() || data.headquarters;

                        // Verificar si se ha cambiado el programa o la sede
                        const programaCambiado = programaSeleccionado !== data.program;
                        const sedeCambiada = sedeSeleccionada !== data.headquarters;

                        if (programaCambiado || sedeCambiada) {
                            // Mostrar el contenedor de horarios
                            $(`#horariosContainer_${id}`).show();

                            // Hacer obligatorio el horario principal
                            $(`#nuevoHorarioPrincipal_${id}`).attr('required', true);

                            // Cargar horarios con el programa y sede seleccionados
                            if (programaSeleccionado && sedeSeleccionada) {
                                cargarHorariosPorSede(id, programaSeleccionado, sedeSeleccionada, data.mode);
                            } else {
                                // Limpiar horarios si no se han seleccionado ambos
                                $(`#nuevoHorarioPrincipal_${id}`).html('<option value="">Seleccione primero programa y sede</option>');
                                $(`#nuevoHorarioAlternativo_${id}`).html('<option value="">Seleccione primero programa y sede</option>');
                            }
                        } else {
                            // Si vuelve a los valores originales, ocultar horarios
                            $(`#horariosContainer_${id}`).hide();
                            $(`#nuevoHorarioPrincipal_${id}`).removeAttr('required');
                        }
                    }

                    // Agregar event listeners para programa Y sede
                    $(`#nuevoPrograma_${id}`).on('change', function() {
                        actualizarHorariosSegunSeleccion();
                    });

                    $(`#nuevoSede_${id}`).on('change', function() {
                        actualizarHorariosSegunSeleccion();
                    });

                    // Validación del formulario actualizada
                    $('#formActualizarPrograma_' + id).on('submit', function(e) {
                        e.preventDefault();

                        const nuevoPrograma = $('#nuevoPrograma_' + id).val();
                        const nuevoNivel = $('#nuevoNivel_' + id).val();
                        const nuevoSede = $('#nuevoSede_' + id).val();
                        const nuevoLote = $('#nuevoLote_' + id).val();
                        const nuevoHorarioPrincipal = $('#nuevoHorarioPrincipal_' + id).val();
                        const nuevoHorarioAlternativo = $('#nuevoHorarioAlternativo_' + id).val();

                        // Verificar si se cambió la sede O el programa y validar horarios
                        const programaCambiado = nuevoPrograma && nuevoPrograma !== data.program;
                        const sedeCambiada = nuevoSede && nuevoSede !== data.headquarters;

                        if (programaCambiado || sedeCambiada) {
                            if (!nuevoHorarioPrincipal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Horario requerido',
                                    text: 'Al cambiar el programa o la sede debe seleccionar al menos un horario principal.'
                                });
                                return;
                            }
                        }

                        // Verificar si se ha seleccionado al menos un campo para actualizar
                        if (!nuevoPrograma && !nuevoNivel && !nuevoSede && !nuevoLote) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sin cambios',
                                text: 'Debe seleccionar al menos un campo para actualizar.'
                            });
                            return;
                        }

                        Swal.fire({
                            title: '¿Está seguro?',
                            html: `
                                <div class="text-start">
                                    <p><strong>Se actualizará:</strong></p>
                                    ${nuevoPrograma ? `<p>• Programa: ${nuevoPrograma}</p>` : ''}
                                    ${nuevoNivel ? `<p>• Nivel: ${nuevoNivel}</p>` : ''}
                                    ${nuevoSede ? `<p>• Sede: ${nuevoSede}</p>` : ''}
                                    ${nuevoLote ? `<p>• Lote: ${nuevoLote}</p>` : ''}
                                    ${nuevoHorarioPrincipal ? `<p>• Horario Principal: ${nuevoHorarioPrincipal}</p>` : ''}
                                    ${nuevoHorarioAlternativo ? `<p>• Horario Alternativo: ${nuevoHorarioAlternativo}</p>` : ''}
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, actualizar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                actualizarProgramaNivel(id, nuevoPrograma, nuevoNivel, nuevoSede, nuevoLote, nuevoHorarioPrincipal, nuevoHorarioAlternativo);
                                $('#modalActualizarPrograma_' + id).modal('hide');
                            }
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los datos del usuario'
                    });
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al conectar con el servidor'
                });
            }
        });
    }

    // Función auxiliar para generar opciones de sedes
    function generateSedeOptions(currentSede) {
        // Esta función será reemplazada por la carga AJAX de sedes
        return '<option value="">Cargando sedes...</option>';
    }

    // Función para cargar sedes según la modalidad
    // Función para cargar sedes según la modalidad
    function cargarSedesParaModal(id, mode, currentSede) {
        console.log('Cargando sedes para modalidad:', mode, 'sede actual:', currentSede);

        if (!mode) {
            $('#nuevoSede_' + id).html('<option value="">Mantener actual (Sin modalidad definida)</option>');
            return;
        }

        // Mostrar indicador de carga
        $('#nuevoSede_' + id).html('<option value="">Cargando sedes...</option>');

        $.ajax({
            url: 'components/individualSearch/get_sedes_por_modalidad.php',
            type: 'POST',
            data: {
                mode: mode
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);

                let options = `<option value="">Mantener actual (${currentSede || 'No asignado'})</option>`;

                if (response.success && response.data && response.data.length > 0) {
                    response.data.forEach(sede => {
                        const selected = sede.name === currentSede ? 'selected' : '';
                        options += `<option value="${sede.name}" ${selected}>${sede.name}</option>`;
                    });
                } else {
                    options += '<option value="">No hay sedes disponibles para esta modalidad</option>';
                }

                $('#nuevoSede_' + id).html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar sedes:', error);
                $('#nuevoSede_' + id).html(`
                    <option value="">Mantener actual (${currentSede || 'No asignado'})</option>
                    <option value="">Error al cargar sedes</option>
                `);
            }
        });
    }

    function cargarHorariosPorSede(id, programa, sede, mode) {
        // Mostrar indicador de carga
        $(`#nuevoHorarioPrincipal_${id}`).html('<option value="">Cargando horarios...</option>');
        $(`#nuevoHorarioAlternativo_${id}`).html('<option value="">Cargando horarios...</option>');

        $.ajax({
            url: 'components/individualSearch/get_horarios_filtrados.php',
            type: 'POST',
            data: {
                id: id,
                programa: programa,
                sede: sede,
                mode: mode
            },
            dataType: 'json',
            success: function(response) {
                console.log('Horarios cargados:', response);

                if (response.success && response.horarios && response.horarios.length > 0) {
                    let optionsPrincipal = '<option value="">Seleccionar horario principal</option>';
                    let optionsAlternativo = '<option value="">Seleccionar horario alternativo (opcional)</option>';

                    response.horarios.forEach(horario => {
                        optionsPrincipal += `<option value="${horario}">${horario}</option>`;
                        optionsAlternativo += `<option value="${horario}">${horario}</option>`;
                    });

                    $(`#nuevoHorarioPrincipal_${id}`).html(optionsPrincipal);
                    $(`#nuevoHorarioAlternativo_${id}`).html(optionsAlternativo);
                } else {
                    $(`#nuevoHorarioPrincipal_${id}`).html('<option value="">No hay horarios disponibles</option>');
                    $(`#nuevoHorarioAlternativo_${id}`).html('<option value="">No hay horarios disponibles</option>');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin horarios disponibles',
                        text: 'No se encontraron horarios para la sede y programa seleccionados.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar horarios:', error);
                $(`#nuevoHorarioPrincipal_${id}`).html('<option value="">Error al cargar horarios</option>');
                $(`#nuevoHorarioAlternativo_${id}`).html('<option value="">Error al cargar horarios</option>');
            }
        });
    }

    function actualizarProgramaNivel(id, nuevoPrograma, nuevoNivel, nuevoSede, nuevoLote, nuevoHorarioPrincipal, nuevoHorarioAlternativo) {
        const formData = new FormData();
        formData.append('id', id);

        // Solo enviar los valores que no estén vacíos
        if (nuevoPrograma) formData.append('nuevoPrograma', nuevoPrograma);
        if (nuevoNivel) formData.append('nuevoNivel', nuevoNivel);
        if (nuevoSede) formData.append('nuevoSede', nuevoSede);
        if (nuevoLote) formData.append('nuevoLote', nuevoLote);

        // NUEVO: Agregar horarios si se seleccionaron
        if (nuevoHorarioPrincipal) formData.append('nuevoHorarioPrincipal', nuevoHorarioPrincipal);
        if (nuevoHorarioAlternativo) formData.append('nuevoHorarioAlternativo', nuevoHorarioAlternativo);

        // Mostrar indicador de carga
        Swal.fire({
            title: 'Actualizando información',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_programa.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    console.log("Respuesta del servidor:", response);

                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La información se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else if (response === "no_changes") {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin cambios',
                            text: 'No se detectaron cambios en la información.'
                        });
                    } else if (response === "user_not_found") {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Usuario no encontrado.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la información: ' + response
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión con el servidor'
                    });
                }
            }
        };

        xhr.onerror = function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión con el servidor'
            });
        };

        xhr.send(formData);
    }

    function mostrarModalActualizarNombre(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarNombre_' + id).remove();

        // Crear el modal dinámicamente
        const modalHtml = `
        <div id="modalActualizarNombre_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark">
                        <h5 class="modal-title text-center">
                            <i class="bi bi-person"></i> Actualizar Nombre
                        </h5>
                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formActualizarNombre_${id}">
                            <div class="form-group mb-3">
                                <label>Primer Nombre:</label>
                                <input type="text" class="form-control" name="primerNombre" value="<?= htmlspecialchars($row['first_name']) ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Segundo Nombre:</label>
                                <input type="text" class="form-control" name="segundoNombre" value="<?= htmlspecialchars($row['second_name']) ?>">
                            </div>
                            <div class="form-group mb-3">
                                <label>Primer Apellido:</label>
                                <input type="text" class="form-control" name="primerApellido" value="<?= htmlspecialchars($row['first_last']) ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Segundo Apellido:</label>
                                <input type="text" class="form-control" name="segundoApellido" value="<?= htmlspecialchars($row['second_last']) ?>">
                            </div>
                            <input type="hidden" name="id" value="${id}">
                            <button type="submit" class="btn bg-indigo-dark text-white ">Actualizar Nombre</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#modalActualizarNombre_' + id).modal('show');

        // Manejar el envío del formulario
        $('#formActualizarNombre_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar el nombre?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);
                    actualizarNombre(id, formData);
                    $('#modalActualizarNombre_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarNombre(id, formData) {
        // Validar que los campos requeridos no estén vacíos
        const primerNombre = formData.get('primerNombre').trim();
        const primerApellido = formData.get('primerApellido').trim();

        if (!primerNombre || !primerApellido) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El primer nombre y primer apellido son obligatorios'
            });
            return;
        }

        // Convertir nombres a formato título (primera letra mayúscula)
        formData.set('primerNombre', capitalizarPalabra(primerNombre));
        formData.set('segundoNombre', capitalizarPalabra(formData.get('segundoNombre').trim()));
        formData.set('primerApellido', capitalizarPalabra(primerApellido));
        formData.set('segundoApellido', capitalizarPalabra(formData.get('segundoApellido').trim()));

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_nombre.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = xhr.responseText.trim();
                    console.log("Respuesta del servidor:", response); // Debug

                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'El nombre se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar el nombre: ' + response
                        });
                    }
                } else {
                    console.error("Error en la petición:", xhr.status);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la conexión con el servidor'
                    });
                }
            }
        };

        xhr.onerror = function() {
            console.error("Error de red");
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión con el servidor'
            });
        };

        xhr.send(formData);
    }

    // Función auxiliar para capitalizar palabras
    function capitalizarPalabra(str) {
        if (!str) return '';
        return str.split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }

    function mostrarModalActualizarNacimiento(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarNacimiento_' + id).remove();

        // Crear el modal dinámicamente
        const modalHtml = `
            <div id="modalActualizarNacimiento_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-indigo-dark">
                            <h5 class="modal-title text-center">
                                <i class="bi bi-calendar-date"></i> Actualizar Fecha de Nacimiento
                            </h5>
                            <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formActualizarNacimiento_${id}">
                                <div class="form-group mb-3">
                                    <label>Nueva fecha de nacimiento:</label>
                                    <input type="date" class="form-control" name="nuevaFecha" required max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <input type="hidden" name="id" value="${id}">
                                <button type="submit" class="btn bg-indigo-dark text-white ">Actualizar Fecha</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>`;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#modalActualizarNacimiento_' + id).modal('show');

        // Manejar el envío del formulario
        $('#formActualizarNacimiento_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar la fecha de nacimiento?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);
                    actualizarNacimiento(id, formData);
                    $('#modalActualizarNacimiento_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarNacimiento(id, formData) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_nacimiento.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La fecha de nacimiento se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la fecha de nacimiento.'
                        });
                    }
                }
            }
        };

        xhr.send(formData);
    }

    function mostrarModalActualizarUbicacion(id) {
        // Resetear el formulario
        $('#formActualizarUbicacion')[0].reset();

        // Asignar el ID del usuario al campo oculto
        $('#ubicacion_id').val(id);

        // Cargar datos actuales del usuario
        $.ajax({
            url: 'components/individualSearch/get_ubicacion_usuario.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id: id
            },
            beforeSend: function() {
                // Mostrar indicador de carga
                $('#department').html('<option value="">Cargando...</option>');
                $('#municipality').html('<option value="">Cargando...</option>');
                $('textarea[name="address"]').attr('placeholder', 'Cargando...');
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    // Cargar opciones de departamentos (que ya están en el HTML)
                    // Solo necesitamos restablecer las opciones originales en caso de que se haya cambiado
                    $.ajax({
                        url: 'components/individualSearch/get_departments.php',
                        type: 'GET',
                        success: function(deptResponse) {
                            $('#department').html(deptResponse);

                            // Seleccionar el departamento del usuario
                            if (data.department_id) {
                                $('#department').val(data.department_id);

                                // Cargar municipios para este departamento
                                $.ajax({
                                    url: 'components/individualSearch/get_municipalities.php',
                                    type: 'POST',
                                    data: {
                                        department_id: data.department_id
                                    },
                                    success: function(munResponse) {
                                        $('#municipality').html(munResponse);

                                        // Seleccionar el municipio del usuario
                                        if (data.municipality_id) {
                                            $('#municipality').val(data.municipality_id);
                                        }
                                    }
                                });
                            }

                            // Establecer la dirección
                            if (data.address) {
                                $('textarea[name="address"]').val(data.address);
                            }
                        }
                    });
                } else {
                    // Si hay un error al cargar los datos
                    Swal.fire({
                        icon: 'warning',
                        title: 'Información',
                        text: 'No se pudieron cargar los datos de ubicación actuales.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al conectar con el servidor.'
                });
            },
            complete: function() {
                // Mostrar el modal una vez que se hayan cargado los datos
                $('#modalActualizarUbicacion').modal('show');
            }
        });

        // Event listener para el cambio de departamento
        if (!$._data($('#department')[0], 'events') || !$._data($('#department')[0], 'events').change) {
            $('#department').on('change', function() {
                const departmentId = $(this).val();
                const municipalitySelect = $('#municipality');

                if (departmentId) {
                    municipalitySelect.html('<option value="">Cargando municipios...</option>');
                    cargarMunicipios(departmentId, 'municipality');
                } else {
                    municipalitySelect.html('<option value="">Seleccione primero un departamento</option>');
                }
            });
        }

        // Manejar el envío del formulario
        if (!$._data($('#formActualizarUbicacion')[0], 'events') || !$._data($('#formActualizarUbicacion')[0], 'events').submit) {
            $('#formActualizarUbicacion').on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea actualizar la ubicación?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData(this);
                        actualizarUbicacion($('#ubicacion_id').val(), formData);
                        $('#modalActualizarUbicacion').modal('hide');
                    }
                });
            });
        }
    }

    function cargarMunicipios(departmentId, municipalitySelectId) {
        $.ajax({
            url: 'components/individualSearch/get_municipalities.php',
            type: 'POST',
            data: {
                department_id: departmentId
            },
            success: function(response) {
                $('#' + municipalitySelectId).html(response);
            }
        });
    }

    function actualizarUbicacion(id, formData) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_ubicacion.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    console.log('Respuesta del servidor:', response); // Para debug
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La ubicación se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la ubicación: ' + response
                        });
                    }
                }
            }
        };

        xhr.onerror = function() {
            console.error('Error en la petición XHR');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión con el servidor'
            });
        };

        xhr.send(formData);
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

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        if (popoverTriggerList.length > 0) {
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    });

    // Animar círculos de progreso
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Animar círculos de progreso
        setTimeout(function() {
            const progressCircles = document.querySelectorAll('.progress-circle');
            progressCircles.forEach(function(circle) {
                const value = parseFloat(circle.getAttribute('data-value')) || 0;
                const leftProgress = circle.querySelector('.progress-left .progress-bar');
                const rightProgress = circle.querySelector('.progress-right .progress-bar');

                if (value > 0) {
                    if (value <= 0.5) {
                        rightProgress.style.transform = 'rotate(' + (value * 360) + 'deg)';
                    } else {
                        rightProgress.style.transform = 'rotate(180deg)';
                        leftProgress.style.transform = 'rotate(' + ((value - 0.5) * 360) + 'deg)';
                    }
                }
            });
        }, 200);
    });

    // Función para mostrar el modal de actualización completa de contacto
    function mostrarModalActualizarContactoCompleto(id) {
        // Asignar el ID del usuario al campo oculto
        $('#contacto_completo_id').val(id);

        // Cargar datos actuales del usuario
        $.ajax({
            url: 'components/individualSearch/get_contacto_usuario.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id: id
            },
            beforeSend: function() {
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Cargando información',
                    text: 'Obteniendo datos de contacto...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();

                if (response.success) {
                    const data = response.data;

                    // Llenar los campos con los datos actuales
                    $('#telefono1').val(data.first_phone || '');
                    $('#telefono2').val(data.second_phone || '');
                    $('#email').val(data.email || '');
                    $('#contactoEmergencia').val(data.emergency_contact_name || '');
                    $('#numeroEmergencia').val(data.emergency_contact_number || '');
                    $('#medioContacto').val(data.contactMedium || 'Correo');

                    // Mostrar el modal
                    $('#modalActualizarContactoCompleto').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los datos de contacto'
                    });
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al conectar con el servidor'
                });
            }
        });
    }

    // Manejar el envío del formulario de contacto completo
    $(document).ready(function() {
        $('#formActualizarContactoCompleto').on('submit', function(e) {
            e.preventDefault();

            // Validaciones adicionales
            const telefono1 = $('#telefono1').val().trim();
            const email = $('#email').val().trim();
            const contactoEmergencia = $('#contactoEmergencia').val().trim();
            const numeroEmergencia = $('#numeroEmergencia').val().trim();

            // Validar campos obligatorios
            if (!telefono1 || !email || !contactoEmergencia || !numeroEmergencia) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos obligatorios',
                    text: 'Por favor complete todos los campos obligatorios marcados con *'
                });
                return;
            }

            // Validar formato de teléfonos
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(telefono1)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato incorrecto',
                    text: 'El teléfono 1 debe tener exactamente 10 dígitos'
                });
                return;
            }

            const telefono2 = $('#telefono2').val().trim();
            if (telefono2 && !phoneRegex.test(telefono2)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato incorrecto',
                    text: 'El teléfono 2 debe tener exactamente 10 dígitos o estar vacío'
                });
                return;
            }

            if (!phoneRegex.test(numeroEmergencia)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato incorrecto',
                    text: 'El número de contacto de emergencia debe tener exactamente 10 dígitos'
                });
                return;
            }

            // Validar formato de email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email inválido',
                    text: 'Por favor ingrese un correo electrónico válido'
                });
                return;
            }

            // Confirmar actualización
            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar toda la información de contacto?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);
                    actualizarContactoCompleto($('#contacto_completo_id').val(), formData);
                    $('#modalActualizarContactoCompleto').modal('hide');
                }
            });
        });
    });

    // Función para actualizar el contacto completo
    function actualizarContactoCompleto(id, formData) {
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Actualizando información',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_contacto_completo.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = xhr.responseText.trim();
                    console.log("Respuesta del servidor:", response);

                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La información de contacto se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la información: ' + response
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la conexión con el servidor'
                    });
                }
            }
        };

        xhr.onerror = function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión con el servidor'
            });
        };

        xhr.send(formData);
    }

    function mostrarModalActualizarDocumento(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarDocumento_' + id).remove();

        // Obtener datos actuales del usuario
        $.ajax({
            url: 'components/individualSearch/get_documento.php',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(userData) {
                // Crear el modal dinámicamente
                const modalHtml = `
                    <div id="modalActualizarDocumento_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-indigo-dark">
                                    <h5 class="modal-title text-center">
                                        <i class="bi bi-card-text"></i> Actualizar Documento de Identidad
                                    </h5>
                                    <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Importante:</strong> Al cambiar el número de documento, se actualizará automáticamente la URL de búsqueda.
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6><strong>Información actual:</strong></h6>
                                        <p><strong>Tipo:</strong> ${userData.typeID}<br>
                                        <strong>Número:</strong> ${userData.number_id}</p>
                                    </div>
                                    
                                    <hr>
                                    
                                    <form id="formActualizarDocumento_${id}">
                                        <div class="form-group mb-3">
                                            <label>Nuevo tipo de documento: <span class="text-danger">*</span></label>
                                            <select class="form-control" name="nuevoTipoDoc" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="CC" ${userData.typeID === 'CC' ? 'selected' : ''}>CC</option>
                                                <option value="Otra" ${userData.typeID === 'Otra' ? 'selected' : ''}>Otra</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label>Nuevo número de documento: <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="nuevoNumeroDoc" 
                                                value="${userData.number_id}" required min="1" max="9999999999">
                                            <small class="form-text text-muted">Máximo 10 dígitos</small>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label>Confirmar número de documento: <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="confirmarNumeroDoc" 
                                                required min="1" max="9999999999">
                                            <small class="form-text text-muted">Debe coincidir con el número anterior</small>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                                        </div>
                                        
                                        <input type="hidden" name="id" value="${id}">
                                        <input type="hidden" name="numeroActual" value="${userData.number_id}">
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn bg-indigo-dark text-white">
                                                <i class="bi bi-check-circle"></i> Actualizar Documento
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>`;

                // Añadir el modal al DOM y mostrarlo
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                $('#modalActualizarDocumento_' + id).modal('show');

                // Manejar el envío del formulario
                $('#formActualizarDocumento_' + id).on('submit', function(e) {
                    e.preventDefault();

                    const nuevoNumero = $('input[name="nuevoNumeroDoc"]').val();
                    const confirmarNumero = $('input[name="confirmarNumeroDoc"]').val();

                    // Validar que los números coincidan
                    if (nuevoNumero !== confirmarNumero) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de confirmación',
                            text: 'Los números de documento no coinciden. Por favor verifique.'
                        });
                        return;
                    }

                    // Validar longitud del número
                    if (nuevoNumero.length < 6 || nuevoNumero.length > 10) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Número inválido',
                            text: 'El número de documento debe tener entre 6 y 10 dígitos.'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Está seguro?',
                        html: `
                            <div class="alert alert-warning">
                                <p><strong>Se cambiará:</strong></p>
                                <p>Tipo: ${userData.typeID} → ${$('select[name="nuevoTipoDoc"]').val()}</p>
                                <p>Número: ${userData.number_id} → ${nuevoNumero}</p>
                                <hr>
                                <p><strong>La página se recargará automáticamente con el nuevo número.</strong></p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, actualizar',
                        cancelButtonText: 'Cancelar',
                        width: '500px'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData(this);
                            actualizarDocumento(id, formData, nuevoNumero);
                            $('#modalActualizarDocumento_' + id).modal('hide');
                        }
                    });
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos del documento'
                });
            }
        });
    }

    function actualizarDocumento(id, formData, nuevoNumero) {
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Actualizando documento',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_documento.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = xhr.responseText.trim();
                    console.log("Respuesta del servidor:", response);

                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'El documento se ha actualizado correctamente. Redirigiendo...',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            // Redirigir a la nueva URL con el nuevo número de documento
                            const currentUrl = new URL(window.location);
                            currentUrl.searchParams.set('search', nuevoNumero);
                            window.location.href = currentUrl.toString();
                        });
                    } else if (response === "duplicate") {
                        Swal.fire({
                            icon: 'error',
                            title: 'Número duplicado',
                            text: 'Ya existe otro usuario registrado con este número de documento.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar el documento: ' + response
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la conexión con el servidor'
                    });
                }
            }
        };

        xhr.onerror = function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión con el servidor'
            });
        };

        xhr.send(formData);
    }

    function mostrarModalActualizarAvailability(id, availabilityActual, internetActual) {
        $('#modalActualizarAvailability_' + id).remove();

        const modalHtml = `
            <div id="modalActualizarAvailability_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-indigo-dark">
                            <h5 class="modal-title text-center">
                                <i class="bi bi-check2-circle"></i> Actualizar Disponibilidad de Compromiso e Internet
                            </h5>
                            <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formActualizarAvailability_${id}">
                                <div class="form-group mb-3">
                                    <label for="nuevaAvailability_${id}">¿Acepta compromiso?</label>
                                    <select class="form-control" id="nuevaAvailability_${id}" name="nuevaAvailability" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Sí" ${availabilityActual === 'Sí' ? 'selected' : ''}>Sí</option>
                                        <option value="No" ${availabilityActual === 'No' ? 'selected' : ''}>No</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nuevoInternet_${id}">¿Tiene Internet?</label>
                                    <select class="form-control" id="nuevoInternet_${id}" name="nuevoInternet" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Sí" ${internetActual === 'Sí' ? 'selected' : ''}>Sí</option>
                                        <option value="No" ${internetActual === 'No' ? 'selected' : ''}>No</option>
                                    </select>
                                </div>
                                <input type="hidden" name="id" value="${id}">
                                <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#modalActualizarAvailability_' + id).modal('show');

        $('#formActualizarAvailability_' + id).on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar la disponibilidad de compromiso e internet?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const nuevaAvailability = $('#nuevaAvailability_' + id).val();
                    const nuevoInternet = $('#nuevoInternet_' + id).val();
                    actualizarAvailability(id, nuevaAvailability, nuevoInternet);
                    $('#modalActualizarAvailability_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarAvailability(id, nuevaAvailability, nuevoInternet) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('nuevaAvailability', nuevaAvailability);
        formData.append('nuevoInternet', nuevoInternet);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_availability.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La disponibilidad e internet se han actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la información: ' + response
                        });
                    }
                }
            }
        };

        xhr.onerror = function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión con el servidor'
            });
        };

        xhr.send(formData);
    }

    function mostrarModalActualizarFechaInscripcion(id, fechaActual) {
        $('#fechaInscripcion_id').val(id);
        $('#nuevaFechaInscripcion').val(fechaActual);
        $('#modalActualizarFechaInscripcion').modal('show');

        $('#formActualizarFechaInscripcion').off('submit').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar la fecha de inscripción?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);
                    actualizarFechaInscripcion($('#fechaInscripcion_id').val(), formData);
                    $('#modalActualizarFechaInscripcion').modal('hide');
                }
            });
        });
    }

    function actualizarFechaInscripcion(id, formData) {
        // Formatear la fecha para enviar como datetime
        const fecha = formData.get('nuevaFechaInscripcion');
        formData.set('nuevaFechaInscripcion', fecha + ' 00:00:00');
        formData.append('id', id);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/individualSearch/actualizar_fecha_inscripcion.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'La fecha de inscripción se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar la fecha de inscripción: ' + response
                        });
                    }
                }
            }
        };

        xhr.send(formData);
    }
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>