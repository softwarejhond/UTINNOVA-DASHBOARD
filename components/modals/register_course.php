<?php

// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Función para llamar a la API de Moodle
function callMoodleAPIB($function, $params = [])
{
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCoursesB()
{
    return callMoodleAPIB('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCoursesB();


// Consulta para tabla Users

// Consulta para obtener usuarios
$sql = "SELECT * FROM users";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo 'No hay datos disponibles';
}

?>

<style>
    /* Estilos para el sistema de selección personalizado */
    .custom-select-wrapper {
        position: relative;
        width: 100%;
    }
    
    /* Estilo para el campo visible que reemplaza al select */
    .custom-select-field {
        width: 100%;
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        cursor: pointer;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Dropdown con el buscador y las opciones */
    .custom-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1050;
        display: none;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
    }
    
    /* Contenedor del buscador */
    .search-container {
        position: sticky;
        top: 0;
        padding: 8px;
        background-color: #fff;
        border-bottom: 1px solid #ced4da;
        z-index: 1051;
    }
    
    /* Opciones dentro del dropdown */
    .custom-select-option {
        padding: 8px 12px;
        cursor: pointer;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .custom-select-option:hover {
        background-color: #f8f9fa;
    }
    
    .custom-select-option.selected {
        background-color: #e9ecef;
    }
    
    /* Ocultar el select original */
    .custom-select-wrapper select {
        display: none;
    }
    
    /* Estilo cuando el select está deshabilitado */
    .custom-select-wrapper.disabled .custom-select-field {
        background-color: #e9ecef;
        opacity: 0.65;
        pointer-events: none;
    }
</style>

<!-- Modal -->
<div class="modal fade" id="registerCourseModal" tabindex="-1" aria-labelledby="registerCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="registerCourseModalLabel">Registrar Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <form id="registerCourseForm">
                    <div class="mb-3 text-start">
                        <label for="typeCourse" class="form-label">Tipo de Curso</label>
                        <select class="form-select" id="typeCourse" required>
                            <option value="">Seleccione tipo de curso</option>
                            <option value="tecnico">Técnico</option>
                            <option value="nivelatorio">Inglés Nivelatorio</option>
                            <option value="english">English Code</option>
                            <option value="habilidades">Habilidades de Poder</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="courseSelect" class="form-label">Curso</label>
                        <select class="form-select" id="courseSelect" required>
                            <option value="">Seleccione un curso</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teacherSelect" class="form-label">Profesor</label>
                        <select class="form-select" id="teacherSelect" required>
                            <option value="">Seleccione un profesor</option>
                            <?php
                            foreach ($data as $user) {
                                if ($user['rol'] == 5) {
                                    echo "<option value='{$user['username']}'>{$user['nombre']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mentorSelect" class="form-label">Mentor</label>
                        <select class="form-select" id="mentorSelect" required>
                            <option value="">Seleccione un mentor</option>
                            <?php
                            foreach ($data as $user) {
                                if ($user['rol'] == 8 || $user['rol_informativo'] == 8) {
                                    echo "<option value='{$user['username']}'>{$user['nombre']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="monitorSelect" class="form-label">Monitor</label>
                        <select class="form-select" id="monitorSelect" required>
                            <option value="">Seleccione un monitor</option>
                            <?php
                            foreach ($data as $user) {
                                if ($user['rol'] == 7 || $user['rol_informativo'] == 7) {
                                    echo "<option value='{$user['username']}'>{$user['nombre']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dateStart" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="dateStart" name="dateStart" required>
                    </div>

                    <!-- Nuevo campo para fecha de finalización -->
                    <div class="mb-3">
                        <label for="dateEnd" class="form-label">Fecha de Finalización</label>
                        <input type="date" class="form-control" id="dateEnd" name="dateEnd" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" required>
                            <option value="">Seleccione estado</option>
                            <option value="0">Inactivo</option>
                            <option value="1">Activo</option>
                        </select>
                    </div>

                    <!-- Nuevo campo para Cohorte -->
                    <div class="mb-3">
                        <label for="cohort" class="form-label">Cohorte</label>
                        <input type="number" class="form-control" id="cohort" name="cohort" min="1" step="1" required>
                    </div>

                    <!-- Nuevo campo para horas reales -->
                    <div class="mb-3">
                        <label for="realHours" class="form-label">Horas Reales</label>
                        <input type="number" class="form-control" id="realHours" name="realHours" min="0" step="1" required>
                    </div>

                    <!-- Campos para horas por día -->
                    <div class="mb-3">
                        <label class="form-label">Horas por Día</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Lunes</span>
                                    <input type="number" class="form-control" id="monday_hours" name="monday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Martes</span>
                                    <input type="number" class="form-control" id="tuesday_hours" name="tuesday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Miércoles</span>
                                    <input type="number" class="form-control" id="wednesday_hours" name="wednesday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Jueves</span>
                                    <input type="number" class="form-control" id="thursday_hours" name="thursday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Viernes</span>
                                    <input type="number" class="form-control" id="friday_hours" name="friday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Sábado</span>
                                    <input type="number" class="form-control" id="saturday_hours" name="saturday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Domingo</span>
                                    <input type="number" class="form-control" id="sunday_hours" name="sunday_hours" min="0" max="10" step="1" value="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn bg-magenta-dark" onclick="saveCourse()">Guardar</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Aplicar select personalizado solo a los selects específicos
        applyCustomSelect('#courseSelect');
        applyCustomSelect('#teacherSelect');
        applyCustomSelect('#mentorSelect');
        applyCustomSelect('#monitorSelect');
        
        // Deshabilitar todos los campos excepto tipo de curso al inicio
        $('#courseSelect, #teacherSelect, #mentorSelect, #monitorSelect, #status').prop('disabled', true);

        // Manejador del cambio en tipo de curso - mantén tu código original aquí
        $('#typeCourse').change(function() {
            let type = $(this).val();
            let categories = [];

            if (!type) {
                $('#courseSelect').prop('disabled', true);
                return;
            }

            // Habilitar select de cursos
            $('#courseSelect').prop('disabled', false);

            // Determinar categorías según el tipo
            switch (type) {
                case 'tecnico':
                    categories = [19, 21, 24, 26, 27, 35, 20, 22, 23, 25, 28, 34, 35];
                    break;
                case 'nivelatorio':
                    categories = [18, 17];
                    break;
                case 'english':
                    categories = [30, 31];
                    break;
                case 'habilidades':
                    categories = [32, 33];
                    break;
            }

            // Limpiar y actualizar el select de cursos
            $('#courseSelect').empty().append('<option value="">Seleccione un curso</option>');

            // Filtrar y mostrar cursos según la categoría
            <?php echo "const coursesData = " . json_encode($courses_data) . ";\n"; ?>

            coursesData.forEach(course => {
                if (categories.includes(parseInt(course.categoryid))) {
                    $('#courseSelect').append(`<option value="${course.id}">${course.id} - ${course.fullname}</option>`);
                }
            });

            // Después de actualizar las opciones:
            updateDropdownOptions($('#courseSelect'), $('#courseSelect').parent().find('.custom-select-dropdown'), '');
        });

        // Habilitar campos restantes cuando se seleccione un curso
        $('#courseSelect').change(function() {
            const isSelected = $(this).val() !== "";
            $('#teacherSelect, #mentorSelect, #monitorSelect, #status').prop('disabled', !isSelected);
            
            // Actualizar visualización
            updateCustomSelectDisplay('#teacherSelect');
            updateCustomSelectDisplay('#mentorSelect');
            updateCustomSelectDisplay('#monitorSelect');
        });
    });

    function applyCustomSelect(selectId) {
        const select = $(selectId);
        const wrapper = $('<div class="custom-select-wrapper"></div>');
        const displayField = $('<div class="custom-select-field">Seleccione una opción</div>');
        const dropdown = $('<div class="custom-select-dropdown"></div>');
        
        // Agregar campo de búsqueda
        const searchContainer = $('<div class="search-container"></div>');
        const searchField = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar...">');
        searchContainer.append(searchField);
        dropdown.append(searchContainer);
        
        // Envolver el select original con nuestra estructura
        select.wrap(wrapper);
        select.after(displayField);
        select.after(dropdown);
        
        // Actualizar el campo visible con el valor seleccionado inicialmente
        updateSelectedText();
        
        // Abrir/cerrar dropdown al hacer clic en el campo visible
        displayField.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (select.prop('disabled')) return;
            
            // Cerrar otros dropdowns abiertos
            $('.custom-select-dropdown').not(dropdown).hide();
            
            // Mostrar/ocultar el dropdown
            dropdown.toggle();
            
            if (dropdown.is(':visible')) {
                searchField.val('').focus();
                updateOptions('');
            }
        });
        
        // Filtrar opciones cuando se escribe en el buscador
        searchField.on('input', function() {
            updateOptions($(this).val().toLowerCase());
        });
        
        // Evitar que el clic en el dropdown lo cierre
        dropdown.on('click', function(e) {
            e.stopPropagation();
        });
        
        // Manejar cambios en el select original
        select.on('change', function() {
            updateSelectedText();
            
            // Manejar el estado disabled
            if (select.prop('disabled')) {
                wrapper.addClass('disabled');
            } else {
                wrapper.removeClass('disabled');
            }
        });
        
        // Cerrar dropdown al hacer clic fuera
        $(document).on('click', function() {
            dropdown.hide();
        });
        
        // Actualizar el texto seleccionado
        function updateSelectedText() {
            const selectedOption = select.find('option:selected');
            if (selectedOption.val()) {
                displayField.text(selectedOption.text());
            } else {
                displayField.text('Seleccione una opción');
            }
        }
        
        // Actualizar las opciones mostradas
        function updateOptions(searchText) {
            // Eliminar opciones existentes
            dropdown.find('.custom-select-option').remove();
            
            // Agregar opciones filtradas
            select.find('option').each(function() {
                const option = $(this);
                const text = option.text();
                const value = option.val();
                
                // No mostrar opción vacía si hay búsqueda
                if (searchText && value === '') return;
                
                // Verificar si coincide con la búsqueda
                if (text.toLowerCase().includes(searchText)) {
                    const optionElement = $('<div class="custom-select-option" data-value="' + value + '">' + text + '</div>');
                    
                    // Marcar seleccionada si corresponde
                    if (value === select.val()) {
                        optionElement.addClass('selected');
                    }
                    
                    // Manejar clic en la opción con verificación para cursos
                    optionElement.on('click', function() {
                        // Si es el select de cursos, verificar asignaciones previas
                        if (selectId === '#courseSelect' && value) {
                            const courseCode = text.split(' - ')[0];
                            
                            // Verificar si el curso ya tiene asignaciones
                            $.ajax({
                                url: 'components/modals/check_course_assignments.php',
                                type: 'GET',
                                data: {
                                    courseCode: courseCode
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success && response.hasAssignments) {
                                        // Crear el mensaje para mostrar
                                        let message = '<p>Este curso ya tiene las siguientes asignaciones:</p><ul>';
                                        
                                        response.assignments.forEach(function(assignment) {
                                            message += `<li><strong>${assignment.role.toUpperCase()}:</strong> ${assignment.id}</li>`;
                                        });
                                        
                                        message += '</ul><p>¿Desea continuar y actualizar estas asignaciones?</p>';
                                        
                                        // Mostrar SweetAlert
                                        Swal.fire({
                                            title: '¡Atención!',
                                            html: message,
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#3085d6',
                                            cancelButtonColor: '#d33',
                                            confirmButtonText: 'Sí, actualizar',
                                            cancelButtonText: 'Cancelar'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // Si el usuario confirma, aplicar el cambio
                                                select.val(value).trigger('change');
                                                dropdown.hide();
                                            }
                                        });
                                    } else {
                                        // Si no hay asignaciones previas, simplemente actualizar el select
                                        select.val(value).trigger('change');
                                        dropdown.hide();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error al verificar asignaciones:', error);
                                    // En caso de error, proceder con la selección
                                    select.val(value).trigger('change');
                                    dropdown.hide();
                                }
                            });
                        } else {
                            // Para otros selects, simplemente actualizar el valor
                            select.val(value).trigger('change');
                            dropdown.hide();
                        }
                    });
                    
                    dropdown.append(optionElement);
                }
            });
        }
    }

    // Función para actualizar la visualización del select personalizado
    function updateCustomSelectDisplay(selectId) {
        const select = $(selectId);
        const wrapper = select.closest('.custom-select-wrapper');
        const displayField = wrapper.find('.custom-select-field');
        
        // Actualizar el texto mostrado
        const selectedOption = select.find('option:selected');
        if (selectedOption.val()) {
            displayField.text(selectedOption.text());
        } else {
            displayField.text('Seleccione una opción');
        }
        
        // Actualizar estado disabled/enabled
        if (select.prop('disabled')) {
            wrapper.addClass('disabled');
        } else {
            wrapper.removeClass('disabled');
        }
    }

    function saveCourse() {
        // Validar que todos los campos estén llenos
        if (!$('#registerCourseForm')[0].checkValidity()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos requeridos',
            });
            return;
        }

        // Obtener los valores de los selects incluyendo el texto seleccionado
        const courseSelect = $('#courseSelect')[0];
        const teacherSelect = $('#teacherSelect')[0];
        const mentorSelect = $('#mentorSelect')[0];
        const monitorSelect = $('#monitorSelect')[0];

        // Extraer solo el nombre del curso (después del ID)
        const courseFullText = courseSelect.options[courseSelect.selectedIndex].text;
        // Separar el ID del nombre (el ID está antes del primer guión)
        const parts = courseFullText.split(' - ');
        const courseId = parts[0];
        // Unir el resto de las partes para mantener el nombre completo con sus guiones internos
        const courseNameOnly = parts.slice(1).join(' - ');

        const courseData = {
            code: courseSelect.value,
            name: courseFullText, // Enviar el texto completo para procesarlo en el servidor
            teacher: {
                username: teacherSelect.value,
                name: teacherSelect.options[teacherSelect.selectedIndex].text
            },
            mentor: {
                username: mentorSelect.value,
                name: mentorSelect.options[mentorSelect.selectedIndex].text
            },
            monitor: {
                username: monitorSelect.value,
                name: monitorSelect.options[monitorSelect.selectedIndex].text
            },
            date_start: $('#dateStart').val(),
            date_end: $('#dateEnd').val(),
            status: $('#status').val(),
            cohort: $('#cohort').val(),
            real_hours: $('#realHours').val(),
            monday_hours: $('#monday_hours').val(),
            tuesday_hours: $('#tuesday_hours').val(),
            wednesday_hours: $('#wednesday_hours').val(),
            thursday_hours: $('#thursday_hours').val(),
            friday_hours: $('#friday_hours').val(),
            saturday_hours: $('#saturday_hours').val(),
            sunday_hours: $('#sunday_hours').val()
        };

        // Justo antes del AJAX
        console.log("Enviando nombre del curso:", courseNameOnly);

        // Mostrar loading
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar datos al servidor
        $.ajax({
            url: 'components/modals/save_course.php',
            type: 'POST',
            data: courseData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Curso registrado exitosamente',
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#registerCourseModal').modal('hide');
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al registrar el curso: ' + response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error en la conexión'
                });
            }
        });
    }
</script>