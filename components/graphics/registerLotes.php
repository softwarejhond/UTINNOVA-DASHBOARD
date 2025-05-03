<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #grafica_lotes { width: 100%; height: 200px; }
</style>

<div id="grafica_lotes"></div>

<script>
    async function cargarDatosLotes() {
        try {
            // Obtener los datos desde PHP
            const respuesta = await fetch('components/graphics/registerLotesQuery.php?json=1');
            const datos = await respuesta.json();

            // Verificar si los datos están correctos
            if (!datos.labels || !datos.data) {
                throw new Error('Datos inválidos recibidos.');
            }

            // Inicializar el gráfico en el div "grafica_lotes"
            const chart = echarts.init(document.getElementById('grafica_lotes'));

            // Configurar la gráfica
            const opciones = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} usuarios ({d}%)' // Muestra: Nombre, Cantidad y Porcentaje
                },
                series: [{
                    type: 'pie',
                    radius: '60%',
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
                        formatter: '{b}: {c} ({d}%)', // Nombre, cantidad y porcentaje
                        fontSize: 14,
                        fontWeight: 'bold'
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
            console.error('Error al cargar los datos de lotes:', error);
            document.getElementById('grafica_lotes').innerHTML = 'Error al cargar los datos.';
        }
    }

    // Cargar la gráfica
    cargarDatosLotes();
</script>