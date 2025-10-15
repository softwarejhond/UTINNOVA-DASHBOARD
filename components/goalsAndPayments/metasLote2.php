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
                    Listado de cursos y totales (Lote 2)
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
                                    <button class="btn w-100 dropdown-toggle" type="button" id="dropdownPagosLote2" data-bs-toggle="dropdown" aria-expanded="false" style="background:#fff; border:1px solid #dee2e6;">
                                        Seleccione número(s) de pago...
                                    </button>
                                    <ul class="dropdown-menu w-100 ps-3" aria-labelledby="dropdownPagosLote2" id="dropdownPagosMenuLote2" style="z-index: 9999;">
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllPagosLote2">
                                                <label class="form-check-label fw-bold" for="selectAllPagosLote2">Seleccionar todo</label>
                                            </div>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <!-- Opciones de pagos se llenan por JS -->
                                        <div id="pagosOpcionesLote2"></div>
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
                                <select id="selectorModalidadLote2" class="form-control">
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
                                <select id="selectorContrapartidaLote2" class="form-control">
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
                                <strong>Convenios</strong>
                            </div>
                            <div class="card-body">
                                <!-- Dropdown múltiple para instituciones -->
                                <div class="dropdown w-100">
                                    <button class="btn w-100 dropdown-toggle" type="button" id="dropdownInstitucionesLote2" data-bs-toggle="dropdown" aria-expanded="false" style="background:#fff; border:1px solid #dee2e6;">
                                        Seleccione institución(es)...
                                    </button>
                                    <ul class="dropdown-menu w-100 ps-3" aria-labelledby="dropdownInstitucionesLote2" id="dropdownInstitucionesMenuLote2" style="max-height: 300px; overflow-y: auto;">
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllInstitucionesLote2">
                                                <label class="form-check-label fw-bold" for="selectAllInstitucionesLote2">Seleccionar todas</label>
                                            </div>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <!-- Opciones de instituciones se llenan por JS -->
                                        <div id="institucionesOpcionesLote2"></div>
                                    </ul>
                                </div>
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
    <div class="col-md-3">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Aproximandose al 75%</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeAlMenosUnaLote2" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
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
<div class="row mt-4 mb-4 justify-content-center w-100" style="display: flex; justify-content: center;">
    <div class="col-12 w-100" style="max-width: 700px; margin: 0 auto;">
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
        const selectorModalidadLote2 = document.getElementById('selectorModalidadLote2');
        const selectorContrapartidaLote2 = document.getElementById('selectorContrapartidaLote2');
        const listadoCursosTotalesLote2 = document.getElementById('listadoCursosTotalesLote2');
        const resumenGeneralTotalesLote2 = document.getElementById('resumenGeneralTotalesLote2');
        const mensajeSeleccionPagoLote2 = document.getElementById('mensajeSeleccionPagoLote2');

        // Referencias
        const pagosOpcionesLote2 = document.getElementById('pagosOpcionesLote2');
        const selectAllPagosLote2 = document.getElementById('selectAllPagosLote2');
        const institucionesOpcionesLote2 = document.getElementById('institucionesOpcionesLote2');
        const selectAllInstitucionesLote2 = document.getElementById('selectAllInstitucionesLote2');
        let pagosSeleccionadosLote2 = [];
        let institucionesSeleccionadasLote2 = [];

        // Cargar opciones de pagos
        async function cargarPagosLote2() {
            try {
                const res = await fetch('components/goalsAndPayments/actualizarMetaLote2.php?action=pagos');
                const pagos = await res.json();
                pagosOpcionesLote2.innerHTML = pagos.map((p, i) => `
                    <li>
                        <div class="form-check">
                            <input class="form-check-input filtro-pago-lote2" type="checkbox" value="${p.payment_number}" id="pagoLote2_${i}">
                            <label class="form-check-label" for="pagoLote2_${i}">Pago ${p.payment_number}</label>
                        </div>
                    </li>
                `).join('');

                // Agregar event listeners a los checkboxes después de crearlos
                document.querySelectorAll('.filtro-pago-lote2').forEach(checkbox => {
                    checkbox.addEventListener('change', actualizarSeleccionPagosLote2);
                });
            } catch (error) {
                console.error('Error cargando pagos:', error);
                pagosOpcionesLote2.innerHTML = '<li><div class="alert alert-danger">Error cargando pagos</div></li>';
            }
        }

        // Cargar opciones de instituciones
        async function cargarInstitucionesLote2() {
            try {
                const res = await fetch('components/goalsAndPayments/actualizarMetaLote2.php?action=instituciones');
                const instituciones = await res.json();
                institucionesOpcionesLote2.innerHTML = instituciones.map((inst, i) => {
                    const displayName = inst.institution === 'Sin institución' ?
                        '<em>Sin institución</em>' : inst.institution;
                    const className = inst.institution === 'Sin institución' ?
                        'form-check-label text-muted fst-italic' : 'form-check-label';

                    return `
                        <li>
                            <div class="form-check">
                                <input class="form-check-input filtro-institucion-lote2" type="checkbox" value="${inst.institution}" id="institucionLote2_${i}">
                                <label class="${className}" for="institucionLote2_${i}">${displayName}</label>
                            </div>
                        </li>
                    `;
                }).join('');

                // Agregar event listeners a los checkboxes después de crearlos
                document.querySelectorAll('.filtro-institucion-lote2').forEach(checkbox => {
                    checkbox.addEventListener('change', actualizarSeleccionInstitucionesLote2);
                });
            } catch (error) {
                console.error('Error cargando instituciones:', error);
                institucionesOpcionesLote2.innerHTML = '<li><div class="alert alert-danger">Error cargando instituciones</div></li>';
            }
        }

        // Función para actualizar selección y realizar búsqueda automática (pagos)
        function actualizarSeleccionPagosLote2() {
            pagosSeleccionadosLote2 = [];
            document.querySelectorAll(".filtro-pago-lote2:checked").forEach(cb => pagosSeleccionadosLote2.push(cb.value));

            // Actualizar texto del botón
            document.getElementById('dropdownPagosLote2').textContent = pagosSeleccionadosLote2.length ?
                `Pago(s): ${pagosSeleccionadosLote2.join(', ')}` :
                'Seleccione número(s) de pago...';

            // Realizar búsqueda automática solo si hay pagos seleccionados
            if (pagosSeleccionadosLote2.length) {
                ocultarMensajeSeleccionPagoLote2();
                cargarDatosAsistenciaLote2();
            } else {
                mostrarMensajeSeleccionPagoLote2();
                // Limpiar tarjetas cuando no hay selección
                document.getElementById('badgeTotal75Lote2').textContent = '0';
                document.getElementById('badgeMetaLote2').textContent = '0';
                document.getElementById('badgeFaltanteLote2').textContent = '0';
                document.getElementById('badgeFormadosLote2').textContent = '0';
                document.getElementById('badgeAlMenosUnaLote2').textContent = '0';  // Agregar esta línea para resetear la tarjeta faltante
                resumenGeneralTotalesLote2.innerHTML = '';
            }
        }

        // Función para actualizar selección de instituciones
        function actualizarSeleccionInstitucionesLote2() {
            institucionesSeleccionadasLote2 = [];
            document.querySelectorAll(".filtro-institucion-lote2:checked").forEach(cb => institucionesSeleccionadasLote2.push(cb.value));

            // Crear texto más descriptivo para el botón
            let textoBoton = 'Seleccione institución(es)...';
            if (institucionesSeleccionadasLote2.length > 0) {
                const sinInstitucion = institucionesSeleccionadasLote2.includes('Sin institución');
                const otrasInstituciones = institucionesSeleccionadasLote2.filter(inst => inst !== 'Sin institución');

                let partes = [];
                if (otrasInstituciones.length > 0) {
                    if (otrasInstituciones.length <= 2) {
                        partes.push(otrasInstituciones.join(', '));
                    } else {
                        partes.push(`${otrasInstituciones.slice(0, 2).join(', ')}... (+${otrasInstituciones.length - 2})`);
                    }
                }

                if (sinInstitucion) {
                    partes.push('Sin institución');
                }

                textoBoton = `Institución(es): ${partes.join(', ')}`;
                if (textoBoton.length > 50) {
                    textoBoton = `Institución(es): ${institucionesSeleccionadasLote2.length} seleccionadas`;
                }
            }

            // Actualizar texto del botón
            document.getElementById('dropdownInstitucionesLote2').textContent = textoBoton;

            // Realizar búsqueda automática solo si hay pagos seleccionados
            if (pagosSeleccionadosLote2.length) {
                cargarDatosAsistenciaLote2();
            }
        }

        // Seleccionar/Deseleccionar todos los pagos
        selectAllPagosLote2.addEventListener("change", function() {
            document.querySelectorAll(".filtro-pago-lote2").forEach(cb => cb.checked = this.checked);
            actualizarSeleccionPagosLote2();
        });

        // Seleccionar/Deseleccionar todas las instituciones
        selectAllInstitucionesLote2.addEventListener("change", function() {
            document.querySelectorAll(".filtro-institucion-lote2").forEach(cb => cb.checked = this.checked);
            actualizarSeleccionInstitucionesLote2();
        });

        // Cargar datos según filtros
        async function cargarDatosAsistenciaLote2() {
            // Muestra el loader solo mientras carga
            listadoCursosTotalesLote2.innerHTML = `
                <div class="text-center w-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted w-100">Cargando datos...</p>
                </div>
            `;

            const modalidad = selectorModalidadLote2.value;
            const contrapartida = selectorContrapartidaLote2.value;
            const pagosParam = pagosSeleccionadosLote2.join(',');
            const institucionesParam = institucionesSeleccionadasLote2.join(',');

            const params = new URLSearchParams({
                pagos: pagosParam,
                instituciones: institucionesParam,
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
                document.getElementById('badgeTotal75Lote2').textContent = datos.total75General;
                document.getElementById('badgeMetaLote2').textContent = datos.metaGoal;
                document.getElementById('badgeFaltanteLote2').textContent = Math.max(0, datos.metaGoal - datos.total75General);
                document.getElementById('badgeFormadosLote2').textContent = totalFormados;
                document.getElementById('badgeAlMenosUnaLote2').textContent = datos.totalAlMenosUnaPresente;  // Agregar esta línea para asignar el valor calculado

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

                listadoCursosTotalesLote2.innerHTML = htmlCursos;

                // Resumen general
                const promedioMayor75 = datos.totalInscritosGeneral > 0 ? ((datos.total75General / datos.totalInscritosGeneral) * 100).toFixed(1) : 0;
                const progresoMeta = datos.metaGoal > 0 ? ((datos.total75General / datos.metaGoal) * 100).toFixed(1) : 0;

                resumenGeneralTotalesLote2.innerHTML = `
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
                listadoCursosTotalesLote2.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error al cargar los datos: ${error.message}
                    </div>
                `;
                resumenGeneralTotalesLote2.innerHTML = '';

                // Resetear tarjetas en caso de error
                document.getElementById('badgeTotal75Lote2').textContent = '0';
                document.getElementById('badgeMetaLote2').textContent = '0';
                document.getElementById('badgeFaltanteLote2').textContent = '0';
                document.getElementById('badgeFormadosLote2').textContent = '0';
            }
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

        // Mejorar el comportamiento de los dropdowns
        document.getElementById('dropdownPagosLote2').addEventListener('show.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '10000';
        });

        document.getElementById('dropdownPagosLote2').addEventListener('hide.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '1000';
        });

        document.getElementById('dropdownInstitucionesLote2').addEventListener('show.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '10000';
        });

        document.getElementById('dropdownInstitucionesLote2').addEventListener('hide.bs.dropdown', function() {
            this.closest('.dropdown').style.zIndex = '1000';
        });

        // Cargar datos iniciales
        await cargarPagosLote2();
        await cargarInstitucionesLote2();

        // Filtros adicionales
        selectorModalidadLote2.addEventListener('change', () => {
            if (pagosSeleccionadosLote2.length) cargarDatosAsistenciaLote2();
        });
        selectorContrapartidaLote2.addEventListener('change', () => {
            if (pagosSeleccionadosLote2.length) cargarDatosAsistenciaLote2();
        });

        document.getElementById('btnActualizarLote2').addEventListener('click', () => {
            if (pagosSeleccionadosLote2.length) cargarDatosAsistenciaLote2();
        });
    });
</script>