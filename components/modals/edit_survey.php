<!-- components/encuestas/modals/editarEncuestaModal.php -->
<div class="modal fade" id="editarEncuestaModal-<?php echo htmlspecialchars($id_encuesta_actual); ?>" tabindex="-1" aria-labelledby="editarEncuestaModalLabel-<?php echo htmlspecialchars($id_encuesta_actual); ?>" aria-hidden="true" style="z-index: 1070 !important;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="editarEncuestaModalLabel-<?php echo htmlspecialchars($id_encuesta_actual); ?>">
                    Editar Encuesta <?php echo htmlspecialchars($fila['id']); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white bg-gray-ligth" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="editarForm-<?php echo htmlspecialchars($id_encuesta_actual); ?>" method="post" action="encuestas.php">
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" name="id" id="encuestaId-<?php echo htmlspecialchars($id_encuesta_actual); ?>" value="<?php echo htmlspecialchars($fila['id']); ?>">
                        <div class="row">
                            <div class="col-md-6 text-start">
                                <div class="mb-3">
                                    <label for="nombreCompleto" class="form-label"><i class="fas fa-user"></i> <strong>Nombre:</strong></label>
                                    <input type="text" class="form-control" id="nombreCompleto-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="nombreCompleto" value="<?php echo htmlspecialchars($fila['nombreCompleto']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cedula" class="form-label mt-4"><i class="fas fa-id-card"></i> <strong>Cédula:</strong></label>
                                    <input type="text" class="form-control" id="cedula-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="cedula" value="<?php echo htmlspecialchars($fila['cedula']); ?>" required>
                                </div>
                                <br>
                                <br>
                                <div class="mb-3">
                                    <label for="situacionLaboral-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label"><i class="fas fa-briefcase"></i> <strong>Situación laboral:</strong></label>
                                    <select class="form-select" id="situacionLaboral-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="situacionLaboral" required>
                                        <option value="">Seleccione una opción</option>
                                        <option value="empleado" <?php echo ($fila['situacionLaboral'] == 'empleado') ? 'selected' : ''; ?>>Empleado</option>
                                        <option value="desempleado" <?php echo ($fila['situacionLaboral'] == 'desempleado') ? 'selected' : ''; ?>>Desempleado</option>
                                        <option value="otro" <?php echo ($fila['situacionLaboral'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="tiempoDesempleoDiv-<?php echo htmlspecialchars($id_encuesta_actual); ?>" style="display: none;">
                                    <label for="tiempoDesempleo-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label"><i class="fas fa-wrench"></i> <strong>Tiempo desempleado:</strong></label>
                                    <input type="text" class="form-control" id="tiempoDesempleo-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="tiempoDesempleo" value="<?php echo htmlspecialchars($fila['tiempoDesempleo'] ?? ''); ?>">
                                </div>
                                <div class="mb-3" id="trabajoDesempenaDiv-<?php echo htmlspecialchars($id_encuesta_actual); ?>" style="display: none;">
                                    <label for="trabajoDesempena-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label"><i class="fas fa-wrench"></i> <strong>Trabajo desempeña:</strong></label>
                                    <input type="text" class="form-control" id="trabajoDesempena-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="trabajoDesempena" value="<?php echo htmlspecialchars($fila['trabajoDesempena'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="talleresAsistidos-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label"><i class="fas fa-users"></i> <strong>Talleres asistidos:</strong></label>
                                    <input type="number" class="form-control" id="talleresAsistidos-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="talleresAsistidos" value="<?php echo htmlspecialchars($fila['talleresAsistidos']); ?>" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 text-start">
                                <div class="mb-3">
                                    <label for="tipoContrato-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label">
                                        <p class="subTitle text-start"><i class="fas fa-file-contract"></i> <strong>Tipo contrato:</strong><br>
                                    </label>
                                    <select class="form-select mt-2" id="tipoContrato-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="tipoContrato">
                                        <option value="">Seleccione una opción</option>
                                        <option value="Contrato a término fijo" <?php echo ($fila['tipoContrato'] == 'Contrato a término fijo') ? 'selected' : ''; ?>>Contrato a término fijo</option>
                                        <option value="Contrato a término indefinido" <?php echo ($fila['tipoContrato'] == 'Contrato a término indefinido') ? 'selected' : ''; ?>>Contrato a término indefinido</option>
                                        <option value="Contrato de obra o labor" <?php echo ($fila['tipoContrato'] == 'Contrato de obra o labor') ? 'selected' : ''; ?>>Contrato de obra o labor</option>
                                        <option value="Contrato civil por prestación de servicios" <?php echo ($fila['tipoContrato'] == 'Contrato civil por prestación de servicios') ? 'selected' : ''; ?>>Contrato civil por prestación de servicios</option>
                                        <option value="Contrato de aprendizaje" <?php echo ($fila['tipoContrato'] == 'Contrato de aprendizaje') ? 'selected' : ''; ?>>Contrato de aprendizaje</option>
                                        <option value="Contrato ocasional de trabajo" <?php echo ($fila['tipoContrato'] == 'Contrato ocasional de trabajo') ? 'selected' : ''; ?>>Contrato ocasional de trabajo</option>
                                        <option value="Contrato agropecuario" <?php echo ($fila['tipoContrato'] == 'Contrato agropecuario') ? 'selected' : ''; ?>>Contrato agropecuario</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-money-bill-wave"></i> <strong>Rango salarial:</strong></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rangoSalarial" id="rango1-<?php echo htmlspecialchars($id_encuesta_actual); ?>" value="1-3" <?php echo ($fila['rangoSalarial'] == '1-3') ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="rango1-<?php echo htmlspecialchars($id_encuesta_actual); ?>">1 a 3 SMMLV</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rangoSalarial" id="rango2-<?php echo htmlspecialchars($id_encuesta_actual); ?>" value="4-6" <?php echo ($fila['rangoSalarial'] == '4-6') ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="rango2-<?php echo htmlspecialchars($id_encuesta_actual); ?>">4 a 6 SMMLV</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rangoSalarial" id="rango3-<?php echo htmlspecialchars($id_encuesta_actual); ?>" value="7+" <?php echo ($fila['rangoSalarial'] == '7+') ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="rango3-<?php echo htmlspecialchars($id_encuesta_actual); ?>">Más de 7 SMMLV</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="hojaVida-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label mt-2"><i class="fas fa-file-alt"></i> <strong>Hoja de vida:</strong></label>
                                    <select class="form-select" id="hojaVida-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="hojaVida" required>
                                        <option value="">Seleccione una opción</option>
                                        <option value="si" <?php echo ($fila['hojaVida'] == 'si') ? 'selected' : ''; ?>>Sí</option>
                                        <option value="no" <?php echo ($fila['hojaVida'] == 'no') ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="contactoEmpleadores-<?php echo htmlspecialchars($id_encuesta_actual); ?>" class="form-label mt-2">
                                        <p class="subTitle text-start"><i class="fas fa-building"></i> <strong>Contacto empleadores:</strong><br>
                                    </label>
                                    <select class="form-select" id="contactoEmpleadores-<?php echo htmlspecialchars($id_encuesta_actual); ?>" name="contactoEmpleadores" required>
                                        <option value="">Seleccione una opción</option>
                                        <option value="si" <?php echo ($fila['contactoEmpleadores'] == 'si') ? 'selected' : ''; ?>>Sí</option>
                                        <option value="no" <?php echo ($fila['contactoEmpleadores'] == 'no') ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-update bg-indigo-dark text-white"><i class="fas fa-save"></i> Guardar cambios</button>
                            <button type="button" class="btn btn-cancel bg-gray-light" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var situacionLaboralSelect = document.getElementById('situacionLaboral-<?php echo htmlspecialchars($id_encuesta_actual); ?>');
    var tiempoDesempleoDiv = document.getElementById('tiempoDesempleoDiv-<?php echo htmlspecialchars($id_encuesta_actual); ?>');
    var trabajoDesempenaDiv = document.getElementById('trabajoDesempenaDiv-<?php echo htmlspecialchars($id_encuesta_actual); ?>');
    var form = document.getElementById('editarForm-<?php echo htmlspecialchars($id_encuesta_actual); ?>');
    var modal = document.getElementById('editarEncuestaModal-<?php echo htmlspecialchars($id_encuesta_actual); ?>'); // Obtener el modal

    function updateVisibility() {
      if (situacionLaboralSelect.value === 'desempleado') {
        tiempoDesempleoDiv.style.display = 'block';
        trabajoDesempenaDiv.style.display = 'none';
      } else if (situacionLaboralSelect.value === 'empleado') {
        tiempoDesempleoDiv.style.display = 'none';
        trabajoDesempenaDiv.style.display = 'block';
      } else {
        tiempoDesempleoDiv.style.display = 'none';
        trabajoDesempenaDiv.style.display = 'none';
      }
    }

    situacionLaboralSelect.addEventListener('change', updateVisibility);
    updateVisibility(); // Initial visibility

    // Interceptar el envío del formulario
    form.addEventListener('submit', function(event) {
      event.preventDefault(); // Evitar el envío normal del formulario

      Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Confirma que deseas guardar los cambios!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar!',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
          const container = document.querySelector('.swal2-container');
          if (container) {
            container.style.zIndex = '2000'; // o un valor mayor que 1070
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar SweetAlert de éxito ANTES de enviar el formulario
            Swal.fire({
              title: '¡Guardando!',
              text: 'Por favor, espera...',
              icon: 'success',
              showConfirmButton: false,
              timer: 1500,
              didOpen: () => {
                const container = document.querySelector('.swal2-container');
                if (container) {
                  container.style.zIndex = '2000'; // o un valor mayor que 1070
                }
              }
            }).then(() => {
              // Enviar el formulario usando AJAX
              $.ajax({
                url: 'encuestas.php', 
                type: 'POST',
                data: $(form).serialize(), // Serializar los datos del formulario
                success: function(response) {
                 
                  if (JSON.parse(response).status === 'success') {
                    Swal.fire({
                      title: '¡Éxito!',
                      text: 'La encuesta ha sido actualizada.',
                      icon: 'success',
                      didOpen: () => {
                        const container = document.querySelector('.swal2-container');
                        if (container) {
                          container.style.zIndex = '2000'; 
                        }
                      }
                    }).then(() => {
                      // Recargar la página
                      location.reload();
                    });

                  } else {
                    Swal.fire(
                      '¡Error!',
                      'Hubo un problema al actualizar la encuesta: ' + JSON.parse(response).message,
                      'icon',
                      'error'
                    );
                  }
                },
                error: function() {
                  Swal.fire(
                    '¡Error!',
                    'Hubo un problema al comunicarse con el servidor.',
                    'icon',
                    'error'
                  );
                }
              });
            });
        }
      });
    });
  });
</script>