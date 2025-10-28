<!-- <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #ageRangesChartDos { width: 350px; height: 200px; }
</style>

<div id="ageRangesChartDos"></div>

<script>
    async function cargarDatosEdadesDos() {
        try {
            // Obtener los datos desde PHP para lote 2
            const respuesta = await fetch('components/graphics/ageRangesQuery.php?json=2');
            const datos = await respuesta.json();

            // Verificar si los datos están correctos
            if (!datos.labels || !datos.data) {
                throw new Error('Datos inválidos recibidos.');
            }

            // Inicializar el gráfico en el div "ageRangesChartDos"
            const chart = echarts.init(document.getElementById('ageRangesChartDos'));

            // Colores para cada rango de edad
            const colores = [
                '#FF6B6B', // 18-25 años - Rojo coral
                '#4ECDC4', // 26-35 años - Turquesa
                '#45B7D1', // 36-45 años - Azul cielo
                '#96CEB4', // 46-55 años - Verde menta
                '#FFEAA7', // 56-65 años - Amarillo suave
                '#DDA0DD', // 65+ años - Lila
                '#C0C0C0'  // Sin especificar - Gris
            ];

            // Configurar la gráfica
            const opciones = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} personas ({d}%)',
                    appendToBody: true
                },
                series: [{
                    type: 'pie',
                    radius: '70%',
                    center: ['45%', '50%'],
                    data: datos.labels.map((label, i) => ({
                        name: label,
                        value: datos.data[i],
                        itemStyle: {
                            color: colores[i] || '#C0C0C0'
                        }
                    })),
                    label: {
                        show: true,
                        formatter: function(params) {
                            if (params.value === 0) {
                                return '';
                            }
                            return `${params.name}: ${params.value}`;
                        },
                        fontSize: 12,
                        color: '#000',
                        overflow: 'none',
                        width: 'auto'
                    },
                    labelLine: {
                        show: true,
                        length: 15,
                        length2: 10
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

            // Renderizar la gráfica
            chart.setOption(opciones);

            // Hacer el gráfico responsive
            window.addEventListener('resize', function() {
                chart.resize();
            });
        } catch (error) {
            console.error('Error al cargar los datos de edades (lote 2):', error);
        }
    }

    // Cargar la gráfica
    cargarDatosEdadesDos();
</script> -->