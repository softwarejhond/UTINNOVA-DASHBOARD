<li class="list-group-item">
    <?php
    // Asigna la clase y el ícono según el valor de 'contactMedium'
    $badgeClass = '';
    $badgeText = htmlspecialchars($row['contactMedium']); // El texto que aparecerá en el badge
    $icon = ''; // Ícono correspondiente

    if ($row['contactMedium'] === 'WhatsApp') {
        $badgeClass = 'badge bg-success'; // Verde para WhatsApp
        $icon = '<i class="bi bi-whatsapp me-1"></i>'; // Ícono de WhatsApp
    } elseif ($row['contactMedium'] === 'Teléfono') {
        $badgeClass = 'badge bg-info'; // Azul para Teléfono
        $icon = '<i class="bi bi-telephone me-1"></i>'; // Ícono de Teléfono
    } elseif ($row['contactMedium'] === 'Correo') {
        $badgeClass = 'badge bg-warning'; // Amarillo para Correo
        $icon = '<i class="bi bi-envelope me-1"></i>'; // Ícono de Correo
    } else {
        $badgeClass = 'badge bg-secondary'; // Clase genérica si no coincide
        $icon = '<i class="bi bi-question-circle me-1"></i>'; // Ícono genérico
        $badgeText = 'Desconocido'; // Texto genérico
    }

    // Mostrar el badge con la clase, ícono y texto correspondientes
    echo '<span class="' . $badgeClass . '">'
        . $icon . $badgeText .
        '</span>';
    ?>
</li>