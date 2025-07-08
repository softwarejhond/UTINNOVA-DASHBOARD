<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<style>
    #graficaRegistrosVsGrupos { width: 100%; height: 200px; }
</style>

<div id="graficaRegistrosVsGrupos"></div>

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

            // Inicializar el gráfico en el div "graficaRegistrosVsGrupos"
            const chartRegistrosVsGrupos = echarts.init(document.getElementById('graficaRegistrosVsGrupos'));

            // Configurar la gráfica
            const opcionesRegistrosVsGrupos = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} registros ({d}%)', // Muestra: Nombre, Cantidad y Porcentaje
                    appendToBody: true
                },
                series: [{
                    type: 'pie',
                    radius: '60%',
                    data: datosRegistrosVsGrupos.labels.map((label, i) => ({
                        name: label,
                        value: datosRegistrosVsGrupos.data[i]
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
            chartRegistrosVsGrupos.setOption(opcionesRegistrosVsGrupos);
        } catch (error) {
            console.error('Error al cargar los datos:', error);
        }
    }

    // Cargar la gráfica
    cargarDatosRegistrosVsGrupos();
</script>