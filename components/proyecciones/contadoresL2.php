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
</style>
<div class="scale-09">
    <div class="row">
        <!-- Presenciales Lote 2 -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Cursos sin asistencia Lote 2
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteDos" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando cursos sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteDos" class="badge badge-total">0</span> cursos sin asistencia
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Cursos sin asistencias (Virtuales Lote 2) -->
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-ban"></i> Cursos sin asistencia Lote 2 Virtuales
                            </b>
                        </h5>
                    </div>
                    <div id="cursosSinAsistenciaListLoteDosVirtual" class="flex-grow-1 h-100 w-100" style="max-height: 300px; min-height: 120px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando cursos sin asistencia...</p>
                    </div>
                    <div class="mt-3 text-center">
                        <h5 class="mb-1 font-weight-bold text-black">
                            TOTAL: <span id="totalCursosSinAsistenciaLoteDosVirtual" class="badge badge-total">0</span> cursos sin asistencia
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" style="margin-bottom: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
                    cursosSinAsistenciaListLoteDos.innerHTML = '<p class="text-muted text-center">No se encontraron cursos sin asistencia.</p>';
                    totalCursosSinAsistenciaLoteDos.textContent = '0';
                    return;
                }

                totalCursosSinAsistenciaLoteDos.textContent = cursos.length;

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
                cursosSinAsistenciaListLoteDos.innerHTML = '<p class="text-danger text-center">Error al cargar cursos sin asistencia.</p>';
                totalCursosSinAsistenciaLoteDos.textContent = '0';
            }
        }

        async function cargarCursosSinAsistenciaLoteDosVirtual() {
            try {
                const respuesta = await fetch('components/proyecciones/actualizarLote2.php');
                const datos = await respuesta.json();
                const cursos = datos.cursosSinAsistenciaLote2Virtual;

                if (!cursos || cursos.length === 0) {
                    cursosSinAsistenciaListLoteDosVirtual.innerHTML = '<p class="text-muted text-center">No se encontraron cursos sin asistencia.</p>';
                    totalCursosSinAsistenciaLoteDosVirtual.textContent = '0';
                    return;
                }

                totalCursosSinAsistenciaLoteDosVirtual.textContent = cursos.length;

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
                cursosSinAsistenciaListLoteDosVirtual.innerHTML = '<p class="text-danger text-center">Error al cargar cursos sin asistencia.</p>';
                totalCursosSinAsistenciaLoteDosVirtual.textContent = '0';
            }
        }

        // Inicial
        await cargarBootcampsPresenciales();
        await cargarBootcampsPresencialesAprobados();
        await cargarProgramasPresencialesPendientes();
        await cargarBootcampsVirtuales();
        await cargarBootcampsVirtualesAprobados();
        await cargarProgramasVirtualesPendientes();
        await cargarCursosSinAsistenciaLoteDos();
        await cargarCursosSinAsistenciaLoteDosVirtual();

        setInterval(() => {
            cargarBootcampsPresenciales();
            cargarBootcampsPresencialesAprobados();
            cargarProgramasPresencialesPendientes();
            cargarBootcampsVirtuales();
            cargarBootcampsVirtualesAprobados();
            cargarProgramasVirtualesPendientes();
            cargarCursosSinAsistenciaLoteDos();
            cargarCursosSinAsistenciaLoteDosVirtual();
        }, 10000); // Actualiza todas cada 10 segundos
    });
</script>