<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #grafica { width: 100%; height: 200px; }
</style>

<div id="grafica"></div>

<script>
    async function cargarDatos() {
        try {
            // Obtener los datos desde PHP
            const respuesta = await fetch('components/graphics/registerDeparmentsQuery.php?json=1');
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
                    formatter: '{b}: {c} registros ({d}%)' // Muestra: Nombre, Cantidad y Porcentaje
                },
                series: [{
                    type: 'pie',
                    radius: '60%',
                    data: datos.labels.map((label, i) => ({
                        name: label,
                        value: datos.data[i]
                    })),
                    label: {
                        show: true,
                        formatter: '{b}: {c} registros ({d}%)', // Nombre, cantidad y porcentaje
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                }]
            };

            // Renderizar la gráfica
            chart.setOption(opciones);
        } catch (error) {
            console.error('Error al cargar los datos:', error);
        }
    }

    // Cargar la gráfica
    cargarDatos();
</script>