<style>
    .readonly-calc { background-color: #f8f9fa; font-weight: 600; color: #495057; }
    
    /* Estilo Saldo Normal (VERDE) */
    .saldo-display {
        font-size: 1.8rem; font-weight: 800; text-align: center;
        color: #198754; background-color: #e2f5ee;
        border: 1px solid #198754; border-radius: 8px; padding: 10px;
        transition: all 0.3s ease;
    }
    
    /* Estilo Saldo Adelanto (ROJO) - Se activa con JS */
    .es-adelanto .saldo-display {
        color: #dc3545; background-color: #f8d7da; border-color: #dc3545;
    }

    .section-label {
        font-size: 0.8rem; font-weight: 700; color: #6c757d;
        text-transform: uppercase; border-bottom: 1px solid #dee2e6;
        margin-bottom: 10px; padding-bottom: 5px; letter-spacing: 0.5px;
    }
</style>

<div class="modal fade" id="modalSolicitudVacaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header bg-light">
                <h5 class="modal-title text-dark">Solicitar Vacaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="solicitud_form"> 
                <div class="modal-body p-4" id="modal_body_container">
                    
                    <input type="hidden" name="vac_id" id="vac_id">
                    <input type="hidden" name="usu_id" id="usu_id" value="<?php echo $_SESSION['usu_id']; ?>">
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <p class="text-center mb-1 text-muted">Saldo Actual Disponible</p>
                            <div class="saldo-display" id="saldo_disponible_modal">Cargando...</div>
                        </div>
                    </div>
                    
                    <div class="section-label">Selección de Fechas</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Desde <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="vac_fecha_inicio" id="vac_fecha_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hasta <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="vac_fecha_fin" id="vac_fecha_fin" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-label mt-3">Resumen de Días</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Días Hábiles (A descontar)</label>
                                <input type="text" class="form-control readonly-calc" name="vac_dias_habiles" id="vac_dias_habiles" readonly value="0.00"> 
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Días Naturales (Calendario)</label>
                                <input type="text" class="form-control readonly-calc" name="vac_dias_naturales" id="vac_dias_naturales" readonly value="0"> 
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-none mt-3" id="div_observaciones_container">
                        <div class="p-3 border border-danger rounded bg-light">
                            <h6 class="text-danger font-weight-bold mb-2">
                                <i class="fa fa-exclamation-circle"></i> Se requiere justificación
                            </h6>
                            <div class="form-group mb-0">
                                <label for="vac_observaciones">Motivo del Adelanto <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="vac_observaciones" id="vac_observaciones" rows="2" placeholder="Explica el motivo del adelanto..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="alerta_saldo" class="alert alert-danger d-none mt-3 text-center font-weight-bold" role="alert">
                        </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnGuardar">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>