<?php
// Procesar solicitudes AJAX PRIMERO, antes de cualquier HTML
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'check_connections') {
        checkDatabaseConnections();
    } elseif ($_GET['action'] === 'migrate') {
        performMigration();
    }
    exit;
}

function checkDatabaseConnections() {
    try {
        // Incluir archivos de conexión
        include_once 'controller/conexion.php';
        include_once 'controller/conexion_empleos.php';
        
        if (!$conn) {
            echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos dashboard']);
            return;
        }
        
        if (!$connEmpleos) {
            echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos plataforma_empleos']);
            return;
        }
        
        echo json_encode(['success' => true, 'message' => 'Conexiones exitosas a ambas bases de datos']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function performMigration() {
    $log = [];
    $migratedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    try {
        // Incluir archivos de conexión
        include_once 'controller/conexion.php';
        include_once 'controller/conexion_empleos.php';
        
        $log[] = "🔄 Iniciando migración de usuarios...";
        
        // Consulta para obtener datos de ambas tablas con JOIN
        $query = "
            SELECT DISTINCT
                g.email,
                g.number_id,
                ur.first_name,
                ur.second_name,
                ur.first_last,
                ur.second_last,
                ur.first_phone
            FROM groups g
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.email IS NOT NULL 
            AND g.email != ''
            AND g.number_id IS NOT NULL
        ";
        
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("Error en la consulta: " . mysqli_error($conn));
        }
        
        $totalUsers = mysqli_num_rows($result);
        $log[] = "📊 Total de usuarios encontrados: $totalUsers";
        
        if ($totalUsers == 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'No se encontraron usuarios para migrar',
                'log' => $log
            ]);
            return;
        }
        
        while ($row = mysqli_fetch_assoc($result)) {
            try {
                // Verificar si el usuario ya existe en la tabla destino
                $checkQuery = "SELECT id FROM usuarios WHERE email = ? OR numero_id = ?";
                $checkStmt = mysqli_prepare($connEmpleos, $checkQuery);
                mysqli_stmt_bind_param($checkStmt, "ss", $row['email'], $row['number_id']);
                mysqli_stmt_execute($checkStmt);
                $checkResult = mysqli_stmt_get_result($checkStmt);
                
                if (mysqli_num_rows($checkResult) > 0) {
                    $skippedCount++;
                    $log[] = "⏭️ Usuario ya existe: " . $row['email'];
                    mysqli_stmt_close($checkStmt);
                    continue;
                }
                
                // Preparar datos para inserción
                $hashedPassword = password_hash($row['number_id'], PASSWORD_DEFAULT);
                $primer_nombre = $row['first_name'] ?? '';
                $segundo_nombre = $row['second_name'] ?? '';
                $primer_apellido = $row['first_last'] ?? '';
                $segundo_apellido = $row['second_last'] ?? '';
                $telefono = $row['first_phone'] ?? '';
                
                // Insertar nuevo usuario
                $insertQuery = "
                    INSERT INTO usuarios 
                    (email, numero_id, password, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, telefono, tipo, foto_perfil)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'candidato', 'default.jpg')
                ";
                
                $insertStmt = mysqli_prepare($connEmpleos, $insertQuery);
                mysqli_stmt_bind_param(
                    $insertStmt, 
                    "ssssssss", 
                    $row['email'], 
                    $row['number_id'], 
                    $hashedPassword, 
                    $primer_nombre, 
                    $segundo_nombre, 
                    $primer_apellido, 
                    $segundo_apellido, 
                    $telefono
                );
                
                if (mysqli_stmt_execute($insertStmt)) {
                    $migratedCount++;
                    $log[] = "✅ Usuario migrado: " . $row['email'];
                } else {
                    $errorCount++;
                    $log[] = "❌ Error al migrar: " . $row['email'] . " - " . mysqli_error($connEmpleos);
                }
                
                mysqli_stmt_close($insertStmt);
                mysqli_stmt_close($checkStmt);
                
            } catch (Exception $e) {
                $errorCount++;
                $log[] = "❌ Error procesando usuario " . ($row['email'] ?? 'email desconocido') . ": " . $e->getMessage();
            }
        }
        
        $log[] = "🎉 Migración completada:";
        $log[] = "   • Usuarios migrados: $migratedCount";
        $log[] = "   • Usuarios omitidos (ya existían): $skippedCount";
        $log[] = "   • Errores: $errorCount";
        
        echo json_encode([
            'success' => true,
            'message' => "Migración completada: $migratedCount usuarios migrados, $skippedCount omitidos, $errorCount errores",
            'log' => $log,
            'stats' => [
                'migrated' => $migratedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
                'total' => $totalUsers
            ]
        ]);
        
    } catch (Exception $e) {
        $log[] = "❌ Error fatal: " . $e->getMessage();
        echo json_encode([
            'success' => false,
            'message' => 'Error en la migración: ' . $e->getMessage(),
            'log' => $log
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración de Usuarios - Dashboard a Plataforma Empleos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .progress-container {
            margin: 20px 0;
            display: none;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background-color: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #28a745;
            width: 0%;
            transition: width 0.3s ease;
            text-align: center;
            line-height: 30px;
            color: white;
            font-weight: bold;
        }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .status.info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .log {
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Migración de Usuarios</h1>
        <p>Esta herramienta migrará los usuarios desde la base de datos <strong>dashboard</strong> hacia la base de datos <strong>plataforma_empleos</strong>.</p>
        
        <div class="status info" id="connectionStatus">
            ⏳ Verificando conexiones a las bases de datos...
        </div>

        <button class="btn" id="startMigration" onclick="startMigration()" disabled>
            🔄 Iniciar Migración
        </button>

        <div class="progress-container" id="progressContainer">
            <h3>Progreso de la migración:</h3>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill">0%</div>
            </div>
            <div id="currentStatus"></div>
        </div>

        <div class="log" id="migrationLog"></div>
    </div>

    <script>
        // Verificar conexiones al cargar la página
        window.onload = function() {
            checkConnections();
        };

        function checkConnections() {
            fetch('sige_migration.php?action=check_connections')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const statusDiv = document.getElementById('connectionStatus');
                    const startBtn = document.getElementById('startMigration');
                    
                    if (data.success) {
                        statusDiv.className = 'status success';
                        statusDiv.innerHTML = '✅ ' + data.message;
                        startBtn.disabled = false;
                    } else {
                        statusDiv.className = 'status error';
                        statusDiv.innerHTML = '❌ ' + data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const statusDiv = document.getElementById('connectionStatus');
                    statusDiv.className = 'status error';
                    statusDiv.innerHTML = '❌ Error al verificar conexiones';
                });
        }

        function startMigration() {
            const startBtn = document.getElementById('startMigration');
            const progressContainer = document.getElementById('progressContainer');
            const migrationLog = document.getElementById('migrationLog');
            
            startBtn.disabled = true;
            startBtn.innerHTML = '⏳ Migrando...';
            progressContainer.style.display = 'block';
            migrationLog.style.display = 'block';
            migrationLog.innerHTML = '';

            // Realizar la migración
            fetch('sige_migration.php?action=migrate')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    updateProgress(data);
                    startBtn.disabled = false;
                    startBtn.innerHTML = '🔄 Iniciar Migración';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('currentStatus').innerHTML = '❌ Error en la migración';
                    document.getElementById('currentStatus').className = 'status error';
                    startBtn.disabled = false;
                    startBtn.innerHTML = '🔄 Iniciar Migración';
                });
        }

        function updateProgress(data) {
            const progressFill = document.getElementById('progressFill');
            const currentStatus = document.getElementById('currentStatus');
            const migrationLog = document.getElementById('migrationLog');
            
            if (data.success) {
                progressFill.style.width = '100%';
                progressFill.innerHTML = '100%';
                currentStatus.innerHTML = `✅ ${data.message}`;
                currentStatus.className = 'status success';
            } else {
                currentStatus.innerHTML = `❌ ${data.message}`;
                currentStatus.className = 'status error';
            }
            
            // Mostrar log detallado
            if (data.log) {
                migrationLog.innerHTML = data.log.join('<br>');
            }
        }
    </script>
</body>
</html>