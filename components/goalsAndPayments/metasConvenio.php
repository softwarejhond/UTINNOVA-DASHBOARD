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

    /* Hover solo para tarjetas que NO sean de filtros */
    .card-meta:not(.card-filtros):hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    /* Tarjeta de filtros sin hover */
    .card-filtros {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        /* Sin transition ni hover */
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

    /* Solución completa para dropdowns */
    .dropdown {
        position: relative;
        z-index: 1000;
    }

    .dropdown.show {
        z-index: 10000 !important;
    }

    .dropdown-menu {
        position: absolute !important;
        z-index: 10001 !important;
        top: 100% !important;
        left: 0 !important;
        width: 100% !important;
        transform: none !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
    }

    /* Contenedores padre deben permitir overflow visible */
    .card-body {
        position: relative;
        overflow: visible !important;
    }

    .card {
        overflow: visible !important;
    }

    .row {
        overflow: visible !important;
    }

    .col-md-3 {
        overflow: visible !important;
    }

    /* Asegurar que los dropdowns estén por encima de todo */
    .dropdown-menu.show {
        z-index: 10050 !important;
    }
</style>

<!-- Selectores de filtro en tarjetas -->
<div class="row mb-4" style="z-index: 9999;">
    <div class="col-12">
        <div class="card card-filtros h-100 bg-white text-dark">
            <div class="card-header bg-teal-dark text-white text-center">
                <h5 class="mb-0 w-100">
                    <i class="fas fa-list-alt"></i>
                    Listado de cursos y totales (Convenio)
                </h5>
            </div>
            <div class="card-body w-100">
                <div class="row mb-2 w-100">
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Número de pago</strong>
                            </div>
                            <div class="card-body">
                                <!-- Dropdown múltiple para pagos -->
                                <div class="dropdown w-100">
                                    <button class="btn w-100 dropdown-toggle" type="button" id="dropdownPagosConvenio" data-bs-toggle="dropdown" aria-expanded="false" style="background:#fff; border:1px solid #dee2e6;">
                                        Seleccione número(s) de pago...
                                    </button>
                                    <ul class="dropdown-menu w-100 ps-3" aria-labelledby="dropdownPagosConvenio" id="dropdownPagosMenuConvenio" style="z-index: 9999;">
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllPagosConvenio">
                                                <label class="form-check-label fw-bold" for="selectAllPagosConvenio">Seleccionar todo</label>
                                            </div>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <!-- Opciones de pagos se llenan por JS -->
                                        <div id="pagosOpcionesConvenio"></div>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Modalidad</strong>
                            </div>
                            <div class="card-body">
                                <select id="selectorModalidadConvenio" class="form-control">
                                    <option value="Todas">Todas las modalidades</option>
                                    <option value="Presencial">Presencial</option>
                                    <option value="Virtual">Virtual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Contrapartida</strong>
                            </div>
                            <div class="card-body">
                                <select id="selectorContrapartidaConvenio" class="form-control">
                                    <option value="Todas">Ambas condiciones</option>
                                    <option value="0">Sin contrapartida</option>
                                    <option value="1">Con contrapartida</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-header bg-magenta-dark text-white text-center">
                                <strong>Lotes</strong>
                            </div>
                            <div class="card-body">
                                <!-- Dropdown múltiple para lotes -->
                                <div class="dropdown w-100">
                                    <button class="btn w-100 dropdown-toggle" type="button" id="dropdownLotesConvenio" data-bs-toggle="dropdown" aria-expanded="false" style="background:#fff; border:1px solid #dee2e6;">
                                        Seleccione lote(s)...
                                    </button>
                                    <ul class="dropdown-menu w-100 ps-3" aria-labelledby="dropdownLotesConvenio" id="dropdownLotesMenuConvenio" style="max-height: 300px; overflow-y: auto;">
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllLotesConvenio">
                                                <label class="form-check-label fw-bold" for="selectAllLotesConvenio">Seleccionar todos</label>
                                            </div>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <!-- Opciones de lotes se llenan por JS -->
                                        <div id="lotesOpcionesConvenio"></div>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3 w-100">
                        <div class="col-12 d-flex justify-content-center">
                            <button id="btnActualizarConvenio" class="btn bg-teal-dark w-25" style="font-size:1.2rem;">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mensajeSeleccionPagoConvenio" class="alert alert-info text-center w-100" style="border:2px solid #30336b; background-color:#d4d7ff;">
    <i class="fas fa-info-circle"></i>
    Seleccione un número de pago para cargar los datos.
</div>

<!-- Tarjetas de totales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total con ≥ 75% Asistencia</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeTotal75Convenio" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total Formados</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeFormadosConvenio" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Meta</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeMetaConvenio" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Faltante</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeFaltanteConvenio" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <!-- Nueva tarjeta para total de usuarios SenaTICS -->
    <div class="col-md-3 mt-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total Usuarios SenaTICS</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeTotalUsuariosConvenio" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
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
                    Listado de cursos y totales (Convenio)
                </h5>
            </div>
            <div class="card-body w-100">
                <div id="listadoCursosTotalesConvenio" class="w-100" style="min-height:120px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen General -->
<div class="row mt-4 mb-4 justify-content-center w-100" style="display: flex; justify-content: center;">
    <div class="col-12 w-100" style="max-width: 700px; margin: 0 auto;">
        <div class="card card-meta bg-white text-dark w-100">
            <div class="card-body text-center w-100">
                <div id="resumenGeneralTotalesConvenio" class="w-100">
                    <!-- Aquí se mostrará el resumen general -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const selectorModalidadConvenio = document.getElementById('selectorModalidadConvenio');
        const selectorContrapartidaConvenio = document.getElementById('selectorContrapartidaConvenio');
        const listadoCursosTotalesConvenio = document.getElementById('listadoCursosTotalesConvenio');
        const resumenGeneralTotalesConvenio = document.getElementById('resumenGeneralTotalesConvenio');
        const mensajeSeleccionPagoConvenio = document.getElementById('mensajeSeleccionPagoConvenio');

        // Referencias
        const lotesOpcionesConvenio = document.getElementById('lotesOpcionesConvenio');
        const selectAllLotesConvenio = document.getElementById('selectAllLotesConvenio');
        const pagosOpcionesConvenio = document.getElementById('pagosOpcionesConvenio');
        const selectAllPagosConvenio = document.getElementById('selectAllPagosConvenio');
        let pagosSeleccionadosConvenio = [];
        let lotesSeleccionadosConvenio = [];

        // Cargar opciones de pagos
        async function cargarPagosConvenio() {
            try {
                const res = await fetch('components/goalsAndPayments/actualizarMetaConvenio.php?action=pagos');
                const pagos = await res.json();
                pagosOpcionesConvenio.innerHTML = pagos.map((p, i) => `
                    <li>
                        <div class="form-check">
                            <input class="form-check-input filtro-pago-convenio" type="checkbox" value="${p.payment_number}" id="pagoConvenio_${i}">
                            <label class="form-check-label" for="pagoConvenio_${i}">Pago ${p.payment_number}</label>
                        </div>
                    </li>
                `).join('');

                // Agregar event listeners a los checkboxes después de crearlos
                document.querySelectorAll('.filtro-pago-convenio').forEach(checkbox => {
                    checkbox.addEventListener('change', actualizarSeleccionPagosConvenio);
                });
            } catch (error) {
                console.error('Error cargando pagos:', error);
                pagosOpcionesConvenio.innerHTML = '<li><div class="alert alert-danger">Error cargando pagos</div></li>';
            }
        }

        // Cargar opciones de lotes
        async function cargarLotesConvenio() {
            try {
                const res = await fetch('components/goalsAndPayments/actualizarMetaConvenio.php?action=lotes');
                const lotes = await res.json();
                lotesOpcionesConvenio.innerHTML = lotes.map((l, i) => `
                    <li>
                        <div class="form-check">
                            <input class="form-check-input filtro-lote-convenio" type="checkbox" value="${l.lote}" id="loteConvenio_${i}">
                            <label class="form-check-label" for="loteConvenio_${i}">Lote ${l.lote}</label>
                        </div>
                    </li>
                `).join('');

                // Agregar event listeners a los checkboxes después de crearlos
                document.querySelectorAll('.filtro-lote-convenio').forEach(checkbox => {
                    checkbox.addEventListener('change', actualizarSeleccionLotesConvenio);
                });
            } catch (error) {
                console.error('Error cargando lotes:', error);
                lotesOpcionesConvenio.innerHTML = '<li><div class="alert alert-danger">Error cargando lotes</div></li>';
            }
        }

        // Función para actualizar selección y realizar búsqueda automática (pagos)
        function actualizarSeleccionPagosConvenio() {
            pagosSeleccionadosConvenio = [];
            document.querySelectorAll(".filtro-pago-convenio:checked").forEach(cb => pagosSeleccionadosConvenio.push(cb.value));

            // Actualizar texto del botón
            document.getElementById('dropdownPagosConvenio').textContent = pagosSeleccionadosConvenio.length ?
                `Pago(s): ${pagosSeleccionadosConvenio.join(', ')}` :
                'Seleccione número(s) de pago...';

            // Realizar búsqueda automática solo si hay pagos seleccionados
            if (pagosSeleccionadosConvenio.length) {
                ocultarMensajeSeleccionPagoConvenio();
                cargarDatosAsistenciaConvenio();
            } else {
                mostrarMensajeSeleccionPagoConvenio();
                // Limpiar tarjetas cuando no hay selección
                document.getElementById('badgeTotal75Convenio').textContent = '0';
                document.getElementById('badgeMetaConvenio').textContent = '0';
                document.getElementById('badgeFaltanteConvenio').textContent = '0';
                document.getElementById('badgeFormadosConvenio').textContent = '0';
                document.getElementById('badgeTotalUsuariosConvenio').textContent = '0';  // Nuevo: resetear la nueva tarjeta
                resumenGeneralTotalesConvenio.innerHTML = '';
            }
        }

        // Función para actualizar selección de lotes
        function actualizarSeleccionLotesConvenio() {
            lotesSeleccionadosConvenio = [];
            document.querySelectorAll(".filtro-lote-convenio:checked").forEach(cb => lotesSeleccionadosConvenio.push(cb.value));

            // Crear texto más descriptivo para el botón
            let textoBoton = 'Seleccione lote(s)...';
            if (lotesSeleccionadosConvenio.length > 0) {
                if (lotesSeleccionadosConvenio.length <= 2) {
                    textoBoton = `Lote(s): ${lotesSeleccionadosConvenio.join(', ')}`;
                } else {
                    textoBoton = `Lote(s): ${lotesSeleccionadosConvenio.length} seleccionados`;
                }
            }

            // Actualizar texto del botón
            document.getElementById('dropdownLotesConvenio').textContent = textoBoton;

            // Realizar búsqueda automática solo si hay pagos seleccionados
            if (pagosSeleccionadosConvenio.length) {
                cargarDatosAsistenciaConvenio();
            }
        }

        // Seleccionar/Deseleccionar todos los pagos
        selectAllPagosConvenio.addEventListener("change", function() {
            document.querySelectorAll(".filtro-pago-convenio").forEach(cb => cb.checked = this.checked);
            actualizarSeleccionPagosConvenio();
        });

        // Seleccionar/Deseleccionar todos los lotes
        selectAllLotesConvenio.addEventListener("change", function() {
            document.querySelectorAll(".filtro-lote-convenio").forEach(cb => cb.checked = this.checked);
            actualizarSeleccionLotesConvenio();
        });

        // Cargar datos según filtros
        async function cargarDatosAsistenciaConvenio() {
            // Muestra el loader solo mientras carga
            listadoCursosTotalesConvenio.innerHTML = `
                <div class="text-center w-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted w-100">Cargando datos...</p>
                </div>
            `;

            const modalidad = selectorModalidadConvenio.value;
            const contrapartida = selectorContrapartidaConvenio.value;
            const pagosParam = pagosSeleccionadosConvenio.join(',');
            const lotesParam = lotesSeleccionadosConvenio.join(',');

            const params = new URLSearchParams({
                pagos: pagosParam,
                lotes: lotesParam,
                modalidad,
                contrapartida
            });

            try {
                const respuesta = await fetch('components/goalsAndPayments/actualizarMetaConvenio.php?' + params.toString());
                const datos = await respuesta.json();

                if (datos.error) throw new Error(datos.error);

                // Calcular total de formados
                let totalFormados = 0;
                Object.values(datos.totalesPorCurso).forEach(curso => {
                    totalFormados += curso.formados || 0;
                });

                // Actualizar tarjetas superiores
                document.getElementById('badgeTotal75Convenio').textContent = datos.total75General;
                document.getElementById('badgeMetaConvenio').textContent = datos.metaGoal;
                document.getElementById('badgeFaltanteConvenio').textContent = Math.max(0, datos.metaGoal - datos.total75General);
                document.getElementById('badgeFormadosConvenio').textContent = totalFormados;
                document.getElementById('badgeTotalUsuariosConvenio').textContent = datos.totalUsuarios;  // Nuevo: actualizar la nueva tarjeta

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

                listadoCursosTotalesConvenio.innerHTML = htmlCursos;

                // Resumen general
                const promedioMayor75 = datos.totalInscritosGeneral > 0 ? ((datos.total75General / datos.totalInscritosGeneral) * 100).toFixed(1) : 0;
                const progresoMeta = datos.metaGoal > 0 ? ((datos.total75General / datos.metaGoal) * 100).toFixed(1) : 0;

                resumenGeneralTotalesConvenio.innerHTML = `
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
                console.error('Error:', error);
                listadoCursosTotalesConvenio.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error al cargar los datos: ${error.message}
                    </div>
                `;
                resumenGeneralTotalesConvenio.innerHTML = '';

                // Resetear tarjetas en caso de error
                document.getElementById('badgeTotal75Convenio').textContent = '0';
                document.getElementById('badgeMetaConvenio').textContent = '0';
                document.getElementById('badgeFaltanteConvenio').textContent = '0';
                document.getElementById('badgeFormadosConvenio').textContent = '0';
                document.getElementById('badgeTotalUsuariosConvenio').textContent = '0';  // Nuevo: resetear en error
            }
        }

        function mostrarMensajeSeleccionPagoConvenio() {
            mensajeSeleccionPagoConvenio.style.display = 'block';
            listadoCursosTotalesConvenio.innerHTML = '';
            listadoCursosTotalesConvenio.style.display = 'none';
        }

        function ocultarMensajeSeleccionPagoConvenio() {
            mensajeSeleccionPagoConvenio.style.display = 'none';
            listadoCursosTotalesConvenio.style.display = 'block';
        }

        // Mejorar el comportamiento de los dropdowns
        document.getElementById('dropdownPagosConvenio').addEventListener('show.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '10000';
        });

        document.getElementById('dropdownPagosConvenio').addEventListener('hide.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '1000';
        });

        document.getElementById('dropdownLotesConvenio').addEventListener('show.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '10000';
        });

        document.getElementById('dropdownLotesConvenio').addEventListener('hide.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '1000';
        });

        // Cargar datos iniciales
        await cargarPagosConvenio();
        await cargarLotesConvenio();

        // Filtros adicionales
        selectorModalidadConvenio.addEventListener('change', () => {
            if (pagosSeleccionadosConvenio.length) cargarDatosAsistenciaConvenio();
        });
        selectorContrapartidaConvenio.addEventListener('change', () => {
            if (pagosSeleccionadosConvenio.length) cargarDatosAsistenciaConvenio();
        });

        document.getElementById('btnActualizarConvenio').addEventListener('click', () => {
            if (pagosSeleccionadosConvenio.length) cargarDatosAsistenciaConvenio();
        });
    });
</script>