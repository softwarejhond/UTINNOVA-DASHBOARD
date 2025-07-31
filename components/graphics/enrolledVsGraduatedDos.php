<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #enrolledVsGraduatedChartDos {
        width: 450px;
        height: 200px;
        margin: 0 auto;
        position: relative;
    }
    .donut-center-enrolled-dos {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        z-index: 10;
    }
    .center-title-enrolled-dos {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .center-stats-enrolled-dos {
        font-size: 12px;
        line-height: 1.4;
    }
    .enrolled-stat-dos {
        margin: 2px 0;
        font-weight: bold;
    }
    .matriculados-color-dos {
        color: #02d7ff;
    }
    .formados-color-dos {
        color: #38cb89;
    }
    .certificados-color-dos {
        color: #ffc107;
    }
</style>

<div id="enrolledVsGraduatedChartDos">
    <div class="donut-center-enrolled-dos" id="centerContentEnrolledDos">
        <div class="center-title-enrolled-dos">Total</div>
        <div class="center-stats-enrolled-dos" id="centerStatsEnrolledDos">
            <div class="enrolled-stat-dos matriculados-color-dos" id="matriculadosStatDos">Matriculados: 0</div>
            <div class="enrolled-stat-dos formados-color-dos" id="formadosStatDos">Formados: 0</div>
            <div class="enrolled-stat-dos certificados-color-dos" id="certificadosStatDos">Certificados: 0</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function actualizarGraficoMatriculadosVsFormadosDos() {
            $.ajax({
                url: 'components/cardContadores/actualizarContadores.php',
                method: 'GET',
                success: function(data) {
                    const matriculados = data.total_matriculados_2 || 0;
                    const formados = data.total_formados_2 || 0;
                    const certificados = data.total_certificados_2 || 0;

                    updateCenterStatsEnrolledDos(matriculados, formados, certificados);

                    const chart = echarts.init(document.getElementById('enrolledVsGraduatedChartDos'));

                    const opciones = {
                        tooltip: {
                            trigger: 'item',
                            formatter: '{b}: {c} usuarios ({d}%)',
                            appendToBody: true
                        },
                        series: [{
                            type: 'pie',
                            radius: ['45%', '75%'],
                            center: ['35%', '50%'],
                            avoidLabelOverlap: false,
                            data: [
                                {
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

                    chart.setOption(opciones);

                    window.addEventListener('resize', function() {
                        chart.resize();
                    });
                },
                error: function(error) {
                    console.error('Error al actualizar gráfico matriculados vs formados (lote 2):', error);
                    document.getElementById('enrolledVsGraduatedChartDos').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error al cargar los datos</div>';
                }
            });
        }

        function updateCenterStatsEnrolledDos(matriculados, formados, certificados) {
            document.getElementById('matriculadosStatDos').textContent = `Matriculados: ${matriculados}`;
            document.getElementById('formadosStatDos').textContent = `Formados: ${formados}`;
            document.getElementById('certificadosStatDos').textContent = `Certificados: ${certificados}`;
        }

        actualizarGraficoMatriculadosVsFormadosDos();
        setInterval(actualizarGraficoMatriculadosVsFormadosDos, 10000);
    });
</script>