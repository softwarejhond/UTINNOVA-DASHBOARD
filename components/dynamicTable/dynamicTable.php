<?php
require_once __DIR__ . '/../../controller/conexion.php'; // Asegura la ruta correcta

// Obtener datos únicos para filtros
function getDistinctValues($conn, $field, $table, $conditions = "") {
    $query = "SELECT DISTINCT $field FROM $table WHERE 1=1 $conditions ORDER BY $field";
    $result = mysqli_query($conn, $query);
    $values = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row[$field])) {
            $values[] = $row[$field];
        }
    }
    return $values;
}

// Condición base para lotes específicos
$loteCondition = " AND (lote = '1' OR lote = '2')";

// Mapeo de status admin
$statusMapping = [
    '0' => 'SIN ESTADO',
    '1' => 'BENEFICIARIO', 
    '2' => 'RECHAZADO',
    '3' => 'MATRICULADO',
    '4' => 'SIN CONTACTO',
    '5' => 'EN PROCESO',
    '6' => 'CERTIFICADO',
    '7' => 'INACTIVO',
    '8' => 'BENEFICIARIO CONTRAPARTIDA',
    '9' => 'APLAZADO',
    '10' => 'FORMADO',
    '11' => 'NO VALIDO',
    '12' => 'NO APROVADO'
];

// Obtener datos para filtros de user_register
$programs = getDistinctValues($conn, 'program', 'user_register', $loteCondition);
$modes = getDistinctValues($conn, 'mode', 'user_register', $loteCondition);
$headquarters = getDistinctValues($conn, 'headquarters', 'user_register', $loteCondition);
$genders = getDistinctValues($conn, 'gender', 'user_register', $loteCondition);
$ethnicGroups = getDistinctValues($conn, 'ethnic_group', 'user_register', $loteCondition);
$trainingLevels = getDistinctValues($conn, 'training_level', 'user_register', $loteCondition);

// Obtener datos únicos de la tabla groups (solo bootcamps y cursos)
$bootcampNames = getDistinctValues($conn, 'bootcamp_name', 'groups', " AND bootcamp_name IS NOT NULL AND bootcamp_name != ''");
$levelingEnglishNames = getDistinctValues($conn, 'leveling_english_name', 'groups', " AND leveling_english_name IS NOT NULL AND leveling_english_name != ''");
$englishCodeNames = getDistinctValues($conn, 'english_code_name', 'groups', " AND english_code_name IS NOT NULL AND english_code_name != ''");
$skillsNames = getDistinctValues($conn, 'skills_name', 'groups', " AND skills_name IS NOT NULL AND skills_name != ''");

// Obtener datos para filtros de courses
$courseNames = getDistinctValues($conn, 'name', 'courses', '');
$courseStatuses = getDistinctValues($conn, 'status', 'courses', '');

