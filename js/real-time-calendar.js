document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("calendar");
  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: "es",
    initialView: "dayGridMonth",
    events: "APIS/citas/verCitasCalendar.php", // URL inicial para obtener los eventos
    eventDidMount: function (info) {
      var estado = info.event.extendedProps.estado;
      var colorTooltip = "";

      // Determinar el color de fondo del tooltip según el estado
      switch (estado) {
        case "Sin Atender":
          colorTooltip = "tooltip-warning"; // Amarillo para "Sin Atender"
          break;
        case "Atendido":
          colorTooltip = "tooltip-success"; // Verde para "Atendido"
          break;
        case "Cancelado":
          colorTooltip = "tooltip-danger"; // Rojo para "Cancelado"
          break;
        default:
          colorTooltip = "tooltip-info"; // Azul para otros estados
      }

      // Crear el tooltip con estilo
      var tooltip = new bootstrap.Tooltip(info.el, {
        title: `
                    <ul class="list-group" style="padding-left: 20px;">
                        <li class="list-group-item text-capitalize"><h2 class="text-left"><i class="bi bi-caret-right-fill"></i> ${info.event.title}</h2></li>
                        <li class="list-group-item text-capitalize"> <i class="bi bi-person-circle"></i> Nombre: <b>${info.event.extendedProps.name}</b></li>
                        <li class="list-group-item"> <i class="bi bi-houses-fill"></i> Propiedad: <b>${info.event.extendedProps.propiedad}</b> </li>
                        <li class="list-group-item"> <i class="bi bi-telephone-fill"></i> Teléfono: <b>${info.event.extendedProps.telefono}</b> </li>
                        <li class="list-group-item"> <i class="bi bi-question-diamond-fill"></i> Estado: <b>${estado}</b> </li>
                        
                        <b class="text-center text-white"><i class="bi bi-alarm"></i> Información en tiempo real</b>
                    </ul>
                   
                `,
        html: true,
        placement: "top",
        trigger: "hover",
        template: `
                    <div class="tooltip ${colorTooltip}" role="tooltip">
                        <div class="tooltip-arrow"></div>
                        <div class="tooltip-inner" style="white-space: normal; word-wrap: break-word; min-width: 150px;"></div>
                    </div>
                `,
      });

      // Configurar el temporizador para cerrar el tooltip a los 4 segundos
      var tooltipTimeout;
      info.el.addEventListener("mouseenter", function () {
        tooltipTimeout = setTimeout(function () {
          var tooltipInstance = bootstrap.Tooltip.getInstance(info.el);
          if (tooltipInstance) {
            tooltipInstance.hide(); // Cerrar el tooltip
          }
        }, 4000); // Espera 4 segundos antes de cerrar el tooltip
      });

      // Cancelar el temporizador si el mouse sale antes de los 4 segundos
      info.el.addEventListener("mouseleave", function () {
        clearTimeout(tooltipTimeout);
      });
    },
  });

  calendar.render();

  // Función para actualizar los eventos usando AJAX
  function updateEvents() {
    $.ajax({
      url: "APIS/citas/verCitasCalendar.php", // URL del archivo que devuelve los eventos
      method: "GET",
      success: function (data) {
        // Imprime la respuesta del servidor para ver qué está devolviendo
        console.log("Respuesta del servidor:", data);

        try {
          // Asegurarse de que la respuesta sea un JSON válido
          var events;
          // Si la respuesta ya es un objeto, no es necesario hacer JSON.parse
          if (typeof data === "string") {
            events = JSON.parse(data); // Si la respuesta es un string, parsearlo
          } else {
            events = data; // Si ya es un objeto, no lo parseamos
          }

          // Actualizar los eventos del calendario
          calendar.removeAllEvents(); // Elimina los eventos actuales
          calendar.addEventSource(events); // Agrega los nuevos eventos

          // Re-renderizar el calendario con los eventos actualizados
          calendar.render();
        } catch (e) {
          console.error("Error al analizar la respuesta JSON:", e);
        }
      },
      error: function (error) {
        console.error("Error al obtener eventos:", error);
      },
    });
  }

  // Función para cerrar todos los tooltips antes de la actualización
  function closeTooltips() {
    var tooltips = document.querySelectorAll(".tooltip");
    tooltips.forEach(function (tooltip) {
      var tooltipInstance = bootstrap.Tooltip.getInstance(tooltip); // Obtener instancia del tooltip
      if (tooltipInstance) {
        tooltipInstance.hide(); // Cerrar el tooltip
      }
    });
  }

  // Función para actualizar los eventos cada 5 segundos sin bloquear la vista
  function autoUpdateEvents() {
    setInterval(function () {
      // Cerrar todos los tooltips antes de la actualización
      closeTooltips();

      // Recargar los eventos del calendario
      updateEvents(); // Solo recarga los eventos
    }, 5000); // Cada 5 segundos (ajustar según necesidad)
  }

  autoUpdateEvents(); // Inicia la actualización periódica
});
