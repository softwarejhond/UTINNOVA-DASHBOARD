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
        font-size: 12px;
        line-height: 1.4;
    }
    
    .registro-stat {
        margin: 2px 0;
        font-weight: bold;
    }
    
    .registrados-color {
        color: #28a745; /* CAMBIADO: Verde para registrados */
    }
    
    .matriculados-color {
        color: #ffc107; /* CAMBIADO: Amarillo para matriculados */
    }
</style>

<div id="graficaRegistrosVsGrupos">
    <div class="donut-center-registro" id="centerContentRegistro">
        <div class="center-title-registro">Total</div>
        <div class="center-stats-registro" id="centerStatsRegistro">
            <div class="registro-stat registrados-color" id="registradosStat">Registrados: 0</div>
            <div class="registro-stat matriculados-color" id="matriculadosStat">Matriculados: 0</div>
        </div>
    </div>
</div>

<script>
    async function cargarDatosRegistrosVsGrupos() {
        try {
            // Obtener los datos desde PHP
            const respuestaRegistrosVsGrupos = await fetch('components/graphics/registeVsEnrollerQuery.php?json=1');
            const datosRegistrosVsGrupos = await respuestaRegistrosVsGrupos.json();

            // Verificar si los datos están correctos
            if (!datosRegistrosVsGrupos.labels || !datosRegistrosVsGrupos.data) {
                throw new Error('Datos inválidos recibidos.');
            }

            // Actualizar las estadísticas en el centro
            updateCenterStatsRegistro(datosRegistrosVsGrupos);

            // Inicializar el gráfico en el div "graficaRegistrosVsGrupos"
            const chartRegistrosVsGrupos = echarts.init(document.getElementById('graficaRegistrosVsGrupos'));

            // Configurar la gráfica tipo donut
            const opcionesRegistrosVsGrupos = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} registros ({d}%)',
                    appendToBody: true
                },
                series: [{
                    type: 'pie',
                    radius: ['45%', '75%'], // Radio interno y externo para crear efecto donut
                    center: ['35%', '50%'],
                    avoidLabelOverlap: false,
                    data: datosRegistrosVsGrupos.labels.map((label, i) => {
                        // Asignar colores específicos para cada categoría
                        let color;
                        if (label.toLowerCase().includes('registrado') || label.toLowerCase().includes('registro')) {
                            color = '#28a745'; // CAMBIADO: Verde para registrados
                        } else if (label.toLowerCase().includes('matriculado') || label.toLowerCase().includes('matricula')) {
                            color = '#ffc107'; // CAMBIADO: Amarillo para matriculados
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

            // Renderizar la gráfica
            chartRegistrosVsGrupos.setOption(opcionesRegistrosVsGrupos);
            
            // Redimensionar el gráfico cuando cambie el tamaño de la ventana
            window.addEventListener('resize', () => {
                chartRegistrosVsGrupos.resize();
            });

        } catch (error) {
            console.error('Error al cargar los datos:', error);
            document.getElementById('graficaRegistrosVsGrupos').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error al cargar los datos</div>';
        }
    }

    function updateCenterStatsRegistro(datos) {
        let registradosValue = 0, matriculadosValue = 0;
        
        datos.labels.forEach((label, index) => {
            if (label.toLowerCase().includes('registrado') || label.toLowerCase().includes('registro')) {
                registradosValue = datos.data[index];
            } else if (label.toLowerCase().includes('matriculado') || label.toLowerCase().includes('matricula')) {
                matriculadosValue = datos.data[index];
            }
        });

        // Actualizar los elementos del centro
        document.getElementById('registradosStat').textContent = `Registrados: ${registradosValue}`;
        document.getElementById('matriculadosStat').textContent = `Matriculados: ${matriculadosValue}`;
    }

    // Cargar la gráfica cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', cargarDatosRegistrosVsGrupos);
</script>