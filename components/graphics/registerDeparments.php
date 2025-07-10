<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #grafica { 
        width: 450px; 
        height: 200px; 
        margin: 0 auto;
        position: relative;
    }
    
    .donut-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        z-index: 10;
    }
    
    .center-title {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .center-stats {
        font-size: 12px;
        line-height: 1.4;
    }
    
    .lote-stat {
        margin: 2px 0;
        font-weight: bold;
    }
    
    .lote1-color {
        color: #6610f2;
    }
    
    .lote2-color {
        color: #20c997;
    }
    
    .sin-asignar-color {
        color: #6c757d;
    }
</style>

<div id="grafica">
    <div class="donut-center" id="centerContent">
        <div class="center-title">Total Lotes</div>
        <div class="center-stats" id="centerStats">
            <div class="lote-stat lote1-color" id="lote1Stat">Lote 1: 0</div>
            <div class="lote-stat lote2-color" id="lote2Stat">Lote 2: 0</div>
            <div class="lote-stat sin-asignar-color" id="sinAsignarStat">Sin asignar: 0</div>
        </div>
    </div>
</div>

<script>
    async function cargarDatos() {
        try {
            // Obtener los datos desde PHP - Cambiado a consulta de lotes
            const respuesta = await fetch('components/graphics/registerLotesQuery.php?json=1');
            const datos = await respuesta.json();

            // Verificar si los datos están correctos
            if (!datos.labels || !datos.data) {
                throw new Error('Datos inválidos recibidos.');
            }

            // Actualizar las estadísticas en el centro
            updateCenterStats(datos);

            // Inicializar el gráfico en el div "grafica"
            const chart = echarts.init(document.getElementById('grafica'));

            // Configurar la gráfica tipo donut
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
                    data: datos.labels.map((label, i) => {
                        // Asignar colores específicos para cada lote
                        let color;
                        if (label === 'Lote 1') {
                            color = '#6610f2';
                        } else if (label === 'Lote 2') {
                            color = '#20c997';
                        } else {
                            color = '#6c757d';
                        }
                        
                        return {
                            name: label,
                            value: datos.data[i],
                            itemStyle: {
                                color: color,
                                borderColor: '#fff',
                                borderWidth: 2
                            }
                        };
                    }),
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}\n{d}%',
                        fontSize: 12,
                        fontWeight: 'bold',
                        color: '#333',
                        lineHeight: 16
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
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

            // Renderizar la gráfica
            chart.setOption(opciones);
            
            // Redimensionar el gráfico cuando cambie el tamaño de la ventana
            window.addEventListener('resize', () => {
                chart.resize();
            });

            // Agregar interactividad al hover
            chart.on('highlight', function(params) {
                highlightCenterStat(params.name);
            });

            chart.on('downplay', function(params) {
                resetCenterStats();
            });

        } catch (error) {
            console.error('Error al cargar los datos:', error);
            document.getElementById('grafica').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error al cargar los datos</div>';
        }
    }

    function updateCenterStats(datos) {
        let lote1Value = 0, lote2Value = 0, sinAsignarValue = 0;
        
        datos.labels.forEach((label, index) => {
            if (label === 'Lote 1') {
                lote1Value = datos.data[index];
            } else if (label === 'Lote 2') {
                lote2Value = datos.data[index];
            } else {
                sinAsignarValue = datos.data[index];
            }
        });

        // Actualizar los elementos del centro
        document.getElementById('lote1Stat').textContent = `Lote 1: ${lote1Value}`;
        document.getElementById('lote2Stat').textContent = `Lote 2: ${lote2Value}`;
        document.getElementById('sinAsignarStat').textContent = `Sin asignar: ${sinAsignarValue}`;
    }

    function highlightCenterStat(labelName) {
        // Resetear todos los estilos
        resetCenterStats();
        
        // Resaltar el elemento correspondiente
        if (labelName === 'Lote 1') {
            document.getElementById('lote1Stat').style.fontSize = '14px';
            document.getElementById('lote1Stat').style.textShadow = '0 0 5px rgba(102, 16, 242, 0.5)';
        } else if (labelName === 'Lote 2') {
            document.getElementById('lote2Stat').style.fontSize = '14px';
            document.getElementById('lote2Stat').style.textShadow = '0 0 5px rgba(32, 201, 151, 0.5)';
        } else {
            document.getElementById('sinAsignarStat').style.fontSize = '14px';
            document.getElementById('sinAsignarStat').style.textShadow = '0 0 5px rgba(108, 117, 125, 0.5)';
        }
    }

    function resetCenterStats() {
        const stats = document.querySelectorAll('.lote-stat');
        stats.forEach(stat => {
            stat.style.fontSize = '12px';
            stat.style.textShadow = 'none';
        });
    }

    // Cargar la gráfica cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', cargarDatos);
</script>