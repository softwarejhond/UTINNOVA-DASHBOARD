<?php
// filepath: c:\xampp\htdocs\DASBOARD-ADMIN-MINTICS\mover_comprobantes.php

/**
 * Script para copiar archivos de comprobantesAsistenciaNotas a R7_PART1
 * con compresión de imágenes - VERSIÓN DE PRUEBA (LIMITADO A 10 CARPETAS)
 * CONSERVA LOS ARCHIVOS ORIGINALES
 */

// Configuración de directorios
$sourceDir = __DIR__ . '/comprobantesAsistenciaNotas';
$targetDir = __DIR__ . '/R8_L1_FALTANTES';

// LÍMITE PARA PRUEBA
$maxCarpetasProcesar = 1300;

// Configuración de compresión de imágenes
$compressionQuality = 75; // Calidad de compresión (1-100)
$maxWidth = 1920; // Ancho máximo de imagen
$maxHeight = 1080; // Alto máximo de imagen

// Log de operaciones
$logFile = __DIR__ . '/mover_comprobantes_log.txt';

/**
 * Función para escribir en el log
 */
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo "[$timestamp] $message" . PHP_EOL;
}

/**
 * Función para comprimir imágenes
 */
function compressImage($sourcePath, $targetPath, $quality = 75, $maxWidth = 1920, $maxHeight = 1080) {
    try {
        // Obtener información de la imagen
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $imageType = $imageInfo[2];

        // Calcular nuevas dimensiones manteniendo la proporción
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
        $newWidth = $originalWidth * $ratio;
        $newHeight = $originalHeight * $ratio;

        // Crear imagen desde el archivo fuente
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if ($sourceImage === false) {
            return false;
        }

        // Crear nueva imagen con las dimensiones calculadas
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG y GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar la imagen
        imagecopyresampled(
            $targetImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );

        // Crear el directorio de destino si no existe
        $targetDirectory = dirname($targetPath);
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        // Guardar la imagen comprimida
        $result = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($targetImage, $targetPath, $quality);
                break;
            case IMAGETYPE_PNG:
                // Para PNG, convertir calidad de 0-100 a 0-9
                $pngQuality = 9 - round(($quality / 100) * 9);
                $result = imagepng($targetImage, $targetPath, $pngQuality);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($targetImage, $targetPath);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($targetImage, $targetPath, $quality);
                break;
        }

        // Liberar memoria
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $result;
    } catch (Exception $e) {
        writeLog("Error al comprimir imagen $sourcePath: " . $e->getMessage());
        return false;
    }
}

/**
 * Función para verificar si un archivo es una imagen
 */
function isImage($filePath) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return in_array($extension, $imageExtensions);
}

/**
 * Función para obtener el tamaño de archivo en formato legible
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Función principal para copiar archivos
 */
