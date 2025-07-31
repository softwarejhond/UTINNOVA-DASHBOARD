<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #graficaRegistrosVsGruposDos { 
        width: 450px; 
        height: 200px; 
        margin: 0 auto;
        position: relative;
    }
    .donut-center-registro-dos {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        z-index: 10;
    }
    .center-title-registro-dos {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .center-stats-registro-dos {
        font-size: 12px;
        line-height: 1.4;
    }
    .registro-stat-dos {
        margin: 2px 0;
        font-weight: bold;
    }
    .registrados-color-dos {
        color: #28a745;
    }
    .matriculados-color-dos {
        color: #ffc107;
    }
</style>

<div id="graficaRegistrosVsGruposDos">
    <div class="donut-center-registro-dos" id="centerContentRegistroDos">
        <div class="center-title-registro-dos">Total</div>
        <div class="center-stats-registro-dos" id="centerStatsRegistroDos">
            <div class="registro-stat-dos registrados-color-dos" id="registradosStatDos">Registrados: 0</div>
            <div class="registro-stat-dos matriculados-color-dos" id="matriculadosStatDos">Matriculados: 0</div>
        </div>
    </div>
</div>

<script>
    async function cargarDatosRegistrosVsGruposDos() {
        try {
            // Obtener los datos desde PHP para lote 2
            const respuesta = await fetch('components/graphics/registeVsEnrollerQuery.php?json=2');
            const datos = await respuesta.json();

            if (!datos.labels || !datos.data) {
                throw new Error('Datos inválidos recibidos.');
            }

            updateCenterStatsRegistroDos(datos);

            const chart = echarts.init(document.getElementById('graficaRegistrosVsGruposDos'));

            const opciones = {
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
                    data: datos.labels.map((label, i) => {
                        let color;
                        if (label.toLowerCase().includes('registrado') || label.toLowerCase().includes('registro')) {
                            color = '#28a745';
                        } else if (label.toLowerCase().includes('matriculado') || label.toLowerCase().includes('matricula')) {
                            color = '#ffc107';
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

            window.addEventListener('resize', () => {
                chart.resize();
            });

        } catch (error) {
            console.error('Error al cargar los datos:', error);
            document.getElementById('graficaRegistrosVsGruposDos').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error al cargar los datos</div>';
        }
    }

    function updateCenterStatsRegistroDos(datos) {
        let registradosValue = 0, matriculadosValue = 0;
        datos.labels.forEach((label, index) => {
            if (label.toLowerCase().includes('registrado') || label.toLowerCase().includes('registro')) {
                registradosValue = datos.data[index];
            } else if (label.toLowerCase().includes('matriculado') || label.toLowerCase().includes('matricula')) {
                matriculadosValue = datos.data[index];
            }
        });
        document.getElementById('registradosStatDos').textContent = `Registrados: ${registradosValue}`;
        document.getElementById('matriculadosStatDos').textContent = `Matriculados: ${matriculadosValue}`;
    }

    document.addEventListener('DOMContentLoaded', cargarDatosRegistrosVsGruposDos);
</script>