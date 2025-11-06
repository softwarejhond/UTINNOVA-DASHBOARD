<?php
function formatearTiempo($milliseconds) {
    if ($milliseconds < 1000) {
        return $milliseconds . ' ms';
    } else {
        return round($milliseconds / 1000, 2) . ' segundos';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba OCR Tesseract - Producción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .test-card {
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-radius: 15px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
        }
        .upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #007bff;
            background-color: rgba(0,123,255,0.05);
        }
        .upload-area.dragover {
            border-color: #28a745;
            background-color: rgba(40,167,69,0.1);
        }
        .result-area {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            min-height: 200px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            overflow-y: auto;
            max-height: 400px;
        }
        .info-badge {
            font-size: 0.85em;
            margin: 2px;
        }
        .error-list {
            max-height: 150px;
            overflow-y: auto;
            font-size: 0.9em;
        }
        @media (max-width: 768px) {
            .test-card {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card test-card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-eye me-2"></i>
                    Prueba OCR Tesseract - Producción Linux
                </h3>
                <small>Sube una imagen para extraer texto usando Tesseract OCR</small>
            </div>
            
            <div class="card-body p-4">
                <form id="ocrForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-upload me-2"></i>Seleccionar Imagen
                        </label>
                        <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-2">Haz clic aquí o arrastra una imagen</p>
                            <small class="text-muted">JPG, PNG, GIF, WEBP (máx. 10MB)</small>
                            <input type="file" 
                                   id="imageInput" 
                                   name="image" 
                                   accept="image/*" 
                                   class="d-none" 
                                   required>
                        </div>
                        <div id="fileInfo" class="mt-2"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="processBtn">
                        <i class="fas fa-cogs me-2"></i>
                        Procesar con OCR
                    </button>
                </form>
                
                <div id="resultContainer" class="mt-4" style="display: none;">
                    <h5 class="mb-3">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Resultado del Procesamiento
                    </h5>
                    
                    <!-- Estado del procesamiento -->
                    <div id="statusAlert" class="alert mb-3"></div>
                    
                    <!-- Información del archivo -->
                    <div id="fileInfoResult" class="mb-3"></div>
                    
                    <!-- Texto extraído -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-font me-2"></i>Texto Extraído:
                        </label>
                        <div id="resultArea" class="result-area border"></div>
                    </div>
                    
                    <!-- Errores si los hay -->
                    <div id="errorContainer" class="alert alert-warning" style="display: none;">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Errores durante el procesamiento:</h6>
                        <div class="error-list">
                            <ul id="errorList" class="mb-0"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <small class="text-white-50">
                <i class="fas fa-info-circle me-1"></i>
                Esta herramienta utiliza Tesseract OCR configurado para Linux en producción
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('imageInput');
            const uploadArea = document.querySelector('.upload-area');
            const fileInfo = document.getElementById('fileInfo');
            const form = document.getElementById('ocrForm');
            const processBtn = document.getElementById('processBtn');
            const resultContainer = document.getElementById('resultContainer');
            const statusAlert = document.getElementById('statusAlert');
            const fileInfoResult = document.getElementById('fileInfoResult');
            const resultArea = document.getElementById('resultArea');
            const errorContainer = document.getElementById('errorContainer');
            const errorList = document.getElementById('errorList');
            
            // Manejar selección de archivo
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    const file = e.target.files[0];
                    mostrarInfoArchivo(file);
                }
            });
            
            // Drag and Drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function() {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    mostrarInfoArchivo(files[0]);
                }
            });
            
            // Mostrar información del archivo seleccionado
            function mostrarInfoArchivo(file) {
                const sizeKB = (file.size / 1024).toFixed(2);
                fileInfo.innerHTML = `
                    <div class="alert alert-info mb-0 py-2">
                        <i class="fas fa-file-image me-2"></i>
                        <strong>${file.name}</strong> - ${sizeKB} KB - ${file.type}
                    </div>
                `;
            }
            
            // Manejar envío del formulario con AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                processBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                processBtn.disabled = true;
                
                fetch('process_ocr.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    mostrarResultado(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarResultado({
                        status: 'error',
                        message: 'Error en la solicitud AJAX',
                        errors: [error.message]
                    });
                })
                .finally(() => {
                    processBtn.innerHTML = '<i class="fas fa-cogs me-2"></i>Procesar con OCR';
                    processBtn.disabled = false;
                });
            });
            
            // Mostrar resultado del procesamiento
            function mostrarResultado(result) {
                resultContainer.style.display = 'block';
                
                // Estado
                statusAlert.className = `alert alert-${result.status === 'success' ? 'success' : (result.status === 'warning' ? 'warning' : 'danger')}`;
                statusAlert.innerHTML = `<i class="fas fa-${result.status === 'success' ? 'check-circle' : (result.status === 'warning' ? 'exclamation-triangle' : 'times-circle')} me-2"></i>${result.message}`;
                
                // Información del archivo
                if (result.file_info) {
                    fileInfoResult.innerHTML = `
                        <small class="text-muted">Información del archivo:</small><br>
                        <span class="badge bg-info info-badge">
                            <i class="fas fa-file me-1"></i>${result.file_info.name}
                        </span>
                        <span class="badge bg-secondary info-badge">
                            <i class="fas fa-weight me-1"></i>${result.file_info.size}
                        </span>
                        <span class="badge bg-primary info-badge">
                            <i class="fas fa-image me-1"></i>${result.file_info.type}
                        </span>
                        <span class="badge bg-success info-badge">
                            <i class="fas fa-clock me-1"></i>${formatearTiempo(result.processing_time)}
                        </span>
                    `;
                } else {
                    fileInfoResult.innerHTML = '';
                }
                
                // Texto extraído
                resultArea.textContent = result.ocr_text || 'Sin texto detectado';
                
                // Errores
                if (result.errors && result.errors.length > 0) {
                    errorContainer.style.display = 'block';
                    errorList.innerHTML = result.errors.map(error => `<li>${error}</li>`).join('');
                } else {
                    errorContainer.style.display = 'none';
                }
            }
            
            function formatearTiempo(milliseconds) {
                if (milliseconds < 1000) {
                    return milliseconds + ' ms';
                } else {
                    return (milliseconds / 1000).toFixed(2) + ' segundos';
                }
            }
        });
    </script>
</body>
</html>