<style>
    /* Personalizar el color del check */
    input[type="checkbox"]:checked::before {
        content: "✓";
        color: #007a7a !important;
        font-size: 1em;
        font-weight: bold;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        line-height: 1;
    }

    input[type="checkbox"] {
        position: relative;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        border: 2px solid #000;
        border-radius: 3px;
        background-color: #d0f7f9;
        cursor: pointer;
    }

    input[type="checkbox"]:checked {
        background-color: #d0f7f9;
        border-color: #007a7a;
    }
</style>

<li class="list-group-item">
    <!-- <span class="badge bg-primary" style="cursor: pointer;" onclick="showDocumentSwal('<?php echo htmlspecialchars($row['file_front_id']); ?>', '<?php echo htmlspecialchars($row['file_back_id']); ?>', <?php echo $row['number_id']; ?>)">
        <i class="bi bi-card-image me-1"></i> Ver documento
    </span> -->

    <!-- NUEVO: Botón para verificación de documento -->
    <?php
    $verificacion = [
        'name_verified' => 0,
        'document_number_verified' => 0,
        'birth_date_verified' => 0,
        'document_type_verified' => 0,
        'notes' => ''
    ];
    $stmt = $conn->prepare("SELECT name_verified, document_number_verified, birth_date_verified, document_type_verified, notes FROM document_verifications WHERE number_id = ? ORDER BY verification_date DESC LIMIT 1");
    $stmt->bind_param("s", $row['number_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $verificacion = $result->fetch_assoc();
    }
    $stmt->close();
    ?>
    <span class="badge bg-indigo-dark ms-2" style="cursor: pointer;"
        onclick="showDocumentVerificationSwal(
            '<?php echo htmlspecialchars($row['file_front_id']); ?>',
            '<?php echo htmlspecialchars($row['file_back_id']); ?>',
            <?php echo $row['number_id']; ?>,
            '<?php echo htmlspecialchars($row['first_name']); ?>',
            '<?php echo htmlspecialchars($row['second_name']); ?>',
            '<?php echo htmlspecialchars($row['first_last']); ?>',
            '<?php echo htmlspecialchars($row['second_last']); ?>',
            '<?php echo htmlspecialchars($row['number_id']); ?>',
            '<?php echo htmlspecialchars($row['birthdate']); ?>',
            '<?php echo htmlspecialchars($row['typeID']); ?>',
            <?php echo $verificacion['name_verified']; ?>,
            <?php echo $verificacion['document_number_verified']; ?>,
            <?php echo $verificacion['birth_date_verified']; ?>,
            <?php echo $verificacion['document_type_verified']; ?>,
            '<?php echo htmlspecialchars($verificacion['notes']); ?>'
        )">
        <i class="bi bi-shield-check me-1"></i> Verificar
    </span>

    <?php
    $pdfPath = "cedulas/cedula_{$row['number_id']}.pdf";
    $isProduction = (strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
    $baseDir = $isProduction ? '/dashboard/' : '/UTINNOVA-DASHBOARD/';
    $cacheBuster = time();
    $pdfPathWithCache = $baseDir . $pdfPath . '?cache=' . $cacheBuster;

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $baseDir . $pdfPath)) {
    ?>
        <span class="badge bg-success ms-2" style="cursor: pointer;" onclick="event.preventDefault(); showPdfModal('<?php echo $pdfPathWithCache; ?>')">
            <i class="bi bi-file-earmark-pdf"></i> Ver PDF
        </span>
        <span class="badge bg-danger ms-2" style="cursor: pointer;" onclick="event.preventDefault(); eliminarCedulaPDF('<?php echo $row['number_id']; ?>', '<?php echo $pdfPath; ?>')">
            <i class="bi bi-trash"></i> Eliminar PDF
        </span>
    <?php
    }
    ?>

    <script>
        // Asegurarse de que la variable existe en el ámbito global
        if (typeof window.imageTransforms === 'undefined') {
            window.imageTransforms = {};
        }

        // Variables para el arrastre
        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let currentImageId = null;

        function showDocumentSwal(frontImage, backImage, numberId) {
            Swal.fire({
                title: 'Imágenes de Identificación',
                width: 900,
                showCloseButton: true,
                showConfirmButton: false,
                html: `
                    <div class="row">
                        <!-- Frente del documento -->
                        <div class="col-12 mb-4 text-center">
                            <h6>Frente del documento</h6>
                            <div class="position-relative" style="overflow: hidden;">
                                <img id="idImageFront_${numberId}"
                                    src="../files/idFilesFront/${frontImage}"
                                    class="img-fluid"
                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                    alt="Frente ID">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageFront_${numberId}', -90)">
                                    ↺ Rotar Izquierda
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="toggleZoom('idImageFront_${numberId}')">
                                    <i class="bi bi-zoom-in"></i> Zoom
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageFront_${numberId}', 90)">
                                    ↻ Rotar Derecha
                                </button>
                            </div>
                        </div>

                        <!-- Reverso del documento -->
                        <div class="col-12 text-center">
                            <h6>Reverso del documento</h6>
                            <div class="position-relative" style="overflow: hidden;">
                                <img id="idImageBack_${numberId}"
                                    src="../files/idFilesBack/${backImage}"
                                    class="img-fluid"
                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                    alt="Reverso ID">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageBack_${numberId}', -90)">
                                    ↺ Rotar Izquierda
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="toggleZoom('idImageBack_${numberId}')">
                                    <i class="bi bi-zoom-in"></i> Zoom
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageBack_${numberId}', 90)">
                                    ↻ Rotar Derecha
                                </button>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="button" class="btn btn-success" onclick="guardarCedulaPDF(${numberId})">
                                <i class="bi bi-file-earmark-pdf"></i> Guardar PDF
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    // Reiniciar transformaciones
                    window.imageTransforms[`idImageFront_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };
                    window.imageTransforms[`idImageBack_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };

                    // Agregar eventos de arrastre
                    const frontImg = document.getElementById(`idImageFront_${numberId}`);
                    const backImg = document.getElementById(`idImageBack_${numberId}`);

                    if (frontImg) {
                        setupDragEvents(frontImg);
                    }

                    if (backImg) {
                        setupDragEvents(backImg);
                    }

                    // Agregar lupa a las imágenes
                    setTimeout(() => {
                        addMagnifierToImage(`idImageFront_${numberId}`);
                        addMagnifierToImage(`idImageBack_${numberId}`);
                    }, 100);
                }
            });
        }

        function setupDragEvents(imgElement) {
            imgElement.addEventListener('mousedown', startDrag);
            imgElement.addEventListener('touchstart', startDrag, {
                passive: false
            });

            // Evitar que la imagen sea arrastrable en navegador por defecto
            imgElement.addEventListener('dragstart', e => e.preventDefault());
        }

        function startDrag(e) {
            e.preventDefault();
            const imageId = e.target.id;

            // Solo permitir arrastrar si está en zoom
            if (!window.imageTransforms[imageId] || window.imageTransforms[imageId].scale <= 1) {
                return;
            }

            currentImageId = imageId;
            isDragging = true;

            // Obtener posición inicial
            if (e.type === 'mousedown') {
                dragStartX = e.clientX;
                dragStartY = e.clientY;
            } else {
                dragStartX = e.touches[0].clientX;
                dragStartY = e.touches[0].clientY;
            }

            // Agregar eventos temporales de documento para rastrear el movimiento
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchmove', drag, {
                passive: false
            });
            document.addEventListener('touchend', endDrag);
        }

        function drag(e) {
            if (!isDragging || !currentImageId) return;
            e.preventDefault();

            let clientX, clientY;

            if (e.type === 'mousemove') {
                clientX = e.clientX;
                clientY = e.clientY;
            } else {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }

            const deltaX = clientX - dragStartX;
            const deltaY = clientY - dragStartY;

            // Actualizar posición
            const transform = window.imageTransforms[currentImageId];
            transform.offsetX += deltaX / transform.scale;
            transform.offsetY += deltaY / transform.scale;

            // Aplicar límites (opcional)
            const maxOffset = 100; // Ajustar según necesidades
            transform.offsetX = Math.min(Math.max(transform.offsetX, -maxOffset), maxOffset);
            transform.offsetY = Math.min(Math.max(transform.offsetY, -maxOffset), maxOffset);

            // Actualizar posición inicial para el próximo movimiento
            dragStartX = clientX;
            dragStartY = clientY;

            // Aplicar transformación
            applyTransform(currentImageId);
        }

        function endDrag() {
            isDragging = false;
            currentImageId = null;

            // Eliminar eventos temporales
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', endDrag);
            document.removeEventListener('touchmove', drag);
            document.removeEventListener('touchend', endDrag);
        }

        function rotateImage(imageId, degrees) {
            if (!window.imageTransforms[imageId]) {
                window.imageTransforms[imageId] = {
                    rotation: 0,
                    scale: 1,
                    offsetX: 0,
                    offsetY: 0
                };
            }
            window.imageTransforms[imageId].rotation += degrees;
            applyTransform(imageId);
        }

        function toggleZoom(imageId) {
            if (!window.imageTransforms[imageId]) {
                window.imageTransforms[imageId] = {
                    rotation: 0,
                    scale: 1,
                    offsetX: 0,
                    offsetY: 0
                };
            }

            // Si estamos saliendo del zoom, resetear la posición
            if (window.imageTransforms[imageId].scale > 1) {
                window.imageTransforms[imageId].offsetX = 0;
                window.imageTransforms[imageId].offsetY = 0;
            }

            window.imageTransforms[imageId].scale = window.imageTransforms[imageId].scale === 1 ? 2 : 1;

            // Cambiar el cursor según el estado del zoom
            const imgElement = document.getElementById(imageId);
            if (imgElement) {
                imgElement.style.cursor = window.imageTransforms[imageId].scale > 1 ? 'move' : 'default';
            }

            applyTransform(imageId);
        }

        function ajustarContenedorImagen(imageId) {
            const img = document.getElementById(imageId);
            if (!img) return;
            const contenedor = img.parentElement;
            const transform = window.imageTransforms[imageId];
            if (!transform || !contenedor) return;

            // Alto fijo, ancho relativo
            contenedor.style.height = '400px';
            contenedor.style.width = '100%';

            // Si la imagen está rotada 90° o 270°, ajusta el max-height y max-width de la imagen
            const rot = Math.abs(transform.rotation % 360);
            if (rot === 90 || rot === 270) {
                img.style.maxWidth = '400px';
                img.style.maxHeight = '100%';
            } else {
                img.style.maxWidth = '100%';
                img.style.maxHeight = '400px';
            }
        }

        // Modifica applyTransform para llamar a ajustarContenedorImagen:
        function applyTransform(imageId) {
            let imgElement = document.getElementById(imageId);
            if (imgElement) {
                let {
                    rotation,
                    scale,
                    offsetX,
                    offsetY
                } = window.imageTransforms[imageId];
                requestAnimationFrame(() => {
                    imgElement.style.transform = `rotate(${rotation}deg) scale(${scale}) translate(${offsetX}px, ${offsetY}px)`;
                    ajustarContenedorImagen(imageId); // <-- Agregado aquí
                });
            }
        }

        function guardarCedulaPDF(numberId) {
            const frontId = `idImageFront_${numberId}`;
            const backId = `idImageBack_${numberId}`;
            const frontTransform = window.imageTransforms[frontId];
            const backTransform = window.imageTransforms[backId];
            const frontImg = document.getElementById(frontId);
            const backImg = document.getElementById(backId);

            // Validar que las transformaciones existan
            if (!frontTransform || !backTransform) {
                Swal.fire('Error', 'Error en las transformaciones de imagen.', 'error');
                return;
            }

            // Obtener las imágenes ya transformadas como base64
            const frontImageBase64 = getTransformedImageBase64(frontImg, frontTransform);
            const backImageBase64 = getTransformedImageBase64(backImg, backTransform);

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Generando PDF...',
                text: 'Por favor espere mientras se procesa el documento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('components/cedulas/export_cedula.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        number_id: numberId,
                        front_image: frontImageBase64,
                        back_image: backImageBase64,
                        // Ya no es necesario enviar las transformaciones
                        // front_transform: JSON.stringify(frontTransform),
                        // back_transform: JSON.stringify(backTransform)
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (jsonError) {
                        console.error('Respuesta no es JSON válido:', text);
                        throw new Error('La respuesta del servidor no es JSON válido. Verifica los logs del servidor.');
                    }

                    if (data.success) {
                        Swal.fire({
                            title: '¡PDF generado!',
                            text: data.message || 'El documento ha sido guardado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Error desconocido al generar el PDF');
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function showPdfModal(pdfPath) {
            // Agregar timestamp adicional para forzar recarga
            const cacheBuster = new Date().getTime();
            const separator = pdfPath.includes('?') ? '&' : '?';
            const pdfUrlWithCache = `${pdfPath}${separator}cache=${cacheBuster}`;

            Swal.fire({
                title: 'Documento PDF generado',
                html: `
                    <div style="width: 100%; height: 80vh; position: relative;">
                        <iframe 
                            src="${pdfUrlWithCache}" 
                            style="width:100%;height:100%;" 
                            frameborder="0"
                            onload="this.style.display='block'"
                            onerror="handlePdfError(this)"
                        ></iframe>
                        <div id="pdf-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando PDF...</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="${pdfUrlWithCache}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-download"></i> Descargar PDF
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="reloadPdf('${pdfPath}')">
                            <i class="bi bi-arrow-clockwise"></i> Recargar
                        </button>
                    </div>
                `,
                width: '90%',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-pdf-modal'
                },
                didOpen: () => {
                    // Ocultar loading después de 3 segundos
                    setTimeout(() => {
                        const loading = document.getElementById('pdf-loading');
                        if (loading) loading.style.display = 'none';
                    }, 3000);
                }
            });
        }

        function reloadPdf(originalPdfPath) {
            const cacheBuster = new Date().getTime();
            const separator = originalPdfPath.includes('?') ? '&' : '?';
            const newPdfUrl = `${originalPdfPath}${separator}reload=${cacheBuster}`;

            const iframe = document.querySelector('.swal2-popup iframe');
            if (iframe) {
                iframe.src = newPdfUrl;
            }
        }

        function handlePdfError(iframe) {
            iframe.style.display = 'none';
            const container = iframe.parentElement;
            container.innerHTML = `
                <div class="alert alert-warning text-center">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error al cargar PDF</h5>
                    <p>El PDF no se pudo cargar. Esto puede deberse a problemas de caché del navegador.</p>
                    <button class="btn btn-primary" onclick="clearCacheAndReload('${iframe.src}')">
                        <i class="bi bi-arrow-clockwise"></i> Intentar de nuevo
                    </button>
                </div>
            `;
        }

        function clearCacheAndReload(pdfUrl) {
            // Forzar limpieza de caché del navegador
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    names.forEach(function(name) {
                        caches.delete(name);
                    });
                });
            }

            // Recargar con nuevo timestamp
            const cacheBuster = new Date().getTime();
            const newUrl = pdfUrl.split('?')[0] + '?nocache=' + cacheBuster;
            window.open(newUrl, '_blank');
        }

        function guardarCedulaPDF(numberId) {
            // Usar los IDs del modal de verificación
            const frontId = `verifyImageFront_${numberId}`;
            const backId = `verifyImageBack_${numberId}`;
            const frontTransform = window.imageTransforms[frontId];
            const backTransform = window.imageTransforms[backId];
            const frontImg = document.getElementById(frontId);
            const backImg = document.getElementById(backId);

            // Validar que las transformaciones existan
            if (!frontTransform || !backTransform) {
                Swal.fire('Error', 'Error en las transformaciones de imagen.', 'error');
                return;
            }

            // Obtener las imágenes ya transformadas como base64
            const frontImageBase64 = getTransformedImageBase64(frontImg, frontTransform);
            const backImageBase64 = getTransformedImageBase64(backImg, backTransform);

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Generando PDF...',
                text: 'Por favor espere mientras se procesa el documento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('components/cedulas/export_cedula.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache',
                        'Expires': '0'
                    },
                    body: new URLSearchParams({
                        number_id: numberId,
                        front_image: frontImageBase64,
                        back_image: backImageBase64,
                        timestamp: new Date().getTime()
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (jsonError) {
                        console.error('Respuesta no es JSON válido:', text);
                        throw new Error('La respuesta del servidor no es JSON válido. Verifica los logs del servidor.');
                    }

                    if (data.success) {
                        // Limpiar caché antes de mostrar el resultado
                        if ('caches' in window) {
                            caches.keys().then(function(names) {
                                names.forEach(function(name) {
                                    caches.delete(name);
                                });
                            });
                        }

                        Swal.fire({
                            title: '¡PDF generado!',
                            text: data.message || 'El documento ha sido guardado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Error desconocido al generar el PDF');
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function getTransformedImageBase64(imgElement, transform) {
            const {
                rotation = 0, scale = 1, offsetX = 0, offsetY = 0
            } = transform;
            const radians = rotation * Math.PI / 180;

            // Calcula el tamaño del canvas considerando la rotación
            let width = imgElement.naturalWidth * scale;
            let height = imgElement.naturalHeight * scale;

            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');

            // Si la rotación es 90 o 270 grados, intercambia ancho y alto
            if (Math.abs(rotation) % 180 === 90) {
                canvas.width = height;
                canvas.height = width;
            } else {
                canvas.width = width;
                canvas.height = height;
            }

            // Mueve el origen al centro y aplica transformaciones
            ctx.save();
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate(radians);
            ctx.scale(scale, scale);
            ctx.drawImage(
                imgElement,
                -imgElement.naturalWidth / 2 + offsetX,
                -imgElement.naturalHeight / 2 + offsetY
            );
            ctx.restore();

            return canvas.toDataURL('image/jpeg', 0.95);
        }

        // Función para eliminar PDF - AGREGAR ESTA FUNCIÓN
        function eliminarCedulaPDF(numberId, pdfPath) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará permanentemente el archivo PDF.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar indicador de carga
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor espere...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('components/cedulas/delete_cedula.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Cache-Control': 'no-cache, no-store, must-revalidate',
                                'Pragma': 'no-cache',
                                'Expires': '0'
                            },
                            body: new URLSearchParams({
                                number_id: numberId,
                                pdf_path: pdfPath,
                                timestamp: new Date().getTime()
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.text();
                        })
                        .then(text => {
                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch (jsonError) {
                                console.error('Respuesta no es JSON válido:', text);
                                throw new Error('La respuesta del servidor no es JSON válido.');
                            }

                            if (data.success) {
                                Swal.fire({
                                    title: '¡Eliminado!',
                                    text: data.message || 'El PDF ha sido eliminado correctamente.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Recargar la página para actualizar la interfaz
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.error || 'Error desconocido al eliminar el PDF');
                            }
                        })
                        .catch(error => {
                            console.error('Error al eliminar PDF:', error);
                            Swal.fire({
                                title: 'Error',
                                text: error.message || 'No se pudo eliminar el PDF. Por favor, inténtalo de nuevo.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        }

        // NUEVA FUNCIÓN: Mostrar modal de verificación de documento
        function showDocumentVerificationSwal(frontImage, backImage, numberId, firstName, secondName, firstLast, secondLast, documentNumber, birthDate, documentType, nameVerified, docVerified, birthVerified, typeVerified, lastObservation) {
            Swal.fire({
                title: 'Verificación de Documento de Identidad',
                width: 1200,
                showCloseButton: true,
                showConfirmButton: false,
                html: `
                    <div class="row">
                        <!-- Columna izquierda: Imágenes del documento -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-indigo-dark text-white">
                                    <h6 class="mb-0"><i class="bi bi-images"></i> Documento de Identidad</h6>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                    <!-- Frente del documento -->
                                    <div class="mb-4 w-100 d-flex flex-column justify-content-center align-items-center">
                                        <h6 class="text-center">Frente del documento</h6>
                                        <div class="position-relative d-flex justify-content-center align-items-center" style="overflow: hidden;">
                                            <img id="verifyImageFront_${numberId}"
                                                src="../files/idFilesFront/${frontImage}"
                                                class="img-fluid border rounded"
                                                style="max-height: 250px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                                alt="Frente ID">
                                        </div>
                                        <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageFront_${numberId}', -90)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm bg-magenta-dark text-white" onclick="toggleZoom('verifyImageFront_${numberId}')">
                                                <i class="bi bi-zoom-in"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageFront_${numberId}', 90)">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Reverso del documento -->
                                    <div class="w-100 d-flex flex-column justify-content-center align-items-center">
                                        <h6 class="text-center">Reverso del documento</h6>
                                        <div class="position-relative d-flex justify-content-center align-items-center" style="overflow: hidden;">
                                            <img id="verifyImageBack_${numberId}"
                                                src="../files/idFilesBack/${backImage}"
                                                class="img-fluid border rounded"
                                                style="max-height: 250px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                                alt="Reverso ID">
                                        </div>
                                        <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageBack_${numberId}', -90)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm bg-magenta-dark text-white" onclick="toggleZoom('verifyImageBack_${numberId}')">
                                                <i class="bi bi-zoom-in"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageBack_${numberId}', 90)">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>

                                        <div class="col-12 text-center mt-4">
                                            <button type="button" class="btn btn-success btn-lg" onclick="guardarCedulaPDF(${numberId})">
                                                <i class="bi bi-file-earmark-pdf"></i> Guardar PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha: Formulario de verificación -->
                        <div class="col-md-6">
                            <div class="card h-100 d-flex justify-content-center align-items-center">
                                <div class="card-header bg-orange-dark text-white w-100">
                                    <h6 class="mb-0"><i class="bi bi-shield-check"></i> Formulario de Verificación</h6>
                                </div>
                                <div class="card-body">
                                    <form id="formVerificacionDocumento_${numberId}">
                                        <!-- Nombres -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nombres y Apellidos:</label>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Primer Nombre *</label>
                                                    <input type="text" class="form-control form-control-sm" name="primerNombre" value="${firstName}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Segundo Nombre</label>
                                                    <input type="text" class="form-control form-control-sm" name="segundoNombre" value="${secondName}">
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Primer Apellido *</label>
                                                    <input type="text" class="form-control form-control-sm" name="primerApellido" value="${firstLast}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Segundo Apellido</label>
                                                    <input type="text" class="form-control form-control-sm" name="segundoApellido" value="${secondLast}">
                                                </div>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="nombreCoincide_${numberId}" name="nombreCoincide"
                                                    style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                    ${nameVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="nombreCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> El nombre completo coincide con el documento
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Número de documento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Número de Documento:</label>
                                            <input type="text" class="form-control" name="numeroDocumento" value="${documentNumber}" required>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="documentoCoincide_${numberId}" name="documentoCoincide"
                                                style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                ${docVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="documentoCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> El número de documento coincide
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Fecha de nacimiento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Fecha de Nacimiento:</label>
                                            <input type="date" class="form-control" name="fechaNacimiento" value="${birthDate}" required>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="fechaCoincide_${numberId}" name="fechaCoincide"
                                                style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                ${birthVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="fechaCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> La fecha de nacimiento coincide
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Tipo de documento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tipo de Documento:</label>
                                            <select class="form-select" name="tipoDocumento" required>
                                                <option value="CC" ${documentType === 'CC' ? 'selected' : ''}>Cédula de Ciudadanía (CC)</option>
                                                <option value="Otra" ${documentType === 'Otra' ? 'selected' : ''}>Otra</option>
                                            </select>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="tipoCoincide_${numberId}" name="tipoCoincide"
                                                style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                ${typeVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="tipoCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> El tipo de documento coincide
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Observaciones -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Observaciones (opcional):</label>
                                            <textarea class="form-control" name="observaciones" rows="3" placeholder="Agregue cualquier observación relevante sobre la verificación...">${lastObservation || ''}</textarea>
                                        </div>

                                        <input type="hidden" name="numberId" value="${numberId}">
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bi bi-shield-check"></i> Confirmar Verificación
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    // Reiniciar transformaciones para las imágenes de verificación
                    window.imageTransforms[`verifyImageFront_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };
                    window.imageTransforms[`verifyImageBack_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };

                    // Agregar eventos de arrastre
                    const frontImg = document.getElementById(`verifyImageFront_${numberId}`);
                    const backImg = document.getElementById(`verifyImageBack_${numberId}`);

                    if (frontImg) {
                        setupDragEvents(frontImg);
                    }

                    if (backImg) {
                        setupDragEvents(backImg);
                    }

                    // Manejar el envío del formulario de verificación
                    document.getElementById(`formVerificacionDocumento_${numberId}`).addEventListener('submit', function(e) {
                        e.preventDefault();

                        // Validar que al menos se haya marcado una verificación
                        const checkboxes = this.querySelectorAll('input[type="checkbox"]');
                        const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

                        if (checkedBoxes.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Verificación incompleta',
                                text: 'Debe marcar al menos una verificación antes de continuar.'
                            });
                            return;
                        }

                        // Confirmar la verificación
                        Swal.fire({
                            title: '¿Confirmar verificación?',
                            text: 'Esta acción guardará la verificación del documento y actualizará los datos si es necesario.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#30336b',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, confirmar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                procesarVerificacionDocumento(numberId, new FormData(this));
                            }
                        });
                    });
                }
            });
        }

        // Función para procesar la verificación del documento
        function procesarVerificacionDocumento(numberId, formData) {
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Procesando verificación',
                text: 'Guardando datos de verificación...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('components/individualSearch/procesar_verificacion_documento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Verificación completada!',
                            text: data.message,
                            confirmButtonColor: '#30336b'
                        }).then(() => {
                            // Recargar la página para mostrar los cambios
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la verificación',
                            text: data.message || 'Hubo un problema al procesar la verificación'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al comunicarse con el servidor'
                    });
                });
        }

        // Configuración de la lupa
        var magnifierSize = 200;
        var magnification = 3;

        function initMagnifier() {
            // Crear el elemento de la lupa si no existe
            if (!document.querySelector('.magnify')) {
                const magnifyDiv = document.createElement('div');
                magnifyDiv.className = 'magnify';
                magnifyDiv.style.cssText = `
                    position: fixed; /* Cambio a fixed para mejor posicionamiento */
                    border: 3px solid #000;
                    border-radius: 50%;
                    z-index: 99999;
                    display: none;
                    pointer-events: none;
                    box-shadow: 0 0 10px rgba(0,0,0,0.5);
                    transform: translate(-50%, -50%); /* Centrar la lupa en el cursor */
                `;
                document.body.appendChild(magnifyDiv);
            }
        }

        function addMagnifierToImage(imageId) {
            const img = document.getElementById(imageId);
            if (!img) return;

            // Remover eventos previos si existen
            removeMagnifierFromImage(imageId);

            const mouseEnterHandler = function() {
                initMagnifier();
                const magnifyEl = document.querySelector('.magnify');
                magnifyEl.style.display = 'block';

                // Configurar la lupa
                const imgRect = img.getBoundingClientRect();
                const imgSrc = img.src;

                magnifyEl.style.width = magnifierSize + 'px';
                magnifyEl.style.height = magnifierSize + 'px';
                magnifyEl.style.backgroundImage = `url("${imgSrc}")`;
                magnifyEl.style.backgroundRepeat = 'no-repeat';
            };

            const mouseMoveHandler = function(e) {
                const magnifyEl = document.querySelector('.magnify');
                if (!magnifyEl || magnifyEl.style.display === 'none') return;

                const imgRect = img.getBoundingClientRect();

                // Verificar si el mouse está dentro de los límites de la imagen
                if (e.clientX < imgRect.left || e.clientX > imgRect.right ||
                    e.clientY < imgRect.top || e.clientY > imgRect.bottom) {
                    magnifyEl.style.display = 'none';
                    return;
                }

                // Obtener las transformaciones actuales de la imagen
                const transform = window.imageTransforms[imageId] || {
                    rotation: 0,
                    scale: 1,
                    offsetX: 0,
                    offsetY: 0
                };
                const {
                    rotation,
                    scale: imgScale
                } = transform;

                // Calcular posición del cursor relativa a la imagen
                const x = e.clientX - imgRect.left;
                const y = e.clientY - imgRect.top;

                // Posicionar la lupa exactamente en la posición del cursor
                magnifyEl.style.left = e.clientX + 'px';
                magnifyEl.style.top = e.clientY + 'px';

                // Calcular el tamaño del background considerando la rotación
                let bgWidth, bgHeight;
                const rot = Math.abs(rotation % 360);

                if (rot === 90 || rot === 270) {
                    // Si está rotado 90° o 270°, intercambiar dimensiones
                    bgWidth = imgRect.height * magnification;
                    bgHeight = imgRect.width * magnification;
                } else {
                    // Rotación normal (0°, 180°)
                    bgWidth = imgRect.width * magnification;
                    bgHeight = imgRect.height * magnification;
                }

                // Aplicar el tamaño del background y la rotación
                magnifyEl.style.backgroundSize = `${bgWidth}px ${bgHeight}px`;

                // Calcular la posición del background considerando la rotación
                const magnifyOffset = magnifierSize / 2;
                let backgroundPosX, backgroundPosY;

                switch (rot) {
                    case 90:
                        backgroundPosX = -(y * magnification - magnifyOffset);
                        backgroundPosY = -((imgRect.width - x) * magnification - magnifyOffset);
                        break;
                    case 180:
                        backgroundPosX = -((imgRect.width - x) * magnification - magnifyOffset);
                        backgroundPosY = -((imgRect.height - y) * magnification - magnifyOffset);
                        break;
                    case 270:
                        backgroundPosX = -((imgRect.height - y) * magnification - magnifyOffset);
                        backgroundPosY = -(x * magnification - magnifyOffset);
                        break;
                    default: // 0°
                        backgroundPosX = -(x * magnification - magnifyOffset);
                        backgroundPosY = -(y * magnification - magnifyOffset);
                        break;
                }

                magnifyEl.style.backgroundPosition = `${backgroundPosX}px ${backgroundPosY}px`;

                // Aplicar la rotación al background de la lupa
                magnifyEl.style.transform = `translate(-50%, -50%) rotate(${rotation}deg)`;
            };

            const mouseLeaveHandler = function() {
                const magnifyEl = document.querySelector('.magnify');
                if (magnifyEl) {
                    magnifyEl.style.display = 'none';
                }
            };

            // Agregar eventos
            img.addEventListener('mouseenter', mouseEnterHandler);
            img.addEventListener('mousemove', mouseMoveHandler);
            img.addEventListener('mouseleave', mouseLeaveHandler);

            // Guardar referencias para poder remover después
            img._magnifierHandlers = {
                mouseenter: mouseEnterHandler,
                mousemove: mouseMoveHandler,
                mouseleave: mouseLeaveHandler
            };
        }

        function removeMagnifierFromImage(imageId) {
            const img = document.getElementById(imageId);
            if (!img || !img._magnifierHandlers) return;

            // Remover eventos
            img.removeEventListener('mouseenter', img._magnifierHandlers.mouseenter);
            img.removeEventListener('mousemove', img._magnifierHandlers.mousemove);
            img.removeEventListener('mouseleave', img._magnifierHandlers.mouseleave);

            delete img._magnifierHandlers;
        }

        // Modificar las funciones existentes para incluir la lupa
        function showDocumentSwal(frontImage, backImage, numberId) {
            Swal.fire({
                title: 'Imágenes de Identificación',
                width: 900,
                showCloseButton: true,
                showConfirmButton: false,
                html: `
                    <div class="row">
                        <!-- Frente del documento -->
                        <div class="col-12 mb-4 text-center">
                            <h6>Frente del documento</h6>
                            <div class="position-relative" style="overflow: hidden;">
                                <img id="idImageFront_${numberId}"
                                    src="../files/idFilesFront/${frontImage}"
                                    class="img-fluid"
                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                    alt="Frente ID">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageFront_${numberId}', -90)">
                                    ↺ Rotar Izquierda
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="toggleZoom('idImageFront_${numberId}')">
                                    <i class="bi bi-zoom-in"></i> Zoom
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageFront_${numberId}', 90)">
                                    ↻ Rotar Derecha
                                </button>
                            </div>
                        </div>

                        <!-- Reverso del documento -->
                        <div class="col-12 text-center">
                            <h6>Reverso del documento</h6>
                            <div class="position-relative" style="overflow: hidden;">
                                <img id="idImageBack_${numberId}"
                                    src="../files/idFilesBack/${backImage}"
                                    class="img-fluid"
                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                    alt="Reverso ID">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageBack_${numberId}', -90)">
                                    ↺ Rotar Izquierda
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="toggleZoom('idImageBack_${numberId}')">
                                    <i class="bi bi-zoom-in"></i> Zoom
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="rotateImage('idImageBack_${numberId}', 90)">
                                    ↻ Rotar Derecha
                                </button>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="button" class="btn btn-success" onclick="guardarCedulaPDF(${numberId})">
                                <i class="bi bi-file-earmark-pdf"></i> Guardar PDF
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    // Reiniciar transformaciones
                    window.imageTransforms[`idImageFront_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };
                    window.imageTransforms[`idImageBack_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };

                    // Agregar eventos de arrastre
                    const frontImg = document.getElementById(`idImageFront_${numberId}`);
                    const backImg = document.getElementById(`idImageBack_${numberId}`);

                    if (frontImg) {
                        setupDragEvents(frontImg);
                    }

                    if (backImg) {
                        setupDragEvents(backImg);
                    }

                    // Agregar lupa a las imágenes
                    setTimeout(() => {
                        addMagnifierToImage(`idImageFront_${numberId}`);
                        addMagnifierToImage(`idImageBack_${numberId}`);
                    }, 100);
                }
            });
        }

        function showDocumentVerificationSwal(frontImage, backImage, numberId, firstName, secondName, firstLast, secondLast, documentNumber, birthDate, documentType, nameVerified, docVerified, birthVerified, typeVerified, lastObservation) {
            Swal.fire({
                title: 'Verificación de Documento de Identidad',
                width: 1200,
                showCloseButton: true,
                showConfirmButton: false,
                html: `
                    <div class="row">
                        <!-- Columna izquierda: Imágenes del documento -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-indigo-dark text-white">
                                    <h6 class="mb-0"><i class="bi bi-images"></i> Documento de Identidad</h6>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                    <!-- Frente del documento -->
                                    <div class="mb-4 w-100 d-flex flex-column justify-content-center align-items-center">
                                        <h6 class="text-center">Frente del documento</h6>
                                        <div class="position-relative d-flex justify-content-center align-items-center" style="overflow: hidden;">
                                            <img id="verifyImageFront_${numberId}"
                                                src="../files/idFilesFront/${frontImage}"
                                                class="img-fluid border rounded"
                                                style="max-height: 250px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                                alt="Frente ID">
                                        </div>
                                        <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageFront_${numberId}', -90)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm bg-magenta-dark text-white" onclick="toggleZoom('verifyImageFront_${numberId}')">
                                                <i class="bi bi-zoom-in"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageFront_${numberId}', 90)">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Reverso del documento -->
                                    <div class="w-100 d-flex flex-column justify-content-center align-items-center">
                                        <h6 class="text-center">Reverso del documento</h6>
                                        <div class="position-relative d-flex justify-content-center align-items-center" style="overflow: hidden;">
                                            <img id="verifyImageBack_${numberId}"
                                                src="../files/idFilesBack/${backImage}"
                                                class="img-fluid border rounded"
                                                style="max-height: 250px; object-fit: contain; transition: transform 0.3s ease; cursor: move;"
                                                alt="Reverso ID">
                                        </div>
                                        <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageBack_${numberId}', -90)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <!-- <button type="button" class="btn btn-sm bg-magenta-dark text-white" onclick="toggleZoom('verifyImageBack_${numberId}')">
                                                <i class="bi bi-zoom-in"></i>
                                            </button> -->
                                            <button type="button" class="btn btn-sm bg-teal-dark text-white" onclick="rotateImage('verifyImageBack_${numberId}', 90)">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>

                                        <div class="col-12 text-center mt-4">
                                            <button type="button" class="btn btn-success btn-lg" onclick="guardarCedulaPDF(${numberId})">
                                                <i class="bi bi-file-earmark-pdf"></i> Guardar PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha: Formulario de verificación -->
                        <div class="col-md-6">
                            <div class="card h-100 d-flex justify-content-center align-items-center">
                                <div class="card-header bg-orange-dark text-white w-100">
                                    <h6 class="mb-0"><i class="bi bi-shield-check"></i> Formulario de Verificación</h6>
                                </div>
                                <div class="card-body">
                                    <form id="formVerificacionDocumento_${numberId}">
                                        <!-- Nombres -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nombres y Apellidos:</label>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Primer Nombre *</label>
                                                    <input type="text" class="form-control form-control-sm" name="primerNombre" value="${firstName}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Segundo Nombre</label>
                                                    <input type="text" class="form-control form-control-sm" name="segundoNombre" value="${secondName}">
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Primer Apellido *</label>
                                                    <input type="text" class="form-control form-control-sm" name="primerApellido" value="${firstLast}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label text-muted small">Segundo Apellido</label>
                                                    <input type="text" class="form-control form-control-sm" name="segundoApellido" value="${secondLast}">
                                                </div>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="nombreCoincide_${numberId}" name="nombreCoincide"
                                                    style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                    ${nameVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="nombreCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> El nombre completo coincide con el documento
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Número de documento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Número de Documento:</label>
                                            <input type="text" class="form-control" name="numeroDocumento" value="${documentNumber}" required>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="documentoCoincide_${numberId}" name="documentoCoincide"
                                                style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                ${docVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="documentoCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> El número de documento coincide
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Fecha de nacimiento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Fecha de Nacimiento:</label>
                                            <input type="date" class="form-control" name="fechaNacimiento" value="${birthDate}" required>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="fechaCoincide_${numberId}" name="fechaCoincide"
                                                style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                ${birthVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="fechaCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> La fecha de nacimiento coincide
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Tipo de documento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tipo de Documento:</label>
                                            <select class="form-select" name="tipoDocumento" required>
                                                <option value="CC" ${documentType === 'CC' ? 'selected' : ''}>Cédula de Ciudadanía (CC)</option>
                                                <option value="Otra" ${documentType === 'Otra' ? 'selected' : ''}>Otra</option>
                                            </select>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="tipoCoincide_${numberId}" name="tipoCoincide"
                                                style="width: 1.2em; height: 1.2em; border: 2px solid #000; background-color: #d0f7f9;"
                                                ${typeVerified ? 'checked disabled' : ''}>
                                                <label class="form-check-label text-success fw-bold" for="tipoCoincide_${numberId}">
                                                    <i class="bi bi-check-circle"></i> El tipo de documento coincide
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Observaciones -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Observaciones (opcional):</label>
                                            <textarea class="form-control" name="observaciones" rows="3" placeholder="Agregue cualquier observación relevante sobre la verificación...">${lastObservation || ''}</textarea>
                                        </div>

                                        <input type="hidden" name="numberId" value="${numberId}">
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bi bi-shield-check"></i> Confirmar Verificación
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    // Reiniciar transformaciones para las imágenes de verificación
                    window.imageTransforms[`verifyImageFront_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };
                    window.imageTransforms[`verifyImageBack_${numberId}`] = {
                        rotation: 0,
                        scale: 1,
                        offsetX: 0,
                        offsetY: 0
                    };

                    // Agregar eventos de arrastre
                    const frontImg = document.getElementById(`verifyImageFront_${numberId}`);
                    const backImg = document.getElementById(`verifyImageBack_${numberId}`);

                    if (frontImg) {
                        setupDragEvents(frontImg);
                    }

                    if (backImg) {
                        setupDragEvents(backImg);
                    }

                    // Agregar lupa a las imágenes de verificación
                    setTimeout(() => {
                        addMagnifierToImage(`verifyImageFront_${numberId}`);
                        addMagnifierToImage(`verifyImageBack_${numberId}`);
                    }, 100);

                    // Manejar el envío del formulario de verificación
                    document.getElementById(`formVerificacionDocumento_${numberId}`).addEventListener('submit', function(e) {
                        e.preventDefault();

                        // Validar que al menos se haya marcado una verificación
                        const checkboxes = this.querySelectorAll('input[type="checkbox"]');
                        const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

                        if (checkedBoxes.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Verificación incompleta',
                                text: 'Debe marcar al menos una verificación antes de continuar.'
                            });
                            return;
                        }

                        // Confirmar la verificación
                        Swal.fire({
                            title: '¿Confirmar verificación?',
                            text: 'Esta acción guardará la verificación del documento y actualizará los datos si es necesario.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#30336b',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, confirmar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                procesarVerificacionDocumento(numberId, new FormData(this));
                            }
                        });
                    });
                }
            });
        }
    </script>
</li>