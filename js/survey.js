function confirmarEliminacion(id) {
    Swal.fire({
      title: '¿Estás seguro?',
      text: "¡No podrás revertir esto!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
       
        $.ajax({
          url: 'encuestas.php', 
          type: 'POST',
          data: {
            action: 'eliminar',
            id: id
          },
          success: function(response) {
           
            if (response === 'success') {
              Swal.fire(
                '¡Eliminado!',
                'La encuesta ha sido eliminada.',
                'success'
              ).then(() => {
                // Recargar la página o actualizar la tabla 
                location.reload();
              });
            } else {
              Swal.fire(
                '¡Error!',
                'Hubo un problema al eliminar la encuesta.',
                'error'
              );
            }
          },
          error: function() {
            Swal.fire(
              '¡Error!',
              'Hubo un problema al comunicarse con el servidor.',
              'error'
            );
          }
        });
      }
    });
  }