function actualizarInquilinosProximos() {
  fetch("controller/obtener_inquilinos_proximos.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error("Error en la consulta:", data.error);
      } else {
        // Actualiza la lista de inquilinos en el dropdown
        document.getElementById("dropdownInquilinos").innerHTML = data.html;
        // Actualiza el total de inquilinos
        document.getElementById("totalInquilinos").textContent =
          data.totalRegistros;
      }
    })
    .catch((error) =>
      console.error("Error al obtener inquilinos próximos:", error)
    );
}

// Actualiza cada 5 segundos
setInterval(actualizarInquilinosProximos, 5000);

// Llama a la función la primera vez para mostrar datos de inmediato
actualizarInquilinosProximos();

document.addEventListener("DOMContentLoaded", function () {
  var dropdownElementList = [].slice.call(
    document.querySelectorAll(".dropdown-toggle")
  );
  var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
    return new bootstrap.Dropdown(dropdownToggleEl);
  });
});
