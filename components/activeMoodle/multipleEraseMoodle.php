<?php
// Obtener el rol del usuario
$rol = $infoUsuario['rol'];

// Consulta SQL para obtener usuarios matriculados
$sql = "SELECT g.* FROM groups g ";
$result = $conn->query($sql);
$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
?>

<div class="container-fluid px-2">
    <div class="row mb-3">
        <div class="col-12">
            <button class="btn bg-magenta-dark text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#selectedUsersList">
                <i class="bi bi-exclamation-diamond-fill"></i> Ver Seleccionados (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <th class="text-center">
                        <i class="bi bi-check2-circle"></i>
                    </th>
                    <th>Tipo ID</th>
                    <th>Número ID</th>
                    <th>Nombre completo</th>
                    <th>Correo institucional</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Bootcamp</th>
                    <th>Inglés Nivelatorio</th>
                    <th>English Code</th>
                    <th>Habilidades</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr data-user='<?php echo json_encode([
                        "number_id" => $row['number_id'],
                        "full_name" => $row['full_name'],
                        "institutional_email" => $row['institutional_email']
                    ]); ?>'>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input usuario-checkbox"
                                   style="width: 25px; height: 25px; 
                                          appearance: none; 
                                          background-color: white; 
                                          border: 2px solid #ec008c; 
                                          cursor: pointer; 
                                          position: relative;"
                                   onclick="this.style.backgroundColor = this.checked ? '#ec008c' : 'white'">
                        </td>
                        <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['institutional_email']); ?></td>
                        <td>
                            <?php
                            $departamento = htmlspecialchars($row['department']);
                            $btnClass = $departamento === 'CUNDINAMARCA' ? 'bg-lime-light' : 
                                     ($departamento === 'BOYACÁ' ? 'bg-indigo-light' : '');
                            echo "<button class='btn $btnClass w-100'><b>{$departamento}</b></button>";
                            ?>
                        </td>
                        <td><b><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['bootcamp_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['leveling_english_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['english_code_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['skills_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de confirmación para desmatriculación múltiple -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="confirmDeleteModalLabel">⚠️ Confirmación de Desmatriculación Masiva</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <div class="alert alert-danger">
                <strong>¡ADVERTENCIA!</strong><br>
                Está a punto de desmatricular a <span id="selectedUsersCount">0</span> usuarios
              </div>
              
              <div class="card shadow-lg p-3 mb-3">
                <p class="mb-1">Para confirmar, ingrese el siguiente código de seguridad:</p>
                <div class="input-group mb-3">
                  <input type="text" id="securityCodeDisplay" class="form-control text-center" 
                         readonly 
                         style="font-family: monospace; letter-spacing: 3px; font-weight: bold;">
                  <button class="btn btn-outline-secondary" type="button" id="copyCodeBtn">
                    <i class="bi bi-clipboard"></i>
                  </button>
                </div>
                <input type="text" 
                       id="securityCodeInput" 
                       class="form-control text-center" 
                       placeholder="Ingrese el código aquí"
                       style="font-family: monospace; letter-spacing: 2px;">
              </div>
              
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Esta acción es irreversible
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" id="confirmDesmatriculacionBtn">Confirmar Desmatriculación</button>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- Panel lateral para usuarios seleccionados -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="selectedUsersList" aria-labelledby="selectedUsersListLabel">
    <div class="offcanvas-header bg-magenta-dark text-white">
        <h5 class="offcanvas-title" id="selectedUsersListLabel">
            <i class="bi bi-person-check"></i> Beneficiarios seleccionados (<span id="offcanvasSelectedCount">0</span>)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="m-3">
            <button id="btnDesmatricularSeleccionados" class="btn btn-danger w-100">
                <i class="bi bi-trash"></i> Desmatricular Seleccionados
            </button>
        </div>
        <div id="selectedUsersContainer">
            <!-- Aquí se mostrarán las tarjetas de usuarios seleccionados -->
        </div>
    </div>
</div>

<script>
const selectedUsers = new Map();

function updateSelectedCount() {
    const count = selectedUsers.size;
    const countElements = document.querySelectorAll('#selectedCount, #offcanvasSelectedCount');
    countElements.forEach(el => el.textContent = count);
    
    // Actualizar estado del botón de desmatriculación
    const btnDesmatricularSeleccionados = document.getElementById('btnDesmatricularSeleccionados');
    if (btnDesmatricularSeleccionados) {
        btnDesmatricularSeleccionados.disabled = count === 0;
    }
}

function updateSelectedUsersList() {
    const container = document.getElementById('selectedUsersContainer');
    if (!container) return;

    container.innerHTML = '';
    selectedUsers.forEach((userData, numberId) => {
        const card = document.createElement('div');
        card.className = 'card mb-2';
        card.innerHTML = `
            <div class="card-body">
                <h6 class="card-title text-center mb-2"><b>${userData.full_name}</b></h6>
                <div class="text-center">
                    <small class="d-block text-muted mb-1">ID: ${numberId}</small>
                    <small class="d-block text-muted mb-2">${userData.institutional_email}</small>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        <span>En espera para desmatricular</span>
                    </div>
                    <button class="btn btn-outline-danger btn-sm w-100 remove-selection" data-id="${numberId}">
                        <i class="bi bi-x-circle"></i> Eliminar selección
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });

    // Agregar eventos a los botones de eliminar selección
    container.querySelectorAll('.remove-selection').forEach(btn => {
        btn.addEventListener('click', function() {
            const numberId = this.dataset.id;
            removeSelectedUser(numberId);
        });
    });

    updateSelectedCount();
}

function removeSelectedUser(numberId) {
    selectedUsers.delete(numberId);
    const checkbox = document.querySelector(`tr[data-user*='"number_id":"${numberId}"'] .usuario-checkbox`);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.style.backgroundColor = 'white';
    }
    updateSelectedUsersList();
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('usuario-checkbox')) {
        const row = e.target.closest('tr');
        if (!row) return;

        const userData = JSON.parse(row.dataset.user);
        if (e.target.checked) {
            selectedUsers.set(userData.number_id, userData);
        } else {
            selectedUsers.delete(userData.number_id);
        }
        
        e.target.style.backgroundColor = e.target.checked ? '#ec008c' : 'white';
        updateSelectedUsersList();
    }
});

// Reemplazar el evento del botón btnDesmatricularSeleccionados y todo lo relacionado con la confirmación
let securityCode = '';

document.getElementById('btnDesmatricularSeleccionados').addEventListener('click', function() {
    if (selectedUsers.size === 0) {
        Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
        return;
    }
    
    // Generar código de seguridad
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
    securityCode = '';
    for (let i = 0; i < 8; i++) {
        securityCode += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    // Actualizar el modal con los datos
    document.getElementById('selectedUsersCount').textContent = selectedUsers.size;
    document.getElementById('securityCodeDisplay').value = securityCode;
    
    // Limpiar el campo de entrada
    document.getElementById('securityCodeInput').value = '';
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
    
    // Enfocar el campo de entrada después de un breve retraso
    setTimeout(() => {
        document.getElementById('securityCodeInput').focus();
    }, 500);
});

// Evento para el botón de copiar código
document.getElementById('copyCodeBtn').addEventListener('click', function() {
    const codeDisplay = document.getElementById('securityCodeDisplay');
    const codeInput = document.getElementById('securityCodeInput');
    
    navigator.clipboard.writeText(codeDisplay.value).then(() => {
        this.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(() => {
            this.innerHTML = '<i class="bi bi-clipboard"></i>';
        }, 1500);
        
        codeInput.focus();
    });
});

// Evento para el botón de confirmación
document.getElementById('confirmDesmatriculacionBtn').addEventListener('click', function() {
    const inputCode = document.getElementById('securityCodeInput').value;
    
    if (!inputCode) {
        // Mostrar error en el campo
        const codeInput = document.getElementById('securityCodeInput');
        codeInput.classList.add('is-invalid');
        
        // Agregar mensaje de error si no existe
        let errorMsg = document.getElementById('codeErrorMsg');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.id = 'codeErrorMsg';
            errorMsg.className = 'invalid-feedback';
            errorMsg.textContent = 'Debe ingresar el código de seguridad';
            codeInput.parentNode.appendChild(errorMsg);
        }
        return;
    }
    
    if (inputCode !== securityCode) {
        // Mostrar error en el campo
        const codeInput = document.getElementById('securityCodeInput');
        codeInput.classList.add('is-invalid');
        
        // Agregar mensaje de error si no existe
        let errorMsg = document.getElementById('codeErrorMsg');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.id = 'codeErrorMsg';
            errorMsg.className = 'invalid-feedback';
            errorMsg.textContent = 'El código ingresado no coincide';
            codeInput.parentNode.appendChild(errorMsg);
        } else {
            errorMsg.textContent = 'El código ingresado no coincide';
        }
        return;
    }
    
    // Ocultar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
    modal.hide();
    
    // Procesar la desmatriculación
    processMultipleDelete();
});

// Limpiar errores cuando se escribe en el campo de confirmación
document.getElementById('securityCodeInput').addEventListener('input', function() {
    this.classList.remove('is-invalid');
    
    const errorMsg = document.getElementById('codeErrorMsg');
    if (errorMsg) {
        errorMsg.remove();
    }
});

async function processMultipleDelete() {
    const totalUsers = selectedUsers.size;
    let processed = 0;
    let successful = 0;
    let errors = [];

    // Mostrar loader con progreso
    Swal.fire({
        title: 'Procesando eliminaciones',
        html: `Progreso: 0/${totalUsers}`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    for (const [numberId, userData] of selectedUsers) {
        try {
            const response = await fetch('components/activeMoodle/deleteMatricula.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    number_id: numberId,
                    isMultiple: true  // Agregar este flag
                })
            });

            const result = await response.json();
            processed++;
            
            if (result.success) {
                successful++;
            } else {
                errors.push(`Error con ${userData.full_name}: ${result.message}`);
            }

            // Actualizar progreso
            Swal.update({
                html: `Progreso: ${processed}/${totalUsers}<br>
                       Exitosos: ${successful}<br>
                       Errores: ${errors.length}`
            });

        } catch (error) {
            errors.push(`Error con ${userData.full_name}: ${error.message}`);
            processed++;
        }
    }

    // Mostrar resultado final
    let resultMessage = `Proceso completado:<br>
        Total procesados: ${processed}<br>
        Exitosos: ${successful}<br>
        Errores: ${errors.length}`;
    
    if (errors.length > 0) {
        resultMessage += '<br><br>Errores encontrados:<br>' + errors.join('<br>');
    }

    await Swal.fire({
        title: 'Proceso completado',
        html: resultMessage,
        icon: errors.length === 0 ? 'success' : 'warning',
        confirmButtonText: 'Aceptar'
    });

    // Recargar la página
    window.location.reload();
}

// Alerta de advertencia al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: '⚠️ ¡ADVERTENCIA IMPORTANTE! ⚠️',
        html: `
            <div class="text-center">
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-2"></i>
                    <strong class="fs-5">ZONA DE ALTO RIESGO</strong>
                </div>
                <p class="mb-3">Está a punto de acceder a una sección donde podrá realizar eliminaciones masivas de matrículas en la plataforma.</p>
                <div class="alert alert-warning">
                    <strong>Por favor, tenga en cuenta:</strong>
                    <ul class="text-start mt-2 mb-0">
                        <li>Esta acción es <strong class="text-danger">COMPLETAMENTE IRREVERSIBLE</strong></li>
                        <li>Verifique <strong>CUIDADOSAMENTE</strong> cada selección</li>
                        <li>Se requiere <strong>MÁXIMA ATENCIÓN</strong> en este proceso</li>
                    </ul>
                </div>
            </div>`,
        icon: 'warning',
        confirmButtonText: 'Entiendo los riesgos',
        confirmButtonColor: '#dc3545',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal2-warning-custom',
            title: 'fs-4 text-danger',
            htmlContainer: 'text-center'
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            window.location.href = 'main.php'; // Redirigir si cancela
        }
    });
});

// Agregar estilos personalizados para la alerta
const style = document.createElement('style');
style.textContent = `
    .swal2-warning-custom {
        border: 3px solid #dc3545 !important;
        border-radius: 10px !important;
    }
    .swal2-warning-custom .swal2-title {
        color: #dc3545 !important;
        font-weight: bold !important;
    }
    .swal2-warning-custom .swal2-icon {
        border-color: #dc3545 !important;
        color: #dc3545 !important;
    }
`;
document.head.appendChild(style);
</script>