<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit;
}

// Obtener información del usuario de la misma manera que en factura_mentorias.php
require __DIR__ . '/../filters/takeUser.php';
$infoUsuario = obtenerInformacionUsuario();
$rol = $infoUsuario['rol'];
$username = $_SESSION['username'] ?? null;

// Debug
error_log("AJAX - Rol: " . $rol);
error_log("AJAX - Username: " . $username);

if (!isset($_POST['status'])) {
    echo json_encode(['error' => 'Estado requerido']);
    exit;
}

$status = $_POST['status'];
$page = intval($_POST['page'] ?? 1);
$limit = intval($_POST['limit'] ?? 16);
$search = trim($_POST['search'] ?? '');
$offset = ($page - 1) * $limit;

function getQRCodesByStatusWithPagination($conn, $status, $search, $offset, $limit)
{
    global $rol, $username;
    
    $whereClause = '';
    $searchClause = '';
    
    // Filtro por estado
    switch ($status) {
        case 'pending':
            $whereClause = 'AND (qr.authorized_by = 0 OR qr.authorized = 0)';
            break;
        case 'authorized':
            $whereClause = 'AND qr.authorized_by != 0 AND qr.authorized != 0';
            break;
        case 'all':
            $whereClause = '';
            break;
        default:
            $whereClause = 'AND (qr.authorized_by = 0 OR qr.authorized = 0)';
    }
    
    // Filtro de búsqueda por cédula del mentor
    if (!empty($search)) {
        $searchClause = 'AND u.username LIKE ?';
    }
    
    $baseWhere = '';
    $params = [];
    $types = '';
    
    if ($rol === 'Mentor' && $username) {
        $baseWhere = 'WHERE qr.created_by = ?';
        $params[] = (int)$username;
        $types = 'i';
    } else {
        $baseWhere = 'WHERE 1=1';
    }
    
    // Consulta para contar total
    $countSql = "SELECT COUNT(*) as total FROM qr_mentorias qr LEFT JOIN users u ON qr.created_by = u.username 
                 $baseWhere $whereClause $searchClause";
    
    $countParams = $params;
    $countTypes = $types;
    
    if (!empty($search)) {
        $countParams[] = '%' . $search . '%';
        $countTypes .= 's';
    }
    
    if (!empty($countParams)) {
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param($countTypes, ...$countParams);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        $countStmt->close();
    } else {
        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
    }
    
    // Consulta para obtener datos con paginación
    $dataSql = "SELECT qr.*, u.username, u.nombre FROM qr_mentorias qr LEFT JOIN users u ON qr.created_by = u.username 
                $baseWhere $whereClause $searchClause
                ORDER BY 
                    (qr.authorized_by = 0 OR qr.authorized = 0) DESC, 
                    qr.created_at DESC
                LIMIT ? OFFSET ?";
    
    $dataParams = $params;
    $dataTypes = $types;
    
    if (!empty($search)) {
        $dataParams[] = '%' . $search . '%';
        $dataTypes .= 's';
    }
    
    $dataParams[] = $limit;
    $dataParams[] = $offset;
    $dataTypes .= 'ii';
    
    $dataStmt = $conn->prepare($dataSql);
    $dataStmt->bind_param($dataTypes, ...$dataParams);
    $dataStmt->execute();
    $dataResult = $dataStmt->get_result();
    $qrs = $dataResult->fetch_all(MYSQLI_ASSOC);
    $dataStmt->close();
    
    return [
        'data' => $qrs,
        'total' => $totalRecords
    ];
}

$result = getQRCodesByStatusWithPagination($conn, $status, $search, $offset, $limit);
$qrCodes = $result['data'];
$totalRecords = $result['total'];

// Calcular información de paginación
$totalPages = ceil($totalRecords / $limit);
$from = $offset + 1;
$to = min($offset + $limit, $totalRecords);

