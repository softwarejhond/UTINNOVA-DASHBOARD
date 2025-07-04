<li class="list-group-item">
    <span class="badge bg-primary" style="cursor: pointer;" onclick="showDocumentSwal('<?php echo htmlspecialchars($row['file_front_id']); ?>', '<?php echo htmlspecialchars($row['file_back_id']); ?>', <?php echo $row['number_id']; ?>)">
        <i class="bi bi-card-image me-1"></i> Ver documento
    </span>

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
                    </div>
                `,
                didOpen: () => {
                    // Reiniciar transformaciones
                    window.imageTransforms[`idImageFront_${numberId}`] = { rotation: 0, scale: 1, offsetX: 0, offsetY: 0 };
                    window.imageTransforms[`idImageBack_${numberId}`] = { rotation: 0, scale: 1, offsetX: 0, offsetY: 0 };
                    
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
            imgElement.addEventListener('touchstart', startDrag, { passive: false });
            
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
            document.addEventListener('touchmove', drag, { passive: false });
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
                let { rotation, scale, offsetX, offsetY } = window.imageTransforms[imageId];
                requestAnimationFrame(() => {
                    imgElement.style.transform = `rotate(${rotation}deg) scale(${scale}) translate(${offsetX}px, ${offsetY}px)`;
                });
            }
        }
    </script>
</li>