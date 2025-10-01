<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use thiagoalessio\TesseractOCR\TesseractOCR;

set_time_limit(0);
ini_set('memory_limit', '2048M');

function processExcelForVerification($filePath, $conn) {
    try {
        session_start(); // Asegurar que la sesión está iniciada
        
        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        
        $results = [];
        $totalRows = $highestRow - 1; // Restar 1 para no contar la fila de encabezados
        
        // Inicializar el progreso
        $_SESSION['verification_progress'] = [
            'current' => 0,
            'total' => $totalRows,
            'percentage' => 0,
            'status' => 'preparando'
        ];
        session_write_close(); // Cerrar la sesión para evitar bloqueos
        
        // Procesar desde la fila 2 (primera fila son headers)
        for ($row = 2; $row <= $highestRow; $row++) {
            // Actualizar el estado de progreso
            session_start();
            $_SESSION['verification_progress'] = [
                'current' => $row - 2, // Ajustar el índice para que empiece en 0
                'total' => $totalRows,
                'percentage' => round((($row - 2) / $totalRows) * 100),
                'status' => 'procesando'
            ];
            session_write_close();
            
            $numberID = $worksheet->getCell('D' . $row)->getValue();
            $name1 = $worksheet->getCell('E' . $row)->getValue();
            $name2 = $worksheet->getCell('F' . $row)->getValue();
            $lastname1 = $worksheet->getCell('G' . $row)->getValue();
            $lastname2 = $worksheet->getCell('H' . $row)->getValue();
            $birthdate = $worksheet->getCell('I' . $row)->getValue();
            $frontImageUrl = $worksheet->getCell('BB' . $row)->getValue();
            $backImageUrl = $worksheet->getCell('BC' . $row)->getValue();
            
            // Convertir fecha de Excel a formato normal si es necesario
            if (is_numeric($birthdate)) {
                $birthdate = date('Y-m-d', ($birthdate - 25569) * 86400);
            }
            
            // Actualizar estado a 'descargando'
            session_start();
            $_SESSION['verification_progress']['status'] = 'descargando';
            session_write_close();
            
            // Procesar la verificación
            $verificationResult = verifyDocument($numberID, $name1, $name2, $lastname1, $lastname2, $birthdate, $frontImageUrl, $backImageUrl);
            
            // Actualizar estado a 'guardando'
            session_start();
            $_SESSION['verification_progress']['status'] = 'guardando';
            session_write_close();
            
            // Guardar en base de datos
            saveVerificationResult($conn, $numberID, $frontImageUrl, $backImageUrl, $verificationResult);
            
            $results[] = [
                'row' => $row,
                'numberID' => $numberID,
                'result' => $verificationResult
            ];
            
            // Pausa pequeña para evitar sobrecarga
            usleep(100000); // 0.1 segundos
        }
        
        // Marcar como completado
        session_start();
        $_SESSION['verification_progress'] = [
            'current' => $totalRows,
            'total' => $totalRows,
            'percentage' => 100,
            'status' => 'completado'
        ];
        session_write_close();
        
        return $results;
        
    } catch (Exception $e) {
        error_log("Error procesando Excel: " . $e->getMessage());
        
        // Reportar error en el progreso
        session_start();
        $_SESSION['verification_progress'] = [
            'current' => 0,
            'total' => 0,
            'percentage' => 0,
            'status' => 'error',
            'message' => $e->getMessage()
        ];
        session_write_close();
        
        return false;
    }
}

