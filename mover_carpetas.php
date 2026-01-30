<?php
include("conexion.php");
set_time_limit(0); // Sin límite de tiempo
ini_set('memory_limit', '512M');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Mover Carpetas por Lotes</title></head><body>";
echo "<h1>Script de Organización de Carpetas por Lotes</h1>";

// Directorios base
$baseDir = "comprobantesAsistenciaNotas";
$lote1Dir = "$baseDir/LOTE 1/Vigencia (2024-2025)";
$lote2Dir = "$baseDir/LOTE 2/Vigencia (2024-2025)";

// Crear directorios de destino si no existen
if (!file_exists($lote1Dir)) {
    mkdir($lote1Dir, 0755, true);
    echo "<p>✓ Creado directorio: $lote1Dir</p>";
}

if (!file_exists($lote2Dir)) {
    mkdir($lote2Dir, 0755, true);
    echo "<p>✓ Creado directorio: $lote2Dir</p>";
}

// Función para mover carpeta
function moverCarpeta($origen, $destino, $cedula) {
    if (file_exists($origen) && is_dir($origen)) {
        if (rename($origen, $destino)) {
            return "✓ Movido: $cedula de $origen a $destino";
        } else {
            return "✗ Error moviendo: $cedula de $origen a $destino";
        }
    }
    return "⚠ No existe carpeta para: $cedula en $origen";
}

// Obtener todas las carpetas de cédulas existentes
$carpetasExistentes = [];
if (is_dir($baseDir)) {
    $dirIterator = new DirectoryIterator($baseDir);
    foreach ($dirIterator as $item) {
        if ($item->isDot() || !$item->isDir()) continue;
        
        $folderName = $item->getFilename();
        
        // Saltar carpetas que ya son de lotes
        if (in_array($folderName, ['LOTE 1', 'LOTE 2'])) continue;
        
        // Solo procesar carpetas que parezcan números de cédula
        if (preg_match('/^\d+$/', $folderName)) {
            $carpetasExistentes[] = $folderName;
        }
    }
}

echo "<h2>Carpetas encontradas: " . count($carpetasExistentes) . "</h2>";

if (empty($carpetasExistentes)) {
    echo "<p>No se encontraron carpetas de cédulas para mover.</p>";
} else {
    // Consultar el lote de cada cédula
    $moved = 0;
    $errors = 0;
    $notFound = 0;
    
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    
    foreach ($carpetasExistentes as $cedula) {
        // Buscar el lote de la cédula en la base de datos
        $sql = "SELECT ur.lote, g.full_name 
                FROM user_register ur 
                LEFT JOIN groups g ON ur.number_id = g.number_id 
                WHERE ur.number_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $lote = $row['lote'];
            $nombreCompleto = $row['full_name'] ?? 'Nombre no disponible';
            
            $origen = "$baseDir/$cedula";
            
            if ($lote == 1) {
                $destino = "$lote1Dir/$cedula";
                $mensaje = moverCarpeta($origen, $destino, $cedula);
                echo "<p style='color: green;'>$mensaje - $nombreCompleto (Lote 1)</p>";
                if (strpos($mensaje, '✓') === 0) $moved++;
                else $errors++;
                
            } elseif ($lote == 2) {
                $destino = "$lote2Dir/$cedula";
                $mensaje = moverCarpeta($origen, $destino, $cedula);
                echo "<p style='color: blue;'>$mensaje - $nombreCompleto (Lote 2)</p>";
                if (strpos($mensaje, '✓') === 0) $moved++;
                else $errors++;
                
            } else {
                echo "<p style='color: orange;'>⚠ Cédula $cedula - $nombreCompleto: Lote no válido ($lote)</p>";
                $notFound++;
            }
        } else {
            echo "<p style='color: red;'>✗ Cédula $cedula: No encontrada en la base de datos</p>";
            $notFound++;
        }
        
        $stmt->close();
        
        // Flush para mostrar progreso en tiempo real
        flush();
    }
    
    echo "</div>";
    
    // Resumen final
    echo "<h2>Resumen del Proceso</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Total carpetas procesadas:</strong> " . count($carpetasExistentes) . "</p>";
    echo "<p><strong>Carpetas movidas exitosamente:</strong> <span style='color: green;'>$moved</span></p>";
    echo "<p><strong>Errores en el movimiento:</strong> <span style='color: red;'>$errors</span></p>";
    echo "<p><strong>No encontradas/lote inválido:</strong> <span style='color: orange;'>$notFound</span></p>";
    echo "</div>";
    
    // Mostrar estructura resultante
    echo "<h2>Estructura Resultante</h2>";
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; font-family: monospace;'>";
    echo "comprobantesAsistenciaNotas/<br>";
    
    // Contar carpetas en cada lote
    $countLote1 = 0;
    $countLote2 = 0;
    
    if (is_dir($lote1Dir)) {
        $lote1Iterator = new DirectoryIterator($lote1Dir);
        foreach ($lote1Iterator as $item) {
            if (!$item->isDot() && $item->isDir() && preg_match('/^\d+$/', $item->getFilename())) {
                $countLote1++;
            }
        }
    }
    
    if (is_dir($lote2Dir)) {
        $lote2Iterator = new DirectoryIterator($lote2Dir);
        foreach ($lote2Iterator as $item) {
            if (!$item->isDot() && $item->isDir() && preg_match('/^\d+$/', $item->getFilename())) {
                $countLote2++;
            }
        }
    }
    
    echo "├── LOTE 1/<br>";
    echo "│   └── Vigencia (2024-2025)/ <strong>($countLote1 carpetas)</strong><br>";
    echo "└── LOTE 2/<br>";
    echo "    └── Vigencia (2024-2025)/ <strong>($countLote2 carpetas)</strong><br>";
    echo "</div>";
}

// Botón para ejecutar de nuevo si es necesario
echo "<br><a href='mover_carpetas.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ejecutar de Nuevo</a>";

echo "</body></html>";
?>