<!-- Tarjetas: Inscritos en Bootcamps Presenciales y Virtuales Lote 1 -->
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
        width: 111.11%;
        margin-left: -5.55%;
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

    <!-- Contrapartida: Participantes (Presenciales/Virtuales, Matriculados/Aprobados) -->
    <div class="row">
        <!-- Presenciales Matriculados Contrapartida -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-chalkboard-teacher"></i>
                                Presenciales Lote 1 matriculados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaPresencialMatriculadosList" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaPresencialMatriculadosTotal" class="badge badge-total">0</span> matriculados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Presenciales Aprobados Contrapartida -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i>
                                Presenciales Lote 1 aprobados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaPresencialAprobadosList" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaPresencialAprobadosTotal" class="badge badge-total">0</span> aprobados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Virtuales Matriculados Contrapartida -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-laptop"></i>
                                Virtuales Lote 1 matriculados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaVirtualMatriculadosList" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaVirtualMatriculadosTotal" class="badge badge-total">0</span> matriculados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Virtuales Aprobados Contrapartida -->
        <div class="col-md-3">
            <div class="card h-100 bg-indigo-light" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i>
                                Virtuales Lote 1 aprobados (Adicionales)
                                <span class="ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Incluye los valores actuales más los campistas adicionales">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </b>
                        </h5>
                    </div>
                    <div id="contrapartidaVirtualAprobadosList" class="flex-grow-1 bg-white h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando datos...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="contrapartidaVirtualAprobadosTotal" class="badge badge-total">0</span> aprobados
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row pt-4">
        <!-- Presenciales -->
        <div class="col-md-3">
            <!-- Matriculados -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-chalkboard-teacher"></i> Presenciales Lote 1 matriculados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampPresencialListLoteUno" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando bootcamps presenciales...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsPresencialesLoteUno" class="badge badge-total">0</span> matriculados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('presencial-matriculados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencial">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <!-- Aprobados -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i> Presenciales Lote 1 Aprobados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampPresencialListLoteUnoAprobados" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando aprobados...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsPresencialesLoteUnoAprobados" class="badge badge-total">0</span> aprobados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('presencial-aprobados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencial">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cursos sin asistencias (Presenciales Lote 1) -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Campistas sin asistencia Lote 1
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteUno" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando campistas sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteUno" class="badge badge-total">0</span> Campistas sin asistencia
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('presencial-sin-asistencia')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencial">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <!-- Programas pendientes -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-hourglass-half"></i> Sin atención Lote 1 Presenciales
                            </b>
                        </h5>
                    </div>
                    <div id="programasPresencialesPendientesList" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando programas pendientes...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalProgramasPresencialesPendientes" class="badge badge-total">0</span> pendientes
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('presencial-pendientes')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapsePresencial">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Colapsable para Presenciales -->
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="collapse" id="collapsePresencial">
                <div class="description-collapse">
                    <div class="card description-card">
                        <div class="card-body d-flex justify-content-center align-items-center flex-column" style="text-align: center;">
                            <div id="descripcionContent" class="w-100" style="text-align: center;">
                                <!-- El contenido se cargará dinámicamente aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Virtuales -->
    <div class="row pt-4">
        <div class="col-md-3">
            <!-- Matriculados Virtual -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-laptop"></i> Virtuales Lote 1 matriculados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampVirtualListLoteUno" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando bootcamps virtuales...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsVirtualesLoteUno" class="badge badge-total">0</span> matriculados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('virtual-matriculados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtual">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <!-- Aprobados Virtual -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-check-circle"></i> Virtuales Lote 1 Aprobados
                            </b>
                        </h5>
                    </div>
                    <div id="bootcampVirtualListLoteUnoAprobados" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando aprobados virtuales...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalBootcampsVirtualesLoteUnoAprobados" class="badge badge-total">0</span> aprobados
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('virtual-aprobados')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtual">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cursos sin asistencias (Virtuales Lote 1) -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Campistas sin asistencia Lote 1 Virtuales
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteUnoVirtual" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando campistas sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteUnoVirtual" class="badge badge-total">0</span> campistas sin asistencia
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('virtual-sin-asistencia')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtual">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <!-- Programas pendientes Virtual -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 text-center">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-hourglass-half"></i> Sin atención Lote 1 Virtuales
                            </b>
                        </h5>
                    </div>
                    <div id="programasVirtualesPendientesList" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando programas pendientes...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalProgramasVirtualesPendientes" class="badge badge-total">0</span> pendientes
                        </h5>
                        <button class="btn bg-indigo-dark text-white btn-sm btn-description mt-2" 
                                onclick="mostrarDescripcion('virtual-pendientes')" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseVirtual">
                            <i class="bi bi-info-circle"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Colapsable para Virtuales -->
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="collapse" id="collapseVirtual">
                <div class="description-collapse">
                    <div class="card description-card">
                        <div class="card-body d-flex justify-content-center align-items-center flex-column" style="text-align: center;">
                            <div id="descripcionVirtualContent" class="w-100" style="text-align: center;">
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
    // Función para mostrar descripción de las tarjetas
    function mostrarDescripcion(tipo) {
        const descriptions = {
            'presencial-matriculados': {
                title: '📊 Presenciales Matriculados - Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Esta tarjeta muestra el número total de participantes matriculados en bootcamps presenciales del Lote 1.</p>
                    <p><strong>Qué incluye:</strong> Todos los campistas que han completado exitosamente el proceso de inscripción y matrícula en modalidad presencial.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-users"></i> Total de Matriculados: <span class="badge badge-total" id="desc-presencial-matriculados">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Este número representa la capacidad instalada actual para la modalidad presencial en el Lote 1.
                    </div>
                `,
                container: 'descripcionContent'
            },
            'presencial-aprobados': {
                title: '✅ Presenciales Aprobados - Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Muestra el número de participantes que han completado exitosamente su bootcamp presencial y han sido aprobados.</p>
                    <p><strong>Criterios de aprobación:</strong> Campistas que cumplieron con los requisitos mínimos de asistencia, participación y evaluaciones.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-certificate"></i> Total de Aprobados: <span class="badge badge-total" id="desc-presencial-aprobados">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Estos participantes contribuyen directamente al cumplimiento de las metas establecidas.
                    </div>
                `,
                container: 'descripcionContent'
            },
            'presencial-sin-asistencia': {
                title: '⚠️ Campistas Sin Asistencia - Presencial Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Participantes matriculados que no han registrado ninguna asistencia en sus respectivos bootcamps presenciales.</p>
                    <p><strong>Impacto:</strong> Estos campistas están ocupando cupos pero no están participando activamente en la formación.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-exclamation-triangle"></i> Campistas sin asistencia: <span class="badge badge-total" id="desc-presencial-sin-asistencia">0</span></h5>
                        <h5><i class="fa-solid fa-calculator"></i> Total matriculados: <span class="badge badge-total" id="desc-presencial-total-matriculados">0</span></h5>
                        <hr>
                        <h4><i class="fa-solid fa-chart-line"></i> Proyección real para meta: <span class="badge" style="background-color: #28a745; color: white; font-size: 1.1rem;" id="desc-presencial-proyeccion">0</span></h4>
                    </div>
                    <div class="warning-note">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Recomendación:</strong> Se sugiere verificar el estado de estos campistas para evaluar si procede la desmatrícula y liberar cupos para nuevos participantes.
                    </div>
                `,
                container: 'descripcionContent'
            },
            'presencial-pendientes': {
                title: '⏳ Programas Sin Atención - Presencial Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Programas académicos que aún no han recibido atención o implementación de bootcamps presenciales en el Lote 1.</p>
                    <p><strong>Oportunidad:</strong> Representa la demanda potencial y los cupos disponibles para futuras cohortes.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-hourglass-half"></i> Total Pendientes: <span class="badge badge-total" id="desc-presencial-pendientes">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Estos programas representan oportunidades de crecimiento y expansión de la cobertura.
                    </div>
                `,
                container: 'descripcionContent'
            },
            'virtual-matriculados': {
                title: '💻 Virtuales Matriculados - Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Esta tarjeta muestra el número total de participantes matriculados en bootcamps virtuales del Lote 1.</p>
                    <p><strong>Qué incluye:</strong> Todos los campistas que han completado exitosamente el proceso de inscripción y matrícula en modalidad virtual.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-laptop"></i> Total de Matriculados: <span class="badge badge-total" id="desc-virtual-matriculados">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> La modalidad virtual permite mayor flexibilidad y alcance geográfico.
                    </div>
                `,
                container: 'descripcionVirtualContent'
            },
            'virtual-aprobados': {
                title: '✅ Virtuales Aprobados - Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Participantes que han completado exitosamente su bootcamp virtual y han sido aprobados.</p>
                    <p><strong>Criterios de aprobación:</strong> Campistas que cumplieron con los requisitos de participación, entregas y evaluaciones en la modalidad virtual.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-certificate"></i> Total de Aprobados: <span class="badge badge-total" id="desc-virtual-aprobados">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> Estos participantes contribuyen al cumplimiento de las metas de formación virtual.
                    </div>
                `,
                container: 'descripcionVirtualContent'
            },
            'virtual-sin-asistencia': {
                title: '⚠️ Campistas Sin Asistencia - Virtual Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Participantes matriculados que no han registrado actividad en sus bootcamps virtuales.</p>
                    <p><strong>Impacto:</strong> Representan cupos no aprovechados en la modalidad virtual.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-exclamation-triangle"></i> Campistas sin asistencia: <span class="badge badge-total" id="desc-virtual-sin-asistencia">0</span></h5>
                        <h5><i class="fa-solid fa-calculator"></i> Total matriculados: <span class="badge badge-total" id="desc-virtual-total-matriculados">0</span></h5>
                        <hr>
                        <h4><i class="fa-solid fa-chart-line"></i> Proyección real para meta: <span class="badge" style="background-color: #28a745; color: white; font-size: 1.1rem;" id="desc-virtual-proyeccion">0</span></h4>
                    </div>
                    <div class="warning-note">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Recomendación:</strong> Verificar el estado de estos campistas para evaluar si procede la desmatrícula y optimizar el uso de cupos virtuales.
                    </div>
                `,
                container: 'descripcionVirtualContent'
            },
            'virtual-pendientes': {
                title: '⏳ Programas Sin Atención - Virtual Lote 1',
                content: `
                    <p><strong>Descripción:</strong> Programas académicos que aún no han recibido implementación de bootcamps virtuales en el Lote 1.</p>
                    <p><strong>Oportunidad:</strong> Potencial de expansión de la oferta virtual y mayor cobertura.</p>
                    <div class="highlight-total">
                        <h5><i class="fa-solid fa-hourglass-half"></i> Total Pendientes: <span class="badge badge-total" id="desc-virtual-pendientes">0</span></h5>
                    </div>
                    <div class="alert alert-info-custom">
                        <i class="bi bi-info-circle-fill"></i> <strong>Nota:</strong> La modalidad virtual ofrece escalabilidad y menor costo por participante.
                    </div>
                `,
                container: 'descripcionVirtualContent'
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
            actualizarValoresDescripcion(tipo);
        }
    }

    // Función para actualizar valores en las descripciones
    function actualizarValoresDescripcion(tipo) {
        setTimeout(() => {
            switch(tipo) {
                case 'presencial-matriculados':
                    const presencialMatriculados = document.getElementById('totalBootcampsPresencialesLoteUno').textContent;
                    const descPresencialMatriculados = document.getElementById('desc-presencial-matriculados');
                    if (descPresencialMatriculados) {
                        descPresencialMatriculados.textContent = presencialMatriculados;
                    }
                    break;
                
                case 'presencial-aprobados':
                    const presencialAprobados = document.getElementById('totalBootcampsPresencialesLoteUnoAprobados').textContent;
                    const descPresencialAprobados = document.getElementById('desc-presencial-aprobados');
                    if (descPresencialAprobados) {
                        descPresencialAprobados.textContent = presencialAprobados;
                    }
                    break;
                
                case 'presencial-sin-asistencia':
                    const sinAsistenciaPresencial = document.getElementById('totalCursosSinAsistenciaLoteUno').textContent;
                    const totalMatriculadosPresencial = document.getElementById('totalBootcampsPresencialesLoteUno').textContent;
                    const proyeccionPresencial = parseInt(totalMatriculadosPresencial) - parseInt(sinAsistenciaPresencial);
                    
                    const descSinAsistenciaPresencial = document.getElementById('desc-presencial-sin-asistencia');
                    const descTotalMatriculadosPresencial = document.getElementById('desc-presencial-total-matriculados');
                    const descProyeccionPresencial = document.getElementById('desc-presencial-proyeccion');
                    
                    if (descSinAsistenciaPresencial) descSinAsistenciaPresencial.textContent = sinAsistenciaPresencial;
                    if (descTotalMatriculadosPresencial) descTotalMatriculadosPresencial.textContent = totalMatriculadosPresencial;
                    if (descProyeccionPresencial) descProyeccionPresencial.textContent = proyeccionPresencial;
                    break;
                
                case 'presencial-pendientes':
                    const presencialPendientes = document.getElementById('totalProgramasPresencialesPendientes').textContent;
                    const descPresencialPendientes = document.getElementById('desc-presencial-pendientes');
                    if (descPresencialPendientes) {
                        descPresencialPendientes.textContent = presencialPendientes;
                    }
                    break;
                
                case 'virtual-matriculados':
                    const virtualMatriculados = document.getElementById('totalBootcampsVirtualesLoteUno').textContent;
                    const descVirtualMatriculados = document.getElementById('desc-virtual-matriculados');
                    if (descVirtualMatriculados) {
                        descVirtualMatriculados.textContent = virtualMatriculados;
                    }
                    break;
                
                case 'virtual-aprobados':
                    const virtualAprobados = document.getElementById('totalBootcampsVirtualesLoteUnoAprobados').textContent;
                    const descVirtualAprobados = document.getElementById('desc-virtual-aprobados');
                    if (descVirtualAprobados) {
                        descVirtualAprobados.textContent = virtualAprobados;
                    }
                    break;
                
                case 'virtual-sin-asistencia':
                    const sinAsistenciaVirtual = document.getElementById('totalCursosSinAsistenciaLoteUnoVirtual').textContent;
                    const totalMatriculadosVirtual = document.getElementById('totalBootcampsVirtualesLoteUno').textContent;
                    const proyeccionVirtual = parseInt(totalMatriculadosVirtual) - parseInt(sinAsistenciaVirtual);
                    
                    const descSinAsistenciaVirtual = document.getElementById('desc-virtual-sin-asistencia');
                    const descTotalMatriculadosVirtual = document.getElementById('desc-virtual-total-matriculados');
                    const descProyeccionVirtual = document.getElementById('desc-virtual-proyeccion');
                    
                    if (descSinAsistenciaVirtual) descSinAsistenciaVirtual.textContent = sinAsistenciaVirtual;
                    if (descTotalMatriculadosVirtual) descTotalMatriculadosVirtual.textContent = totalMatriculadosVirtual;
                    if (descProyeccionVirtual) descProyeccionVirtual.textContent = proyeccionVirtual;
                    break;
                
                case 'virtual-pendientes':
                    const virtualPendientes = document.getElementById('totalProgramasVirtualesPendientes').textContent;
                    const descVirtualPendientes = document.getElementById('desc-virtual-pendientes');
                    if (descVirtualPendientes) {
                        descVirtualPendientes.textContent = virtualPendientes;
                    }
                    break;
            }
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Presenciales
        const bootcampList = document.getElementById('bootcampPresencialListLoteUno');
        const totalBootcamps = document.getElementById('totalBootcampsPresencialesLoteUno');
        const bootcampListAprobados = document.getElementById('bootcampPresencialListLoteUnoAprobados');
        const totalBootcampsAprobados = document.getElementById('totalBootcampsPresencialesLoteUnoAprobados');
        const programasPendientesList = document.getElementById('programasPresencialesPendientesList');
        const totalProgramasPendientes = document.getElementById('totalProgramasPresencialesPendientes');
        // Virtuales
        const bootcampVirtualList = document.getElementById('bootcampVirtualListLoteUno');
        const totalBootcampsVirtuales = document.getElementById('totalBootcampsVirtualesLoteUno');
        const bootcampVirtualListAprobados = document.getElementById('bootcampVirtualListLoteUnoAprobados');
        const totalBootcampsVirtualesAprobados = document.getElementById('totalBootcampsVirtualesLoteUnoAprobados');
        const programasVirtualesPendientesList = document.getElementById('programasVirtualesPendientesList');
        const totalProgramasVirtualesPendientes = document.getElementById('totalProgramasVirtualesPendientes');
        // Cursos sin asistencia
        const cursosSinAsistenciaList = document.getElementById('cursosSinAsistenciaListLoteUno');
        const totalCursosSinAsistencia = document.getElementById('totalCursosSinAsistenciaLoteUno');
        const cursosSinAsistenciaListVirtual = document.getElementById('cursosSinAsistenciaListLoteUnoVirtual');
        const totalCursosSinAsistenciaVirtual = document.getElementById('totalCursosSinAsistenciaLoteUnoVirtual');

        // Contrapartida: Presenciales Matriculados
        const contrapartidaPresencialMatriculadosList = document.getElementById('contrapartidaPresencialMatriculadosList');
        const contrapartidaPresencialMatriculadosTotal = document.getElementById('contrapartidaPresencialMatriculadosTotal');
        // Contrapartida: Presenciales Aprobados
        const contrapartidaPresencialAprobadosList = document.getElementById('contrapartidaPresencialAprobadosList');
        const contrapartidaPresencialAprobadosTotal = document.getElementById('contrapartidaPresencialAprobadosTotal');
        // Contrapartida: Virtuales Matriculados
        const contrapartidaVirtualMatriculadosList = document.getElementById('contrapartidaVirtualMatriculadosList');
        const contrapartidaVirtualMatriculadosTotal = document.getElementById('contrapartidaVirtualMatriculadosTotal');
        // Contrapartida: Virtuales Aprobados
        const contrapartidaVirtualAprobadosList = document.getElementById('contrapartidaVirtualAprobadosList');
        const contrapartidaVirtualAprobadosTotal = document.getElementById('contrapartidaVirtualAprobadosTotal');

        

        async function cargarBootcampsPresenciales() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsPresencialesLote1;

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
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsPresencialesLote1Aprobados;

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
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const programas = datos.programasPresencialesPendientes;

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
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsVirtualesLote1;

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
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsVirtualesLote1Aprobados;

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
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const programas = datos.programasVirtualesPendientes;

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

        async function cargarCursosSinAsistencia() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const cursos = datos.cursosSinAsistenciaLote1;

                if (!cursos || cursos.length === 0) {
                    cursosSinAsistenciaList.innerHTML = '<p class="text-muted text-center">No se encontraron campistas sin asistencia.</p>';
                    totalCursosSinAsistencia.textContent = '0';
                    return;
                }

                // Sumar inscritos de todos los cursos
                const totalInscritosSinAsistencia = cursos.reduce((sum, curso) => sum + parseInt(curso.inscritos), 0);
                totalCursosSinAsistencia.textContent = totalInscritosSinAsistencia;

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

                cursosSinAsistenciaList.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar cursos sin asistencia:', error);
                cursosSinAsistenciaList.innerHTML = '<p class="text-danger text-center">Error al cargar campistas sin asistencia.</p>';
                totalCursosSinAsistencia.textContent = '0';
            }
        }

        async function cargarCursosSinAsistenciaVirtual() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const cursos = datos.cursosSinAsistenciaLote1Virtual;

                if (!cursos || cursos.length === 0) {
                    cursosSinAsistenciaListVirtual.innerHTML = '<p class="text-muted text-center">No se encontraron campistas sin asistencia.</p>';
                    totalCursosSinAsistenciaVirtual.textContent = '0';
                    return;
                }

                // Sumar inscritos de todos los cursos virtuales
                const totalInscritosSinAsistenciaVirtual = cursos.reduce((sum, curso) => sum + parseInt(curso.inscritos), 0);
                totalCursosSinAsistenciaVirtual.textContent = totalInscritosSinAsistenciaVirtual;

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

                cursosSinAsistenciaListVirtual.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar cursos sin asistencia virtual:', error);
                cursosSinAsistenciaListVirtual.innerHTML = '<p class="text-danger text-center">Error al cargar campistas sin asistencia.</p>';
                totalCursosSinAsistenciaVirtual.textContent = '0';
            }
        }

        async function cargarContrapartidaPresencialMatriculados() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaPresencialMatriculados;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaPresencialMatriculadosList.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaPresencialMatriculadosTotal.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaPresencialMatriculadosTotal.textContent = total;

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
                contrapartidaPresencialMatriculadosList.innerHTML = listHtml;
            } catch (error) {
                contrapartidaPresencialMatriculadosList.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaPresencialMatriculadosTotal.textContent = '0';
            }
        }

        async function cargarContrapartidaPresencialAprobados() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaPresencialAprobados;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaPresencialAprobadosList.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaPresencialAprobadosTotal.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaPresencialAprobadosTotal.textContent = total;

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
                contrapartidaPresencialAprobadosList.innerHTML = listHtml;
            } catch (error) {
                contrapartidaPresencialAprobadosList.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaPresencialAprobadosTotal.textContent = '0';
            }
        }

        async function cargarContrapartidaVirtualMatriculados() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaVirtualMatriculados;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaVirtualMatriculadosList.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaVirtualMatriculadosTotal.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaVirtualMatriculadosTotal.textContent = total;

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
                contrapartidaVirtualMatriculadosList.innerHTML = listHtml;
            } catch (error) {
                contrapartidaVirtualMatriculadosList.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaVirtualMatriculadosTotal.textContent = '0';
            }
        }

        async function cargarContrapartidaVirtualAprobados() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const bootcamps = datos.contrapartidaVirtualAprobados;

                if (!bootcamps || bootcamps.length === 0) {
                    contrapartidaVirtualAprobadosList.innerHTML = '<p class="text-muted text-center">No se encontraron datos.</p>';
                    contrapartidaVirtualAprobadosTotal.textContent = '0';
                    return;
                }

                const total = bootcamps.reduce((sum, b) => sum + parseInt(b.cantidad), 0);
                contrapartidaVirtualAprobadosTotal.textContent = total;

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
                contrapartidaVirtualAprobadosList.innerHTML = listHtml;
            } catch (error) {
                contrapartidaVirtualAprobadosList.innerHTML = '<p class="text-danger text-center">Error al cargar datos.</p>';
                contrapartidaVirtualAprobadosTotal.textContent = '0';
            }
        }

        // Inicial
        await cargarBootcampsPresenciales();
        await cargarBootcampsPresencialesAprobados();
        await cargarProgramasPresencialesPendientes();
        await cargarBootcampsVirtuales();
        await cargarBootcampsVirtualesAprobados();
        await cargarProgramasVirtualesPendientes();
        await cargarCursosSinAsistencia();
        await cargarCursosSinAsistenciaVirtual();
        await cargarContrapartidaPresencialMatriculados();
        await cargarContrapartidaPresencialAprobados();
        await cargarContrapartidaVirtualMatriculados();
        await cargarContrapartidaVirtualAprobados();

        setInterval(() => {
            cargarBootcampsPresenciales();
            cargarBootcampsPresencialesAprobados();
            cargarProgramasPresencialesPendientes();
            cargarBootcampsVirtuales();
            cargarBootcampsVirtualesAprobados();
            cargarProgramasVirtualesPendientes();
            cargarCursosSinAsistencia();
            cargarCursosSinAsistenciaVirtual();
            cargarContrapartidaPresencialMatriculados();
            cargarContrapartidaPresencialAprobados();
            cargarContrapartidaVirtualMatriculados();
            cargarContrapartidaVirtualAprobados();
        }, 10000); // Actualiza todas cada 10 segundos
    });
</script>