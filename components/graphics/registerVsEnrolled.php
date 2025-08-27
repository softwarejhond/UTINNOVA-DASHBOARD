<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #graficaRegistrosVsGrupos { 
        width: 450px; 
        height: 200px; 
        margin: 0 auto;
        position: relative;
    }
    
    .donut-center-registro {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        z-index: 10;
    }
    
    .center-title-registro {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .center-stats-registro {
        font-size: 10px;
        line-height: 1.3;
    }
    
    .registro-stat {
        margin: 1px 0;
        font-weight: bold;
    }
    
    .registrados-color {
        color: #28a745;
    }
    
    .presencial-color {
        color: #007bff;
    }
    
    .virtual-color {
        color: #ffc107;
    }
</style>

<div id="graficaRegistrosVsGrupos">
    <div class="donut-center-registro" id="centerContentRegistro">
        <div class="center-title-registro">Total</div>
        <div class="center-stats-registro" id="centerStatsRegistro">
            <div class="registro-stat registrados-color" id="registradosStat">Registrados: 0</div>
            <div class="registro-stat presencial-color" id="presencialStat">Presencial: 0</div>
            <div class="registro-stat virtual-color" id="virtualStat">Virtual: 0</div>
        </div>
    </div>
</div>

<script>
    async function cargarDatosRegistrosVsGrupos() {
        try {
            const respuestaRegistrosVsGrupos = await fetch('components/graphics/registeVsEnrollerQuery.php?json=1');
            const datosRegistrosVsGrupos = await respuestaRegistrosVsGrupos.json();

            if (!datosRegistrosVsGrupos.labels || !datosRegistrosVsGrupos.data) {
                throw new Error('Datos inválidos recibidos.');
            }

            updateCenterStatsRegistro(datosRegistrosVsGrupos);

            const chartRegistrosVsGrupos = echarts.init(document.getElementById('graficaRegistrosVsGrupos'));

            const opcionesRegistrosVsGrupos = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} registros ({d}%)',
                    appendToBody: true
                },
                series: [{
                    type: 'pie',
                    radius: ['45%', '75%'],
                    center: ['35%', '50%'],
                    avoidLabelOverlap: false,
                    data: datosRegistrosVsGrupos.labels.map((label, i) => {
                        let color;
                        if (label.toLowerCase().includes('registrado')) {
                            color = '#28a745'; // Verde para registrados
                        } else if (label.toLowerCase().includes('presencial')) {
                            color = '#007bff'; // Azul para presencial
                        } else if (label.toLowerCase().includes('virtual')) {
                            color = '#ffc107'; // Amarillo para virtual
                        } else {
                            color = '#6c757d'; // Gris para otros
                        }
                        
                        return {
                            name: label,
                            value: datosRegistrosVsGrupos.data[i],
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
                        fontSize: 10,
                        fontWeight: 'bold',
                        color: '#333',
                        lineHeight: 14
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
                            fontSize: 12,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: true,
                        length: 8,
                        length2: 6,
                        smooth: 0.2
                    }
                }],
                animationType: 'expansion',
                animationDuration: 1000
            };

            chartRegistrosVsGrupos.setOption(opcionesRegistrosVsGrupos);
            
            window.addEventListener('resize', () => {
                chartRegistrosVsGrupos.resize();
            });

        } catch (error) {
            console.error('Error al cargar los datos:', error);
            document.getElementById('graficaRegistrosVsGrupos').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error al cargar los datos</div>';
        }
    }

    function updateCenterStatsRegistro(datos) {
        let registradosValue = 0, presencialValue = 0, virtualValue = 0;
        
        datos.labels.forEach((label, index) => {
            if (label.toLowerCase().includes('registrado')) {
                registradosValue = datos.data[index];
            } else if (label.toLowerCase().includes('presencial')) {
                presencialValue = datos.data[index];
            } else if (label.toLowerCase().includes('virtual')) {
                virtualValue = datos.data[index];
            }
        });

        document.getElementById('registradosStat').textContent = `Registrados: ${registradosValue}`;
        document.getElementById('presencialStat').textContent = `Presencial: ${presencialValue}`;
        document.getElementById('virtualStat').textContent = `Virtual: ${virtualValue}`;
    }

    document.addEventListener('DOMContentLoaded', cargarDatosRegistrosVsGrupos);
</script>