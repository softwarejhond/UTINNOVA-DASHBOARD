document
  .getElementById("smtpForm")
  .addEventListener("submit", function (event) {
    event.preventDefault(); // Evita la recarga de la página

    const formData = new FormData(this);

    fetch("funciones.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        

        // Verificar si la respuesta es correcta (status 200)
        if (!response.ok) {
          throw new Error("Error en la respuesta de la red"); // Lanzar error si no es OK
        }

        // Intentar convertir la respuesta en JSON
        return response.json().catch((jsonError) => {
          throw new Error("Error al parsear la respuesta del servidor");
        });
      })
      .then((data) => {

        // Muestra el mensaje en el toast
        const toastElement = document.getElementById("liveToastSmtp");
        const toastBody = toastElement.querySelector(".toast-body");

        // Verifica si hay un mensaje de error específico y muestra el mensaje correspondiente
        if (data.tipo_mensaje === "error") {
          toastBody.innerHTML = data.mensaje || "Error desconocido.";
          toastElement.className = "toast bg-amber-light"; // Cambia el color del toast a uno de advertencia
        } else {
          toastBody.innerHTML = data.mensaje;
          toastElement.className =
            "toast " +
            (data.tipo_mensaje === "success"
              ? "bg-lime-light"
              : "bg-amber-light");
        }

        // Muestra el toast con el mensaje
        toastElement.style.display = "block";
        var toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Actualiza las imágenes si se han recibido nuevas rutas en la respuesta
        if (data.urlPicture) {
          document.getElementById("currentImage").src = data.urlPicture;
        }
        if (data.logoEncabezado) {
          document.getElementById("currentLogo").src = data.logoEncabezado;
        }

        // Refresca la página después de 3 segundos si la operación fue exitosa
        if (data.tipo_mensaje === "success") {
          setTimeout(() => {
            location.reload();
          }, 3000);
        }
      })
      .catch((error) => {

        // Muestra un mensaje de error genérico en caso de fallo
        const toastElement = document.getElementById("liveToastSmtp");
        toastElement.querySelector(".toast-body").innerHTML =
          "Hubo un error al enviar el formulario. " + error.message;
        toastElement.className = "toast bg-amber-light";
        var toast = new bootstrap.Toast(toastElement);
        toast.show();
      });
  });
