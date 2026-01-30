<?php
// filepath: c:\xampp\htdocs\UTINNOVA-DASHBOARD\generarTarjetasAutomatico.php
$timestamp = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador Automático de Tarjetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; background-color: #f8f9fa; }
        #processing-iframe {
            width: 1200px;
            height: 800px; /* Altura suficiente para ver la tarjeta */
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-3">Generador Automático de Tarjetas</h1>
        <p>Este script procesará a todos los estudiantes elegibles y generará sus tarjetas de asistencia y notas automáticamente.</p>
        
        <div class="d-flex align-items-center mb-3">
            <button id="startButton" class="btn btn-primary btn-lg">▶️ Iniciar Proceso</button>
            <div id="spinner" class="spinner-border text-primary ms-3 d-none" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="progress mb-3" style="height: 30px;">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0 / 0</div>
        </div>

        <h5 class="mt-4">Registro de Actividad:</h5>
        <div id="log" class="log-container"></div>

        <h5 class="mt-4">Vista Previa (Estudiante Actual):</h5>
        <iframe id="processing-iframe" class="d-none"></iframe>
    </div>

    <script>
        const startButton = document.getElementById('startButton');
        const progressBar = document.getElementById('progressBar');
        const logContainer = document.getElementById('log');
        const iframe = document.getElementById('processing-iframe');
        const spinner = document.getElementById('spinner');

        let studentIds = [];
        let currentIndex = 0;
        const timestamp = '<?php echo $timestamp; ?>';

        function addLog(message, type = 'log-info') {
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            logContainer.appendChild(entry);
            logContainer.scrollTop = logContainer.scrollHeight;
        }

        async function startProcessing() {
            startButton.disabled = true;
            spinner.classList.remove('d-none');
            iframe.classList.remove('d-none');
            addLog('Obteniendo lista de estudiantes...');

            try {
                const response = await fetch('get_next_student.php');
                studentIds = await response.json();

                if (studentIds.error || studentIds.length === 0) {
                    addLog('No se encontraron estudiantes para procesar o hubo un error.', 'log-error');
                    spinner.classList.add('d-none');
                    return;
                }

                addLog(`Se encontraron ${studentIds.length} estudiantes.`, 'log-success');
                progressBar.setAttribute('aria-valuemax', studentIds.length);
                processNextStudent();

            } catch (error) {
                addLog(`Error al obtener la lista de estudiantes: ${error}`, 'log-error');
                startButton.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        function processNextStudent() {
            if (currentIndex >= studentIds.length) {
                addLog('🎉 ¡Proceso completado!', 'log-success');
                startButton.disabled = false;
                spinner.classList.add('d-none');
                iframe.classList.add('d-none');
                progressBar.classList.remove('progress-bar-animated');
                return;
            }

            const studentId = studentIds[currentIndex];
            addLog(`Cargando estudiante ${currentIndex + 1} de ${studentIds.length}: ${studentId}`);
            
            // Actualizar barra de progreso
            const percentage = ((currentIndex + 1) / studentIds.length) * 100;
            progressBar.style.width = `${percentage}%`;
            progressBar.textContent = `${currentIndex + 1} / ${studentIds.length}`;

            // Cargar pantallazos.php en el iframe para el estudiante actual
            iframe.src = `pantallazos.php?number_id=${studentId}`;

            iframe.onload = async () => {
                addLog(`Estudiante ${studentId} cargado. Generando tarjetas...`);
                try {
                    // Esperar a que el contenido del iframe esté listo
                    await new Promise(resolve => setTimeout(resolve, 2000)); // Pausa para renderizado

                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    const iframeWindow = iframe.contentWindow;

                    // Crear directorio para el estudiante
                    const studentDir = `comprobantesAsistenciaNotas/${studentId}`;
                    
                    // Llamar a la función de captura del iframe para asistencias
                    await iframeWindow.capturarTarjeta('tarjetaAsistencia', 'asistencias', studentDir, timestamp);
                    addLog(`✓ Tarjeta de asistencias guardada para ${studentId}.`, 'log-success');

                    // Llamar a la función de captura del iframe para notas
                    await iframeWindow.capturarTarjeta('tarjetaNotas', 'notas', studentDir, timestamp);
                    addLog(`✓ Tarjeta de notas guardada para ${studentId}.`, 'log-success');

                    currentIndex++;
                    setTimeout(processNextStudent, 1000); // Pequeña pausa antes del siguiente

                } catch (e) {
                    addLog(`✗ Error procesando a ${studentId}: ${e.message}`, 'log-error');
                    // Opcional: reintentar o saltar
                    currentIndex++;
                    setTimeout(processNextStudent, 1000);
                }
            };
        }

        startButton.addEventListener('click', startProcessing);
    </script>
</body>
</html>