function generateQRCard($qr, $rol) {
    $isPending = ($qr['authorized_by'] == 0 || $qr['authorized'] == 0);
    $headerClass = $isPending ? 'bg-warning text-dark' : 'bg-teal-dark text-white';
    
    $html = '<div class="col">
        <div class="card h-100 shadow-sm">
            <div class="card-header ' . $headerClass . '">
                <h5 class="card-title mb-0 text-center">
                    ' . htmlspecialchars($qr['title']) . '';
    
    if ($isPending) {
        $html .= '<span class="badge bg-danger ms-2">Pendiente</span>';
    }
    
    $html .= '</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center">
                <div class="qr-image mb-3">
                    <img src="img/qrcodes/' . htmlspecialchars($qr['image_filename']) . '"
                        class="img-fluid"
                        alt="Código QR"
                        style="max-width: 200px;">
                </div>
                <div class="separator w-100 border-bottom my-2"></div>
                <div class="qr-info w-100">
                    <p class="card-text small text-muted mb-2">
                        <button type="button" class="btn btn-sm ms-2 copy-url-btn" title="Copiar URL" data-url="' . htmlspecialchars($qr['url']) . '">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <span class="qr-url-text">' . htmlspecialchars($qr['url']) . '</span>
                    </p>
                    <div class="separator w-100 border-bottom my-2"></div>
                    <p class="card-text small mb-0">
                        <i class="bi bi-calendar3"></i>
                        ' . date('d/m/Y H:i', strtotime($qr['created_at'])) . '
                    </p>';
    
    if ($rol !== 'Mentor') {
        $html .= '<p class="card-text small mb-0">
                        <i class="bi bi-person"></i> Creado por: <br>' . htmlspecialchars($qr['username'] ?? '') . '
                        (' . htmlspecialchars($qr['nombre'] ?? '') . ')
                    </p>';
    }
    
    $html .= '</div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between gap-2">
                    <button class="btn bg-teal-dark text-white btn-sm flex-grow-1 download-qr"
                        data-filename="' . htmlspecialchars($qr['image_filename']) . '">
                        <i class="bi bi-download"></i> Descargar QR
                    </button>';
    
    // Verificar rol para botón de eliminación - USAR EXACTAMENTE LA MISMA COMPARACIÓN
    if ($rol === 'Control maestro') {
        $html .= '<button class="btn btn-danger btn-sm delete-qr"
                        data-id="' . $qr['id'] . '"
                        data-title="' . htmlspecialchars($qr['title']) . '"
                        data-filename="' . htmlspecialchars($qr['image_filename']) . '">
                        <i class="bi bi-trash"></i>
                    </button>';
    }
    
    $html .= '</div>';
    
    // Verificar rol para botón de asignación - USAR EXACTAMENTE LA MISMA COMPARACIÓN
    if ($rol === 'Administrador' || $rol === 'Control maestro') {
        $html .= '<div class="d-flex justify-content-center mt-2">
                    <button class="btn btn-warning btn-sm edit-qr w-100"
                        data-id="' . $qr['id'] . '"
                        data-title="' . htmlspecialchars($qr['title']) . '"
                        data-url="' . htmlspecialchars($qr['url']) . '"
                        data-clases="' . (int)$qr['clases_equivalentes'] . '"
                        data-filename="' . htmlspecialchars($qr['image_filename']) . '">
                        <i class="bi bi-pencil"></i> Asignar clases
                    </button>
                </div>';
    }
    
    $html .= '</div>
        </div>
    </div>';
    
    return $html;
}

$cardsHtml = '';
foreach ($qrCodes as $qr) {
    $cardsHtml .= generateQRCard($qr, $rol);
}

// Si no hay resultados
if (empty($qrCodes)) {
    $message = !empty($search) ? 'No se encontraron códigos QR para la cédula: ' . htmlspecialchars($search) : 'No hay códigos QR disponibles.';
    $cardsHtml = '<div class="col-12 text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="text-muted mt-3">' . $message . '</h4>
    </div>';
}

echo json_encode([
    'success' => true,
    'html' => $cardsHtml,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total' => $totalRecords,
        'from' => $totalRecords > 0 ? $from : 0,
        'to' => $totalRecords > 0 ? $to : 0,
        'per_page' => $limit
    ],
    'debug' => [
        'rol' => $rol,
        'username' => $username,
        'infoUsuario' => $infoUsuario
    ]
]);
?>