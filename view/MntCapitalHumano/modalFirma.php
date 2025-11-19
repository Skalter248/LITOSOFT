<?php
    // ARCHIVO: view/MntCapitalHumano/modalFirma.php
?>

<div class="modal fade" id="modalFirma" tabindex="-1" role="dialog" aria-labelledby="modalFirmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFirmaLabel" style="color: white;">Cargar/Actualizar Firma Digital</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form method="post" id="firma_form" enctype="multipart/form-data"> 
                <div class="modal-body">
                    <input type="hidden" name="usu_id_firma" id="usu_id_firma">
                    
                    <div class="form-group">
                        <label for="firma_preview">Firma Actual de: <span id="usu_nombre_firma" class="font-weight-bold text-primary"></span></label>
                        <div id="firma_preview" class="border p-3 mb-3 text-center" style="min-height: 80px; background-color: #f9f9f9;">
                            <small class="text-muted">Cargando...</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="firma_file" class="font-weight-bold">Seleccionar Nuevo Archivo de Firma</label>
                        <input type="file" class="form-control-file" name="firma_file" id="firma_file" accept=".png,.jpg,.jpeg" required>
                        <small class="form-text text-muted">
                            Solo se permiten archivos **PNG** (idealmente con fondo transparente) o **JPG**. 
                            Máximo 1MB. El archivo se guardará en `public/upload/firmas/`.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarFirma">Guardar Firma</button>
                </div>
            </form>
        </div>
    </div>
</div>