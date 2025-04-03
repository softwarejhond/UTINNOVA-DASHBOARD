<li class="list-group-item">
                                    <strong>Medio de contacto:</strong><br>
                                    <?php
                                    // Asigna la clase y el ícono según el valor de 'contactMedium'
                                    $btnClass = '';
                                    $btnText = htmlspecialchars($row['contactMedium']); // El texto que aparecerá en la tooltip
                                    $icon = ''; // Ícono correspondiente

                                    if ($row['contactMedium'] === 'WhatsApp') {
                                        $btnClass = 'btn bg-lime-dark text-white'; // Verde para WhatsApp
                                        $icon = '<i class="bi bi-whatsapp"></i>'; // Ícono de WhatsApp
                                    } elseif ($row['contactMedium'] === 'Teléfono') {
                                        $btnClass = 'btn bg-teal-dark text-white'; // Azul para Teléfono
                                        $icon = '<i class="bi bi-telephone"></i>'; // Ícono de Teléfono
                                    } elseif ($row['contactMedium'] === 'Correo') {
                                        $btnClass = 'btn bg-orange-light'; // Amarillo para Correo
                                        $icon = '<i class="bi bi-envelope"></i>'; // Ícono de Correo
                                    } else {
                                        $btnClass = 'btn btn-secondary'; // Clase genérica si no coincide
                                        $icon = '<i class="bi bi-question-circle"></i>'; // Ícono genérico
                                        $btnText = 'Desconocido'; // Texto genérico
                                    }

                                    // Mostrar el botón con la clase, ícono y tooltip correspondientes
                                    echo '<button type="button" class="' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $btnText . '">'
                                        . $icon .
                                        '</button>';
                                    ?>
                                </li>