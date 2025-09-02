<div class="mt-4">
    <div class="card shadow">
        <div class="card-header bg-indigo-dark text-white">
            <h5 class="mb-0"><i class="bi bi-search"></i> Buscar persona por número de identificación</h5>
        </div>
        <div class="card-body">
            <form id="form-buscar-persona" autocomplete="off" class="w-100">
                <div class="row mb-3">
                    <div class="col-12 d-flex">
                        <input type="number" class="form-control me-2 text-center" id="number_id" name="number_id" placeholder="Número de identificación" required style="font-size: 1.25rem;">
                        <button type="submit" class="btn bg-indigo-dark text-white d-flex align-items-center">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mx-4">
            <div id="resultado-busqueda"></div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#form-buscar-persona').on('submit', function(e) {
            e.preventDefault();
            let number_id = $('#number_id').val();
            $('#resultado-busqueda').html('<div class="text-center"><div class="spinner-border"></div> Buscando...</div>');
            $.ajax({
                url: 'components/studentsReports/buscarPersona.php',
                type: 'POST',
                data: {
                    number_id: number_id
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        let persona = res.persona;

                        // Si no hay cursos, muestra solo el mensaje
                        let cursosHtml = '';
                        if (persona.bootcamp_name) {
                            cursosHtml += `
                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                    <span class="text-muted"><i class="bi bi-journal-bookmark"></i> Bootcamp:</span>
                                    <span class="text-end">${persona.bootcamp_name}</span>
                                </div>`;
                        }
                        if (persona.leveling_english_name) {
                            cursosHtml += `
                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                    <span class="text-muted"><i class="bi bi-journal-bookmark"></i> Nivelatorio Inglés:</span>
                                    <span class="text-end">${persona.leveling_english_name}</span>
                                </div>`;
                        }
                        if (persona.english_code_name) {
                            cursosHtml += `
                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                    <span class="text-muted"><i class="bi bi-journal-bookmark"></i> English Code:</span>
                                    <span class="text-end">${persona.english_code_name}</span>
                                </div>`;
                        }
                        if (persona.skills_name) {
                            cursosHtml += `
                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                    <span class="text-muted"><i class="bi bi-journal-bookmark"></i> Habilidades de Poder:</span>
                                    <span class="text-end">${persona.skills_name}</span>
                                </div>`;
                        }
                        if (!cursosHtml) {
                            cursosHtml = `
                                <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                    <span class="text-muted"><i class="bi bi-journal-bookmark"></i> Cursos matriculados:</span>
                                    <span class="text-end">No está matriculado en cursos</span>
                                </div>`;
                        }

                        // Card con estilo similar al de individualSearch.php
                        let html = `
                        <div class="card mb-3">
                            <div class="card-header bg-teal-dark text-white">
                                <h6 class="mb-0"><i class="bi bi-person-badge"></i> Información de la persona</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex flex-column gap-2 w-100">
                                    <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2">
                                        <span class="text-muted"><i class="bi bi-person"></i> Nombre:</span>
                                        <span class="fw-bold text-end">${persona.full_name}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                        <span class="text-muted"><i class="bi bi-credit-card"></i> Identificación:</span>
                                        <span class="text-end">${persona.number_id}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                        <span class="text-muted"><i class="bi bi-telephone"></i> Teléfonos:</span>
                                        <span class="text-end">${persona.phones}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                                        <span class="text-muted"><i class="bi bi-envelope"></i> Correo electrónico:</span>
                                        <span class="text-primary text-end">${persona.email}</span>
                                    </div>
                                    <div class="border-top pt-2 mt-2"></div>
                                    ${cursosHtml}
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-header bg-indigo-dark text-white">
                                <h6 class="mb-0"><i class="bi bi-gear"></i> Reporte</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row w-100 justify-content-center">
                                    <div class="col-12 mb-3 d-flex justify-content-center">
                                        <div class="w-100">
                                            <label for="grupo" class="form-label w-100 text-center"><i class="bi bi-people"></i> Seleccionar grupo:</label>
                                            <select id="grupo" class="form-select w-100 text-center mt-2">
                                                <option value="">Cargando cursos...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 d-flex justify-content-center">
                                        <div class="w-100">
                                            <label for="gestion" class="form-label w-100 text-center"><i class="bi bi-clipboard-check"></i> Gestión requerida:</label>
                                            <textarea id="gestion" class="form-control w-100 text-center mt-2" rows="3" placeholder="Describe la gestión requerida..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 d-flex justify-content-center">
                                        <button id="btn-guardar-reporte" class="btn bg-magenta-dark text-white" type="button">
                                            <i class="bi bi-save"></i> Guardar reporte
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                        $('#resultado-busqueda').html(html);

                        // Llenar el selector de grupo dinámicamente
                        $.ajax({
                            url: 'components/studentsReports/getMonitorCourses.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function(courses) {
                                let options = '';
                                if (courses.length > 0) {
                                    options = '<option value="">-- Selecciona --</option>';
                                    courses.forEach(function(course) {
                                        options += `<option value="${course.code}">${course.name}</option>`;
                                    });
                                } else {
                                    options = '<option value="">Sin cursos asignados</option>';
                                }
                                $('#grupo').html(options);
                            },
                            error: function() {
                                $('#grupo').html('<option value="">Error al cargar cursos</option>');
                            }
                        });

                        $('#btn-guardar-reporte').on('click', function() {
                            let grupo = $('#grupo option:selected').text();
                            let code = '';

                            if (grupo === 'Sin cursos asignados') {
                                code = 'Pendiente';
                            } else {
                                // Extraer el código del curso del texto seleccionado (ejemplo: C1L1-G1V)
                                let match = grupo.match(/C\d+L\d+-G\d+[A-Z]?/);
                                if (match) {
                                    code = match[0];
                                } else {
                                    code = grupo.slice(-8);
                                }
                            }

                            let gestion = $('#gestion').val();
                            let number_id = persona.number_id;

                            if (!code || code === "" || code === "Sin cursos asignados") {
                                Swal.fire('Selecciona un grupo', '', 'warning');
                                return;
                            }
                            if (!gestion.trim()) {
                                Swal.fire('Describe la gestión requerida', '', 'warning');
                                return;
                            }

                            $.ajax({
                                url: 'components/studentsReports/guardarReporte.php',
                                type: 'POST',
                                data: {
                                    number_id: number_id,
                                    code: code,
                                    grupo: grupo,
                                    gestion: gestion
                                },
                                dataType: 'json',
                                success: function(res) {
                                    if (res.success) {
                                        Swal.fire('¡Reporte guardado!', '', 'success');
                                        $('#gestion').val('');
                                        $('#grupo').val('');
                                    } else {
                                        Swal.fire('Error', res.message, 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'No se pudo guardar el reporte.', 'error');
                                }
                            });
                        });
                    } else {
                        $('#resultado-busqueda').html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> ${res.message}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#resultado-busqueda').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Error en la búsqueda.
                        </div>
                    `);
                }
            });
        });
    });
</script>