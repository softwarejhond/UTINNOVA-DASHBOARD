<?php

// Query to fetch all cohorts
$query = "SELECT * FROM cohorts ORDER BY cohort_number";
$result = mysqli_query($conn, $query);
?>

<!-- Modal -->
<div class="modal fade" id="cohortModal" tabindex="-1" aria-labelledby="cohortModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cohortModalLabel">Información de Cohortes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Date display section -->
                <div id="dateInfo" class="mb-3" style="display: none;">
                    <div class="alert alert-info">
                        <h4 class="text-center mb-3">Cohorte <span id="cohortNumber"></span></h4>
                        <p><strong>Fecha de inicio:</strong> <span id="startDate"></span></p>
                        <p><strong>Fecha de finalización:</strong> <span id="endDate"></span></p>
                    </div>
                </div>

                <!-- Cohort selection -->
                <div class="form-group">
                    <label for="cohortSelect">Seleccione una cohorte:</label>
                    <select class="form-control" id="cohortSelect">
                        <option value="">Seleccione una cohorte</option>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <option value="<?php echo $row['cohort_number']; ?>"
                                data-start="<?php echo $row['start_date']; ?>"
                                data-end="<?php echo $row['finish_date']; ?>">
                                Cohorte <?php echo $row['cohort_number']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para obtener el cohorte seleccionado desde la base de datos
    async function getCurrentCohort() {
        try {
            const response = await fetch('components/modals/getActiveCohort.php');
            const data = await response.json();
            if (data.success && data.cohort) {
                return data.cohort.cohort_number;
            }
            return '';
        } catch (error) {
            console.error('Error obteniendo cohorte activa:', error);
            return '';
        }
    }

    document.getElementById('cohortSelect').addEventListener('change', async function() {
        const dateInfo = document.getElementById('dateInfo');
        const cohortNumber = document.getElementById('cohortNumber');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const selectElement = this;
        const selectedValue = this.value;
        const previousValue = await getCurrentCohort();
        
        // Si se ha seleccionado un valor y es diferente al actual
        if (selectedValue && selectedValue !== previousValue) {
            const selectedOption = this.options[this.selectedIndex];
            
            // Mostrar confirmación antes de cambiar
            Swal.fire({
                title: '¿Cambiar de cohorte?',
                text: `¿Estás seguro de que deseas cambiar a la Cohorte ${selectedValue}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Actualizar estado en la base de datos
                    fetch('components/modals/updateCohortState.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            cohort_number: selectedValue
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar la información mostrada
                            cohortNumber.textContent = selectedValue;
                            startDate.textContent = selectedOption.dataset.start;
                            endDate.textContent = selectedOption.dataset.end;
                            dateInfo.style.display = 'block';
                            
                            // Mostrar SweetAlert de éxito
                            Swal.fire({
                                title: '¡Cohorte cambiada!',
                                text: `Has seleccionado la Cohorte ${selectedValue}`,
                                icon: 'success',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                            
                            // Disparar evento personalizado para notificar cambio de cohorte
                            const event = new CustomEvent('cohortChanged', {
                                detail: {
                                    cohort: selectedValue
                                }
                            });
                            document.dispatchEvent(event);
                        } else {
                            Swal.fire('Error', data.message, 'error');
                            selectElement.value = previousValue || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Hubo un problema al actualizar la cohorte', 'error');
                        selectElement.value = previousValue || '';
                    });
                } else {
                    // Si el usuario cancela, restaurar el valor previo en el select
                    selectElement.value = previousValue || '';
                }
            });
        } else if (!selectedValue) {
            // Si se selecciona la opción vacía, borrar la selección actual
            fetch('components/modals/updateCohortState.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cohort_number: ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    dateInfo.style.display = 'none';
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
    
    // Cargar cohorte guardado al inicializar
    document.addEventListener('DOMContentLoaded', async function() {
        const savedCohort = await getCurrentCohort();
        if (savedCohort) {
            const cohortSelect = document.getElementById('cohortSelect');
            cohortSelect.value = savedCohort;
            
            // Actualizar la visualización sin mostrar confirmación al cargar la página
            const selectedOption = cohortSelect.options[cohortSelect.selectedIndex];
            if (selectedOption) {
                const dateInfo = document.getElementById('dateInfo');
                document.getElementById('cohortNumber').textContent = savedCohort;
                document.getElementById('startDate').textContent = selectedOption.dataset.start;
                document.getElementById('endDate').textContent = selectedOption.dataset.end;
                dateInfo.style.display = 'block';
            }
        }
    });
</script>