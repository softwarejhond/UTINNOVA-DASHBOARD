<!-- Incluir Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Contenedor para las estadísticas -->
<div id="statisticsContainer" class="container-fluid px-0" style="display: none;">
    <!-- Gráficos de dona existentes -->
    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 g-3 mx-0 w-100 mb-4">
        <!-- Panel de Cumplimiento Sin Inglés Nivelador -->
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white p-2">
                    <h6 class="card-title mb-0 text-center">
                        <i class="fas fa-chart-pie me-1"></i>
                        Cumplimiento Global (159h)
                        <i class="fas fa-info-circle ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Horas actuales sin incluir las horas de inglés nivelador"></i>
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div class="row">
                        <!-- Gráfico de horas esperadas -->
                        <div class="col-6 text-center">
                            <h6 class="text-muted mb-2">Horas Esperadas</h6>
                            <div style="position: relative; height: 180px; width: 180px; margin: 0 auto;">
                                <canvas id="complianceChart"></canvas>
                            </div>
                        </div>
                        <!-- Gráfico de horas reales -->
                        <div class="col-6 text-center">
                            <h6 class="text-muted mb-2">Horas Reales</h6>
                            <div style="position: relative; height: 180px; width: 180px; margin: 0 auto;">
                                <canvas id="realHoursChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Cumplimiento Con Inglés Nivelador -->
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white p-2">
                    <h6 class="card-title mb-0 text-center">
                        <i class="fas fa-chart-pie me-1"></i>
                        Cumplimiento Total (179h)
                        <i class="fas fa-info-circle ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Horas actuales incluyendo las horas de inglés nivelador"></i>
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div class="row">
                        <!-- Gráfico de horas esperadas con inglés -->
                        <div class="col-6 text-center">
                            <h6 class="text-muted mb-2">Horas Esperadas</h6>
                            <div style="position: relative; height: 180px; width: 180px; margin: 0 auto;">
                                <canvas id="complianceChartWithEnglish"></canvas>
                            </div>
                        </div>
                        <!-- Gráfico de horas reales con inglés -->
                        <div class="col-6 text-center">
                            <h6 class="text-muted mb-2">Horas Reales</h6>
                            <div style="position: relative; height: 180px; width: 180px; margin: 0 auto;">
                                <canvas id="realHoursChartWithEnglish"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de barras para asistencias por tipo de curso -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mx-0 w-100 mb-4">
        <!-- Gráfico de Técnico -->
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white p-2">
                    <h6 class="card-title mb-0 text-center">
                        <i class="fas fa-chart-bar me-1"></i>
                        Técnico - Asistencias
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div style="position: relative; height: 250px;">
                        <canvas id="bootcampAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Inglés Nivelador -->
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-white p-2">
                    <h6 class="card-title mb-0 text-center">
                        <i class="fas fa-chart-bar me-1"></i>
                        Inglés Nivelador - Asistencias
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div style="position: relative; height: 250px;">
                        <canvas id="levelingEnglishAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de English Code -->
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white p-2">
                    <h6 class="card-title mb-0 text-center">
                        <i class="fas fa-chart-bar me-1"></i>
                        English Code - Asistencias
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div style="position: relative; height: 250px;">
                        <canvas id="englishCodeAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Habilidades -->
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white p-2">
                    <h6 class="card-title mb-0 text-center">
                        <i class="fas fa-chart-bar me-1"></i>
                        Habilidades - Asistencias
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div style="position: relative; height: 250px;">
                        <canvas id="skillsAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nuevo carrusel de estadísticas por clase -->
    <div class="row mx-0 w-100">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white p-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Estadísticas por Clase
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <select id="classStatsTypeSelector" class="form-select form-select-sm">
                                <option value="bootcamp">Técnico</option>
                                <option value="leveling_english">Inglés Nivelador</option>
                                <option value="english_code">English Code</option>
                                <option value="skills">Habilidades</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Carrusel -->
                    <div id="classStatsCarousel" class="carousel slide" data-bs-ride="false">
                        <!-- Contenido del carrusel -->
                        <div class="carousel-inner" id="classCarouselContent">
                            <!-- Se llenará dinámicamente -->
                        </div>

                        <!-- Controles del carrusel -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#classStatsCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#classStatsCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>

                    <!-- Mensaje cuando no hay clases -->
                    <div id="noClassesMessage" class="text-center py-4" style="display: none;">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No hay clases registradas para este tipo de curso</h6>
                        <p class="text-muted small">Las estadísticas aparecerán cuando haya registros de asistencia.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mensaje cuando no hay curso seleccionado -->
