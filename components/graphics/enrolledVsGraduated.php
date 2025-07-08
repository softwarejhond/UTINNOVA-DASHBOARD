
<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #enrolledVsGraduatedChart { 
        width: 100%; 
        height: 200px; 
    }
</style>

<div id="enrolledVsGraduatedChart"></div>

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
                
                // Inicializar el gráfico
                const chart = echarts.init(document.getElementById('enrolledVsGraduatedChart'));
                
                // Configurar las opciones del gráfico
                const opciones = {
                    tooltip: {
                        trigger: 'item',
                        formatter: function(params) {
                            return `${params.name}: ${params.value} (${params.percent}%)`;
                        }
                    },
                    series: [{
                        type: 'pie',
                        radius: '70%',
                        data: [
                            {
                                name: 'Matriculados',
                                value: matriculados,
                                itemStyle: {
                                    color: '#02d7ff'
                                }
                            },
                            {
                                name: 'Formados',
                                value: formados,
                                itemStyle: {
                                    color: '#38cb89'
                                }
                            },
                            {
                                name: 'Certificados',
                                value: certificados,
                                itemStyle: {
                                    color: '#ffc107'
                                }
                            }
                        ],
                        label: {
                            show: true,
                            formatter: function(params) {
                                if (params.value === 0) {
                                    return '';
                                }
                                return `${params.name}: ${params.value}`;
                            },
                            fontSize: 12,
                            color: '#000'
                        },
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }]
                };
                
                // Renderizar el gráfico
                chart.setOption(opciones);
                
                // Hacer el gráfico responsive
                window.addEventListener('resize', function() {
                    chart.resize();
                });
            },
            error: function(error) {
                console.error('Error al actualizar gráfico matriculados vs formados:', error);
            }
        });
    }

    // Actualizar el gráfico inicialmente
    actualizarGraficoMatriculadosVsFormados();
    
    // Actualizar cada 10 segundos
    setInterval(actualizarGraficoMatriculadosVsFormados, 10000);
});
</script>