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
                        TIMESTAMPDIFF(YEAR, ur.birthdate, CURDATE()) as age
                        FROM user_register ur
                        LEFT JOIN municipios m ON ur.municipality = m.id_municipio 
                        LEFT JOIN departamentos d ON ur.department = d.id_departamento
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
                                'observation' => $log['observation']
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
                ?>
            </div>

            <form method="POST">
                <div class="card items-center mt-3 mx-auto">
                    <div class="card-header bg-indigo-dark text-white text-center items-center">
                        <i class="bi bi-person-lines-fill"></i> INFORMACIÓN DEL USUARIO
                    </div>

                    <div class="row justify-content-center">
                        <div class="row">
                            <!--Columana uno-->
                            <div class="col-md-3 col-lg-3 col-sm-12 p-3 ">
                                <strong>Nombre:</strong><br>
                                <b style="text-transform:capitalize"><?= $nombre ?></b>
                                <hr>
                                <strong>Actualizar nombre:</strong><br>
                                <button type="button" class="btn btn-primary mt-2" onclick="mostrarModalActualizarNombre(<?= $row['number_id'] ?>)" data-bs-toggle="tooltip" data-bs-placement="top" title="Actualizar Nombre">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <hr>
                                <strong>Tipo de identificación:</strong><br>
                                <?= htmlspecialchars($row['typeID']) ?>
                                <hr>
                                <strong>Numero de identificación:</strong><br>
                                <?= htmlspecialchars($number_id) ?>
                                <hr>
                                <?php include 'showPictureId.php'; ?>
                                <hr>
                                <strong>Edad:</strong><br>
                                <?= htmlspecialchars($row['age']) ?> años
                                <hr>
                                <strong>Actualizar fecha de nacimiento:</strong><br>
                                <button type="button" class="btn btn-secondary mt-2" onclick="mostrarModalActualizarNacimiento(<?= $row['number_id'] ?>)" data-bs-toggle="tooltip" data-bs-placement="top" title="Actualizar fecha de nacimiento">
                                    <i class="bi bi-calendar-date"></i>
                                </button>
                                <hr>
                                <script>
                                    function actualizarNacimiento(id) {
                                        var form = document.getElementById('formActualizarNacimiento_' + id);
                                        var formData = new FormData(form);

                                        var xhr = new XMLHttpRequest();
                                        xhr.open("POST", "components/registrationsContact/actualizar_nacimiento.php", true);
                                        xhr.onreadystatechange = function() {
                                            if (xhr.readyState == 4) {
                                                if (xhr.status == 200 && xhr.responseText.trim() === "success") {
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: '¡Actualizado!',
                                                        text: 'La fecha de nacimiento se actualizó correctamente.',
                                                        showConfirmButton: false,
                                                        timer: 2000
                                                    }).then(() => {
                                                        location.reload();
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: 'No se pudo actualizar la fecha de nacimiento.'
                                                    });
                                                }
                                            }
                                        };
                                        xhr.send(formData);
                                    }
                                </script>

                                <strong>Correo:</strong><br>
                                <?= htmlspecialchars($row['email']) ?>
                                <hr>

                                <strong>Es campesino:</strong><br>
                                <?= htmlspecialchars($row['country_person']) ?>
                                <hr>
                            </div>


                            <!--Columana dos-->
                            <div class="col-md-3 col-lg-3 col-sm-12 p-3">

                                <strong>Telefono 1:</strong><br>
                                <?= htmlspecialchars($row['first_phone']) ?>
                                <hr>
                                <strong>Telefono 2:</strong><br>
                                <?= htmlspecialchars($row['second_phone']) ?>
                                <hr>
                                <?php include 'contactMedium.php'; ?>
                                <hr>
                                <strong>Actualizar medio de contacto:</strong><br>
                                <button class="btn bg-indigo-dark text-white " type="button" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar medio de contacto">
                                    <i class="bi bi-arrow-left-right"></i></button>
                                <hr>
                                <strong>Contacto de emergencia:</strong><br>
                                <?= htmlspecialchars($row['emergency_contact_name']) ?>
                                <hr>
                                <strong>Numero de contacto:</strong><br>
                                <?= htmlspecialchars($row['emergency_contact_number']) ?>
                                <hr>
                                <strong>Dirección:</strong><br>
                                <?= htmlspecialchars($row['address']) ?>
                                <hr>
                                <strong>Nacionalidad:</strong><br>
                                <?= htmlspecialchars($row['nationality']) ?>
                                <hr>
                                <strong>Departamento:</strong><br>
                                <?= htmlspecialchars($row['departamento']) ?>
                                <hr>
                                <strong>Municipio:</strong><br>
                                <?= htmlspecialchars($row['municipio']) ?>



                            </div>
                            <!--Columana tres-->
                            <div class="col-md-3 col-lg-3 col-sm-12 p-3">
                                <strong>Actualizar ubicación:</strong><br>
                                <a href="#" class="btn text-white" style="background-color: #dc143c;"
                                    onclick="mostrarModalActualizarUbicacion(<?php echo $row['number_id']; ?>)"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar ubicación">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </a>
                                <hr>
                                <strong>Ocupación:</strong><br>
                                <?= htmlspecialchars($row['occupation']) ?>
                                <hr>
                                <strong>Tiempo para obligaciones:</strong><br>
                                <?= htmlspecialchars($row['time_obligations']) ?>
                                <hr>
                                <strong>Sede:</strong><br>
                                <?= htmlspecialchars($row['headquarters']) ?>
                                <hr>
                                <strong>Modalidad:</strong><br>
                                <?= htmlspecialchars($row['mode']) ?>
                                <hr>
                                <strong>Actualizar modalidad:</strong><br>
                                <a href="#" class="btn text-white" style="background-color: #fc4b08;" onclick="modalActualizarModalidad(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar modalidad">
                                    <i class="bi bi-arrow-left-right"></i>
                                </a>
                                <hr>
                                <strong>Programa:</strong><br>
                                <?= htmlspecialchars($row['program']) ?>
                                <hr>

                                <strong>Nivel de preferencia:</strong><br>
                                <?= htmlspecialchars($row['level']) ?>
                                <hr>

                                <strong>Lote:</strong><br>
                                <?= htmlspecialchars($row['lote']) ?>
                                <hr>

                                <strong>Actualizar programa, nivel, sede y lote:</strong><br>
                                <button type="button" class="btn btn-warning" onclick="mostrarModalActualizarPrograma(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar información de progrma">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <hr>

                                <strong>Actualizar registro de contacto:</strong><br>
                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>">
                                    <i class="bi bi-telephone"></i>
                                </button>
                                <hr>

                            </div>
                            <!--Columana cuatro-->
                            <div class="col-md-3 col-lg-3 col-sm-12 p-3">

                                <strong>Actualizar estado de admision:</strong><br>
                                <button class="btn bg-indigo-dark text-white" type="button" onclick="mostrarModalActualizarAdmision(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar estado de admisión">
                                    <i class="bi bi-arrow-left-right"></i></button>
                                <hr>

                                <strong>Horarios: <br>Principal/Alternativo:</strong><br>
                                <button type="button" class="btn bg-indigo-light"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="<?= htmlspecialchars($row['schedules']) ?>">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                <button type="button" class="btn bg-teal-light"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="<?= htmlspecialchars($row['schedules_alternative']) ?>">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                <hr>
                                <strong>Actualizar Horarios</strong><br>
                                <button class="btn text-white" type="button" style="background-color: #b624d5;" onclick="mostrarModalActualizarHorario(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar horario">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <hr>
                                <strong>Dispositivo:</strong><br>
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
                                echo '
                                    <button class="btn ' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" 
                                    data-bs-custom-class="custom-tooltip" data-bs-title="' . $btnText . '">
                                        ' . $icon . '
                                    </button>';
                                ?>
                                <hr>
                                <strong>Internet:</strong><br>

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
                                echo '<button class="btn ' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" 
                                    data-bs-custom-class="custom-tooltip" data-bs-title="' . $btnText . '">
                                        ' . $icon . '
                                    </button>'
                                ?>
                                <hr>
                                <strong>Estado:</strong><br>
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
                                    echo '<a class="btn bg-teal-dark" style="width:43px" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" title="CUMPLE"><i class="bi bi-check-circle"></i></a>';
                                } else {
                                    echo '<a class="btn bg-danger text-white" style="width:43px" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top" title="NO CUMPLE"><i class="bi bi-x-circle"></i></a>';
                                }
                                ?>
                                <hr>
                                <strong>Estado de admisión:</strong><br>
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
                                <hr>
                                <strong>Puntaje de prueba:</strong><br>
                                <?php
                                if (isset($nivelesUsuarios[$row['number_id']])) {
                                    $puntaje = $nivelesUsuarios[$row['number_id']];
                                    if ($puntaje >= 0 && $puntaje <= 5) {
                                        echo '<button class="btn bg-magenta-dark " style="max-width: 100px;" role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                                    } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                        echo '<button class="btn bg-orange-dark " style="max-width: 100px;" role="alert"role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                                    } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                        echo '<button class="btn bg-teal-dark " style="max-width: 100px;" role="alert" role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                                    }
                                } else {
                                    echo '<button class="btn bg-silver " style="max-width: 100px;" role="alert"role="alert data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-custom-class="custom-tooltip"
                                            data-bs-title="No ha presebtado la prueba" >
                                        <i class="bi bi-ban"></i>
                                            </button>';
                                }
                                ?>
                                <hr>
                                <strong>Nivel de prueba:</strong><br>
                                <?php
                                if (isset($nivelesUsuarios[$row['number_id']])) {
                                    $puntaje = $nivelesUsuarios[$row['number_id']];
                                    if ($puntaje >= 0 && $puntaje <= 5) {
                                        echo '<button class="btn bg-magenta-dark " style="max-width: 150px;" role="alert">Básico</div>';
                                    } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                        echo '<button class="btn bg-orange-dark " style="max-width: 150px;" role="alert"role="alert">Intermedio</div>';
                                    } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                        echo '<button class="btn bg-teal-dark " style="max-width: 150px;" role="alert" role="alert">Avanzado</div>';
                                    }
                                } else {
                                    echo '<button class="btn bg-silver " style="max-width: 150px;" role="alert"role="alert  data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-custom-class="custom-tooltip"
                                                data-bs-title="No ha presentado la prueba" >
                                                <i class="bi bi-ban"></i></button>';
                                }
                                ?>
                            </div>
                            <hr>

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
                                    <option value="1">Aceptado</option>
                                    <option value="2">Rechazado</option>
                                    <?php if ($rol == 'Administrador'): ?>
                                    <option value="3">Matriculado</option>
                                    <?php endif; ?>
                                    <option value="4">Sin contacto</option>
                                    <option value="5">En proceso</option>
                                    <option value="6">Culmino proceso</option>
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
        $('#modalActualizarPrograma_' + id).modal('show');

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
                                    $sedes = obtenerSedes($conn, $row['mode']); 
                                    foreach($sedes as $sede):
                                    ?>
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
                            text: 'La información se ha actualizado correctamente.',
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

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        if (popoverTriggerList.length > 0) {
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>