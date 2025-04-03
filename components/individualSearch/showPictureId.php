<li class="list-group-item">

<li class="list-group-item">
    <strong>Fotos de identificación:</strong><br>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalID_<?php echo $row['number_id']; ?>">
        <i class="bi bi-card-image"></i>
    </button>


    <!-- Modal para mostrar las imágenes -->
    <div class="modal fade" id="modalID_<?php echo $row['number_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-indigo-dark">
                    <h5 class="modal-title">Imágenes de Identificación</h5>
                    <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative" style="overflow: visible;">
                    <div class="row">
                        <!-- Frente del documento -->
                        <div class="col-12 mb-4 text-center">
                            <h6>Frente del documento</h6>
                            <div class="position-relative overflow-visible">
                                <img id="idImageFront_<?php echo $row['number_id']; ?>"
                                    src="../files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
                                    class="img-fluid w-100 zoomable"
                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                    alt="Frente ID"
                                    onclick="toggleZoom('idImageFront_<?php echo $row['number_id']; ?>')">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                            </div>
                        </div>

                        <!-- Reverso del documento -->
                        <div class="col-12 text-center">
                            <h6>Reverso del documento</h6>
                            <div class="position-relative overflow-visible">
                                <img id="idImageBack_<?php echo $row['number_id']; ?>"
                                    src="../files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
                                    class="img-fluid w-100 zoomable"
                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                    alt="Reverso ID"
                                    onclick="toggleZoom('idImageBack_<?php echo $row['number_id']; ?>')">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Verificar si la variable ya existe en el ámbito global
        if (typeof window.imageTransforms === 'undefined') {
            window.imageTransforms = {};
        }

        function rotateImage(imageId, degrees) {
            if (!window.imageTransforms[imageId]) {
                window.imageTransforms[imageId] = {
                    rotation: 0,
                    scale: 1
                };
            }
            window.imageTransforms[imageId].rotation += degrees;
            applyTransform(imageId);
        }

        function toggleZoom(imageId) {
            if (!window.imageTransforms[imageId]) {
                window.imageTransforms[imageId] = {
                    rotation: 0,
                    scale: 1
                };
            }
            window.imageTransforms[imageId].scale = window.imageTransforms[imageId].scale === 1 ? 2 : 1;
            applyTransform(imageId);
        }

        function applyTransform(imageId) {
            let imgElement = document.getElementById(imageId);
            if (imgElement) {
                let {
                    rotation,
                    scale
                } = window.imageTransforms[imageId];
                imgElement.style.transform = `rotate(${rotation}deg) scale(${scale})`;
            }
        }
    </script>
</li>
</li>