function verifyDocument($numberID, $name1, $name2, $lastname1, $lastname2, $birthdate, $frontImageUrl, $backImageUrl) {
    // Actualizar estado a 'analizando'
    session_start();
    if (isset($_SESSION['verification_progress'])) {
        $_SESSION['verification_progress']['status'] = 'analizando';
    }
    session_write_close();
    
    $result = [
        'document_match' => 0,
        'name1_match' => 0,
        'name2_match' => 0,
        'lastname1_match' => 0,
        'lastname2_match' => 0,
        'birthdate_match' => 0,
        'overall_percentage' => 0.0,
        'message' => '',
        'front_ocr_text' => '',
        'back_ocr_text' => ''
    ];
    
    try {
        // Procesar imagen frontal
        $frontResult = processDocumentFront($frontImageUrl, $numberID, $name1, $name2, $lastname1, $lastname2);
        if ($frontResult) {
            $result['document_match'] = $frontResult['document_match'];
            $result['name1_match'] = $frontResult['name1_match'];
            $result['name2_match'] = $frontResult['name2_match'];
            $result['lastname1_match'] = $frontResult['lastname1_match'];
            $result['lastname2_match'] = $frontResult['lastname2_match'];
            $result['front_ocr_text'] = $frontResult['ocr_text'];
        }
        
        // Procesar imagen posterior
        $backResult = processDocumentBack($backImageUrl, $birthdate);
        if ($backResult) {
            $result['birthdate_match'] = $backResult['birthdate_match'];
            $result['back_ocr_text'] = $backResult['ocr_text'];
        }
        
        // Calcular porcentaje general (siempre sobre 6 campos, pero los vacíos cuentan como válidos)
        $totalFields = 6;
        $matchedFields = $result['document_match'] + $result['name1_match'] + $result['name2_match'] + 
                        $result['lastname1_match'] + $result['lastname2_match'] + $result['birthdate_match'];
        $result['overall_percentage'] = ($matchedFields / $totalFields) * 100;
        
        // Determinar mensaje
        if ($result['overall_percentage'] >= 80) {
            $result['message'] = 'Verificación exitosa - Documento válido';
        } elseif ($result['overall_percentage'] >= 60) {
            $result['message'] = 'Verificación parcial - Revisar manualmente';
        } else {
            $result['message'] = 'Verificación fallida - Documento requiere revisión';
        }
        
    } catch (Exception $e) {
        $result['message'] = 'Error en el procesamiento: ' . $e->getMessage();
    }
    
    return $result;
}

// Función mejorada para crear imágenes temporales con transformaciones
function crearImagenTemporal($imagenOriginal, $tipo) {
    $tempDir = sys_get_temp_dir();
    $extension = pathinfo($imagenOriginal, PATHINFO_EXTENSION);
    
    $nombreTemp = $tempDir . '/ocr_temp_' . $tipo . '_' . uniqid() . '.png';
    
    // Crear imagen desde el archivo original
    switch (strtolower($extension)) {
        case 'jpg':
        case 'jpeg':
            $imagen = imagecreatefromjpeg($imagenOriginal);
            break;
        case 'png':
            $imagen = imagecreatefrompng($imagenOriginal);
            break;
        case 'gif':
            $imagen = imagecreatefromgif($imagenOriginal);
            break;
        case 'webp':
            $imagen = imagecreatefromwebp($imagenOriginal);
            break;
        default:
            return false;
    }
    
    if (!$imagen) return false;
    
    $width = imagesx($imagen);
    $height = imagesy($imagen);
    
    // Crear imagen de salida con fondo blanco
    $imagenProcesada = imagecreatetruecolor($width, $height);
    $blanco = imagecolorallocate($imagenProcesada, 255, 255, 255);
    imagefill($imagenProcesada, 0, 0, $blanco);
    
    switch ($tipo) {
        case 'contraste_alto':
            imagecopy($imagenProcesada, $imagen, 0, 0, 0, 0, $width, $height);
            imagefilter($imagenProcesada, IMG_FILTER_CONTRAST, -100);
            break;
            
        case 'escala_grises':
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($imagen, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $gray = ($r * 0.299 + $g * 0.587 + $b * 0.114);
                    $gray = ($gray > 128) ? 255 : 0; // Umbralización
                    $color = imagecolorallocate($imagenProcesada, $gray, $gray, $gray);
                    imagesetpixel($imagenProcesada, $x, $y, $color);
                }
            }
            break;
            
        case 'redimensionar':
            $nuevoWidth = $width * 2;
            $nuevoHeight = $height * 2;
            $imagenRedimensionada = imagecreatetruecolor($nuevoWidth, $nuevoHeight);
            $blancoRedim = imagecolorallocate($imagenRedimensionada, 255, 255, 255);
            imagefill($imagenRedimensionada, 0, 0, $blancoRedim);
            imagecopyresampled($imagenRedimensionada, $imagen, 0, 0, 0, 0, $nuevoWidth, $nuevoHeight, $width, $height);
            imagedestroy($imagenProcesada);
            $imagenProcesada = $imagenRedimensionada;
            break;
            
        default:
            imagedestroy($imagen);
            imagedestroy($imagenProcesada);
            return false;
    }
    
    $resultado = imagepng($imagenProcesada, $nombreTemp, 0);
    
    imagedestroy($imagen);
    imagedestroy($imagenProcesada);
    
    return $resultado ? $nombreTemp : false;
}

