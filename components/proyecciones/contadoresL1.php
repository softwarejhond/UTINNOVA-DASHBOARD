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
        /* 1 / 0.9 = 1.111... */
        margin-left: -5.55%;
        /* Centra el contenido escalado */
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
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <!-- Aprobados -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Cursos sin asistencias (Presenciales Lote 1) -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Cursos sin asistencia Lote 1
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteUno" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando cursos sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteUno" class="badge badge-total">0</span> cursos sin asistencia
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <!-- Programas pendientes -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <!-- Aprobados Virtual -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Cursos sin asistencias (Virtuales Lote 1) -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Cursos sin asistencia Lote 1 Virtuales
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteUnoVirtual" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando cursos sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteUnoVirtual" class="badge badge-total">0</span> cursos sin asistencia
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <!-- Programas pendientes Virtual -->
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>

<script>
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
                    cursosSinAsistenciaList.innerHTML = '<p class="text-muted text-center">No se encontraron cursos sin asistencia.</p>';
                    totalCursosSinAsistencia.textContent = '0';
                    return;
                }

                totalCursosSinAsistencia.textContent = cursos.length;

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
                cursosSinAsistenciaList.innerHTML = '<p class="text-danger text-center">Error al cargar cursos sin asistencia.</p>';
                totalCursosSinAsistencia.textContent = '0';
            }
        }

        async function cargarCursosSinAsistenciaVirtual() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote1.PHP');
                const datos = await respuesta.json();
                const cursos = datos.cursosSinAsistenciaLote1Virtual;

                if (!cursos || cursos.length === 0) {
                    cursosSinAsistenciaListVirtual.innerHTML = '<p class="text-muted text-center">No se encontraron cursos sin asistencia.</p>';
                    totalCursosSinAsistenciaVirtual.textContent = '0';
                    return;
                }

                totalCursosSinAsistenciaVirtual.textContent = cursos.length;

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
                cursosSinAsistenciaListVirtual.innerHTML = '<p class="text-danger text-center">Error al cargar cursos sin asistencia.</p>';
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