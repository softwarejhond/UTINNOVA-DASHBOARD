<?php
$timestamp = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador Automático de Tarjetas - Cohorte Especial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; background-color: #f8f9fa; }
        #processing-iframe {
            width: 1200px;
            height: 800px;
            border: 1px solid #ccc;
            transform: scale(0.8);
            transform-origin: top left;
            margin-top: 1rem;
        }
        .log-container {
            height: 300px;
            background: #212529;
            color: #f8f9fa;
            font-family: monospace;
            padding: 1rem;
            overflow-y: scroll;
            border-radius: 5px;
        }
        .log-entry { margin-bottom: 0.5rem; }
        .log-success { color: #28a745; }
        .log-error { color: #dc3545; }
        .log-info { color: #0dcaf0; }
        .log-warning { color: #ffc107; }
        .config-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        .cohorte-info {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-3">🎯 Generador de Tarjetas - Estudiantes</h1>

        <div class="config-section">
            <h5>📋 Ingresar Number IDs</h5>
            <p class="text-muted mb-2">Pega los <code>number_id</code> separados por saltos de línea, comas o espacios. Se admiten hasta 500 registros.</p>
            <textarea id="idsTextarea" class="form-control font-monospace" rows="6" placeholder="Ejemplo:
1052404320
79897960
88230799
..."></textarea>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">IDs ingresados: <span id="inputCount">0</span></small>
                <button id="clearBtn" class="btn btn-sm btn-outline-secondary">Limpiar</button>
            </div>
        </div>

        <div class="config-section">
            <h5>⚙️ Configuración del Proceso</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="testMode" value="1" checked>
                        <label class="form-check-label" for="testMode">
                            Modo de prueba (pausa de 4 segundos entre estudiantes para API calls)
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="showPreview" value="1" checked>
                        <label class="form-check-label" for="showPreview">
                            Mostrar vista previa de cada tarjeta
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <h6>📊 Estadísticas</h6>
                        <div id="statsContent">
                            <small>Haz clic en "Cargar Cohorte" para ver las estadísticas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex align-items-center mb-3">
            <button id="loadButton" class="btn btn-outline-success me-2">� Validar IDs</button>
            <button id="startButton" class="btn btn-success btn-lg" disabled>▶️ Iniciar Proceso</button>
            <button id="pauseButton" class="btn btn-warning ms-2 d-none">⏸️ Pausar</button>
            <button id="stopButton" class="btn btn-danger ms-2 d-none">⏹️ Detener</button>
            <div id="spinner" class="spinner-border text-success ms-3 d-none" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="progress mb-3" style="height: 30px;">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="30">0 / 30</div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5 class="mt-4">📜 Registro de Actividad:</h5>
                <div id="log" class="log-container"></div>
            </div>
            <div class="col-md-6">
                <h5 class="mt-4">📈 Resumen de Progreso:</h5>
                <div id="summary" class="log-container">
                    <div class="text-center text-muted">
                        <small>El resumen aparecerá cuando inicie el proceso...</small>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4">👁️ Vista Previa (Estudiante Actual):</h5>
        <iframe id="processing-iframe" class="d-none"></iframe>
    </div>

    <script>
        const loadButton = document.getElementById('loadButton');
        const startButton = document.getElementById('startButton');
        const pauseButton = document.getElementById('pauseButton');
        const stopButton = document.getElementById('stopButton');
        const progressBar = document.getElementById('progressBar');
        const logContainer = document.getElementById('log');
        const summaryContainer = document.getElementById('summary');
        const iframe = document.getElementById('processing-iframe');
        const spinner = document.getElementById('spinner');
        const statsContent = document.getElementById('statsContent');

        let studentIds = [];
        let currentIndex = 0;
        let isPaused = false;
        let isStopped = false;
        const timestamp = '<?php echo $timestamp; ?>';
        let processStats = {
            total: 0,
            processed: 0,
            successful: 0,
            errors: 0
        };

        const idsTextarea = document.getElementById('idsTextarea');
        const inputCount = document.getElementById('inputCount');
        const clearBtn = document.getElementById('clearBtn');

        function parseInputIds() {
            return idsTextarea.value.split(/[\n,;\s]+/).map(id => id.trim()).filter(id => id.length > 0);
        }

        idsTextarea.addEventListener('input', () => {
            inputCount.textContent = parseInputIds().length;
        });

        clearBtn.addEventListener('click', () => {
            idsTextarea.value = '';
            inputCount.textContent = '0';
            studentIds = [];
            startButton.disabled = true;
            statsContent.innerHTML = '<small>Pega los IDs y haz clic en "Validar IDs"</small>';
        });

        function addLog(message, type = 'log-info') {
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            logContainer.appendChild(entry);
            logContainer.scrollTop = logContainer.scrollHeight;
        }

        function updateSummary() {
            const pct = processStats.total > 0 ? ((processStats.processed / processStats.total) * 100).toFixed(1) : '0.0';
            summaryContainer.innerHTML = `
                <div class="log-entry log-info"><strong>📊 RESUMEN DE PROCESO</strong></div>
                <div class="log-entry">IDs ingresados: ${processStats.total}</div>
                <div class="log-entry">Válidos en BD: ${studentIds.length}</div>
                <div class="log-entry">Procesados: ${processStats.processed}</div>
                <div class="log-entry log-success">Exitosos: ${processStats.successful}</div>
                <div class="log-entry log-error">Errores: ${processStats.errors}</div>
                <div class="log-entry">Progreso: ${pct}%</div>
            `;
        }

        function updateStats(inputTotal) {
            const omitidos = inputTotal - studentIds.length;
            statsContent.innerHTML = `
                <div><strong>${studentIds.length}/${inputTotal}</strong> IDs válidos en BD</div>
                <div><span class="badge bg-light text-dark">${studentIds.length}</span> Listos para procesar</div>
                ${omitidos > 0 ?
                    `<div><span class="badge bg-danger">${omitidos}</span> No encontrados en BD</div>` :
                    '<div><span class="badge bg-success">✓</span> Todos los IDs válidos</div>'}
            `;
        }

        async function loadStudents() {
            const inputIds = parseInputIds();

            if (inputIds.length === 0) {
                addLog('⚠️ Pega al menos un number_id en el campo de texto.', 'log-warning');
                return;
            }

            loadButton.disabled = true;
            spinner.classList.remove('d-none');
            addLog(`🔄 Validando ${inputIds.length} IDs contra la base de datos...`);

            try {
                const formData = new FormData();
                inputIds.forEach(id => formData.append('ids[]', id));

                const response = await fetch('get_next_student.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.error || data.length === 0) {
                    addLog('❌ Ningún ID fue encontrado en la base de datos.', 'log-error');
                    spinner.classList.add('d-none');
                    loadButton.disabled = false;
                    return;
                }

                studentIds = data;
                processStats.total = inputIds.length;
                processStats.processed = 0;
                processStats.successful = 0;
                processStats.errors = 0;

                updateStats(inputIds.length);
                addLog(`✅ ${studentIds.length} de ${inputIds.length} IDs encontrados en BD.`, 'log-success');

                if (studentIds.length < inputIds.length) {
                    addLog(`⚠️ ${inputIds.length - studentIds.length} IDs omitidos (no encontrados en BD).`, 'log-warning');
                }

                progressBar.setAttribute('aria-valuemax', studentIds.length);
                progressBar.textContent = `0 / ${studentIds.length}`;
                startButton.disabled = false;

            } catch (error) {
                addLog(`❌ Error al validar IDs: ${error.message}`, 'log-error');
                loadButton.disabled = false;
            }

            spinner.classList.add('d-none');
            loadButton.disabled = false;
        }

        async function startProcessing() {
            if (studentIds.length === 0) {
                addLog('⚠️ Primero debes cargar la cohorte especial.', 'log-warning');
                return;
            }

            startButton.disabled = true;
            loadButton.disabled = true;
            pauseButton.classList.remove('d-none');
            stopButton.classList.remove('d-none');
            spinner.classList.remove('d-none');
            
            if (document.getElementById('showPreview').checked) {
                iframe.classList.remove('d-none');
            }
            
            isPaused = false;
            isStopped = false;
            currentIndex = 0;
            
            addLog('🚀 Iniciando procesamiento de estudiantes...');
            addLog(`📊 Procesando ${studentIds.length} estudiantes`, 'log-info');
            
            processNextStudent();
        }

        function pauseProcessing() {
            isPaused = !isPaused;
            if (isPaused) {
                pauseButton.textContent = '▶️ Continuar';
                addLog('⏸️ Proceso pausado por el usuario.', 'log-warning');
            } else {
                pauseButton.textContent = '⏸️ Pausar';
                addLog('▶️ Proceso reanudado.', 'log-success');
                processNextStudent();
            }
        }

        function stopProcessing() {
            isStopped = true;
            addLog('⏹️ Proceso detenido por el usuario.', 'log-warning');
            addLog(`📊 Se procesaron ${processStats.processed} de ${studentIds.length} estudiantes antes de detener.`, 'log-info');
            resetUI();
        }

        function resetUI() {
            startButton.disabled = false;
            loadButton.disabled = false;
            pauseButton.classList.add('d-none');
            stopButton.classList.add('d-none');
            spinner.classList.add('d-none');
            iframe.classList.add('d-none');
            progressBar.classList.remove('progress-bar-animated');
        }

        function processNextStudent() {
            if (isPaused || isStopped) return;

            if (currentIndex >= studentIds.length) {
                addLog('🎉 ¡Cohorte especial procesada completamente!', 'log-success');
                addLog(`📊 Resultado final: ${processStats.successful} exitosos, ${processStats.errors} errores de ${studentIds.length} estudiantes`, 'log-info');
                addLog('📁 Las tarjetas se guardaron en comprobantesAsistenciaNotas/[numero_id]/', 'log-info');
                resetUI();
                updateSummary();
                return;
            }

            const studentId = studentIds[currentIndex];
            
            addLog(`🎯 Cohorte ${currentIndex + 1}/${studentIds.length}: Procesando ${studentId}`);
            
            // Actualizar barra de progreso
            const percentage = ((currentIndex + 1) / studentIds.length) * 100;
            progressBar.style.width = `${percentage}%`;
            progressBar.textContent = `${currentIndex + 1} / ${studentIds.length}`;

            // Cargar pantallazos.php en el iframe para el estudiante actual
            iframe.src = `pantallazos.php?number_id=${studentId}`;

            iframe.onload = async () => {
                if (isPaused || isStopped) return;
                
                try {
                    // Tiempo extra para API calls de Moodle y carga del JSON
                    const waitTime = document.getElementById('testMode').checked ? 4000 : 3000;
                    await new Promise(resolve => setTimeout(resolve, waitTime));

                    const iframeWindow = iframe.contentWindow;
                    const studentDir = `comprobantesAsistenciaNotas/${studentId}`;
                    
                    // Capturar tarjetas
                    await iframeWindow.capturarTarjeta('tarjetaAsistencia', 'asistencias', studentDir, timestamp);
                    addLog(`  ✅ Tarjeta de asistencias generada`, 'log-success');

                    await iframeWindow.capturarTarjeta('tarjetaNotas', 'notas', studentDir, timestamp);
                    addLog(`  ✅ Tarjeta de notas generada`, 'log-success');

                    processStats.successful++;
                    addLog(`  🎯 Estudiante ${studentId} completado exitosamente`, 'log-success');

                } catch (e) {
                    processStats.errors++;
                    addLog(`  ❌ Error con ${studentId}: ${e.message}`, 'log-error');
                }

                processStats.processed++;
                currentIndex++;
                updateSummary();

                // Pausa antes del siguiente
                const delay = document.getElementById('testMode').checked ? 2000 : 1000;
                setTimeout(() => {
                    if (!isPaused && !isStopped) {
                        processNextStudent();
                    }
                }, delay);
            };

            iframe.onerror = () => {
                addLog(`  ❌ Error cargando iframe para ${studentId}`, 'log-error');
                processStats.errors++;
                processStats.processed++;
                currentIndex++;
                updateSummary();
                setTimeout(() => {
                    if (!isPaused && !isStopped) {
                        processNextStudent();
                    }
                }, 1000);
            };
        }

        // Event listeners
        loadButton.addEventListener('click', loadStudents);
        startButton.addEventListener('click', startProcessing);
        pauseButton.addEventListener('click', pauseProcessing);
        stopButton.addEventListener('click', stopProcessing);

        // Inicializar contador del textarea
        window.addEventListener('load', () => {
            statsContent.innerHTML = '<small>Pega los IDs y haz clic en "Validar IDs"</small>';
        });
    </script>
</body>
</html>