<!-- Botón para subir Excel -->
<button id="btnSubirCertificados" class="btn bg-indigo-dark mb-3"><i class="bi bi-file-earmark-excel-fill me-2"></i>Subir Certificados desde Excel</button>


<!-- Tabla simple de certificados, id="listaCertificados" con bordes Bootstrap, scroll horizontal y sin saltos de texto -->
<style>
    #listaCertificados td,
    #listaCertificados th {
        white-space: nowrap;
    }
</style>
<div class="table-responsive">
    <table id="listaCertificados" class="table table-bordered table-sm mb-0">
        <thead class="table-light">
            <tr>
                <th>Número de Identificación</th>
                <th>Nombre</th>
                <th>Correo Institucional</th>
                <th>Modalidad</th>
                <th>Sede</th>
                <th>Bootcamp</th>
                <th>Link de Diploma</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta para cruzar certificates con groups
            $sql = "
            SELECT 
              c.number_id,
              g.full_name,
              g.institutional_email,
              g.mode,
              g.headquarters,
              CONCAT(g.id_bootcamp, ' - ', g.bootcamp_name) AS bootcamp,
              c.link
            FROM certificates c
            INNER JOIN `groups` g ON c.number_id = g.number_id
          ";

            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['number_id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['institutional_email']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['mode']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['headquarters']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['bootcamp']) . '</td>';
                    echo '<td><a href="' . htmlspecialchars($row['link']) . '" target="_blank">Ver diploma</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="7">No hay certificados disponibles.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Script para manejar el botón y la subida con AJAX -->
<script>
    document.getElementById('btnSubirCertificados').addEventListener('click', function() {
        Swal.fire({
            title: 'Subir Archivo Excel',
            input: 'file',
            inputAttributes: {
                'accept': '.xlsx,.xls',
                'aria-label': 'Sube tu archivo Excel'
            },
            showCancelButton: true,
            confirmButtonText: 'Subir',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes seleccionar un archivo!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const file = result.value;
                const formData = new FormData();
                formData.append('archivo_certificates', file);

                fetch('components/certificateTable/upload_certificates.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Éxito', data.message, 'success').then(() => {
                                location.reload(); // Recargar la página para actualizar la tabla
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Ocurrió un error al subir el archivo.', 'error');
                    });
            }
        });
    });
</script>