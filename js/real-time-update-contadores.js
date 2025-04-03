// ACTUALIZACION EN TIEMPO REAL AJAX PARA LOS CONTADORES
function actualizarProporciones() {
  fetch("../controller/obtener_proporciones.php")
    .then((response) => response.json())
    .then((data) => {
      // Actualizar los valores de propiedades
      document.getElementById("venta").innerHTML = data.total_en_venta;
      document.getElementById("arriendo").innerHTML = data.total_en_arriendo;
      document.getElementById("alquiler_venta").innerHTML = data.total_alquiler_o_venta;
      document.getElementById("total").innerHTML = data.total_propiedades;
      document.getElementById("porc_venta").innerHTML = data.porcentaje_en_venta.toFixed(2);
      document.getElementById("porc_arriendo").innerHTML = data.porcentaje_en_arriendo.toFixed(2);
      document.getElementById("porc_alquiler_venta").innerHTML = data.porcentaje_alquiler_o_venta.toFixed(2);

      // Actualizar los valores de usuarios
      document.getElementById("total_usuarios").innerHTML = data.total_usuarios;
      document.getElementById("masculinos").innerHTML = data.total_masculinos;
      document.getElementById("porc_masculinos").innerHTML = data.porcentaje_masculino.toFixed(2);
      document.getElementById("femeninos").innerHTML = data.total_femeninos;
      document.getElementById("porc_femeninos").innerHTML = data.porcentaje_femenino.toFixed(2);
      document.getElementById("con_programa").innerHTML = data.total_con_programa;
      document.getElementById("porc_con_programa").innerHTML = data.porcentaje_con_programa.toFixed(2);
    })
    .catch((error) => console.error("Error al obtener proporciones:", error));
}

// Actualiza cada 5 segundos
setInterval(actualizarProporciones, 5000);

// Llama a la función la primera vez para mostrar datos de inmediato
actualizarProporciones();
