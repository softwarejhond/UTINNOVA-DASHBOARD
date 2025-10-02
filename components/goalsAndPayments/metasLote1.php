<style>
    .badge-total-metas {
        background-color: #30336b !important;
        color: #fff !important;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.4em 1em;
    }

    /* Segundo badge: Total Formados */
    .row.mb-4 .col-md-3:nth-child(2) .badge-total-metas {
        background-color: #ec008c !important;
    }

    /* Tercer badge: Meta */
    .row.mb-4 .col-md-3:nth-child(3) .badge-total-metas {
        background-color: #006d68 !important;
    }

    /* Cuarto badge: Faltante */
    .row.mb-4 .col-md-3:nth-child(4) .badge-total-metas {
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
                    Listado de cursos y totales
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
                                <select id="selectorPago" class="form-control">
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
                                <select id="selectorModalidad" class="form-control">
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
                                <select id="selectorContrapartida" class="form-control">
                                    <option value="Todas">Ambas condiciones</option>
                                    <option value="0">Sin contrapartida</option>
                                    <option value="1">Con contrapartida</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3 w-100">
                        <div class="col-12 d-flex justify-content-center">
                            <button id="btnActualizar" class="btn bg-teal-dark w-25" style="font-size:1.2rem;">
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

<div id="mensajeSeleccionPago" class="alert alert-info text-center w-100" style="border:2px solid #30336b; background-color:#d4d7ff;">
    <i class="fas fa-info-circle"></i>
    Seleccione un número de pago para cargar los datos.
</div>

<!-- Tarjetas de prueba -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total con ≥ 75% Asistencia</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeTotal75" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total Formados</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeFormados" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Meta</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeMeta" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Faltante</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeFaltante" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
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
                    Listado de cursos y totales
                </h5>
            </div>
            <div class="card-body w-100">
                <div id="listadoCursosTotales" class="w-100" style="min-height:120px;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen General -->
<div class="row mt-4 mb-4 justify-content-center w-100" style="display: flex; justify-content: center;">
    <div class="col-12 w-100" style="max-width: 700px; margin: 0 auto;">
        <div class="card card-meta bg-white text-dark w-100">
            <div class="card-body text-center w-100">
                <div id="resumenGeneralTotales" class="w-100">
                    <!-- Aquí se mostrará el resumen general -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const selectorPago = document.getElementById('selectorPago');
        const selectorModalidad = document.getElementById('selectorModalidad');
        const selectorContrapartida = document.getElementById('selectorContrapartida');
        const listadoCursosTotales = document.getElementById('listadoCursosTotales');
        const resumenGeneralTotales = document.getElementById('resumenGeneralTotales');
        const mensajeSeleccionPago = document.getElementById('mensajeSeleccionPago');

        // Referencias a las tarjetas
        const tarjeta1Total75 = document.querySelector('.row.mb-4 .col-md-4:nth-child(1) .badge');
        const tarjeta2Meta = document.querySelector('.row.mb-4 .col-md-4:nth-child(2) .badge');
        const tarjeta3Faltante = document.querySelector('.row.mb-4 .col-md-4:nth-child(3) .badge');

        // Cargar opciones de pagos
        async function cargarPagos() {
            const res = await fetch('components/goalsAndPayments/actualizarMetaLote1.php?action=pagos');
            const pagos = await res.json();
            selectorPago.innerHTML = `<option value="">Seleccione un número de pago...</option>` +
                pagos.map(p => `<option value="${p.payment_number}">${p.payment_number}</option>`).join('');
        }

        // Cargar datos según filtros
        async function cargarDatosAsistencia() {
            // Muestra el loader solo mientras carga
            listadoCursosTotales.innerHTML = `
                <div class="text-center w-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted w-100">Cargando datos...</p>
                </div>
            `;

            const pago = selectorPago.value;
            const modalidad = selectorModalidad.value;
            const contrapartida = selectorContrapartida.value;

            const params = new URLSearchParams({
                pago,
                modalidad,
                contrapartida
            });

            try {
                const respuesta = await fetch('components/goalsAndPayments/actualizarMetaLote1.php?' + params.toString());
                const datos = await respuesta.json();

                // Calcular total de formados
                let totalFormados = 0;
                Object.values(datos.totalesPorCurso).forEach(curso => {
                    totalFormados += curso.formados || 0;
                });

                // Actualizar tarjetas superiores
                document.getElementById('badgeTotal75').textContent = datos.total75General;
                document.getElementById('badgeMeta').textContent = datos.metaGoal;
                document.getElementById('badgeFaltante').textContent = Math.max(0, datos.metaGoal - datos.total75General);
                document.getElementById('badgeFormados').textContent = totalFormados;

                // Listado de cursos en tabla
                let htmlCursos = `
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 22%;">Curso</th>
                            <th style="width: 18%;">Fechas / Estado</th>
                            <th style="width: 12%;" class="text-center">Total Inscritos</th>
                            <th style="width: 12%;" class="text-center">Formados</th>
                            <th style="width: 12%;" class="text-center">Asistencia ≥ 75%</th>
                            <th style="width: 12%;" class="text-center">Asistencia < 75%</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
                let totalCursos = 0;

                // Ordenar cursos
                const cursosOrdenados = Object.keys(datos.totalesPorCurso).sort((a, b) => {
                    const ta = datos.totalesPorCurso[a];
                    const tb = datos.totalesPorCurso[b];
                    if (tb.inscritos !== ta.inscritos) return tb.inscritos - ta.inscritos;
                    if (tb.mayor75 !== ta.mayor75) return tb.mayor75 - ta.mayor75;
                    return ta.menor75 - tb.menor75;
                });

                cursosOrdenados.forEach(curso => {
                    const totales = datos.totalesPorCurso[curso];
                    // Formatear fechas
                    const startDMY = totales.start_date ? new Date(totales.start_date).toLocaleDateString('es-ES') : '';
                    const endDMY = totales.end_date ? new Date(totales.end_date).toLocaleDateString('es-ES') : '';
                    const hoy = new Date();
                    const endDateObj = totales.end_date ? new Date(totales.end_date) : null;
                    const estadoCurso = endDateObj && endDateObj < hoy ? '<span class="badge bg-danger">Finalizado</span>' : '<span class="badge bg-success">En curso</span>';

                    htmlCursos += `
                <tr>
                    <td>${curso}</td>
                    <td>
                        <div><strong>Inicio:</strong> ${startDMY}</div>
                        <div><strong>Fin:</strong> ${endDMY}</div>
                        <div>${estadoCurso}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-indigo-dark" style="font-size:1.1rem; padding:0.5em 1em;">${totales.inscritos}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-magenta-dark" style="font-size:1.1rem; padding:0.5em 1em;">${totales.formados}</span>
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

                listadoCursosTotales.innerHTML = htmlCursos;

                // Resumen general
                const promedioMayor75 = datos.totalInscritosGeneral > 0 ? ((datos.total75General / datos.totalInscritosGeneral) * 100).toFixed(1) : 0;
                const progresoMeta = datos.metaGoal > 0 ? ((datos.total75General / datos.metaGoal) * 100).toFixed(1) : 0;

                resumenGeneralTotales.innerHTML = `
            <div>
                <strong>Total cursos:</strong> ${totalCursos} &nbsp; 
                <strong>Total matriculados:</strong> ${datos.totalInscritosGeneral} &nbsp; 
                <strong>% promedio ≥ 75%:</strong> ${promedioMayor75}% 
            </div>
            <div class="progreso-meta mt-3">
                <div class="progreso-bar" style="width: ${Math.min(100, progresoMeta)}%"></div>
            </div>
            <small class="text-muted">Progreso de meta: ${progresoMeta}% (${datos.total75General}/${datos.metaGoal})</small>
        `;
            } catch (error) {
                listadoCursosTotales.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar los datos: ${error.message}
            </div>
        `;
                resumenGeneralTotales.innerHTML = '';

                // Resetear tarjetas en caso de error
                tarjeta1Total75.textContent = '0';
                tarjeta2Meta.textContent = '0';
                tarjeta3Faltante.textContent = '0';
            }
        }

        function mostrarMensajeSeleccionPago() {
            mensajeSeleccionPago.style.display = 'block';
            listadoCursosTotales.innerHTML = ''; // Limpia el contenido, no muestra loader
            listadoCursosTotales.style.display = 'none';
        }

        function ocultarMensajeSeleccionPago() {
            mensajeSeleccionPago.style.display = 'none';
            listadoCursosTotales.style.display = 'block';
        }

        await cargarPagos();

        // Solo dispara la búsqueda si hay un número de pago seleccionado
        selectorPago.addEventListener('change', () => {
            if (selectorPago.value && selectorPago.value !== '') {
                ocultarMensajeSeleccionPago();
                cargarDatosAsistencia();
            } else {
                mostrarMensajeSeleccionPago();
            }
        });

        selectorModalidad.addEventListener('change', () => {
            if (selectorPago.value && selectorPago.value !== '') {
                cargarDatosAsistencia();
            }
        });

        selectorContrapartida.addEventListener('change', () => {
            if (selectorPago.value && selectorPago.value !== '') {
                cargarDatosAsistencia();
            }
        });

        document.getElementById('btnActualizar').addEventListener('click', () => {
            if (selectorPago.value && selectorPago.value !== '') {
                cargarDatosAsistencia();
            }
        });

        // No llamar cargarDatosAsistencia() al inicio
    });
</script>