<div id="noDataMessage" class="text-center py-5 w-100">
    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Seleccione un curso para ver las estadísticas</h5>
    <p class="text-muted">Las gráficas se mostrarán cuando elija un curso válido</p>
</div>

<script>
    // Variables globales para los gráficos
    let complianceChart = null;
    let complianceChartWithEnglish = null;
    let realHoursChart = null;
    let realHoursChartWithEnglish = null;
    
    // Variables para los gráficos de barras
    let bootcampAttendanceChart = null;
    let levelingEnglishAttendanceChart = null;
    let englishCodeAttendanceChart = null;
    let skillsAttendanceChart = null;

    // Variables para las estadísticas de clases
    let currentClassesData = null;

    // Función para cargar las estadísticas del curso
    function loadCourseStatistics(courseCode) {
        if (!courseCode) {
            $('#statisticsContainer').hide();
            $('#noDataMessage').show();
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Cargando estadísticas',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Obtener el tipo de curso seleccionado para las estadísticas de clases
        const selectedClassType = $('#classStatsTypeSelector').val() || 'bootcamp';

        // Cargar estadísticas de cumplimiento, asistencias y clases en paralelo
        Promise.all([
            // Petición para estadísticas de cumplimiento
            $.ajax({
                url: 'components/attendanceGraphics/getCourseStatistics.php',
                method: 'POST',
                data: { courseCode: courseCode },
                dataType: 'json'
            }),
            // Petición para estadísticas de asistencias
            $.ajax({
                url: 'components/attendanceGraphics/getCourseAttendanceStats.php',
                method: 'POST',
                data: { courseCode: courseCode },
                dataType: 'json'
            }),
            // Petición para estadísticas de clases
            $.ajax({
                url: 'components/attendanceGraphics/getClassesStatistics.php',
                method: 'POST',
                data: { 
                    courseCode: courseCode,
                    courseType: selectedClassType
                },
                dataType: 'json'
            })
        ]).then(function(responses) {
            Swal.close();
            
            const complianceResponse = responses[0];
            const attendanceResponse = responses[1];
            const classesResponse = responses[2];
            
            if (complianceResponse.success && attendanceResponse.success && classesResponse.success) {
                // Mostrar el contenedor de estadísticas
                $('#noDataMessage').hide();
                $('#statisticsContainer').show();

                // Actualizar todas las gráficas
                updateComplianceCharts(complianceResponse.data);
                updateAttendanceCharts(attendanceResponse.data);
                updateClassesCarousel(classesResponse.data);
                
                // Guardar datos de clases para cambios de tipo
                currentClassesData = classesResponse.data;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar estadísticas'
                });
            }
        }).catch(function(error) {
            Swal.close();
            console.error("Error cargando estadísticas:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudieron cargar las estadísticas del curso'
            });
        });
    }

    // Función para actualizar el carrusel de clases
    function updateClassesCarousel(classesData) {
        const classes = classesData.classes || [];
        const totalStudents = classesData.totalStudents || 0;
        
        const contentContainer = $('#classCarouselContent');
        
        // Limpiar contenido anterior
        contentContainer.empty();
        
        if (classes.length === 0) {
            $('#classStatsCarousel').hide();
            $('#noClassesMessage').show();
            return;
        }
        
        $('#noClassesMessage').hide();
        $('#classStatsCarousel').show();
        
        // Crear contenido del carrusel
        classes.forEach((classData, index) => {
            // Crear contenido de la clase
            const classContent = `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center mb-4">
                                <h5 class="mb-1">Clase ${index + 1}</h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-calendar me-1"></i>
                                    ${classData.formatted_date} - ${classData.day_name}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 justify-content-center">
                        <!-- Presente -->
                        <div class="col-md-4 col-12">
                            <div class="card bg-success text-white h-100 stats-card">
                                <div class="card-body text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                    <div class="stats-content">
                                        <div class="stats-icon mb-3">
                                            <i class="fas fa-check-circle fa-3x"></i>
                                        </div>
                                        <h2 class="stats-number mb-2">${classData.present}</h2>
                                        <p class="stats-label mb-2">Presentes</p>
                                        <span class="stats-percentage">${classData.present_percentage}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarde -->
                        <div class="col-md-4 col-12">
                            <div class="card bg-warning text-white h-100 stats-card">
                                <div class="card-body text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                    <div class="stats-content">
                                        <div class="stats-icon mb-3">
                                            <i class="fas fa-clock fa-3x"></i>
                                        </div>
                                        <h2 class="stats-number mb-2">${classData.late}</h2>
                                        <p class="stats-label mb-2">Tardíos</p>
                                        <span class="stats-percentage">${classData.late_percentage}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ausente -->
                        <div class="col-md-4 col-12">
                            <div class="card bg-danger text-white h-100 stats-card">
                                <div class="card-body text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                    <div class="stats-content">
                                        <div class="stats-icon mb-3">
                                            <i class="fas fa-times-circle fa-3x"></i>
                                        </div>
                                        <h2 class="stats-number mb-2">${classData.absent}</h2>
                                        <p class="stats-label mb-2">Ausentes</p>
                                        <span class="stats-percentage">${classData.absent_percentage}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón de exportación centrado debajo de las tarjetas -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button class="btn btn-success export-class-btn" 
                                    data-class-date="${classData.class_date}"
                                    data-course-code="${currentCourseCode}"
                                    data-course-type="${$('#classStatsTypeSelector').val()}"
                                    title="Exportar asistencia de esta clase a Excel">
                                <i class="fas fa-file-excel me-2"></i>
                                Exportar Asistencia
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            contentContainer.append(classContent);
        });

        // Agregar event listener para los botones de exportación
        $('.export-class-btn').off('click').on('click', function() {
            const classDate = $(this).data('class-date');
            const courseCode = $(this).data('course-code');
            const courseType = $(this).data('course-type');
            
            if (!classDate || !courseCode || !courseType) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Faltan datos para la exportación'
                });
                return;
            }

            // Mostrar confirmación
            Swal.fire({
                title: '¿Exportar asistencia?',
                text: `Se exportará la asistencia de la clase del ${classDate}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Exportar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear URL para la exportación
                    const exportUrl = `components/attendanceGraphics/exportar_clase.php?courseCode=${encodeURIComponent(courseCode)}&classDate=${encodeURIComponent(classDate)}&courseType=${encodeURIComponent(courseType)}`;
                    
                    // Crear enlace temporal para descarga
                    const link = document.createElement('a');
                    link.href = exportUrl;
                    link.download = `asistencia_clase_${courseCode}_${classDate}.xlsx`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación iniciada',
                        text: 'La descarga del archivo Excel comenzará en breve.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
    }

    // Event listener para el selector de tipo de curso en estadísticas de clases
    $('#classStatsTypeSelector').on('change', function() {
        const selectedType = $(this).val();
        
        if (currentCourseCode) {
            // Cargar solo las estadísticas de clases para el nuevo tipo
            $.ajax({
                url: 'components/attendanceGraphics/getClassesStatistics.php',
                method: 'POST',
                data: { 
                    courseCode: currentCourseCode,
                    courseType: selectedType
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#classStatsCarousel').hide();
                    $('#noClassesMessage').html(`
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    `).show();
                },
                success: function(response) {
                    if (response.success) {
                        updateClassesCarousel(response.data);
                    } else {
                        $('#noClassesMessage').html(`
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h6 class="text-muted">Error al cargar las estadísticas</h6>
                            <p class="text-muted small">${response.message}</p>
                        `).show();
                    }
                },
                error: function() {
                    $('#noClassesMessage').html(`
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h6 class="text-muted">Error de conexión</h6>
                        <p class="text-muted small">No se pudieron cargar las estadísticas de clases.</p>
                    `).show();
                }
            });
        }
    });

    // Función para actualizar todas las gráficas de cumplimiento con nuevos colores
    function updateComplianceCharts(data) {
        const totalHours = data.totalHours || 0;
        const realHours = data.realHours || 0;

        // Usar los nuevos colores personalizados
        createComplianceChart('complianceChart', totalHours, 159, '#006d68'); // bs-teal-dark
        createComplianceChart('realHoursChart', realHours, 159, '#30336b'); // bs-indigo-dark
        createComplianceChart('complianceChartWithEnglish', totalHours, 179, '#006d68'); // bs-teal-dark
        createComplianceChart('realHoursChartWithEnglish', realHours, 179, '#bf6900'); // bs-amber-dark
    }

    // Nueva función para actualizar los gráficos de barras de asistencias con nuevos colores
    function updateAttendanceCharts(data) {
        console.log("Datos de asistencias recibidos:", data);
        
        // Técnico - Teal oscuro
        createAttendanceBarChart('bootcampAttendanceChart', data.bootcamp, '#006d68', 120);
        
        // Inglés Nivelador - Ámbar oscuro  
        createAttendanceBarChart('levelingEnglishAttendanceChart', data.leveling_english, '#bf6900', 20);
        
        // English Code - Rosa/Magenta oscuro
        createAttendanceBarChart('englishCodeAttendanceChart', data.english_code, '#b53471', 24);
        
        // Habilidades - Gris oscuro
        createAttendanceBarChart('skillsAttendanceChart', data.skills, '#495057', 15);
    }

    // Función para crear gráficos de barras de asistencias con escala máxima
    function createAttendanceBarChart(canvasId, data, color, maxScale) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        // Destruir gráfico anterior si existe usando las variables globales directamente
        if (canvasId === 'bootcampAttendanceChart' && bootcampAttendanceChart) {
            bootcampAttendanceChart.destroy();
        } else if (canvasId === 'levelingEnglishAttendanceChart' && levelingEnglishAttendanceChart) {
            levelingEnglishAttendanceChart.destroy();
        } else if (canvasId === 'englishCodeAttendanceChart' && englishCodeAttendanceChart) {
            englishCodeAttendanceChart.destroy();
        } else if (canvasId === 'skillsAttendanceChart' && skillsAttendanceChart) {
            skillsAttendanceChart.destroy();
        }

        const newChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Promedio Asistencias', 'Horas Reales'],
                datasets: [{
                    label: 'Horas',
                    data: [data.average_attendance || 0, data.real_hours_attendance || 0],
                    backgroundColor: [
                        color,
                        color + '80' // Versión más transparente
                    ],
                    borderColor: color,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.y.toFixed(1) + 'h';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxScale, // Establecer el máximo según el tipo de curso
                        ticks: {
                            callback: function(value) {
                                return value + 'h';
                            },
                            stepSize: Math.ceil(maxScale / 10) // Dividir la escala en 10 pasos aproximadamente
                        }
                    }
                }
            }
        });

        // Asignar a la variable global correspondiente
        if (canvasId === 'bootcampAttendanceChart') {
            bootcampAttendanceChart = newChart;
        } else if (canvasId === 'levelingEnglishAttendanceChart') {
            levelingEnglishAttendanceChart = newChart;
        } else if (canvasId === 'englishCodeAttendanceChart') {
            englishCodeAttendanceChart = newChart;
        } else if (canvasId === 'skillsAttendanceChart') {
            skillsAttendanceChart = newChart;
        }
    }

    // Función para crear una gráfica de cumplimiento (sin cambios)
    function createComplianceChart(canvasId, completedHours, totalRequiredHours, color) {
        const ctx = document.getElementById(canvasId).getContext('2d');

        // Calcular porcentajes
        const completedPercent = Math.min((completedHours / totalRequiredHours) * 100, 100);
        const remainingPercent = 100 - completedPercent;

        // Destruir gráfico anterior si existe
        if (canvasId === 'complianceChart' && complianceChart) {
            complianceChart.destroy();
        }
        if (canvasId === 'complianceChartWithEnglish' && complianceChartWithEnglish) {
            complianceChartWithEnglish.destroy();
        }
        if (canvasId === 'realHoursChart' && realHoursChart) {
            realHoursChart.destroy();
        }
        if (canvasId === 'realHoursChartWithEnglish' && realHoursChartWithEnglish) {
            realHoursChartWithEnglish.destroy();
        }

        // Crear nuevo gráfico
        const newChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completado', 'Pendiente'],
                datasets: [{
                    data: [completedPercent, remainingPercent],
                    backgroundColor: [
                        color,
                        '#e9ecef'
                    ],
                    borderColor: [
                        color,
                        '#dee2e6'
                    ],
                    borderWidth: 2,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed.toFixed(1);
                                const hours = (value * totalRequiredHours / 100).toFixed(1);
                                return `${label}: ${value}% (${hours}h)`;
                            }
                        },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 6,
                        displayColors: true,
                        appendToBody: true
                    }
                },
                layout: {
                    padding: 10
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    ctx.restore();

                    const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                    const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;

                    const currentHours = completedHours.toFixed(1);
                    const totalHours = totalRequiredHours.toFixed(1);

                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = chart.data.datasets[0].backgroundColor[0];
                    ctx.fillText(chart.data.datasets[0].data[0].toFixed(1) + '%', centerX, centerY - 8);

                    ctx.font = '10px Arial';
                    ctx.fillStyle = '#6c757d';
                    ctx.fillText(`${currentHours}h / ${totalHours}h`, centerX, centerY + 12);

                    ctx.save();
                }
            }]
        });

        // Asignar a la variable correspondiente
        if (canvasId === 'complianceChart') {
            complianceChart = newChart;
        } else if (canvasId === 'complianceChartWithEnglish') {
            complianceChartWithEnglish = newChart;
        } else if (canvasId === 'realHoursChart') {
            realHoursChart = newChart;
        } else if (canvasId === 'realHoursChartWithEnglish') {
            realHoursChartWithEnglish = newChart;
        }
    }

    // Función para actualizar las estadísticas cuando se carga un curso
    function updateStatisticsPanel(courseCode) {
        if ($('#estadistico-tab').hasClass('active')) {
            loadCourseStatistics(courseCode);
        }
    }

    // Event listener para cuando se active el tab de estadísticas
    $('#estadistico-tab').on('shown.bs.tab', function() {
        if (currentCourseCode) {
            loadCourseStatistics(currentCourseCode);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        const popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });
</script>

<style>
    #complianceChart,
    #complianceChartWithEnglish,
    #realHoursChart,
    #realHoursChartWithEnglish {
        max-width: 100%;
        max-height: 180px;
    }

    #estadistico-tab-pane {
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    #statisticsContainer {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    #statisticsContainer .row {
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    #statisticsContainer .col {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .card.shadow-sm {
        width: 100% !important;
    }

    /* Estilos para el carrusel - SECCIÓN CORREGIDA */
    #classStatsCarousel {
        width: 100%;
        padding: 0 40px;
        box-sizing: border-box;
    }

    #classStatsCarousel .carousel-inner {
        width: 100%;
        overflow: hidden;
    }

    #classStatsCarousel .carousel-item {
        width: 100%;
        transition: transform .6s ease-in-out;
    }

    /* Estilos para los controles del carrusel */
    .carousel-control-prev,
    .carousel-control-next {
        width: 5%;
        color: #006d68; /* bs-teal-dark */
        opacity: 0.8;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: #006d68; /* bs-teal-dark */
        border-radius: 50%;
        padding: 20px;
        opacity: 0.9;
    }
    
    .carousel-control-prev {
        left: 0;
    }
    
    .carousel-control-next {
        right: 0;
    }

    /* Estilos responsive para las tarjetas de estadísticas */
    @media (max-width: 768px) {
        #classStatsCarousel {
            padding: 0 10px;
        }
    }

    /* Estilos mejorados para las tarjetas de estadísticas con nuevos colores */
    .stats-card {
        min-height: 200px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: none;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Tarjeta de Presentes - Verde Teal */
    .stats-card.bg-success {
        background: linear-gradient(135deg, #006d68, #008a84) !important; /* bs-teal-dark */
        border-left: 4px solid #004d4a;
    }

    /* Tarjeta de Tardíos - Ámbar */
    .stats-card.bg-warning {
        background: linear-gradient(135deg, #bf6900, #e67300) !important; /* bs-amber-dark a bs-orange-dark */
        border-left: 4px solid #a05500;
    }

    /* Tarjeta de Ausentes - Rosa/Magenta */
    .stats-card.bg-danger {
        background: linear-gradient(135deg, #b53471, #ec008c) !important; /* bs-pink-dark a bs-magenta-dark */
        border-left: 4px solid #8e2659;
    }

    .stats-icon {
        opacity: 0.9;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    .stats-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin: 0;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .stats-label {
        font-size: 1.1rem;
        font-weight: 500;
        margin: 0;
        opacity: 0.95;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    }

    .stats-percentage {
        font-size: 1.2rem;
        font-weight: 600;
        background-color: rgba(255, 255, 255, 0.25);
        padding: 6px 14px;
        border-radius: 20px;
        min-width: 60px;
        display: inline-block;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stats-card {
            min-height: 180px;
        }
        
        .stats-number {
            font-size: 2rem;
        }
        
        .stats-label {
            font-size: 1rem;
        }
        
        .stats-percentage {
            font-size: 1rem;
            padding: 4px 12px;
        }
        
        .stats-icon i {
            font-size: 2rem !important;
        }
    }

    @media (max-width: 576px) {
        .stats-card {
            min-height: 160px;
        }
        
        .stats-number {
            font-size: 1.8rem;
        }
        
        .stats-icon i {
            font-size: 1.8rem !important;
        }
    }

    /* Estilos para el botón de exportación con nuevo color */
    .export-class-btn {
        padding: 10px 24px;
        font-size: 1rem;
        font-weight: 500;
        border-radius: 8px;
        border: none;
        background: linear-gradient(135deg, #30336b, #495057) !important; /* bs-indigo-dark a bs-gray-dark */
        box-shadow: 0 3px 6px rgba(48, 51, 107, 0.3);
        transition: all 0.3s ease-in-out;
        color: white;
    }

    .export-class-btn:hover {
        background: linear-gradient(135deg, #495057, #30336b) !important; /* Invertir gradiente */
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(48, 51, 107, 0.4);
        color: white;
    }

    .export-class-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(48, 51, 107, 0.3);
    }

    /* Responsive para el botón */
    @media (max-width: 768px) {
        .export-class-btn {
            font-size: 0.9rem;
            padding: 8px 20px;
        }
    }

    /* Mejoras adicionales en headers de las tarjetas principales */
    .card-header.bg-primary {
        background: linear-gradient(135deg, #006d68, #30336b) !important; /* bs-teal-dark a bs-indigo-dark */
        border-bottom: 2px solid #004d4a;
    }

    .card-header.bg-success {
        background: linear-gradient(135deg, #006d68, #008a84) !important; /* bs-teal-dark */
        border-bottom: 2px solid #004d4a;
    }

    .card-header.bg-info {
        background: linear-gradient(135deg, #30336b, #495057) !important; /* bs-indigo-dark a bs-gray-dark */
        border-bottom: 2px solid #1f2142;
    }

    .card-header.bg-warning {
        background: linear-gradient(135deg, #bf6900, #e67300) !important; /* bs-amber-dark a bs-orange-dark */
        border-bottom: 2px solid #a05500;
    }

    .card-header.bg-danger {
        background: linear-gradient(135deg, #b53471, #ec008c) !important; /* bs-pink-dark a bs-magenta-dark */
        border-bottom: 2px solid #8e2659;
    }

    .card-header.bg-secondary {
        background: linear-gradient(135deg, #495057, #30336b) !important; /* bs-gray-dark a bs-indigo-dark */
        border-bottom: 2px solid #343a40;
    }

    .card-header.bg-dark {
        background: linear-gradient(135deg, #495057, #30336b) !important; /* bs-gray-dark a bs-indigo-dark */
        border-bottom: 2px solid #1f2142;
    }

    /* Mejora en las animaciones de hover para todas las tarjetas */
    .card.shadow-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12) !important;
        transition: all 0.3s ease-in-out;
    }

    /* Iconos con mejor contraste */
    .card-header i {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.1));
    }
</style>