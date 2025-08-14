<?php
// Activar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Datos de entrada inválidos']);
    exit;
}

$rowFields = $input['rowFields'] ?? [];
$columnFields = $input['columnFields'] ?? [];
$filters = $input['filters'] ?? [];

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

try {
    // Determinar qué tablas necesitamos basado en campos Y filtros
    $needsGroups = false;
    $needsCoursePeriods = false;
    
    $allFields = array_merge($rowFields, $columnFields);
    
    // Verificar campos seleccionados
    foreach ($allFields as $fieldInfo) {
        if (is_array($fieldInfo)) {
            $table = $fieldInfo['table'];
            $field = $fieldInfo['field'];
            $needsJoin = $fieldInfo['needsJoin'] ?? false;
        } else {
            // Compatibilidad con formato anterior
            continue;
        }
        
        if ($table === 'groups') {
            $needsGroups = true;
        } elseif ($table === 'course_periods' || $field === 'course_cohort') {
            $needsCoursePeriods = true;
            $needsGroups = true; // Necesitamos groups para hacer el join
        }
    }
    
    // Verificar filtros activos para determinar si necesitamos JOINs adicionales
    $groupFilters = ['bootcamp_name', 'leveling_english_name', 'english_code_name', 'skills_name'];
    foreach ($filters as $field => $values) {
        if (!empty($values) && in_array($field, $groupFilters)) {
            $needsGroups = true;
            break;
        }
    }
    
    // Construir la consulta base
    $fromClause = "user_register ur";
    $selectFields = [];
    $groupByFields = [];  // Campos reales para GROUP BY
    $orderByFields = [];  // Campos reales para ORDER BY
    
    // Añadir JOINs según sea necesario
    if ($needsGroups) {
        $fromClause .= " LEFT JOIN groups g ON ur.number_id = g.number_id";
    }
    
    if ($needsCoursePeriods) {
        $fromClause .= " LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code";
    }
    
    // Procesar campos para SELECT
    foreach ($allFields as $fieldInfo) {
        if (is_array($fieldInfo)) {
            $field = $fieldInfo['field'];
            $table = $fieldInfo['table'];
            
            if ($table === 'user_register') {
                $selectFields[] = "ur.$field";
                $groupByFields[] = "ur.$field";
                $orderByFields[] = "ur.$field";
            } elseif ($table === 'groups') {
                $selectFields[] = "g.$field";
                $groupByFields[] = "g.$field";
                $orderByFields[] = "g.$field";
            } elseif ($table === 'course_periods') {
                if ($field === 'course_cohort') {
                    $selectFields[] = "cp.cohort as course_cohort";
                    $groupByFields[] = "cp.cohort";  // Campo real sin alias
                    $orderByFields[] = "cp.cohort";  // Campo real sin alias
                }
            }
        }
    }
    
    if (empty($selectFields)) {
        echo json_encode(['success' => false, 'error' => 'No se han seleccionado campos válidos']);
        exit;
    }
    
    $selectClause = implode(', ', $selectFields);
    $groupByClause = implode(', ', $groupByFields);
    $orderByClause = implode(', ', $orderByFields);
    
    // Construir condiciones WHERE
    $whereConditions = ["(ur.lote = '1' OR ur.lote = '2')"];
    
    // Aplicar filtros solo si tienen valores seleccionados
    foreach ($filters as $field => $values) {
        if (isset($values) && is_array($values)) {
            // Si el array está vacío, significa que no se seleccionó nada = no mostrar resultados
            if (empty($values)) {
                $whereConditions[] = "1 = 0"; // Condición que nunca es verdadera
                break; // No necesitamos más condiciones
            } else {
                // Si tiene valores, aplicar el filtro normalmente
                $escapedValues = array_map(function($value) use ($conn) {
                    return "'" . mysqli_real_escape_string($conn, $value) . "'";
                }, $values);
                
                // Solo aplicar filtros de groups si tenemos el JOIN
                if (in_array($field, $groupFilters)) {
                    if ($needsGroups) {
                        $whereConditions[] = "g.$field IN (" . implode(',', $escapedValues) . ")";
                    }
                    // Si es un filtro de groups pero no tenemos JOIN, lo ignoramos silenciosamente
                } else {
                    // Filtros de user_register siempre disponibles
                    $whereConditions[] = "ur.$field IN (" . implode(',', $escapedValues) . ")";
                }
            }
        }
        // Si $values no está definido o no es array, significa "todos" = sin filtro
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Construir query principal
    $query = "SELECT $selectClause, COUNT(*) as count_records 
              FROM $fromClause 
              WHERE $whereClause 
              GROUP BY $groupByClause 
              ORDER BY $orderByClause";
    
    // Debug: log de la consulta (comentar en producción)
    error_log("Query generada: " . $query);
    error_log("Filtros recibidos: " . json_encode($filters));
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }
    
    $data = [];
    $totalRecords = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $totalRecords += $row['count_records'];
        $data[] = $row;
    }
    
    // Extraer solo los nombres de campos para las funciones de generación
    $rowFieldNames = [];
    $columnFieldNames = [];
    
    foreach ($rowFields as $fieldInfo) {
        if (is_array($fieldInfo)) {
            $rowFieldNames[] = $fieldInfo['field'];
        }
    }
    
    foreach ($columnFields as $fieldInfo) {
        if (is_array($fieldInfo)) {
            $columnFieldNames[] = $fieldInfo['field'];
        }
    }
    
    // Generar HTML de la tabla
    $html = generateTableHTML($data, $rowFieldNames, $columnFieldNames, $statusMapping, [], []);
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'totalRecords' => number_format($totalRecords),
        'dataCount' => count($data),
        'debug' => [
            'query' => $query,
            'filters' => $filters,
            'needsGroups' => $needsGroups
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function generateTableHTML($data, $rowFields, $columnFields, $statusMapping, $departamentosMap, $municipiosMap) {
    if (empty($data)) {
        return '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No se encontraron datos con los filtros aplicados.</div>';
    }
    
    // Si solo hay campos de fila, mostrar tabla simple
    if (empty($columnFields)) {
        return generateSimpleTable($data, $rowFields, $statusMapping, $departamentosMap, $municipiosMap);
    }
    
    // Si hay campos de fila y columna, generar tabla cruzada
    if (!empty($rowFields) && !empty($columnFields)) {
        return generateCrossTable($data, $rowFields, $columnFields, $statusMapping, $departamentosMap, $municipiosMap);
    }
    
    // Si solo hay campos de columna
    if (empty($rowFields) && !empty($columnFields)) {
        return generateColumnOnlyTable($data, $columnFields, $statusMapping, $departamentosMap, $municipiosMap);
    }
    
    return '<div class="alert alert-warning">Configuración de tabla no válida</div>';
}

function formatFieldValue($field, $value, $statusMapping, $departamentosMap, $municipiosMap) {
    // Verificar si el valor es null o vacío
    if ($value === null || $value === '') {
        return 'N/A';
    }
    
    if ($field === 'statusAdmin') {
        return $statusMapping[$value] ?? 'DESCONOCIDO';
    }
    return $value;
}

function generateSimpleTable($data, $rowFields, $statusMapping, $departamentosMap, $municipiosMap) {
    $html = '<table class="table table-striped table-hover">';
    $html .= '<thead class="table-dark">';
    $html .= '<tr>';
    
    foreach ($rowFields as $field) {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        if ($field === 'course_cohort') {
            $fieldName = 'Cohorte';
        }
        $html .= '<th>' . $fieldName . '</th>';
    }
    $html .= '<th class="text-end">Cantidad</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($rowFields as $field) {
            // Verificar si el campo existe en el resultado
            $value = isset($row[$field]) ? $row[$field] : null;
            $value = formatFieldValue($field, $value, $statusMapping, $departamentosMap, $municipiosMap);
            $html .= '<td>' . htmlspecialchars((string)$value) . '</td>';
        }
        $html .= '<td class="text-end fw-bold">' . number_format($row['count_records']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    
    return $html;
}

function generateCrossTable($data, $rowFields, $columnFields, $statusMapping, $departamentosMap, $municipiosMap) {
    // Organizar datos en estructura de tabla cruzada
    $rowValues = [];
    $columnValues = [];
    $crossData = [];
    
    foreach ($data as $row) {
        $rowKey = '';
        foreach ($rowFields as $field) {
            $value = isset($row[$field]) ? $row[$field] : null;
            $value = formatFieldValue($field, $value, $statusMapping, $departamentosMap, $municipiosMap);
            $rowKey .= $value . '|';
        }
        $rowKey = rtrim($rowKey, '|');
        
        $colKey = '';
        foreach ($columnFields as $field) {
            $value = isset($row[$field]) ? $row[$field] : null;
            $value = formatFieldValue($field, $value, $statusMapping, $departamentosMap, $municipiosMap);
            $colKey .= $value . '|';
        }
        $colKey = rtrim($colKey, '|');
        
        if (!in_array($rowKey, $rowValues)) {
            $rowValues[] = $rowKey;
        }
        if (!in_array($colKey, $columnValues)) {
            $columnValues[] = $colKey;
        }
        
        $crossData[$rowKey][$colKey] = $row['count_records'];
    }
    
    sort($rowValues);
    sort($columnValues);
    
    $html = '<div class="table-responsive">';
    $html .= '<table class="table table-bordered table-sm">';
    $html .= '<thead class="table-dark">';
    $html .= '<tr>';
    $html .= '<th rowspan="2" class="align-middle">Filas</th>';
    $html .= '<th colspan="' . count($columnValues) . '" class="text-center">Columnas</th>';
    $html .= '<th rowspan="2" class="align-middle">Total</th>';
    $html .= '</tr>';
    $html .= '<tr>';
    
    foreach ($columnValues as $colValue) {
        $html .= '<th class="text-center" style="min-width: 80px;">' . htmlspecialchars((string)$colValue) . '</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($rowValues as $rowValue) {
        $html .= '<tr>';
        $html .= '<td class="fw-bold">' . htmlspecialchars((string)$rowValue) . '</td>';
        
        $rowTotal = 0;
        foreach ($columnValues as $colValue) {
            $count = $crossData[$rowValue][$colValue] ?? 0;
            $rowTotal += $count;
            $cellClass = $count > 0 ? 'text-center fw-bold' : 'text-center text-muted';
            $html .= '<td class="' . $cellClass . '">' . ($count > 0 ? number_format($count) : '-') . '</td>';
        }
        
        $html .= '<td class="text-center fw-bold bg-light">' . number_format($rowTotal) . '</td>';
        $html .= '</tr>';
    }
    
    // Fila de totales
    $html .= '<tr class="table-secondary">';
    $html .= '<td class="fw-bold">Total</td>';
    $grandTotal = 0;
    
    foreach ($columnValues as $colValue) {
        $colTotal = 0;
        foreach ($rowValues as $rowValue) {
            $colTotal += $crossData[$rowValue][$colValue] ?? 0;
        }
        $grandTotal += $colTotal;
        $html .= '<td class="text-center fw-bold">' . number_format($colTotal) . '</td>';
    }
    
    $html .= '<td class="text-center fw-bold bg-primary text-white">' . number_format($grandTotal) . '</td>';
    $html .= '</tr>';
    
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';
    
    return $html;
}

function generateColumnOnlyTable($data, $columnFields, $statusMapping, $departamentosMap, $municipiosMap) {
    $html = '<table class="table table-striped table-hover">';
    $html .= '<thead class="table-dark">';
    $html .= '<tr>';
    
    foreach ($columnFields as $field) {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        if ($field === 'course_cohort') {
            $fieldName = 'Cohorte';
        }
        $html .= '<th>' . $fieldName . '</th>';
    }
    $html .= '<th class="text-end">Cantidad</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($columnFields as $field) {
            $value = isset($row[$field]) ? $row[$field] : null;
            $value = formatFieldValue($field, $value, $statusMapping, $departamentosMap, $municipiosMap);
            $html .= '<td>' . htmlspecialchars((string)$value) . '</td>';
        }
        $html .= '<td class="text-end fw-bold">' . number_format($row['count_records']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    
    return $html;
}
?>