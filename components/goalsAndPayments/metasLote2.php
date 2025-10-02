<style>
    .badge-total-metas {
        background-color: #30336b !important;
        color: #fff !important;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.4em 1em;
    }

    .row.mb-4 .col-md-4:nth-child(2) .badge-total-metas {
        background-color: #006d68 !important;
    }

    .row.mb-4 .col-md-4:nth-child(3) .badge-total-metas {
        background-color: #e67300 !important;
    }

    .card-meta {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .card-meta:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .progreso-meta {
        height: 12px;
        background: #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        margin-top: 8px;
    }

    .progreso-bar {
        height: 100%;
        background: #30336b;
        transition: width 0.5s;
    }
</style>

<!-- Selectores de filtro en tarjetas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-header bg-teal-dark text-white text-center">
                <h5 class="mb-0 w-100">
                    <i class="fas fa-list-alt"></i>
                    Listado de cursos y totales (Lote 2)
                </h5>
            </div>
            <div class="card-body w-100">
                <div class="row mb-2 w-100">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Número de pago</strong>
                            </div>
                            <div class="card-body">
                                <select id="selectorPagoLote2" class="form-control">
                                    <option value="">Seleccione un número de pago...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Modalidad</strong>
                            </div>
                            <div class="card-body">
                                <select id="selectorModalidadLote2" class="form-control">
                                    <option value="Todas">Todas las modalidades</option>
                                    <option value="Presencial">Presencial</option>
                                    <option value="Virtual">Virtual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Contrapartida</strong>
                            </div>
                            <div class="card-body">
                                <select id="selectorContrapartidaLote2" class="form-control">
                                    <option value="Todas">Ambas condiciones</option>
                                    <option value="0">Sin contrapartida</option>
                                    <option value="1">Con contrapartida</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3 w-100">
                        <div class="col-12 d-flex justify-content-center">
                            <button id="btnActualizarLote2" class="btn bg-teal-dark w-25" style="font-size:1.2rem;">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4" />

<div id="mensajeSeleccionPagoLote2" class="alert alert-info text-center w-100" style="border:2px solid #30336b; background-color:#d4d7ff;">
    <i class="fas fa-info-circle"></i>
    Seleccione un número de pago para cargar los datos.
</div>

<!-- Tarjetas de totales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total con ≥ 75% Asistencia</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeTotal75Lote2" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total Formados</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeFormadosLote2" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Meta</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeMetaLote2" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Faltante</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeFaltanteLote2" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
</div>

<!-- Listado de cursos y totales -->
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card w-100">
            <div class="card-header bg-indigo-dark text-white text-center">
                <h5 class="mb-0 w-100">
                    <i class="fas fa-list-alt"></i>
                    Listado de cursos y totales (Lote 2)
                </h5>
            </div>
            <div class="card-body w-100">
                <div id="listadoCursosTotalesLote2" class="w-100" style="min-height:120px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen General -->
<div class="row mt-4 mb-4 justify-content-center w-100">
    <div class="col-12 w-100">
        <div class="card card-meta bg-white text-dark w-100">
            <div class="card-body text-center w-100">
                <div id="resumenGeneralTotalesLote2" class="w-100">
                    <!-- Aquí se mostrará el resumen general -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const selectorPagoLote2 = document.getElementById('selectorPagoLote2');
    const selectorModalidadLote2 = document.getElementById('selectorModalidadLote2');
    const selectorContrapartidaLote2 = document.getElementById('selectorContrapartidaLote2');
    const listadoCursosTotalesLote2 = document.getElementById('listadoCursosTotalesLote2');
    const resumenGeneralTotalesLote2 = document.getElementById('resumenGeneralTotalesLote2');
    const mensajeSeleccionPagoLote2 = document.getElementById('mensajeSeleccionPagoLote2');

    // Referencias a las tarjetas
    const badgeTotal75Lote2 = document.getElementById('badgeTotal75Lote2');
    const badgeMetaLote2 = document.getElementById('badgeMetaLote2');
    const badgeFaltanteLote2 = document.getElementById('badgeFaltanteLote2');
    const badgeFormadosLote2 = document.getElementById('badgeFormadosLote2');

    // Cargar opciones de pagos
    async function cargarPagosLote2() {
        const res = await fetch('components/goalsAndPayments/actualizarMetaLote2.php?action=pagos');
        const pagos = await res.json();
        selectorPagoLote2.innerHTML = `<option value="">Seleccione un número de pago...</option>` +
            pagos.map(p => `<option value="${p.payment_number}">${p.payment_number}</option>`).join('');
    }

    function mostrarMensajeSeleccionPagoLote2() {
        mensajeSeleccionPagoLote2.style.display = 'block';
        listadoCursosTotalesLote2.innerHTML = '';
        listadoCursosTotalesLote2.style.display = 'none';
    }

    function ocultarMensajeSeleccionPagoLote2() {
        mensajeSeleccionPagoLote2.style.display = 'none';
        listadoCursosTotalesLote2.style.display = 'block';
    }

    // Cargar datos según filtros
    async function cargarDatosAsistenciaLote2() {
        const pago = selectorPagoLote2.value;
        const modalidad = selectorModalidadLote2.value;
        const contrapartida = selectorContrapartidaLote2.value;

        if (!pago) {
            mostrarMensajeSeleccionPagoLote2();
            return;
        }

        ocultarMensajeSeleccionPagoLote2();

        // Loader mientras carga
        listadoCursosTotalesLote2.innerHTML = `
            <div class="text-center w-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2 text-muted w-100">Cargando datos...</p>
            </div>
        `;

        const params = new URLSearchParams({
            pago,
            modalidad,
            contrapartida
        });

        try {
            const respuesta = await fetch('components/goalsAndPayments/actualizarMetaLote2.php?' + params.toString());
            const datos = await respuesta.json();

            if (datos.error) throw new Error(datos.error);

            // Calcular total de formados
            let totalFormados = 0;
            Object.values(datos.totalesPorCurso).forEach(curso => {
                totalFormados += curso.formados || 0;
            });

            // Actualizar tarjetas superiores
            badgeTotal75Lote2.textContent = datos.total75General;
            badgeMetaLote2.textContent = datos.metaGoal;
            badgeFaltanteLote2.textContent = Math.max(0, datos.metaGoal - datos.total75General);
            badgeFormadosLote2.textContent = totalFormados;

            // Listado de cursos en tabla
            let htmlCursos = `
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 22%;">Curso</th>
                            <th style="width: 12%;" class="text-center">Total Inscritos</th>
                            <th style="width: 12%;" class="text-center">Formados</th>
                            <th style="width: 12%;" class="text-center">Asistencia ≥ 75%</th>
                            <th style="width: 12%;" class="text-center">Asistencia < 75%</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            let totalCursos = 0;

            const cursosOrdenados = Object.keys(datos.totalesPorCurso).sort((a, b) => {
                const ta = datos.totalesPorCurso[a];
                const tb = datos.totalesPorCurso[b];
                if (tb.inscritos !== ta.inscritos) return tb.inscritos - ta.inscritos;
                if (tb.mayor75 !== ta.mayor75) return tb.mayor75 - ta.mayor75;
                return ta.menor75 - tb.menor75;
            });

            cursosOrdenados.forEach(curso => {
                const totales = datos.totalesPorCurso[curso];
                htmlCursos += `
                <tr>
                    <td>${curso}</td>
                    <td class="text-center">
                        <span class="badge bg-indigo-dark" style="font-size:1.1rem; padding:0.5em 1em;">${totales.inscritos}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-magenta-dark" style="font-size:1.1rem; padding:0.5em 1em;">${totales.formados || 0}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-teal-dark" style="font-size:1.1rem; padding:0.5em 1em;">${totales.mayor75}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-orange-light text-dark" style="font-size:1.1rem; padding:0.5em 1em;">${totales.menor75}</span>
                    </td>
                </tr>
            `;
                totalCursos++;
            });

            htmlCursos += `
                    </tbody>
                </table>
            </div>
        `;

            listadoCursosTotalesLote2.innerHTML = htmlCursos;

            // Resumen general
            const promedioMayor75 = datos.totalInscritosGeneral > 0 ? ((datos.total75General / datos.totalInscritosGeneral) * 100).toFixed(1) : 0;
            const progresoMeta = datos.metaGoal > 0 ? ((datos.total75General / datos.metaGoal) * 100).toFixed(1) : 0;

            resumenGeneralTotalesLote2.innerHTML = `
            <div>
                <strong>Total cursos:</strong> ${totalCursos} &nbsp; 
                <strong>Total inscritos:</strong> ${datos.totalInscritosGeneral} &nbsp; 
                <strong>% promedio ≥ 75%:</strong> ${promedioMayor75}% 
            </div>
            <div class="progreso-meta mt-3">
                <div class="progreso-bar" style="width: ${Math.min(100, progresoMeta)}%"></div>
            </div>
            <small class="text-muted">Progreso de meta: ${progresoMeta}% (${datos.total75General}/${datos.metaGoal})</small>
        `;
        } catch (error) {
            listadoCursosTotalesLote2.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar los datos: ${error.message}
            </div>
        `;
            resumenGeneralTotalesLote2.innerHTML = '';

            badgeTotal75Lote2.textContent = '0';
            badgeMetaLote2.textContent = '0';
            badgeFaltanteLote2.textContent = '0';
            badgeFormadosLote2.textContent = '0';
        }
    }

    await cargarPagosLote2();

    selectorPagoLote2.addEventListener('change', () => {
        if (selectorPagoLote2.value && selectorPagoLote2.value !== '') {
            ocultarMensajeSeleccionPagoLote2();
            cargarDatosAsistenciaLote2();
        } else {
            mostrarMensajeSeleccionPagoLote2();
        }
    });

    selectorModalidadLote2.addEventListener('change', () => {
        if (selectorPagoLote2.value && selectorPagoLote2.value !== '') {
            cargarDatosAsistenciaLote2();
        }
    });

    selectorContrapartidaLote2.addEventListener('change', () => {
        if (selectorPagoLote2.value && selectorPagoLote2.value !== '') {
            cargarDatosAsistenciaLote2();
        }
    });

    document.getElementById('btnActualizarLote2').addEventListener('click', () => {
        if (selectorPagoLote2.value && selectorPagoLote2.value !== '') {
            cargarDatosAsistenciaLote2();
        }
    });

    // No llamar cargarDatosAsistenciaLote2() al inicio
});
</script>