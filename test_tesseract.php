<?php
require __DIR__ . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

// Ruta de la imagen
$imagePath = __DIR__ . '/lorem_ipsum_prueba.png';

// Ejecutar OCR
$text = (new TesseractOCR($imagePath))->run();

// Buscar el texto
$search = 'consectetur adipiscing';

if (stripos($text, $search) !== false) {
    echo "Texto encontrado: '$search'\n";
} else {
    echo "Texto NO encontrado: '$search'\n";
}

// Opcional: mostrar el texto extraído
echo "\nTexto extraído:\n$text\n";
?>