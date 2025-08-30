<?php
session_start();
require __DIR__ . '/../../conexion.php';

// Habilitar reporte de errores detallado
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establecer el Content-Type para JSON
header('Content-Type: application/json');

// Función para enviar respuesta JSON y terminar
function sendJsonResponse($success, $message) {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método no permitido');
}

// Obtener datos del formulario
$id_constancia = $_POST['id_constancia'] ?? '';
$serie_constancia = $_POST['serie_constancia'] ?? '';

// Validar datos requeridos
if (empty($id_constancia) || empty($serie_constancia)) {
    sendJsonResponse(false, 'Datos incompletos. ID y serie de constancia son requeridos.');
}

try {
    // Iniciar transacción
    mysqli_autocommit($conn, false);

    // 1. Verificar que la constancia existe en la base de datos
    $checkQuery = "SELECT id, serie_constancia FROM constancias_emitidas WHERE id = ? AND serie_constancia = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    
    if (!$checkStmt) {
        throw new Exception("Error al preparar consulta de verificación: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($checkStmt, "is", $id_constancia, $serie_constancia);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) === 0) {
        mysqli_stmt_close($checkStmt);
        throw new Exception("La constancia no existe en la base de datos");
    }
    
    mysqli_stmt_close($checkStmt);

    // 2. Eliminar el archivo PDF si existe
    $rutaArchivo = __DIR__ . '/../../constancias/' . $serie_constancia . '.pdf';
    $archivoEliminado = false;
    
    if (file_exists($rutaArchivo)) {
        if (unlink($rutaArchivo)) {
            $archivoEliminado = true;
            error_log("Archivo eliminado: " . $rutaArchivo);
        } else {
            error_log("No se pudo eliminar el archivo: " . $rutaArchivo);
            // No lanzamos excepción aquí para permitir eliminar el registro aunque el archivo no se pueda eliminar
        }
    } else {
        error_log("Archivo no existe: " . $rutaArchivo);
        $archivoEliminado = true; // Consideramos exitoso si el archivo ya no existe
    }

    // 3. Eliminar el registro de la base de datos
    $deleteQuery = "DELETE FROM constancias_emitidas WHERE id = ? AND serie_constancia = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    
    if (!$deleteStmt) {
        throw new Exception("Error al preparar consulta de eliminación: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($deleteStmt, "is", $id_constancia, $serie_constancia);
    
    if (!mysqli_stmt_execute($deleteStmt)) {
        throw new Exception("Error al eliminar registro de la base de datos: " . mysqli_stmt_error($deleteStmt));
    }

    $filasAfectadas = mysqli_stmt_affected_rows($deleteStmt);
    mysqli_stmt_close($deleteStmt);

    if ($filasAfectadas === 0) {
        throw new Exception("No se pudo eliminar el registro de la base de datos");
    }

    // Confirmar transacción
    mysqli_commit($conn);

    // Log de la operación
    $usuario_sesion = $_SESSION['username'] ?? 'Usuario desconocido';
    error_log("Constancia eliminada - ID: $id_constancia, Serie: $serie_constancia, Usuario: $usuario_sesion, Archivo eliminado: " . ($archivoEliminado ? 'Sí' : 'No'));

    // Respuesta exitosa
    $mensaje = "Constancia eliminada correctamente";
    if (!$archivoEliminado) {
        $mensaje .= " (registro eliminado de BD, pero el archivo no se pudo eliminar)";
    }

    sendJsonResponse(true, $mensaje);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conn);
    
    error_log("Error al eliminar constancia - ID: $id_constancia, Serie: $serie_constancia, Error: " . $e->getMessage());
    sendJsonResponse(false, 'Error al eliminar la constancia: ' . $e->getMessage());

} finally {
    // Restaurar autocommit
    mysqli_autocommit($conn, true);
}
?>