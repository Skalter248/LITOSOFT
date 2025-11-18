<div class="modal fade" id="modalSolicitudVacaciones" tabindex="-1" aria-labelledby="modalSolicitudVacacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="solicitud_form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSolicitudVacacionesLabel">Solicitar Vacaciones</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="vac_id" id="vac_id">
                    <input type="hidden" name="usu_id" id="usu_id" value="<?php echo $_SESSION['usu_id']; ?>">
                    
                    <div class="form-group">
                        <label for="vac_fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="vac_fecha_inicio" id="vac_fecha_inicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="vac_fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="vac_fecha_fin" id="vac_fecha_fin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="vac_dias_habiles">Días Hábiles Solicitados</label>
                        <input type="text" class="form-control" name="vac_dias_habiles" id="vac_dias_habiles" readonly>
                        <small class="form-text text-muted" id="dias_habiles_info">Calculando...</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="vac_observaciones">Observaciones</label>
                        <textarea class="form-control" name="vac_observaciones" id="vac_observaciones" rows="3"></textarea>
                    </div>

                    <div id="alerta_saldo" class="alert alert-danger d-none" role="alert">
                        <strong>¡Advertencia!</strong> Los días solicitados exceden tu saldo disponible. Esto podría ser un adelanto.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>