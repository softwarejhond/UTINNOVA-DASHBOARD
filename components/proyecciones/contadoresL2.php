<!-- Tarjetas: Inscritos en Bootcamps Presenciales y Virtuales Lote 2 -->
<style>
    .badge-total {
        background-color: #30336b !important;
        color: #fff !important;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.4em 1em;
    }

    .badge-grupo {
        background-color: #30336b !important;
        color: #fff !important;
        font-size: 0.95rem;
        font-weight: 500;
        border-radius: 0.5rem;
        padding: 0.3em 0.8em;
    }

     .scale-09 {
        transform: scale(0.9);
        transform-origin: top center;
        width: 111.11%; /* 1 / 0.9 = 1.111... */
        margin-left: -5.55%; /* Centra el contenido escalado */
    }

    /* Nuevos estilos para colapsables */
    .description-collapse {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-left: 4px solid #30336b;
        margin-top: 15px;
        border-radius: 8px;
    }

    .description-card {
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .btn-description {
        font-size: 0.85rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .btn-description:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .highlight-total {
        background-color: #fff3cd;
        border: 2px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin: 10px 0;
    }

    .warning-note {
        background-color: #fff3cd;
        border-left: 4px solid #f39c12;
        padding: 12px;
        border-radius: 0 8px 8px 0;
        margin-top: 10px;
    }

    .alert-info-custom {
        background-color: #e8f4f8;
        border-left: 4px solid #17a2b8;
        color: #0c5460;
    }
</style>

<div class="scale-09">

    <!-- Contrapartida: Participantes Lote 2 (Presenciales/Virtuales, Matriculados/Aprobados) -->
    <div class="row">
        <!-- Presenciales Matriculados Contrapartida Lote 2 -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-chalkboard-teacher"></i>
                                Presenciales Lote 2 matriculados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales del Lote 2">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaPresencialMatriculadosListL2" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaPresencialMatriculadosTotalL2" class="badge badge-total">0</span> matriculados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Presenciales Aprobados Contrapartida Lote 2 -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i>
                                Presenciales Lote 2 aprobados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales del Lote 2">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaPresencialAprobadosListL2" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaPresencialAprobadosTotalL2" class="badge badge-total">0</span> aprobados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Virtuales Matriculados Contrapartida Lote 2 -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-laptop"></i>
                                Virtuales Lote 2 matriculados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales del Lote 2">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaVirtualMatriculadosListL2" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaVirtualMatriculadosTotalL2" class="badge badge-total">0</span> matriculados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Virtuales Aprobados Contrapartida Lote 2 -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i>
                                Virtuales Lote 2 aprobados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales del Lote 2">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaVirtualAprobadosListL2" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaVirtualAprobadosTotalL2" class="badge badge-total">0</span> aprobados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas principales del Lote 2 -->
    <div class="row pt-4">
        <!-- Presenciales Lote 2 -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-chalkboard-teacher"></i> Presenciales Lote 2 matriculados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampPresencialListLoteDos" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando bootcamps presenciales...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsPresencialesLoteDos" class="badge badge-total">0</span> matriculados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('presencial-matriculados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencialL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i> Presenciales Lote 2 Aprobados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampPresencialListLoteDosAprobados" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando aprobados...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsPresencialesLoteDosAprobados" class="badge badge-total">0</span> aprobados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('presencial-aprobados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencialL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Campistas sin asistencia Lote 2
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteDos" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando campistas sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteDos" class="badge badge-total">0</span> Campistas sin asistencia
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('presencial-sin-asistencia')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencialL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-hourglass-half"></i> Sin atención Lote 2 Presenciales
                            </b>
                        </h5>
                    </div>
                    <div id="programasPresencialesPendientesListLoteDos" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando programas pendientes...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalProgramasPresencialesPendientesLoteDos" class="badge badge-total">0</span> pendientes
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('presencial-pendientes')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencialL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Colapsable para Presenciales Lote 2 -->
    <div class="row">
        <div class="col-12">
            <div class="collapse" id="collapsePresencialL2">
                <div class="description-collapse">
                    <div class="card description-card">
                        <div class="card-body d-flex justify-content-center align-items-center flex-column" style="text-align: center;">
                            <div id="descripcionContentL2" class="w-100" style="text-align: center;">
                                <!-- El contenido se cargará dinámicamente aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Virtuales Lote 2 -->
    <div class="row pt-4">
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-laptop"></i> Virtuales Lote 2 matriculados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampVirtualListLoteDos" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando bootcamps virtuales...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsVirtualesLoteDos" class="badge badge-total">0</span> matriculados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('virtual-matriculados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtualL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i> Virtuales Lote 2 Aprobados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampVirtualListLoteDosAprobados" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando aprobados virtuales...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsVirtualesLoteDosAprobados" class="badge badge-total">0</span> aprobados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('virtual-aprobados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtualL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cursos sin asistencias (Virtuales Lote 2) -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Campistas sin asistencia Lote 2 Virtuales
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteDosVirtual" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando campistas sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteDosVirtual" class="badge badge-total">0</span> campistas sin asistencia
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('virtual-sin-asistencia')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtualL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-hourglass-half"></i> Sin atención Lote 2 Virtuales
                            </b>
                        </h5>
                    </div>
                    <div id="programasVirtualesPendientesListLoteDos" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando programas pendientes...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalProgramasVirtualesPendientesLoteDos" class="badge badge-total">0</span> pendientes
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcionL2('virtual-pendientes')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtualL2">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Colapsable para Virtuales Lote 2 -->
    <div class="row">
        <div class="col-12">
            <div class="collapse" id="collapseVirtualL2">
                <div class="description-collapse">
                    <div class="card description-card">
                        <div class="card-body d-flex justify-content-center align-items-center flex-column" style="text-align: center;">
                            <div id="descripcionVirtualContentL2" class="w-100" style="text-align: center;">
                                <!-- El contenido se cargará dinámicamente aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para mostrar descripción de las tarjetas Lote 2
    function mostrarDescripcionL2(tipo) {
        const descriptions = {
            'presencial-matriculados': {
                title: '📊 Presenciales Matriculados - Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Esta tarjeta muestra el número total de participantes matriculados en bootcamps presenciales del Lote 2.</p>
                    <p><strong>Qué incluye:</strong> Todos los campistas que han completado exitosamente el proceso de inscripción y matrícula en modalidad presencial para el segundo lote.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-users"></i> Total de Matriculados: <span class="badge badge-total" id="desc-presencial-matriculados-l2">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Este número representa la capacidad instalada actual para la modalidad presencial en el Lote 2.
                    </div>
                `,
                container: 'descripcionContentL2'
            },
            'presencial-aprobados': {
                title: '✅ Presenciales Aprobados - Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Muestra el número de participantes que han completado exitosamente su bootcamp presencial y han sido aprobados en el Lote 2.</p>
                    <p><strong>Criterios de aprobación:</strong> Campistas que cumplieron con los requisitos mínimos de asistencia, participación y evaluaciones.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-certificate"></i> Total de Aprobados: <span class="badge badge-total" id="desc-presencial-aprobados-l2">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Estos participantes contribuyen directamente al cumplimiento de las metas establecidas para el segundo lote.
                    </div>
                `,
                container: 'descripcionContentL2'
            },
            'presencial-sin-asistencia': {
                title: '⚠️ Campistas Sin Asistencia - Presencial Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Participantes matriculados que no han registrado ninguna asistencia en sus respectivos bootcamps presenciales del Lote 2.</p>
                    <p><strong>Impacto:</strong> Estos campistas están ocupando cupos pero no están participando activamente en la formación.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-exclamation-triangle"></i> Campistas sin asistencia: <span class="badge badge-total" id="desc-presencial-sin-asistencia-l2">0</span></h5>
                        <h5><i class="fa-solid fa-calculator"></i> Total matriculados: <span class="badge badge-total" id="desc-presencial-total-matriculados-l2">0</span></h5>
                        <hr>
                        <h4><i class="fa-solid fa-chart-line"></i> Proyección real para meta: <span class="badge" style="background-color: #28a745; color: white; font-size: 1.1rem;" id="desc-presencial-proyeccion-l2">0</span></h4>
                    </div>
                    <div class="warning-note">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Recomendación:</strong> Se sugiere verificar el estado de estos campistas para evaluar si procede la desmatrícula y liberar cupos para nuevos participantes.
                    </div>
                `,
                container: 'descripcionContentL2'
            },
            'presencial-pendientes': {
                title: '⏳ Programas Sin Atención - Presencial Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Programas académicos que aún no han recibido atención o implementación de bootcamps presenciales en el Lote 2.</p>
                    <p><strong>Oportunidad:</strong> Representa la demanda potencial y los cupos disponibles para futuras cohortes del segundo lote.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-hourglass-half"></i> Total Pendientes: <span class="badge badge-total" id="desc-presencial-pendientes-l2">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Estos programas representan oportunidades de crecimiento y expansión de la cobertura en el Lote 2.
                    </div>
                `,
                container: 'descripcionContentL2'
            },
            'virtual-matriculados': {
                title: '💻 Virtuales Matriculados - Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Esta tarjeta muestra el número total de participantes matriculados en bootcamps virtuales del Lote 2.</p>
                    <p><strong>Qué incluye:</strong> Todos los campistas que han completado exitosamente el proceso de inscripción y matrícula en modalidad virtual para el segundo lote.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-laptop"></i> Total de Matriculados: <span class="badge badge-total" id="desc-virtual-matriculados-l2">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> La modalidad virtual permite mayor flexibilidad y alcance geográfico en el Lote 2.
                    </div>
                `,
                container: 'descripcionVirtualContentL2'
            },
            'virtual-aprobados': {
                title: '✅ Virtuales Aprobados - Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Participantes que han completado exitosamente su bootcamp virtual y han sido aprobados en el Lote 2.</p>
                    <p><strong>Criterios de aprobación:</strong> Campistas que cumplieron con los requisitos de participación, entregas y evaluaciones en la modalidad virtual.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-certificate"></i> Total de Aprobados: <span class="badge badge-total" id="desc-virtual-aprobados-l2">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Estos participantes contribuyen al cumplimiento de las metas de formación virtual del segundo lote.
                    </div>
                `,
                container: 'descripcionVirtualContentL2'
            },
            'virtual-sin-asistencia': {
                title: '⚠️ Campistas Sin Asistencia - Virtual Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Participantes matriculados que no han registrado actividad en sus bootcamps virtuales del Lote 2.</p>
                    <p><strong>Impacto:</strong> Representan cupos no aprovechados en la modalidad virtual del segundo lote.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-exclamation-triangle"></i> Campistas sin asistencia: <span class="badge badge-total" id="desc-virtual-sin-asistencia-l2">0</span></h5>
                        <h5><i class="fa-solid fa-calculator"></i> Total matriculados: <span class="badge badge-total" id="desc-virtual-total-matriculados-l2">0</span></h5>
                        <hr>
                        <h4><i class="fa-solid fa-chart-line"></i> Proyección real para meta: <span class="badge" style="background-color: #28a745; color: white; font-size: 1.1rem;" id="desc-virtual-proyeccion-l2">0</span></h4>
                    </div>
                    <div class="warning-note">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Recomendación:</strong> Verificar el estado de estos campistas para evaluar si procede la desmatrícula y optimizar el uso de cupos virtuales.
                    </div>
                `,
                container: 'descripcionVirtualContentL2'
            },
            'virtual-pendientes': {
                title: '⏳ Programas Sin Atención - Virtual Lote 2',
                content: `
                    <p><strong>Descripción:</strong> Programas académicos que aún no han recibido implementación de bootcamps virtuales en el Lote 2.</p>
                    <p><strong>Oportunidad:</strong> Potencial de expansión de la oferta virtual y mayor cobertura en el segundo lote.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-hourglass-half"></i> Total Pendientes: <span class="badge badge-total" id="desc-virtual-pendientes-l2">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> La modalidad virtual ofrece escalabilidad y menor costo por participante en el Lote 2.
                    </div>
                `,
                container: 'descripcionVirtualContentL2'
            }
        };

        const desc = descriptions[tipo];
        if (desc) {
            const container = document.getElementById(desc.container);
            container.innerHTML = `
                <h4 class="mb-3"><strong>${desc.title}</strong></h4>
                ${desc.content}
            `;

            // Actualizar valores en la descripción
            actualizarValoresDescripcionL2(tipo);
        }
    }

    // Función para actualizar valores en las descripciones Lote 2
    function actualizarValoresDescripcionL2(tipo) {
        setTimeout(() => {
            switch(tipo) {
                case 'presencial-matriculados':
                    const presencialMatriculados = document.getElementById('totalBootcampsPresencialesLoteDos').textContent;
                    const descPresencialMatriculados = document.getElementById('desc-presencial-matriculados-l2');
                    if (descPresencialMatriculados) {
                        descPresencialMatriculados.textContent = presencialMatriculados;
                    }
                    break;
                
                case 'presencial-aprobados':
                    const presencialAprobados = document.getElementById('totalBootcampsPresencialesLoteDosAprobados').textContent;
                    const descPresencialAprobados = document.getElementById('desc-presencial-aprobados-l2');
                    if (descPresencialAprobados) {
                        descPresencialAprobados.textContent = presencialAprobados;
                    }
                    break;
                
                case 'presencial-sin-asistencia':
                    const sinAsistenciaPresencial = document.getElementById('totalCursosSinAsistenciaLoteDos').textContent;
                    const totalMatriculadosPresencial = document.getElementById('totalBootcampsPresencialesLoteDos').textContent;
                    const proyeccionPresencial = parseInt(totalMatriculadosPresencial) - parseInt(sinAsistenciaPresencial);
                    
                    const descSinAsistenciaPresencial = document.getElementById('desc-presencial-sin-asistencia-l2');
                    const descTotalMatriculadosPresencial = document.getElementById('desc-presencial-total-matriculados-l2');
                    const descProyeccionPresencial = document.getElementById('desc-presencial-proyeccion-l2');
                    
                    if (descSinAsistenciaPresencial) descSinAsistenciaPresencial.textContent = sinAsistenciaPresencial;
                    if (descTotalMatriculadosPresencial) descTotalMatriculadosPresencial.textContent = totalMatriculadosPresencial;
                    if (descProyeccionPresencial) descProyeccionPresencial.textContent = proyeccionPresencial;
                    break;
                
                case 'presencial-pendientes':
                    const presencialPendientes = document.getElementById('totalProgramasPresencialesPendientesLoteDos').textContent;
                    const descPresencialPendientes = document.getElementById('desc-presencial-pendientes-l2');
                    if (descPresencialPendientes) {
                        descPresencialPendientes.textContent = presencialPendientes;
                    }
                    break;
                
                case 'virtual-matriculados':
                    const virtualMatriculados = document.getElementById('totalBootcampsVirtualesLoteDos').textContent;
                    const descVirtualMatriculados = document.getElementById('desc-virtual-matriculados-l2');
                    if (descVirtualMatriculados) {
                        descVirtualMatriculados.textContent = virtualMatriculados;
                    }
                    break;
                
                case 'virtual-aprobados':
                    const virtualAprobados = document.getElementById('totalBootcampsVirtualesLoteDosAprobados').textContent;
                    const descVirtualAprobados = document.getElementById('desc-virtual-aprobados-l2');
                    if (descVirtualAprobados) {
                        descVirtualAprobados.textContent = virtualAprobados;
                    }
                    break;
                
                case 'virtual-sin-asistencia':
                    const sinAsistenciaVirtual = document.getElementById('totalCursosSinAsistenciaLoteDosVirtual').textContent;
                    const totalMatriculadosVirtual = document.getElementById('totalBootcampsVirtualesLoteDos').textContent;
                    const proyeccionVirtual = parseInt(totalMatriculadosVirtual) - parseInt(sinAsistenciaVirtual);
                    
                    const descSinAsistenciaVirtual = document.getElementById('desc-virtual-sin-asistencia-l2');
                    const descTotalMatriculadosVirtual = document.getElementById('desc-virtual-total-matriculados-l2');
                    const descProyeccionVirtual = document.getElementById('desc-virtual-proyeccion-l2');
                    
                    if (descSinAsistenciaVirtual) descSinAsistenciaVirtual.textContent = sinAsistenciaVirtual;
                    if (descTotalMatriculadosVirtual) descTotalMatriculadosVirtual.textContent = totalMatriculadosVirtual;
                    if (descProyeccionVirtual) descProyeccionVirtual.textContent = proyeccionVirtual;
                    break;
                
                case 'virtual-pendientes':
                    const virtualPendientes = document.getElementById('totalProgramasVirtualesPendientesLoteDos').textContent;
                    const descVirtualPendientes = document.getElementById('desc-virtual-pendientes-l2');
                    if (descVirtualPendientes) {
                        descVirtualPendientes.textContent = virtualPendientes;
                    }
                    break;
            }
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Presenciales
        const bootcampList = document.getElementById('bootcampPresencialListLoteDos');
        const totalBootcamps = document.getElementById('totalBootcampsPresencialesLoteDos');
        const bootcampListAprobados = document.getElementById('bootcampPresencialListLoteDosAprobados');
        const totalBootcampsAprobados = document.getElementById('totalBootcampsPresencialesLoteDosAprobados');
        const programasPendientesList = document.getElementById('programasPresencialesPendientesListLoteDos');
        const totalProgramasPendientes = document.getElementById('totalProgramasPresencialesPendientesLoteDos');
        // Virtuales
        const bootcampVirtualList = document.getElementById('bootcampVirtualListLoteDos');
        const totalBootcampsVirtuales = document.getElementById('totalBootcampsVirtualesLoteDos');
        const bootcampVirtualListAprobados = document.getElementById('bootcampVirtualListLoteDosAprobados');
        const totalBootcampsVirtualesAprobados = document.getElementById('totalBootcampsVirtualesLoteDosAprobados');
        const programasVirtualesPendientesList = document.getElementById('programasVirtualesPendientesListLoteDos');
        const totalProgramasVirtualesPendientes = document.getElementById('totalProgramasVirtualesPendientesLoteDos');
        // Sin Asistencia
        const cursosSinAsistenciaListLoteDos = document.getElementById('cursosSinAsistenciaListLoteDos');
        const totalCursosSinAsistenciaLoteDos = document.getElementById('totalCursosSinAsistenciaLoteDos');
        const cursosSinAsistenciaListLoteDosVirtual = document.getElementById('cursosSinAsistenciaListLoteDosVirtual');
        const totalCursosSinAsistenciaLoteDosVirtual = document.getElementById('totalCursosSinAsistenciaLoteDosVirtual');

        // Contrapartida Lote 2: Variables
        const contrapartidaPresencialMatriculadosListL2 = document.getElementById('contrapartidaPresencialMatriculadosListL2');
        const contrapartidaPresencialMatriculadosTotalL2 = document.getElementById('contrapartidaPresencialMatriculadosTotalL2');
        const contrapartidaPresencialAprobadosListL2 = document.getElementById('contrapartidaPresencialAprobadosListL2');
        const contrapartidaPresencialAprobadosTotalL2 = document.getElementById('contrapartidaPresencialAprobadosTotalL2');
        const contrapartidaVirtualMatriculadosListL2 = document.getElementById('contrapartidaVirtualMatriculadosListL2');
        const contrapartidaVirtualMatriculadosTotalL2 = document.getElementById('contrapartidaVirtualMatriculadosTotalL2');
        const contrapartidaVirtualAprobadosListL2 = document.getElementById('contrapartidaVirtualAprobadosListL2');
        const contrapartidaVirtualAprobadosTotalL2 = document.getElementById('contrapartidaVirtualAprobadosTotalL2');

        async function cargarBootcampsPresenciales() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsPresencialesLote2;

                if (!bootcamps || bootcamps.length === 0) {
                    bootcampList.innerHTML = '<p class="text-muted text-center">No se encontraron bootcamps presenciales.</p>';
                    totalBootcamps.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, bootcamp) => sum + parseInt(bootcamp.cantidad), 0);
                totalBootcamps.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((bootcamp) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${bootcamp.bootcamp}</span>
                        <span class="badge badge-grupo">${bootcamp.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                bootcampList.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar bootcamps presenciales:', error);
                bootcampList.innerHTML = '<p class="text-danger text-center">Error al cargar bootcamps presenciales.</p>';
                totalBootcamps.textContent = '0';
            }
        }

        async function cargarBootcampsPresencialesAprobados() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsPresencialesLote2Aprobados;

                if (!bootcamps || bootcamps.length === 0) {
                    bootcampListAprobados.innerHTML = '<p class="text-muted text-center">No se encontraron aprobados.</p>';
                    totalBootcampsAprobados.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, bootcamp) => sum + parseInt(bootcamp.cantidad), 0);
                totalBootcampsAprobados.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((bootcamp) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${bootcamp.bootcamp}</span>
                        <span class="badge badge-grupo">${bootcamp.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                bootcampListAprobados.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar aprobados:', error);
                bootcampListAprobados.innerHTML = '<p class="text-danger text-center">Error al cargar aprobados.</p>';
                totalBootcampsAprobados.textContent = '0';
            }
        }

        async function cargarProgramasPresencialesPendientes() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const programas = datos.programasPresencialesPendientesLote2;

                if (!programas || programas.length === 0) {
                    programasPendientesList.innerHTML = '<p class="text-muted text-center">No se encontraron programas pendientes.</p>';
                    totalProgramasPendientes.textContent = '0';
                    return;
                }

                const total = programas.reduce((sum, programa) => sum + parseInt(programa.cantidad), 0);
                totalProgramasPendientes.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                programas.forEach((programa) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${programa.program}</span>
                        <span class="badge badge-grupo">${programa.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                programasPendientesList.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar programas pendientes:', error);
                programasPendientesList.innerHTML = '<p class="text-danger text-center">Error al cargar programas pendientes.</p>';
                totalProgramasPendientes.textContent = '0';
            }
        }

        // Virtuales
        async function cargarBootcampsVirtuales() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsVirtualesLote2;

                if (!bootcamps || bootcamps.length === 0) {
                    bootcampVirtualList.innerHTML = '<p class="text-muted text-center">No se encontraron bootcamps virtuales.</p>';
                    totalBootcampsVirtuales.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, bootcamp) => sum + parseInt(bootcamp.cantidad), 0);
                totalBootcampsVirtuales.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((bootcamp) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${bootcamp.bootcamp}</span>
                        <span class="badge badge-grupo">${bootcamp.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                bootcampVirtualList.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar bootcamps virtuales:', error);
                bootcampVirtualList.innerHTML = '<p class="text-danger text-center">Error al cargar bootcamps virtuales.</p>';
                totalBootcampsVirtuales.textContent = '0';
            }
        }

        async function cargarBootcampsVirtualesAprobados() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsVirtualesLote2Aprobados;

                if (!bootcamps || bootcamps.length === 0) {
                    bootcampVirtualListAprobados.innerHTML = '<p class="text-muted text-center">No se encontraron aprobados virtuales.</p>';
                    totalBootcampsVirtualesAprobados.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, bootcamp) => sum + parseInt(bootcamp.cantidad), 0);
                totalBootcampsVirtualesAprobados.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((bootcamp) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${bootcamp.bootcamp}</span>
                        <span class="badge badge-grupo">${bootcamp.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                bootcampVirtualListAprobados.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar aprobados virtuales:', error);
                bootcampVirtualListAprobados.innerHTML = '<p class="text-danger text-center">Error al cargar aprobados virtuales.</p>';
                totalBootcampsVirtualesAprobados.textContent = '0';
            }
        }

        async function cargarProgramasVirtualesPendientes() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const programas = datos.programasVirtualesPendientesLote2;

                if (!programas || programas.length === 0) {
                    programasVirtualesPendientesList.innerHTML = '<p class="text-muted text-center">No se encontraron programas pendientes virtuales.</p>';
                    totalProgramasVirtualesPendientes.textContent = '0';
                    return;
                }

                const total = programas.reduce((sum, programa) => sum + parseInt(programa.cantidad), 0);
                totalProgramasVirtualesPendientes.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                programas.forEach((programa) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${programa.program}</span>
                        <span class="badge badge-grupo">${programa.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                programasVirtualesPendientesList.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar programas pendientes virtuales:', error);
                programasVirtualesPendientesList.innerHTML = '<p class="text-danger text-center">Error al cargar programas pendientes virtuales.</p>';
                totalProgramasVirtualesPendientes.textContent = '0';
            }
        }

        async function cargarCursosSinAsistenciaLoteDos() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const cursos = datos.cursosSinAsistenciaLote2;

                if (!cursos || cursos.length === 0) {
                    cursosSinAsistenciaListLoteDos.innerHTML = '<p class="text-muted text-center">No se encontraron campistas sin asistencia.</p>';
                    totalCursosSinAsistenciaLoteDos.textContent = '0';
                    return;
                }

                // Sumar inscritos de todos los cursos
                const totalInscritosSinAsistencia = cursos.reduce((sum, curso) => sum + parseInt(curso.inscritos), 0);
                totalCursosSinAsistenciaLoteDos.textContent = totalInscritosSinAsistencia;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                cursos.forEach((curso) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${curso.nombre}</span>
                        <span class="badge badge-grupo">${curso.inscritos}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                cursosSinAsistenciaListLoteDos.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar cursos sin asistencia:', error);
                cursosSinAsistenciaListLoteDos.innerHTML = '<p class="text-danger text-center">Error al cargar campistas sin asistencia.</p>';
                totalCursosSinAsistenciaLoteDos.textContent = '0';
            }
        }

        async function cargarCursosSinAsistenciaLoteDosVirtual() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const cursos = datos.cursosSinAsistenciaLote2Virtual;

                if (!cursos || cursos.length === 0) {
                    cursosSinAsistenciaListLoteDosVirtual.innerHTML = '<p class="text-muted text-center">No se encontraron campistas sin asistencia.</p>';
                    totalCursosSinAsistenciaLoteDosVirtual.textContent = '0';
                    return;
                }

                // Sumar inscritos de todos los cursos virtuales
                const totalInscritosSinAsistenciaVirtual = cursos.reduce((sum, curso) => sum + parseInt(curso.inscritos), 0);
                totalCursosSinAsistenciaLoteDosVirtual.textContent = totalInscritosSinAsistenciaVirtual;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                cursos.forEach((curso) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${curso.nombre}</span>
                        <span class="badge badge-grupo">${curso.inscritos}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                cursosSinAsistenciaListLoteDosVirtual.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar cursos sin asistencia virtual:', error);
                cursosSinAsistenciaListLoteDosVirtual.innerHTML = '<p class="text-danger text-center">Error al cargar campistas sin asistencia.</p>';
                totalCursosSinAsistenciaLoteDosVirtual.textContent = '0';
            }
        }

        // Contrapartida Lote 2: Funciones
        async function cargarContrapartidaPresencialMatriculadosL2() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaPresencialMatriculadosL2;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaPresencialMatriculadosListL2.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaPresencialMatriculadosTotalL2.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaPresencialMatriculadosTotalL2.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((b) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${b.bootcamp}</span>
                        <span class="badge badge-grupo">${b.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';
                contrapartidaPresencialMatriculadosListL2.innerHTML = listHtml;
            } catch (error) {
                contrapartidaPresencialMatriculadosListL2.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaPresencialMatriculadosTotalL2.textContent = '0';
            }
        }

        async function cargarContrapartidaPresencialAprobadosL2() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaPresencialAprobadosL2;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaPresencialAprobadosListL2.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaPresencialAprobadosTotalL2.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaPresencialAprobadosTotalL2.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((b) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${b.bootcamp}</span>
                        <span class="badge badge-grupo">${b.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';
                contrapartidaPresencialAprobadosListL2.innerHTML = listHtml;
            } catch (error) {
                contrapartidaPresencialAprobadosListL2.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaPresencialAprobadosTotalL2.textContent = '0';
            }
        }

        async function cargarContrapartidaVirtualMatriculadosL2() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaVirtualMatriculadosL2;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaVirtualMatriculadosListL2.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaVirtualMatriculadosTotalL2.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaVirtualMatriculadosTotalL2.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((b) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${b.bootcamp}</span>
                        <span class="badge badge-grupo">${b.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';
                contrapartidaVirtualMatriculadosListL2.innerHTML = listHtml;
            } catch (error) {
                contrapartidaVirtualMatriculadosListL2.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaVirtualMatriculadosTotalL2.textContent = '0';
            }
        }

        async function cargarContrapartidaVirtualAprobadosL2() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaVirtualAprobadosL2;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaVirtualAprobadosListL2.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaVirtualAprobadosTotalL2.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaVirtualAprobadosTotalL2.textContent = total;

                let listHtml = '<ul class="list-group" style="max-height:220px;overflow-y:auto;">';
                bootcamps.forEach((b) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:0.95rem;">${b.bootcamp}</span>
                        <span class="badge badge-grupo">${b.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';
                contrapartidaVirtualAprobadosListL2.innerHTML = listHtml;
            } catch (error) {
                contrapartidaVirtualAprobadosListL2.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaVirtualAprobadosTotalL2.textContent = '0';
            }
        }

        // Cargar datos inicial (agregar estas llamadas)
        await cargarBootcampsPresenciales();
        await cargarBootcampsPresencialesAprobados();
        await cargarProgramasPresencialesPendientes();
        await cargarBootcampsVirtuales();
        await cargarBootcampsVirtualesAprobados();
        await cargarProgramasVirtualesPendientes();
        await cargarCursosSinAsistenciaLoteDos();
        await cargarCursosSinAsistenciaLoteDosVirtual();
        // Nuevas funciones de contrapartida
        await cargarContrapartidaPresencialMatriculadosL2();
        await cargarContrapartidaPresencialAprobadosL2();
        await cargarContrapartidaVirtualMatriculadosL2();
        await cargarContrapartidaVirtualAprobadosL2();

        setInterval(() => {
            cargarBootcampsPresenciales();
            cargarBootcampsPresencialesAprobados();
            cargarProgramasPresencialesPendientes();
            cargarBootcampsVirtuales();
            cargarBootcampsVirtualesAprobados();
            cargarProgramasVirtualesPendientes();
            cargarCursosSinAsistenciaLoteDos();
            cargarCursosSinAsistenciaLoteDosVirtual();
            // Nuevas funciones de contrapartida en el intervalo
            cargarContrapartidaPresencialMatriculadosL2();
            cargarContrapartidaPresencialAprobadosL2();
            cargarContrapartidaVirtualMatriculadosL2();
            cargarContrapartidaVirtualAprobadosL2();
        }, 10000); // Actualiza todas cada 10 segundos
    });
</script>