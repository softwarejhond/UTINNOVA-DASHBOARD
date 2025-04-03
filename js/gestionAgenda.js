$(document).ready(function () {
  $("#citas-table").DataTable({
    ajax: {
      url: "APIS/citas/citasView.php",
      dataSrc: function (json) {
        console.log(json); // Aquí puedes ver los datos de la API
        return json; // Asegúrate de devolver la estructura correcta
      },
    },
    columns: [
      {
        data: "fecha",
        render: function (data, type, row) {
          return (
            '<button class="btn naranjas btn-sm w-100">' + data + "</button>"
          );
        },
      },
      {
        data: "hora",
        render: function (data, type, row) {
          // Dividir la cadena de hora en partes de hora y minutos
          var [hour, minute] = data.split(":");

          // Crear un objeto Date estableciendo las horas y minutos
          var date = new Date();
          date.setHours(parseInt(hour));
          date.setMinutes(parseInt(minute));

          // Obtener la hora en formato AM/PM
          var hora12 = date.toLocaleTimeString("en-US", {
            hour: "numeric",
            minute: "numeric",
            hour12: true,
          });

          return (
            '<button class="btn blue btn-sm w-100">' + hora12 + "</button>"
          );
        },
      },
      {
        data: "tipoCita",
      },
      {
        data: "nombre",
      },
      {
        data: "codigoPropiedad",
      },
      {
        data: "telefono",
      },
      {
        data: "estado",
        render: function (data, type, row) {
          if (data == 0) {
            return '<button class="btn bg-orange-dark text-white p-1 w-100 btn-sm" >PENDIENTE</button>';
          } else if (data == 1) {
            return '<button class="btn bg-indigo-dark text-white p-1 w-100 btn-sm"> ATENDIDO</button>';
          } else if (data == 2) {
            return '<button class="btn bg-magenta-dark text-white p-1 w-100 btn-sm"> CANCELADO</button>';
          } else {
            return data;
          }
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          var estadoOptions = `
          <select class="form-select custom-select text-center custom-select-sm bg-indigo-dark text-white" onchange="actualizarEstado(${
            row.id
          }, this)">
    <option value="0" ${row.estado == 0 ? "selected" : ""}>PENDIENTE</option>
    <option value="1" ${row.estado == 1 ? "selected" : ""}>ATENDIDO</option>
    <option value="2" ${row.estado == 2 ? "selected" : ""}>CANCELADO</option>
</select>

        `;
          return estadoOptions;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          var eliminarButton = `
            <button class="btn bg-red-dark text-white p-1 w-100 btn-sm" title="Eliminar Cita" onclick="eliminarCita(${row.id})">
               <i class="bi bi-trash3-fill"></i>
            </button>
        `;
          return eliminarButton;
        },
      },
    ],
  });
});

function eliminarCita(id) {
  // Preguntar al usuario si está seguro de eliminar la cita
  if (confirm("¿Estás seguro de que deseas eliminar esta cita?")) {
    // Realizar una solicitud DELETE a la API para eliminar la cita
    fetch(`APIS/citas/citasDelete.php?id=${id}`, {
      method: "DELETE",
    })
      .then((response) => {
        // Verificar si la solicitud fue exitosa
        if (!response.ok) {
          throw new Error("Ocurrió un problema al eliminar la cita.");
        }
        return response.json();
      })
      .then((data) => {
        // Procesar la respuesta si la eliminación fue exitosa
        console.log(data.message); // Muestra un mensaje de éxito en la consola
        // Actualizar los datos de la tabla
        actualizarTabla();
      })
      .catch((error) => {
        console.error("Error:", error.message); // Muestra un mensaje de error en la consola
      });
  }
}

function actualizarEstado(id, selectElement) {
  var nuevoEstado = selectElement.value;

  // Realizar una solicitud a la API para actualizar el estado de la cita
  fetch(`APIS/citas/citasUpdate.php?id=${id}&estado=${nuevoEstado}`, {
    method: "POST",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Estado actualizado");
        // Actualiza la tabla o la interfaz según sea necesario
        $("#citas-table").DataTable().ajax.reload();
      } else {
        alert("Error al actualizar el estado");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function actualizarTabla() {
  // Recargar los datos de la tabla en tiempo real
  $("#citas-table").DataTable().ajax.reload();
}
