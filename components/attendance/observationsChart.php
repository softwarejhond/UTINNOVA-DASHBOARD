<!-- Gráfico de barras lateral para tipos de observación -->
<div class="row mx-0 w-100 mb-4 mt-5">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white p-2">
                <h6 class="card-title mb-0 text-center">
                    <i class="fas fa-chart-bar me-1"></i>
                    Tipos de Observación
                </h6>
            </div>
            <div class="card-body p-2">
                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="observationsChart" style="max-width: 100%; max-height: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variable para el gráfico de observaciones
let observationsChart = null;

// Función para actualizar el gráfico de observaciones
function updateObservationsChart(data) {
    console.log('Actualizando gráfico con datos:', data);
    
    const canvas = document.getElementById('observationsChart');
    if (!canvas) {
        console.error('Canvas observationsChart no encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    if (observationsChart) {
        observationsChart.destroy();
    }

    try {
        if (!data || data.length === 0) {
            console.log('No hay datos, creando gráfico vacío');
            observationsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sin datos'],
                    datasets: [{
                        label: 'Observaciones',
                        data: [0],
                        backgroundColor: '#e9ecef',
                        borderColor: '#dee2e6',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        y: { beginAtZero: true, display: false }
                    }
                },
                plugins: [{
                    id: 'emptyMessage',
                    beforeDraw: function(chart) {
                        const ctx = chart.ctx;
                        const width = chart.width;
                        const height = chart.height;
                        ctx.restore();
                        ctx.font = '16px Arial';
                        ctx.fillStyle = '#6c757d';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('No hay observaciones registradas para este curso', width / 2, height / 2);
                        ctx.save();
                    }
                }]
            });
            return;
        }

        const labels = data.map(item => item.observation_type);
        const counts = data.map(item => parseInt(item.count));

        console.log('Labels:', labels, 'Counts:', counts);

        // Barras verticales para mejor compatibilidad
        observationsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Observaciones',
                    data: counts,
                    backgroundColor: '#30336b',
                    borderColor: '#30336b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.y} observaciones`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxTicksLimit: 10
                        }
                    }
                }
            }
        });
        
        console.log('Gráfico creado exitosamente');
    } catch (error) {
        console.error('Error creando gráfico:', error);
    }
}

// Función para cargar los datos de observaciones por AJAX
function loadObservationsChart(courseCode) {
    console.log('Iniciando carga del gráfico para courseCode:', courseCode);
    
    if (!courseCode) {
        console.log('No hay courseCode, actualizando con datos vacíos');
        updateObservationsChart([]);
        return;
    }
    
    $.ajax({
        url: 'components/attendance/getObservationsStats.php',
        method: 'GET',
        data: { course_code: courseCode },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta AJAX exitosa:', response);
            if (response.success) {
                updateObservationsChart(response.data);
            } else {
                console.log('Respuesta no exitosa, actualizando con datos vacíos');
                updateObservationsChart([]);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en AJAX:', status, error, xhr.responseText);
            updateObservationsChart([]);
        }
    });
}

// Carga independiente: se ejecuta cuando el DOM esté listo
$(document).ready(function() {
    console.log('DOM listo, inicializando gráfico de observaciones');
    
    // Verificar si el selector existe
    const selector = $('#bootcamp');
    console.log('Selector #bootcamp encontrado:', selector.length > 0);
    
    // Cargar inicialmente si hay un curso seleccionado
    const initialCourse = selector.val();
    console.log('Curso inicial:', initialCourse);
    
    if (initialCourse) {
        loadObservationsChart(initialCourse);
    }

    // Escuchar cambios en el selector de curso para actualizar el gráfico
    selector.on('change', function() {
        const courseCode = $(this).val();
        console.log('Cambio en selector, nuevo courseCode:', courseCode);
        loadObservationsChart(courseCode);
    });
});
</script>