function copiarArchivos() {
    global $sourceDir, $targetDir, $compressionQuality, $maxWidth, $maxHeight, $maxCarpetasProcesar;

    writeLog("=== INICIANDO PROCESO DE COPIA CON COMPRESIÓN - MODO PRUEBA ===");
    writeLog("LÍMITE: Solo se procesarán $maxCarpetasProcesar carpetas");
    writeLog("MODO: COPIA (conserva archivos originales)");
    writeLog("Directorio origen: $sourceDir");
    writeLog("Directorio destino: $targetDir");

    // Verificar que los directorios existan
    if (!is_dir($sourceDir)) {
        writeLog("ERROR: El directorio origen no existe: $sourceDir");
        return false;
    }

    if (!is_dir($targetDir)) {
        writeLog("ERROR: El directorio destino no existe: $targetDir");
        return false;
    }

    // Obtener carpetas del directorio destino (R7_PART1)
    $targetFolders = array_filter(scandir($targetDir), function($item) use ($targetDir) {
        return $item !== '.' && $item !== '..' && is_dir($targetDir . '/' . $item);
    });

    // LIMITAMOS A SOLO LAS PRIMERAS CARPETAS PARA LA PRUEBA
    $targetFolders = array_slice($targetFolders, 0, $maxCarpetasProcesar);

    writeLog("Carpetas totales en R7_PARTE_2: " . count(array_filter(scandir($targetDir), function($item) use ($targetDir) {
        return $item !== '.' && $item !== '..' && is_dir($targetDir . '/' . $item);
    })));
    writeLog("Carpetas a procesar en esta prueba: " . count($targetFolders));
    writeLog("Carpetas seleccionadas: " . implode(', ', $targetFolders));

    $totalProcesados = 0;
    $totalCopiados = 0;
    $totalComprimidos = 0;
    $totalErrores = 0;
    $totalSaltados = 0;
    $espacioOriginal = 0;
    $espacioComprimido = 0;
    $carpetasProcesadas = 0;

    foreach ($targetFolders as $folderName) {
        $carpetasProcesadas++;
        
        $sourceFolderPath = $sourceDir . '/' . $folderName;
        $targetFolderPath = $targetDir . '/' . $folderName;

        // Verificar si existe la carpeta correspondiente en el directorio origen
        if (!is_dir($sourceFolderPath)) {
            writeLog("SKIP: No existe carpeta $folderName en comprobantesAsistenciaNotas");
            continue;
        }

        writeLog("Procesando carpeta $carpetasProcesadas/$maxCarpetasProcesar: $folderName");

        // Obtener archivos de la carpeta origen
        $files = array_filter(scandir($sourceFolderPath), function($item) use ($sourceFolderPath) {
            return $item !== '.' && $item !== '..' && is_file($sourceFolderPath . '/' . $item);
        });

        writeLog("  - Archivos encontrados: " . count($files));

        foreach ($files as $fileName) {
            $sourceFilePath = $sourceFolderPath . '/' . $fileName;
            $targetFilePath = $targetFolderPath . '/' . $fileName;

            $totalProcesados++;

            try {
                // Verificar si el archivo ya existe en destino
                if (file_exists($targetFilePath)) {
                    writeLog("  SKIP: El archivo ya existe en destino: $fileName");
                    $totalSaltados++;
                    continue;
                }

                $originalSize = filesize($sourceFilePath);
                $espacioOriginal += $originalSize;

                if (isImage($sourceFilePath)) {
                    // Comprimir y copiar imagen
                    $success = compressImage(
                        $sourceFilePath, 
                        $targetFilePath, 
                        $compressionQuality, 
                        $maxWidth, 
                        $maxHeight
                    );

                    if ($success) {
                        $compressedSize = filesize($targetFilePath);
                        $espacioComprimido += $compressedSize;
                        $savedSpace = $originalSize - $compressedSize;
                        $compressionPercent = round(($savedSpace / $originalSize) * 100, 2);

                        writeLog("  COMPRIMIDO: $fileName - " . 
                               formatBytes($originalSize) . " → " . 
                               formatBytes($compressedSize) . 
                               " (Ahorro: {$compressionPercent}%)");
                        
                        $totalComprimidos++;
                        $totalCopiados++;

                        // NOTA: NO eliminamos el archivo original
                    } else {
                        writeLog("  ERROR: No se pudo comprimir la imagen: $fileName");
                        $totalErrores++;
                    }
                } else {
                    // Copiar archivo no-imagen directamente
                    if (copy($sourceFilePath, $targetFilePath)) {
                        $copiedSize = filesize($targetFilePath);
                        $espacioComprimido += $copiedSize;
                        
                        writeLog("  COPIADO: $fileName - " . formatBytes($originalSize));
                        $totalCopiados++;
                        
                        // NOTA: NO eliminamos el archivo original
                    } else {
                        writeLog("  ERROR: No se pudo copiar el archivo: $fileName");
                        $totalErrores++;
                    }
                }
            } catch (Exception $e) {
                writeLog("  ERROR: Excepción al procesar $fileName: " . $e->getMessage());
                $totalErrores++;
            }
        }

        writeLog("Carpeta $folderName completada");
        writeLog("---");
    }

    $espacioAhorrado = $espacioOriginal - $espacioComprimido;
    $ahorroPercentTotal = $espacioOriginal > 0 ? round(($espacioAhorrado / $espacioOriginal) * 100, 2) : 0;

    writeLog("=== RESUMEN DE LA PRUEBA ===");
    writeLog("Carpetas procesadas: $carpetasProcesadas de $maxCarpetasProcesar");
    writeLog("Archivos procesados: $totalProcesados");
    writeLog("Archivos copiados exitosamente: $totalCopiados");
    writeLog("Archivos saltados (ya existían): $totalSaltados");
    writeLog("Imágenes comprimidas: $totalComprimidos");
    writeLog("Errores: $totalErrores");
    writeLog("Espacio original total: " . formatBytes($espacioOriginal));
    writeLog("Espacio final total: " . formatBytes($espacioComprimido));
    writeLog("Espacio ahorrado total: " . formatBytes($espacioAhorrado) . " ({$ahorroPercentTotal}%)");
    writeLog("IMPORTANTE: Los archivos originales se mantuvieron intactos");
    writeLog("=== PRUEBA COMPLETADA ===");

    return true;
}

// Verificar extensiones requeridas
if (!extension_loaded('gd')) {
    writeLog("ERROR: La extensión GD no está instalada. Necesaria para compresión de imágenes.");
    exit(1);
}

// Ejecutar el script si se llama directamente
if (php_sapi_name() === 'cli' || basename($_SERVER['SCRIPT_NAME']) === 'mover_comprobantes.php') {
    // Aumentar límite de tiempo de ejecución
    set_time_limit(0);
    ini_set('memory_limit', '512M');

    echo "=== COPIA DE COMPROBANTES CON COMPRESIÓN - MODO PRUEBA ===\n";
    echo "⚠️  ATENCIÓN: Solo se procesarán las primeras 10 carpetas\n";
    echo "✅ MODO SEGURO: Los archivos originales NO se eliminarán\n";
    echo "Presiona Enter para continuar o Ctrl+C para cancelar...";
    
    if (php_sapi_name() === 'cli') {
        fgets(STDIN);
    }

    $resultado = copiarArchivos();
    
    if ($resultado) {
        echo "\n✅ Prueba completada exitosamente!\n";
        echo "📁 Los archivos originales permanecen intactos\n";
        echo "Revisa el archivo de log: mover_comprobantes_log.txt\n";
        echo "Si todo se ve bien, puedes cambiar \$maxCarpetasProcesar a un número mayor o eliminarlo.\n";
    } else {
        echo "\n❌ Error durante la prueba.\n";
        echo "Revisa el archivo de log para más detalles: mover_comprobantes_log.txt\n";
    }
}
?>