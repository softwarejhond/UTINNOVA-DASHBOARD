<li class="list-group-item">
    <span class="badge bg-primary" style="cursor: pointer;" onclick="showDocumentSwal('<?php echo htmlspecialchars($row['file_front_id']); ?>', '<?php echo htmlspecialchars($row['file_back_id']); ?>', <?php echo $row['number_id']; ?>)">
        <i class="bi bi-card-image me-1"></i> Ver documento
    </span>

    <?php
    $pdfPath = "cedulas/cedula_{$row['number_id']}.pdf";
    // Detect environment: production or local
    $isProduction = (strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
    $baseDir = $isProduction ? '/dashboard/' : '/UTINNOVA-DASHBOARD/';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $baseDir . $pdfPath)) {
    ?>
        <span class="badge bg-success ms-2" style="cursor: pointer;" onclick="event.preventDefault(); showPdfModal('<?php echo $pdfPath; ?>')">
            <i class="bi bi-file-earmark-pdf"></i> Ver PDF
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
            Swal.fire({
                title: 'Documento PDF generado',
                html: `<iframe src="${pdfPath}" style="width:100%;height:80vh;" frameborder="0"></iframe>`,
                width: '90%',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-pdf-modal'
                }
            });
        }

        function getTransformedImageBase64(imgElement, transform) {
            const { rotation = 0, scale = 1, offsetX = 0, offsetY = 0 } = transform;
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
    </script>
</li>