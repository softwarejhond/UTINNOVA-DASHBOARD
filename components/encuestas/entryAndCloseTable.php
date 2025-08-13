<style>
    .nav-tabs .nav-link.active {
        color: #30336b !important;
        font-weight: bold;
        background-color: transparent !important;
    }

    .nav-tabs .nav-link {
        color: #000 !important;
        font-weight: normal;
    }

    table th,
    table td {
        text-align: center !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .popover-hover {
        cursor: pointer;
        color: #30336b;
        text-decoration: underline dotted;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
</style>

<ul class="nav nav-tabs justify-content-center" id="encuestasTab" role="tablist" style="display: flex;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ingreso-tab" data-bs-toggle="tab" data-bs-target="#ingreso" type="button" role="tab" aria-controls="ingreso" aria-selected="true">
            Encuestas de ingreso
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cierre-tab" data-bs-toggle="tab" data-bs-target="#cierre" type="button" role="tab" aria-controls="cierre" aria-selected="false">
            Encuestas de cierre
        </button>
    </li>
</ul>

<div class="tab-content" id="encuestasTabContent">
    <div class="tab-pane fade show active" id="ingreso" role="tabpanel" aria-labelledby="ingreso-tab">
        <!-- Botón de exportación para tabla de ingreso -->
        <div class="d-flex justify-content-start mb-2 mt-3">
            <button id="exportEntryBtn" class="btn bg-teal-dark text-white">
                <i class="bi bi-file-earmark-excel me-2"></i>Exportar a Excel
            </button>
        </div>
        <!-- Tabla de Encuestas de ingreso -->
        <div class="table-responsive mt-3">
            <table id="tablaEmployability" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Tipo ID</th>
                        <th>Identificación</th>
                        <th>Nombre completo</th>
                        <th>Email</th>
                        <th>Interés</th>
                        <th>Lote</th>
                        <th>Cohorte</th>
                        <th>Fecha inicio formación</th> <!-- Se actualizará -->
                        <th>Descripción personal</th>
                        <th>Localidad</th>
                        <th>Nivel educativo</th>
                        <th>Género</th>
                        <th>Experiencia laboral</th>
                        <th>Estado laboral</th>
                        <th>Experiencia tech</th>
                        <th>Perfil laboral</th>
                        <th>Años exp. tech</th>
                        <th>Último rol tech</th>
                        <th>Conocimientos</th>
                        <th>Habilidades digitales</th>
                        <th>Habilidades blandas</th>
                        <th>Redes profesionales</th>
                        <th>Rol deseado</th>
                        <th>Acepta requisitos</th>
                        <th>Acepta datos</th>
                        <th>Fecha registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once __DIR__ . '/../../controller/conexion.php';
                    $sql = "SELECT * FROM employability ORDER BY fecha_registro DESC";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $nombreCompleto = trim(
                                $row['first_name'] . ' ' .
                                    ($row['second_name'] ? $row['second_name'] . ' ' : '') .
                                    $row['first_last'] . ' ' .
                                    $row['second_last']
                            );

                            // Obtener lote
                            $lote = '-';
                            $number_id = $row['number_id'];
                            $sqlLote = "SELECT lote FROM user_register WHERE number_id = '$number_id' LIMIT 1";
                            $resLote = $conn->query($sqlLote);
                            if ($resLote && $resLote->num_rows > 0) {
                                $loteRow = $resLote->fetch_assoc();
                                $lote = $loteRow['lote'];
                            }

                            // Obtener cohorte y fecha inicio formación
                            $cohorte = '-';
                            $fechaInicioFormacion = '';
                            $sqlGroup = "SELECT id_bootcamp FROM groups WHERE number_id = '$number_id' LIMIT 1";
                            $resGroup = $conn->query($sqlGroup);
                            if ($resGroup && $resGroup->num_rows > 0) {
                                $groupRow = $resGroup->fetch_assoc();
                                $id_bootcamp = $groupRow['id_bootcamp'];
                                $sqlCohorte = "SELECT cohort, start_date FROM course_periods WHERE bootcamp_code = '$id_bootcamp' LIMIT 1";
                                $resCohorte = $conn->query($sqlCohorte);
                                if ($resCohorte && $resCohorte->num_rows > 0) {
                                    $cohorteRow = $resCohorte->fetch_assoc();
                                    $cohorte = $cohorteRow['cohort'];
                                    $fechaInicioFormacion = $cohorteRow['start_date'];
                                }
                            }
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                                <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                                <td><?php echo htmlspecialchars($nombreCompleto); ?></td>
                                <td><?php echo !empty($row['email']) ? htmlspecialchars($row['email']) : '-'; ?></td>
                                <td><?php echo !empty($row['interest']) ? htmlspecialchars($row['interest']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($lote); ?></td>
                                <td><?php echo htmlspecialchars($cohorte); ?></td>
                                <td>
                                    <?php
                                    if ($fechaInicioFormacion && $fechaInicioFormacion !== '0000-00-00' && $fechaInicioFormacion !== '0000-00-00 00:00:00') {
                                        echo date('d/m/Y', strtotime($fechaInicioFormacion));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['personal_description'])): ?>
                                        <button type="button" class="btn btn-sm bg-orange-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['personal_description']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['localidad']); ?></td>
                                <td><?php echo htmlspecialchars($row['nivel_educativo']); ?></td>
                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                <td>
                                    <?php if (!empty($row['work_experience'])): ?>
                                        <button type="button" class="btn btn-sm bg-purple-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['work_experience']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['current_employment_status']); ?></td>
                                <td><?php echo htmlspecialchars($row['tech_experience']); ?></td>
                                <td>
                                    <?php if (!empty($row['job_profile'])): ?>
                                        <button type="button" class="btn btn-sm bg-lime-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['job_profile']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['tech_experience_years']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_tech_role']); ?></td>
                                <td><?php echo htmlspecialchars($row['skills_knowledge']); ?></td>
                                <td>
                                    <?php if (!empty($row['digital_skills'])): ?>
                                        <button type="button" class="btn btn-sm bg-indigo-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['digital_skills']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['soft_skills'])): ?>
                                        <button type="button" class="btn btn-sm bg-teal-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['soft_skills']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['professional_networks'])): ?>
                                        <button type="button" class="btn btn-sm bg-magenta-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['professional_networks']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['desired_role']); ?></td>
                                <td><?php echo $row['accept_requirements'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo $row['accept_data_policies'] ? 'Sí' : 'No'; ?></td>
                                <td>
                                    <?php
                                    $fecha = $row['fecha_registro'];
                                    if ($fecha && $fecha !== '0000-00-00' && $fecha !== '0000-00-00 00:00:00') {
                                        echo date('d/m/Y', strtotime($fecha));
                                    } else {
                                        echo '';
                                    }
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla de encuestas de cierre -->
    <div class="tab-pane fade" id="cierre" role="tabpanel" aria-labelledby="cierre-tab">
        <!-- Botón de exportación para tabla de cierre -->
        <div class="d-flex justify-content-start mb-2 mt-3">
            <button id="exportCloseBtn" class="btn bg-teal-dark text-white">
                <i class="bi bi-file-earmark-excel me-2"></i>Exportar a Excel
            </button>
        </div>
        <div class="table-responsive mt-3">
            <table id="tablaEmployabilityClose" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Tipo ID</th>
                        <th>Identificación</th>
                        <th>Nombre completo</th>
                        <th>Email</th>
                        <th>Interés</th>
                        <th>Lote</th> <!-- Nueva columna -->
                        <th>Cohorte</th> <!-- Nueva columna -->
                        <th>Fecha inicio formación</th>
                        <th>Grupos poblacionales</th>
                        <th>Nivel educativo</th>
                        <th>Género</th>
                        <th>Estado laboral</th>
                        <th>¿Trabaja en tech?</th>
                        <th>Empleo conseguido por</th>
                        <th>Tipo de contrato</th>
                        <th>Nivel de ingresos</th>
                        <th>Rol actual</th>
                        <th>Espacios ruta empleabilidad</th>
                        <th>Utilidad del contenido</th>
                        <th>Apoyo empleabilidad</th>
                        <th>Satisfacción general</th>
                        <th>Acción de mejora</th>
                        <th>Acepta requisitos</th>
                        <th>Acepta datos</th>
                        <th>Fecha registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once __DIR__ . '/../../controller/conexion.php';
                    $sql = "SELECT * FROM employability_close ORDER BY created_at DESC";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $nombreCompleto = trim(
                                $row['first_name'] . ' ' .
                                    ($row['second_name'] ? $row['second_name'] . ' ' : '') .
                                    $row['first_last'] . ' ' .
                                    $row['second_last']
                            );

                            // Obtener lote
                            $lote = '-';
                            $number_id = $row['number_id'];
                            $sqlLote = "SELECT lote FROM user_register WHERE number_id = '$number_id' LIMIT 1";
                            $resLote = $conn->query($sqlLote);
                            if ($resLote && $resLote->num_rows > 0) {
                                $loteRow = $resLote->fetch_assoc();
                                $lote = $loteRow['lote'];
                            }

                            // Obtener cohorte y fecha inicio formación
                            $cohorte = '-';
                            $fechaInicioFormacion = '';
                            $sqlGroup = "SELECT id_bootcamp FROM groups WHERE number_id = '$number_id' LIMIT 1";
                            $resGroup = $conn->query($sqlGroup);
                            if ($resGroup && $resGroup->num_rows > 0) {
                                $groupRow = $resGroup->fetch_assoc();
                                $id_bootcamp = $groupRow['id_bootcamp'];
                                $sqlCohorte = "SELECT cohort, start_date FROM course_periods WHERE bootcamp_code = '$id_bootcamp' LIMIT 1";
                                $resCohorte = $conn->query($sqlCohorte);
                                if ($resCohorte && $resCohorte->num_rows > 0) {
                                    $cohorteRow = $resCohorte->fetch_assoc();
                                    $cohorte = $cohorteRow['cohort'];
                                    $fechaInicioFormacion = $cohorteRow['start_date'];
                                }
                            }
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                                <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                                <td><?php echo htmlspecialchars($nombreCompleto); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['interest']); ?></td>
                                <td><?php echo htmlspecialchars($lote); ?></td>
                                <td><?php echo htmlspecialchars($cohorte); ?></td>
                                <td>
                                    <?php
                                    if ($fechaInicioFormacion && $fechaInicioFormacion !== '0000-00-00' && $fechaInicioFormacion !== '0000-00-00 00:00:00') {
                                        echo date('d/m/Y', strtotime($fechaInicioFormacion));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['grupos_poblacionales']); ?></td>
                                <td><?php echo htmlspecialchars($row['nivel_educativo']); ?></td>
                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                <td><?php echo htmlspecialchars($row['current_employment_status']); ?></td>
                                <td><?php echo htmlspecialchars($row['current_tech_job']); ?></td>
                                <td><?php echo htmlspecialchars($row['employment_obtained_by']); ?></td>
                                <td><?php echo htmlspecialchars($row['contract_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['income_level']); ?></td>
                                <td><?php echo htmlspecialchars($row['current_job_role']); ?></td>
                                <td>
                                    <?php if (!empty($row['employment_route_spaces'])): ?>
                                        <button type="button" class="btn btn-sm bg-indigo-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['employment_route_spaces']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $stars = (int)$row['content_usefulness'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $stars) {
                                            echo '<i class="fa fa-star text-warning"></i>';
                                        } else {
                                            echo '<i class="fa fa-star-o text-muted"></i>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $stars = (int)$row['employment_support'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $stars) {
                                            echo '<i class="fa fa-star text-warning"></i>';
                                        } else {
                                            echo '<i class="fa fa-star-o text-muted"></i>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['general_satisfaction']); ?></td>
                                <td>
                                    <?php if (!empty($row['improvement_action'])): ?>
                                        <button type="button" class="btn btn-sm bg-orange-dark text-white" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="<?php echo htmlspecialchars($row['improvement_action']); ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['accept_requirements'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo $row['accept_data_policies'] ? 'Sí' : 'No'; ?></td>
                                <td>
                                    <?php
                                    $fecha = $row['created_at'];
                                    if ($fecha && $fecha !== '0000-00-00' && $fecha !== '0000-00-00 00:00:00') {
                                        echo date('d/m/Y', strtotime($fecha));
                                    } else {
                                        echo '';
                                    }
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tablaEmployability').DataTable({
            responsive: true,
            language: {
                url: "controller/datatable_esp.json"
            },
            pagingType: "simple"
        });

        $('#tablaEmployabilityClose').DataTable({
            responsive: true,
            language: {
                url: "controller/datatable_esp.json"
            },
            pagingType: "simple"
        });

        // Inicializar popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                html: true,
                container: 'body'
            });
        });

        // Manejar exportación a Excel para tabla de ingreso
        $('#exportEntryBtn').on('click', function() {
            Swal.fire({
                title: 'Generando Excel',
                html: 'Por favor espera mientras se genera el archivo...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            });

            window.location.href = 'components/encuestas/export_employability.php';

            setTimeout(function() {
                Swal.close();
            }, 3000);
        });

        // Manejar exportación a Excel para tabla de cierre
        $('#exportCloseBtn').on('click', function() {
            Swal.fire({
                title: 'Generando Excel',
                html: 'Por favor espera mientras se genera el archivo...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            });

            window.location.href = 'components/encuestas/export_employability_close.php';

            setTimeout(function() {
                Swal.close();
            }, 3000);
        });
    });
</script>