// Obtener datos únicos para filtros de lote
$lotes = getDistinctValues($conn, 'lote', 'user_register', $loteCondition);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla Dinámica con Drag & Drop</title>
    <style>
        .drag-area {
            min-height: 60px;
            border: 2px dashed #dee2e6;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin: 10px 0;
            transition: all 0.3s ease;
        }
        
        .drag-area.drag-over {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        
        .field-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 20px;
            cursor: move;
            display: inline-block;
            font-size: 0.9rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .field-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        
        .field-item.in-use {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .field-item.group-field {
            background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%);
        }
        
        .field-item.course-field {
            background: linear-gradient(135deg, #20c997 0%, #0dcaf0 100%);
        }
        
        .sidebar {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            border-right: 1px solid #dee2e6;
            height: 100vh;
            overflow-y: auto;
        }
        
        .category-section {
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .dynamic-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .summary-header {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .table-container {
            max-height: 500px;
            overflow: auto;
        }
        
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="transform: scale(0.8); transform-origin: top left; width: 125%; height: 200%;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar p-3">
                <h5 class="mb-4"><i class="fas fa-filter"></i> Filtros y Campos</h5>
                
                <!-- Campos disponibles para arrastrar -->
                <div class="category-section">
                    <h6><i class="fas fa-grip-vertical"></i> Campos de Registros</h6>
                    <div id="available-fields">
                        <div class="field-item" data-field="program" data-table="user_register">Programa</div>
                        <div class="field-item" data-field="mode" data-table="user_register">Modalidad</div>
                        <div class="field-item" data-field="headquarters" data-table="user_register">Sede</div>
                        <div class="field-item" data-field="gender" data-table="user_register">Género</div>
                        <div class="field-item" data-field="statusAdmin" data-table="user_register">Estado Admin</div>
                        <div class="field-item" data-field="ethnic_group" data-table="user_register">Grupo Étnico</div>
                        <div class="field-item" data-field="training_level" data-table="user_register">Nivel Formación</div>
                        <div class="field-item" data-field="lote" data-table="user_register">Lote</div>
                    </div>
                </div>
                
                <!-- Campos de Groups -->
                <div class="category-section">
                    <h6><i class="fas fa-users"></i> Campos de Grupos</h6>
                    <div id="group-fields">
                        <div class="field-item group-field" data-field="bootcamp_name" data-table="groups">Bootcamp</div>
                        <div class="field-item group-field" data-field="leveling_english_name" data-table="groups">Nivelación Inglés</div>
                        <div class="field-item group-field" data-field="english_code_name" data-table="groups">English Code</div>
                        <div class="field-item group-field" data-field="skills_name" data-table="groups">Skills</div>
                    </div>
                </div>
                
                <!-- Campos de Cursos -->
                <div class="category-section">
                    <h6><i class="fas fa-graduation-cap"></i> Campos de Cursos</h6>
                    <div id="course-fields">
                        <div class="field-item course-field" data-field="course_cohort" data-table="course_periods" data-needs-join="true">Cohorte Curso</div>
                    </div>
                </div>
                
                <!-- Filtros de Lote -->
                <div class="category-section">
                    <h6><i class="fas fa-layer-group"></i> Lote</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllLotes" checked>
                        <label class="form-check-label" for="selectAllLotes">Seleccionar todos</label>
                    </div>
                    <div class="mt-2" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($lotes as $lote): ?>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" 
                                   data-filter="lote" value="<?= htmlspecialchars($lote) ?>" 
                                   id="lote_<?= md5($lote) ?>" checked>
                            <label class="form-check-label" for="lote_<?= md5($lote) ?>">
                                Lote <?= htmlspecialchars($lote) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="category-section">
                    <h6><i class="fas fa-graduation-cap"></i> Programas</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllPrograms" checked>
                        <label class="form-check-label" for="selectAllPrograms">Seleccionar todos</label>
                    </div>
                    <div class="mt-2" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($programs as $program): ?>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" 
                                   data-filter="program" value="<?= htmlspecialchars($program) ?>" 
                                   id="prog_<?= md5($program) ?>" checked>
                            <label class="form-check-label" for="prog_<?= md5($program) ?>">
                                <?= htmlspecialchars($program) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="category-section">
                    <h6><i class="fas fa-user-check"></i> Estados</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllStatus" checked>
                        <label class="form-check-label" for="selectAllStatus">Seleccionar todos</label>
                    </div>
                    <div class="mt-2" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($statusMapping as $key => $status): ?>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" 
                                   data-filter="statusAdmin" value="<?= $key ?>" 
                                   id="status_<?= $key ?>" checked>
                            <label class="form-check-label" for="status_<?= $key ?>">
                                <?= htmlspecialchars($status) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Filtros de Modalidad -->
                <div class="category-section">
                    <h6><i class="fas fa-desktop"></i> Modalidad</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllModes" checked>
                        <label class="form-check-label" for="selectAllModes">Seleccionar todos</label>
                    </div>
                    <div class="mt-2" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($modes as $mode): ?>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" 
                                   data-filter="mode" value="<?= htmlspecialchars($mode) ?>" 
                                   id="mode_<?= md5($mode) ?>" checked>
                            <label class="form-check-label" for="mode_<?= md5($mode) ?>">
                                <?= htmlspecialchars($mode) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Filtros de Bootcamps -->
                <div class="category-section">
                    <h6><i class="fas fa-code"></i> Bootcamps</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllBootcamps" checked>
                        <label class="form-check-label" for="selectAllBootcamps">Seleccionar todos</label>
                    </div>
                    <div class="mt-2" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($bootcampNames as $bootcamp): ?>
                        <div class="form-check">
                            <input class="form-check-input filter-checkbox" type="checkbox" 
                                   data-filter="bootcamp_name" value="<?= htmlspecialchars($bootcamp) ?>" 
                                   id="bootcamp_<?= md5($bootcamp) ?>" checked>
                            <label class="form-check-label" for="bootcamp_<?= md5($bootcamp) ?>">
                                <?= htmlspecialchars($bootcamp) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Contenido principal -->
            <div class="col-md-9 p-3">
                <!-- Header con resumen -->
                <div class="summary-header mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><i class="fas fa-chart-bar"></i> Resumen Dinámico</h4>
                            <p class="mb-0" id="summary-text">Total de registros: <span id="total-records">0</span></p>
                            <small id="filters-applied">Filtros aplicados: Todos los departamentos, programas y estados</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light btn-sm" onclick="resetTable()">
                                <i class="fas fa-refresh"></i> Reiniciar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Áreas de drag & drop -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Campos para Filas</label>
                        <div class="drag-area" id="rows-area">
                            <span class="text-muted">Arrastra campos aquí para crear filas</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Campos para Columnas</label>
                        <div class="drag-area" id="columns-area">
                            <span class="text-muted">Arrastra campos aquí para crear columnas</span>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla dinámica -->
                <div class="dynamic-table">
                    <div class="table-container">
                        <div id="dynamic-table-content">
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-table fa-3x mb-3"></i>
                                <h5>Arrastra campos a las áreas de filas y columnas para generar la tabla</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentFilters = {};
        let rowFields = [];
        let columnFields = [];
        
        // Inicializar drag & drop
        document.addEventListener('DOMContentLoaded', function() {
            initializeDragAndDrop();
            initializeFilters();
            updateTable();
        });
        
        function initializeDragAndDrop() {
            // Hacer que los campos sean arrastrables
            const fields = document.querySelectorAll('.field-item');
            fields.forEach(field => {
                field.draggable = true;
                field.addEventListener('dragstart', handleDragStart);
            });
            
            // Configurar áreas de drop
            const dropAreas = document.querySelectorAll('.drag-area');
            dropAreas.forEach(area => {
                area.addEventListener('dragover', handleDragOver);
                area.addEventListener('drop', handleDrop);
                area.addEventListener('dragenter', handleDragEnter);
                area.addEventListener('dragleave', handleDragLeave);
            });
        }
        
        function handleDragStart(e) {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                field: e.target.dataset.field,
                table: e.target.dataset.table,
                text: e.target.textContent,
                needsJoin: e.target.dataset.needsJoin || false
            }));
        }
        
        function handleDragOver(e) {
            e.preventDefault();
        }
        
        function handleDragEnter(e) {
            e.target.classList.add('drag-over');
        }
        
        function handleDragLeave(e) {
            e.target.classList.remove('drag-over');
        }
        
        function handleDrop(e) {
            e.preventDefault();
            e.target.classList.remove('drag-over');

            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            const areaId = e.target.id;

            // Evitar duplicados
            if (areaId === 'rows-area') {
                if (!rowFields.some(f => f.field === data.field)) {
                    rowFields.push({
                        field: data.field,
                        table: data.table,
                        needsJoin: data.needsJoin
                    });
                    addFieldElement(e.target, data);
                }
            } else if (areaId === 'columns-area') {
                if (!columnFields.some(f => f.field === data.field)) {
                    columnFields.push({
                        field: data.field,
                        table: data.table,
                        needsJoin: data.needsJoin
                    });
                    addFieldElement(e.target, data);
                }
            }

            updateTable();
        }

        function addFieldElement(target, data) {
            const fieldElement = document.createElement('div');
            fieldElement.className = 'field-item in-use';
            fieldElement.innerHTML = `${data.text} <i class="fas fa-times ms-2" onclick="removeField(this)"></i>`;
            fieldElement.dataset.field = data.field;
            fieldElement.dataset.table = data.table;
            fieldElement.dataset.needsJoin = data.needsJoin;
            target.appendChild(fieldElement);
        }

        function removeField(element) {
            const fieldElement = element.parentElement;
            const field = fieldElement.dataset.field;
            const areaId = fieldElement.parentElement.id;

            // Elimina todas las instancias del campo en el array correspondiente
            if (areaId === 'rows-area') {
                rowFields = rowFields.filter(f => f.field !== field);
            } else if (areaId === 'columns-area') {
                columnFields = columnFields.filter(f => f.field !== field);
            }

            fieldElement.remove();
            updateTable();
        }
        
        function initializeFilters() {
            // Inicializar filtros con todos los valores seleccionados
            updateFilters();
            
            // Manejar checkboxes de "seleccionar todos"
            document.getElementById('selectAllLotes').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('[data-filter="lote"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateFilters();
            });
            
            document.getElementById('selectAllPrograms').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('[data-filter="program"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateFilters();
            });
            
            document.getElementById('selectAllStatus').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('[data-filter="statusAdmin"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateFilters();
            });
            
            document.getElementById('selectAllModes').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('[data-filter="mode"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateFilters();
            });
            
            document.getElementById('selectAllBootcamps').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('[data-filter="bootcamp_name"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateFilters();
            });
            
            // Manejar filtros individuales
            document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Actualizar el estado del "seleccionar todos" correspondiente
                    const filterType = this.dataset.filter;
                    const allCheckboxes = document.querySelectorAll(`[data-filter="${filterType}"]`);
                    const checkedCheckboxes = document.querySelectorAll(`[data-filter="${filterType}"]:checked`);
                    
                    // Actualizar el checkbox "seleccionar todos"
                    const selectAllId = getSelectAllId(filterType);
                    if (selectAllId) {
                        const selectAllCheckbox = document.getElementById(selectAllId);
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
                        }
                    }
                    
                    updateFilters();
                });
            });
        }
        
        function getSelectAllId(filterType) {
            switch(filterType) {
                case 'lote': return 'selectAllLotes';
                case 'program': return 'selectAllPrograms';
                case 'statusAdmin': return 'selectAllStatus';
                case 'mode': return 'selectAllModes';
                case 'bootcamp_name': return 'selectAllBootcamps';
                default: return null;
            }
        }
        
        function updateFilters() {
            currentFilters = {};
            
            // Obtener todos los filtros disponibles (sin department y municipality)
            const filterTypes = ['lote', 'program', 'statusAdmin', 'mode', 'bootcamp_name'];
            
            filterTypes.forEach(filterType => {
                const allCheckboxes = document.querySelectorAll(`[data-filter="${filterType}"]`);
                const checkedBoxes = document.querySelectorAll(`[data-filter="${filterType}"]:checked`);
                
                // Solo agregar al filtro si NO están todos seleccionados
                // Si todos están seleccionados, significa "sin filtro" para ese campo
                if (checkedBoxes.length > 0 && checkedBoxes.length < allCheckboxes.length) {
                    currentFilters[filterType] = [];
                    checkedBoxes.forEach(checkbox => {
                        currentFilters[filterType].push(checkbox.value);
                    });
                } else if (checkedBoxes.length === 0) {
                    // Si no hay ninguno seleccionado, poner un array vacío para indicar "no mostrar nada"
                    currentFilters[filterType] = [];
                }
                // Si todos están seleccionados, no agregamos nada al filtro (equivale a sin filtro)
            });
            
            console.log('Filtros actualizados:', currentFilters); // Debug
            updateTable();
        }
        
        function updateTable() {
            if (rowFields.length === 0 && columnFields.length === 0) {
                document.getElementById('dynamic-table-content').innerHTML = `
                    <div class="text-center p-5 text-muted">
                        <i class="fas fa-table fa-3x mb-3"></i>
                        <h5>Arrastra campos a las áreas de filas y columnas para generar la tabla</h5>
                    </div>
                `;
                return;
            }
            
            // Mostrar loading
            document.getElementById('dynamic-table-content').innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Calculando datos...</p>
                </div>
            `;
            
            // Realizar petición AJAX
            fetch('components/dynamicTable/ajax/getDynamicTableData.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rowFields: rowFields,
                    columnFields: columnFields,
                    filters: currentFilters
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('dynamic-table-content').innerHTML = data.html;
                    document.getElementById('total-records').textContent = data.totalRecords;
                    updateSummary();
                } else {
                    document.getElementById('dynamic-table-content').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar los datos: ${data.error}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('dynamic-table-content').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error de conexión
                    </div>
                `;
            });
        }
        
        function updateSummary() {
            let filterText = [];
            
            // Contar los filtros aplicados
            Object.keys(currentFilters).forEach(filterType => {
                if (currentFilters[filterType] && currentFilters[filterType].length > 0) {
                    const filterName = getFilterDisplayName(filterType);
                    filterText.push(`${currentFilters[filterType].length} ${filterName}`);
                }
            });
            
            document.getElementById('filters-applied').textContent = 
                'Filtros aplicados: ' + (filterText.length > 0 ? filterText.join(', ') : 'Todos los elementos');
        }
        
        function getFilterDisplayName(filterType) {
            switch(filterType) {
                case 'lote': return 'lotes';
                case 'program': return 'programas';
                case 'statusAdmin': return 'estados';
                case 'mode': return 'modalidades';
                case 'bootcamp_name': return 'bootcamps';
                default: return filterType;
            }
        }
        
        function resetTable() {
            rowFields = [];
            columnFields = [];
            
            document.getElementById('rows-area').innerHTML = '<span class="text-muted">Arrastra campos aquí para crear filas</span>';
            document.getElementById('columns-area').innerHTML = '<span class="text-muted">Arrastra campos aquí para crear columnas</span>';
            
            updateTable();
        }
    </script>
</body>
</html>