// Función mejorada para ejecutar OCR con múltiples configuraciones
function ejecutarOCRMejorado($rutaImagen) {
    $configuraciones = [
        ['psm' => 3, 'desc' => 'Segmentación automática'],
        ['psm' => 6, 'desc' => 'Bloque uniforme'],
        ['psm' => 4, 'desc' => 'Columna única'],
        ['psm' => 1, 'desc' => 'Segmentación automática OSD'],
        ['psm' => 7, 'desc' => 'Línea de texto'],
        ['psm' => 11, 'desc' => 'Texto disperso'],
        ['psm' => 12, 'desc' => 'Texto disperso OSD'],
        ['psm' => 13, 'desc' => 'Raw line'],
    ];
    
    foreach ($configuraciones as $config) {
        try {
            $ocr = new TesseractOCR($rutaImagen);
            $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe');
            $ocr->lang('spa+eng');
            $ocr->psm($config['psm']);
            
            $texto = trim($ocr->run());
            
            if (!empty($texto)) {
                return $texto;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    return '';
}

function processDocumentFront($imageUrl, $numberID, $name1, $name2, $lastname1, $lastname2) {
    try {
        // Descargar imagen
        $tempImage = downloadImage($imageUrl);
        if (!$tempImage) {
            return false;
        }
        
        // Datos a buscar
        $busquedas = [
            'documento' => $numberID,
            'nombre1' => $name1,
            'nombre2' => $name2,
            'apellido1' => $lastname1,
            'apellido2' => $lastname2
        ];
        
        // Procesar imagen con transformaciones mejoradas
        $transformaciones = [
            'original' => null,
            'contraste_alto' => 'Alto contraste',
            'escala_grises' => 'Escala de grises',
            'redimensionar' => 'Redimensionado 2x'
        ];
        
        $mejorTexto = '';
        $mejorPuntuacion = 0;
        $imagenesTemporales = [];
        
        foreach ($transformaciones as $tipo => $descripcion) {
            if ($tipo === 'original') {
                $rutaImagen = $tempImage;
            } else {
                $rutaImagen = crearImagenTemporal($tempImage, $tipo);
                if (!$rutaImagen) continue;
                $imagenesTemporales[] = $rutaImagen;
            }
            
            $texto = ejecutarOCRMejorado($rutaImagen);
            $puntuacion = verificarDatosMejorado($texto, $busquedas);
            
            if ($puntuacion > $mejorPuntuacion) {
                $mejorPuntuacion = $puntuacion;
                $mejorTexto = $texto;
            }
            
            // Si encontramos todos los datos, podemos parar
            if ($puntuacion === count($busquedas)) { // Cambié array_filter por count directo
                break;
            }
        }
        
        // Limpiar archivos temporales
        foreach ($imagenesTemporales as $archivo) {
            if (file_exists($archivo)) {
                unlink($archivo);
            }
        }
        unlink($tempImage);
        
        // Verificar cada campo individualmente (los vacíos se marcarán como válidos automáticamente)
        $result = [
            'ocr_text' => $mejorTexto,
            'document_match' => verificarDocumento($mejorTexto, $numberID),
            'name1_match' => verificarTexto($mejorTexto, $name1),
            'name2_match' => verificarTexto($mejorTexto, $name2), // Se marcará como 1 si $name2 está vacío
            'lastname1_match' => verificarTexto($mejorTexto, $lastname1),
            'lastname2_match' => verificarTexto($mejorTexto, $lastname2) // Se marcará como 1 si $lastname2 está vacío
        ];
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error procesando imagen frontal: " . $e->getMessage());
        return false;
    }
}

// Función mejorada para verificar documentos con múltiples formatos
function verificarDocumento($texto, $documento) {
    if (empty($texto) || empty($documento)) return 0;
    
    // Normalizar el texto OCR
    $textoNormalizado = strtoupper($texto);
    
    // Reemplazar caracteres que OCR confunde comúnmente con números
    $reemplazosOCR = [
        'O' => '0',  // O por 0
        'I' => '1',  // I por 1  
        'L' => '1',  // L por 1
        'S' => '5',  // S por 5
        'G' => '6',  // G por 6
        'Z' => '2',  // Z por 2
        'B' => '8',  // B por 8
        'D' => '0',  // D por 0
        'Q' => '0',  // Q por 0
        'T' => '7',  // T por 7
        ',' => '.',
        ';' => '.',
        ':' => '.',
        '°' => '.',
        '·' => '.',
        '`' => '.',
        '\'' => '.',
        ' ' => '',
        '-' => '',
        '_' => '',
        '/' => '',
        '\\' => '',
        '|' => '1'
    ];
    
    // Aplicar reemplazos al texto OCR
    $textoLimpio = str_replace(array_keys($reemplazosOCR), array_values($reemplazosOCR), $textoNormalizado);
    
    // Limpiar el documento buscado
    $documentoLimpio = str_replace(['.', ',', ' ', '-', '_'], '', $documento);
    
    // Búsqueda exacta sin separadores
    if (strpos($textoLimpio, $documentoLimpio) !== false) {
        return 1;
    }
    
    // Búsqueda con diferentes formatos de separadores de cédula colombiana
    $formatosDocumento = [];
    
    // Para cédulas de 7-10 dígitos, generar formatos comunes
    if (strlen($documentoLimpio) >= 7) {
        // Formato con puntos cada 3 dígitos desde la derecha
        $numeroFormateado = '';
        $contador = 0;
        for ($i = strlen($documentoLimpio) - 1; $i >= 0; $i--) {
            if ($contador > 0 && $contador % 3 == 0) {
                $numeroFormateado = '.' . $numeroFormateado;
            }
            $numeroFormateado = $documentoLimpio[$i] . $numeroFormateado;
            $contador++;
        }
        $formatosDocumento[] = $numeroFormateado;
        
        // Variaciones de separadores
        $formatosDocumento[] = str_replace('.', ',', $numeroFormateado);
        $formatosDocumento[] = str_replace('.', ' ', $numeroFormateado);
        $formatosDocumento[] = str_replace('.', '-', $numeroFormateado);
        $formatosDocumento[] = str_replace('.', ':', $numeroFormateado);
        $formatosDocumento[] = str_replace('.', ';', $numeroFormateado);
        $formatosDocumento[] = str_replace('.', '·', $numeroFormateado);
    }
    
    // Buscar cada formato
    foreach ($formatosDocumento as $formato) {
        $formatoLimpio = str_replace(array_keys($reemplazosOCR), array_values($reemplazosOCR), strtoupper($formato));
        if (strpos($textoLimpio, str_replace(' ', '', $formatoLimpio)) !== false) {
            return 1;
        }
    }
    
    // Búsqueda por partes (útil si el OCR fragmenta el número)
    if (strlen($documentoLimpio) >= 8) {
        // Dividir el documento en partes y buscar cada una
        $partes = [
            substr($documentoLimpio, 0, 3),  // Primeros 3 dígitos
            substr($documentoLimpio, 3, 3),  // Siguientes 3 dígitos
            substr($documentoLimpio, -3)     // Últimos 3 dígitos
        ];
        
        $partesEncontradas = 0;
        foreach ($partes as $parte) {
            if (strlen($parte) >= 3 && strpos($textoLimpio, $parte) !== false) {
                $partesEncontradas++;
            }
        }
        
        // Si encontramos al menos 2 de las 3 partes, considerarlo válido
        if ($partesEncontradas >= 2) {
            return 1;
        }
    }
    
    // Búsqueda aproximada - permitir 1-2 caracteres de diferencia
    $similitud = 0;
    similar_text($textoLimpio, $documentoLimpio, $similitud);
    
    if ($similitud >= 85) { // 85% de similitud
        return 1;
    }
    
    // Último intento: extraer todos los números del texto y compararlos
    preg_match_all('/\d+/', $textoLimpio, $numerosEnTexto);
    if (!empty($numerosEnTexto[0])) {
        foreach ($numerosEnTexto[0] as $numero) {
            if (strlen($numero) >= 7 && strlen($numero) <= 12) {
                // Calcular similitud con el documento buscado
                similar_text($numero, $documentoLimpio, $similitudNumero);
                if ($similitudNumero >= 80) {
                    return 1;
                }
                
                // También verificar si el documento contiene este número
                if (strpos($documentoLimpio, $numero) !== false || strpos($numero, $documentoLimpio) !== false) {
                    return 1;
                }
            }
        }
    }
    
    return 0;
}

// También mejoro la función para nombres y apellidos
function verificarTexto($texto, $valorBuscado) {
    if (empty($texto) || empty($valorBuscado)) return 0;
    
    $textoNormalizado = strtoupper($texto);
    $valorNormalizado = strtoupper($valorBuscado);
    
    // Reemplazar caracteres que OCR confunde comúnmente en nombres
    $reemplazosTexto = [
        'Ñ' => 'N',
        'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A',
        'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U',
        'C' => 'G', // C por G en algunos casos
        'G' => 'C', // G por C en algunos casos
        'B' => 'R', // B por R
        'R' => 'B', // R por B
        'M' => 'N', // M por N
        'N' => 'M', // N por M
        'H' => '',  // H a menudo se pierde
        '.' => '',
        ',' => '',
        '-' => '',
        '_' => '',
        ' ' => ''
    ];
    
    $textoLimpio = str_replace(array_keys($reemplazosTexto), array_values($reemplazosTexto), $textoNormalizado);
    $valorLimpio = str_replace(array_keys($reemplazosTexto), array_values($reemplazosTexto), $valorNormalizado);
    
    // Búsqueda exacta
    if (strpos($textoLimpio, $valorLimpio) !== false) {
        return 1;
    }
    
    // Búsqueda por similitud
    $similitud = 0;
    similar_text($textoLimpio, $valorLimpio, $similitud);
    
    if ($similitud >= 75) { // 75% de similitud para nombres
        return 1;
    }
    
    // Búsqueda por palabras individuales (para nombres compuestos)
    $palabrasTexto = explode(' ', $textoNormalizado);
    $palabrasValor = explode(' ', $valorNormalizado);
    
    $coincidencias = 0;
    foreach ($palabrasValor as $palabra) {
        if (strlen($palabra) >= 3) { // Solo palabras de 3+ caracteres
            foreach ($palabrasTexto as $palabraTexto) {
                if (strpos(strtoupper($palabraTexto), strtoupper($palabra)) !== false) {
                    $coincidencias++;
                    break;
                }
            }
        }
    }
    
    // Si encontramos al menos la mitad de las palabras del nombre
    if ($coincidencias >= ceil(count($palabrasValor) / 2)) {
        return 1;
    }
    
    return 0;
}

// Función mejorada para verificar datos
function verificarDatosMejorado($texto, $busquedas) {
    if (empty($texto)) return 0;
    
    $encontrados = 0;
    
    foreach ($busquedas as $clave => $valor) {
        if (empty($valor)) continue;
        
        if ($clave === 'documento') {
            $encontrados += verificarDocumento($texto, $valor);
        } else {
            $encontrados += verificarTexto($texto, $valor);
        }
    }
    
    return $encontrados;
}

// Función mejorada para convertir fecha de BD a formato colombiano
function convertirFechaColombianaOCR($fechaBD) {
    if (empty($fechaBD)) return '';
    
    $meses = [
        '01' => 'ENE', '02' => 'FEB', '03' => 'MAR', '04' => 'ABR',
        '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AGO',
        '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DIC'
    ];
    
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaBD);
    if (!$fecha) return '';
    
    $dia = $fecha->format('d');
    $mes = $fecha->format('m');
    $año = $fecha->format('Y');
    
    return sprintf('%02d-%s-%04d', $dia, $meses[$mes], $año);
}

// Función mejorada para buscar fechas en texto
function buscarFechasEnTexto($texto, $fechaEsperada) {
    if (empty($texto)) return [];
    
    $fechasEncontradas = [];
    
    // Patrones más amplios para capturar fechas
    $patrones = [
        '/\b(\d{1,2})\s*[-]\s*([A-Z]{3})\s*[-]\s*(\d{4})\b/i',
        '/\b(\d{1,2})\s+([A-Z]{3})\s+(\d{4})\b/i',
        '/\b(\d{1,2})\s*[.]\s*([A-Z]{3})\s*[.]\s*(\d{4})\b/i',
        '/\b(\d{1,2})\s*[\/]\s*([A-Z]{3})\s*[\/]\s*(\d{4})\b/i',
        '/\b(\d{1,2})([A-Z]{3})(\d{4})\b/i',
        '/\b(\d{1,2})\s*[^\w\s]\s*([A-Z]{3})\s*[^\w\s]\s*(\d{4})\b/i',
    ];
    
    foreach ($patrones as $patron) {
        if (preg_match_all($patron, $texto, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $dia = str_pad($match[1], 2, '0', STR_PAD_LEFT);
                $mes = strtoupper($match[2]);
                $año = $match[3];
                
                $mesesValidos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 
                               'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
                
                if (in_array($mes, $mesesValidos)) {
                    $fechaFormateada = "$dia-$mes-$año";
                    
                    if (!in_array($fechaFormateada, $fechasEncontradas)) {
                        $fechasEncontradas[] = $fechaFormateada;
                        
                        if (strtoupper($fechaFormateada) === strtoupper($fechaEsperada)) {
                            return [$fechaFormateada]; // Retornar inmediatamente si encontramos coincidencia exacta
                        }
                    }
                }
            }
        }
    }
    
    return $fechasEncontradas;
}

function processDocumentBack($imageUrl, $birthdate) {
    try {
        // Descargar imagen
        $tempImage = downloadImage($imageUrl);
        if (!$tempImage) {
            return false;
        }
        
        $fechaEsperada = convertirFechaColombianaOCR($birthdate);
        
        // Procesar imagen con transformaciones mejoradas
        $transformaciones = [
            'original' => null,
            'contraste_alto' => 'Alto contraste',
            'escala_grises' => 'Escala de grises',
            'redimensionar' => 'Redimensionado 2x'
        ];
        
        $mejorTexto = '';
        $fechaEncontrada = false;
        $imagenesTemporales = [];
        
        foreach ($transformaciones as $tipo => $descripcion) {
            if ($tipo === 'original') {
                $rutaImagen = $tempImage;
            } else {
                $rutaImagen = crearImagenTemporal($tempImage, $tipo);
                if (!$rutaImagen) continue;
                $imagenesTemporales[] = $rutaImagen;
            }
            
            $texto = ejecutarOCRMejorado($rutaImagen);
            $fechasEncontradas = buscarFechasEnTexto($texto, $fechaEsperada);
            
            if (!empty($fechasEncontradas)) {
                foreach ($fechasEncontradas as $fecha) {
                    if (strtoupper($fecha) === strtoupper($fechaEsperada)) {
                        $fechaEncontrada = true;
                        $mejorTexto = $texto;
                        break 2; // Salir de ambos bucles
                    }
                }
            }
            
            if (empty($mejorTexto)) {
                $mejorTexto = $texto;
            }
        }
        
        // Limpiar archivos temporales
        foreach ($imagenesTemporales as $archivo) {
            if (file_exists($archivo)) {
                unlink($archivo);
            }
        }
        unlink($tempImage);
        
        $result = [
            'ocr_text' => $mejorTexto,
            'birthdate_match' => $fechaEncontrada ? 1 : 0
        ];
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error procesando imagen posterior: " . $e->getMessage());
        return false;
    }
}

function downloadImage($url) {
    try {
        $tempFile = tempnam(sys_get_temp_dir(), 'doc_verify_');
        $imageData = file_get_contents($url);
        
        if ($imageData === false) {
            return false;
        }
        
        file_put_contents($tempFile, $imageData);
        return $tempFile;
        
    } catch (Exception $e) {
        error_log("Error descargando imagen: " . $e->getMessage());
        return false;
    }
}

function normalizeText($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    return strtoupper(preg_replace('/\s+/', ' ', trim($text)));
}

function saveVerificationResult($conn, $numberID, $frontUrl, $backUrl, $result) {
    $sql = "INSERT INTO document_verification 
            (number_id, front_image_url, back_image_url, document_match, name1_match, name2_match, 
             lastname1_match, lastname2_match, birthdate_match, overall_match_percentage, 
             verification_message, front_ocr_text, back_ocr_text) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sssiiiiiidsss',
        $numberID,
        $frontUrl,
        $backUrl,
        $result['document_match'],
        $result['name1_match'],
        $result['name2_match'],
        $result['lastname1_match'],
        $result['lastname2_match'],
        $result['birthdate_match'],
        $result['overall_percentage'],
        $result['message'],
        $result['front_ocr_text'],
        $result['back_ocr_text']
    );
    
    return $stmt->execute();
}

// Procesar si se llama directamente
if (isset($_POST['process_verification'])) {
    header('Content-Type: application/json');
    
    $filePath = __DIR__ . '/../../uploads/E29_a_verificar.xlsx';
    
    if (file_exists($filePath)) {
        $results = processExcelForVerification($filePath, $conn);
        
        if ($results !== false) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Verificación completada exitosamente',
                'processed' => count($results),
                'results' => $results
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error procesando el archivo Excel'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Archivo E29_a_verificar.xlsx no encontrado'
        ]);
    }
    
    // Asegurar que no haya salida adicional
    exit;
}
?>