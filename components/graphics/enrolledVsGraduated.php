<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #enrolledVsGraduatedChart {
        width: 450px;
        height: 200px;
        margin: 0 auto;
        position: relative;
    }

    .donut-center-enrolled {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        z-index: 10;
    }

    .center-title-enrolled {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .center-stats-enrolled {
        font-size: 12px;
        line-height: 1.4;
    }

    .enrolled-stat {
        margin: 2px 0;
        font-weight: bold;
    }

    .matriculados-color {
        color: #02d7ff;
    }

    .formados-color {
        color: #38cb89;
    }

    .certificados-color {
        color: #ffc107;
    }
</style>

<div id="enrolledVsGraduatedChart">
    <div class="donut-center-enrolled" id="centerContentEnrolled">
        <div class="center-title-enrolled">Total</div>
        <div class="center-stats-enrolled" id="centerStatsEnrolled">
            <div class="enrolled-stat matriculados-color" id="matriculadosStat">Matriculados: 0</div>
            <div class="enrolled-stat formados-color" id="formadosStat">Formados: 0</div>
            <div class="enrolled-stat certificados-color" id="certificadosStat">Certificados: 0</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para actualizar el gráfico
        function actualizarGraficoMatriculadosVsFormados() {
            $.ajax({
                url: 'components/cardContadores/actualizarContadores.php',
                method: 'GET',
                success: function(data) {
                    const matriculados = data.total_matriculados || 0;
                    const formados = data.total_formados || 0;
                    const certificados = data.total_certificados || 0;

                    // Actualizar las estadísticas en el centro
                    updateCenterStatsEnrolled(matriculados, formados, certificados);

                    // Inicializar el gráfico
                    const chart = echarts.init(document.getElementById('enrolledVsGraduatedChart'));

                    // Configurar las opciones del gráfico tipo donut
                    const opciones = {
                        tooltip: {
                            trigger: 'item',
                            formatter: '{b}: {c} usuarios ({d}%)',
                            appendToBody: true
                        },
                        series: [{
                            type: 'pie',
                            radius: ['45%', '75%'], // Radio interno y externo para crear efecto donut
                            center: ['35%', '50%'],
                            avoidLabelOverlap: false,
                            data: [{
                                    name: 'Matriculados',
                                    value: matriculados,
                                    itemStyle: {
                                        color: '#02d7ff',
                                        borderColor: '#fff',
                                        borderWidth: 2
                                    }
                                },
                                {
                                    name: 'Formados',
                                    value: formados,
                                    itemStyle: {
                                        color: '#38cb89',
                                        borderColor: '#fff',
                                        borderWidth: 2
                                    }
                                },
                                {
                                    name: 'Certificados',
                                    value: certificados,
                                    itemStyle: {
                                        color: '#ffc107',
                                        borderColor: '#fff',
                                        borderWidth: 2
                                    }
                                }
                            ],
                            label: {
                                show: true,
                                position: 'outside',
                                formatter: function(params) {
                                    if (params.value === 0) {
                                        return '';
                                    }
                                    return `${params.name}\n${params.percent}%`;
                                },
                                fontSize: 12,
                                fontWeight: 'bold',
                                color: '#333',
                                lineHeight: 16
                            },
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                                    scale: true,
                                    scaleSize: 5
                                },
                                label: {
                                    show: true,
                                    fontSize: 14,
                                    fontWeight: 'bold'
                                }
                            },
                            labelLine: {
                                show: true,
                                length: 10,
                                length2: 8,
                                smooth: 0.2
                            }
                        }],
                        animationType: 'expansion',
                        animationDuration: 1000
                    };

                    // Renderizar el gráfico
                    chart.setOption(opciones);

                    // Redimensionar el gráfico cuando cambie el tamaño de la ventana
                    window.addEventListener('resize', function() {
                        chart.resize();
                    });
                },
                error: function(error) {
                    console.error('Error al actualizar gráfico matriculados vs formados:', error);
                    document.getElementById('enrolledVsGraduatedChart').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error al cargar los datos</div>';
                }
            });
        }

        function updateCenterStatsEnrolled(matriculados, formados, certificados) {
            // Actualizar los elementos del centro
            document.getElementById('matriculadosStat').textContent = `Matriculados: ${matriculados}`;
            document.getElementById('formadosStat').textContent = `Formados: ${formados}`;
            document.getElementById('certificadosStat').textContent = `Certificados: ${certificados}`;
        }

        // Actualizar el gráfico inicialmente
        actualizarGraficoMatriculadosVsFormados();

        // Actualizar cada 10 segundos
        setInterval(actualizarGraficoMatriculadosVsFormados, 10000);
    });
</script>