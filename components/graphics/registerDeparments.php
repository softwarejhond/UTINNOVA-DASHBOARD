<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #grafica { 
        width: 350px; 
        height: 200px; 
        margin: 0 auto; /* Para centrar el contenedor */
    }
</style>

<div id="grafica"></div>

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

            // Inicializar el gráfico en el div "grafica"
            const chart = echarts.init(document.getElementById('grafica'));

            // Configurar la gráfica
            const opciones = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} usuarios ({d}%)', // Cambiado a usuarios en lugar de registros'
                    appendToBody: true
                },
                series: [{
                    type: 'pie',
                    radius: '60%',
                    center: ['45%', '50%'], // Centrado en 45% horizontal, 50% vertical
                    avoidLabelOverlap: false,
                    data: datos.labels.map((label, i) => {
                        // Asignar colores específicos para cada lote
                        let color;
                        if (label === 'Lote 1') {
                            color = '#6610f2'; // Color indigo para lote 1
                        } else if (label === 'Lote 2') {
                            color = '#20c997'; // Color teal para lote 2
                        } else {
                            color = '#6c757d'; // Gris para sin asignar
                        }
                        
                        return {
                            name: label,
                            value: datos.data[i],
                            itemStyle: {
                                color: color
                            }
                        };
                    }),
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}', // Solo mostrar el nombre del lote sin valores
                        fontSize: 14,
                        fontWeight: 'bold',
                        backgroundColor: 'rgba(255, 255, 255, 0.7)',
                        borderRadius: 4,
                        padding: [4, 8],
                        color: '#333'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: true,
                        length: 15,
                        length2: 10
                    }
                }]
            };

            // Renderizar la gráfica
            chart.setOption(opciones);
            
            // Redimensionar el gráfico cuando cambie el tamaño de la ventana
            window.addEventListener('resize', () => {
                chart.resize();
            });
        } catch (error) {
            console.error('Error al cargar los datos:', error);
            document.getElementById('grafica').innerHTML = 'Error al cargar los datos.';
        }
    }

    // Cargar la gráfica
    cargarDatos();
</script>