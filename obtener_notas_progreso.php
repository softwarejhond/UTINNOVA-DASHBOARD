<?php
// Script con vista de progreso para obtener notas

// Configurar para mostrar salida inmediatamente
ob_implicit_flush(true);
ob_end_flush();

// Aumentar el tiempo máximo de ejecución
set_time_limit(0);

// Incluir conexión
require_once 'conexion.php';

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión a la base de datos");
}

// Configuración del API de Moodle
$apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
$token = '3f158134506350615397c83d861c2104';
$format = 'json';

// Función para obtener las notas de un estudiante
function obtenerNotas($username, $courseid) {
    global $apiUrl, $token, $format;
    
    $functionGetUser = 'core_user_get_users_by_field';
    $paramsUser = ['field' => 'username', 'values[0]' => $username];
    $postdataUser = http_build_query(['wstoken' => $token, 'wsfunction' => $functionGetUser, 'moodlewsrestformat' => $format] + $paramsUser);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdataUser);
    
    $responseUser = curl_exec($ch);
    $userData = json_decode($responseUser, true);
    
    if (empty($userData)) {
        curl_close($ch);
        return ['nota1' => 0, 'nota2' => 0];
    }
    
    $userid = $userData[0]['id'];
    
    $function = 'gradereport_user_get_grade_items';
    $params = ['courseid' => $courseid, 'userid' => $userid];
    $postdata = http_build_query(['wstoken' => $token, 'wsfunction' => $function, 'moodlewsrestformat' => $format] + $params);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    $response = curl_exec($ch);
    
    if ($response === false) {
        curl_close($ch);
        return ['nota1' => 0, 'nota2' => 0];
    }
    
    $data = json_decode($response, true);
    curl_close($ch);
    
    if ($data === null || !isset($data['usergrades'][0])) {
        return ['nota1' => 0, 'nota2' => 0];
    }
    
    $usergrade = $data['usergrades'][0];
    $notas = [];
    
    if (isset($usergrade['gradeitems'])) {
        foreach ($usergrade['gradeitems'] as $item) {
            if (isset($item['graderaw']) && $item['graderaw'] !== null) {
                $notas[] = $item['graderaw'];
                if (count($notas) == 2) break;
            }
        }
    }
    
    return [
        'nota1' => isset($notas[0]) ? $notas[0] : 0, 
        'nota2' => isset($notas[1]) ? $notas[1] : 0
    ];
}

// Función para guardar las notas
function guardarNotas($number_id, $nota1, $nota2, $code) {
    global $conn;
    try {
        $sql = "INSERT INTO notas_estudiantes (number_id, nota1, nota2, code) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE nota1 = ?, nota2 = ?, code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddsdds", $number_id, $nota1, $nota2, $code, $nota1, $nota2, $code);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obtener Notas - Progreso</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .progress-bar { width: 100%; height: 30px; background-color: #e0e0e0; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4CAF50, #45a049); transition: width 0.3s ease; }
        .status { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Obteniendo Notas de Estudiantes</h1>
        
        <?php
        // Obtener estudiantes
        $sql = "SELECT number_id, id_bootcamp FROM groups WHERE number_id IS NOT NULL AND id_bootcamp IS NOT NULL";
        $result = $conn->query($sql);

        if (!$result || $result->num_rows == 0) {
            echo '<div class="status error">❌ No se encontraron estudiantes para procesar.</div>';
            exit;
        }

        $estudiantes = $result->fetch_all(MYSQLI_ASSOC);
        $total = count($estudiantes);
        $procesados = 0;
        $exitosos = 0;
        $errores = 0;

        echo '<div class="status info">📊 Se encontraron <strong>' . $total . '</strong> estudiantes para procesar.</div>';
        echo '<div class="progress-bar"><div class="progress-fill" id="progressFill" style="width: 0%"></div></div>';
        echo '<div class="stats">
                <div class="stat-box"><div class="stat-number" id="totalCount">' . $total . '</div><div class="stat-label">Total</div></div>
                <div class="stat-box"><div class="stat-number" id="processedCount">0</div><div class="stat-label">Procesados</div></div>
                <div class="stat-box"><div class="stat-number" id="successCount">0</div><div class="stat-label">Exitosos</div></div>
                <div class="stat-box"><div class="stat-number" id="errorCount">0</div><div class="stat-label">Errores</div></div>
              </div>';
        
        echo '<div id="log" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
        
        // Procesar cada estudiante
        foreach ($estudiantes as $estudiante) {
            $number_id = $estudiante['number_id'];
            $id_bootcamp = $estudiante['id_bootcamp'];
            
            echo '<div>⏳ Procesando estudiante: <strong>' . htmlspecialchars($number_id) . '</strong> (Curso: ' . htmlspecialchars($id_bootcamp) . ')...</div>';
            flush();
            
            // Obtener notas
            $notas = obtenerNotas($number_id, $id_bootcamp);
            
            // Guardar notas
            if (guardarNotas($number_id, $notas['nota1'], $notas['nota2'], $id_bootcamp)) {
                echo '<div class="status success">✅ Estudiante ' . htmlspecialchars($number_id) . ' -> Nota1: ' . $notas['nota1'] . ', Nota2: ' . $notas['nota2'] . '</div>';
                $exitosos++;
            } else {
                echo '<div class="status error">❌ Error al guardar notas para el estudiante ' . htmlspecialchars($number_id) . '</div>';
                $errores++;
            }
            
            $procesados++;
            $progreso = ($procesados / $total) * 100;
            
            // Actualizar progreso con JavaScript
            echo '<script>
                    document.getElementById("progressFill").style.width = "' . $progreso . '%";
                    document.getElementById("processedCount").textContent = "' . $procesados . '";
                    document.getElementById("successCount").textContent = "' . $exitosos . '";
                    document.getElementById("errorCount").textContent = "' . $errores . '";
                  </script>';
            flush();
            
            // Pequeña pausa para visualizar el progreso
            usleep(100000); // 0.1 segundos
        }
        
        echo '</div>';
        echo '<div class="status success">🎉 <strong>Proceso completado!</strong> Se procesaron ' . $procesados . ' de ' . $total . ' estudiantes. Exitosos: ' . $exitosos . ', Errores: ' . $errores . '</div>';
        
        $conn->close();
        ?>
        
        <div style="margin-top: 20px; text-align: center;">
            <button onclick="location.reload()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">🔄 Ejecutar de Nuevo</button>
            <a href="javascript:history.back()" style="margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">← Volver</a>
        </div>
    </div>
</body>
</html>