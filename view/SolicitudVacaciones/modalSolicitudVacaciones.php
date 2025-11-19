<style>
    /* Estilo sutil para campos calculados (solo lectura) */
    .readonly-calc {
        background-color: #f8f9fa !important; /* Gris muy claro */
        font-weight: 600;
        color: #495057;
    }
    /* Estilo para destacar el saldo disponible */
    .saldo-display {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        color: #198754; /* Verde (Success) */
        background-color: #e2f5ee;
        border: 1px solid #198754;
        border-radius: 0.25rem;
        padding: 5px;
        margin-bottom: 10px;
    }
    /* Separadores de sección minimalistas */
    .section-divider {
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 15px;
        padding-bottom: 5px;
        margin-top: 10px;
        color: #6c757d;
        font-size: 0.85rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="modal fade" id="modalSolicitudVacaciones" tabindex="-1" aria-labelledby="modalSolicitudVacacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header bg-light">
                <h5 class="modal-title text-dark" id="modalSolicitudVacacionesLabel">Solicitar Vacaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="solicitud_form"> 
                <div class="modal-body p-4">
                    <input type="hidden" name="vac_id" id="vac_id">
                    <input type="hidden" name="usu_id" id="usu_id" value="<?php echo $_SESSION['usu_id']; ?>">
                    
                    <div class="section-divider">Saldo Disponible</div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="d-block text-center mb-1">Tu saldo disponible es:</label>
                            <div class="saldo-display" id="saldo_disponible_modal">
                                Cargando...
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-divider">Periodo Solicitado</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vac_fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="vac_fecha_inicio" id="vac_fecha_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vac_fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="vac_fecha_fin" id="vac_fecha_fin" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-divider mt-2">Días Calculados</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vac_dias_habiles">Días Hábiles (A descontar)</label>
                                <input type="text" class="form-control readonly-calc" name="vac_dias_habiles" id="vac_dias_habiles" readonly value="0.00"> 
                                <small class="form-text text-muted mt-1" id="dias_habiles_info">Selecciona las fechas para ver el cálculo.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vac_dias_naturales">Días Naturales (Calendario)</label>
                                <input type="text" class="form-control readonly-calc" name="vac_dias_naturales" id="vac_dias_naturales" readonly value="0"> 
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group d-none mt-2" id="div_observaciones_container">
                        <label for="vac_observaciones" class="font-weight-bold text-danger">
                            Motivo del Adelanto <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control border-danger" name="vac_observaciones" id="vac_observaciones" rows="2" placeholder="Explica por qué necesitas un adelanto de vacaciones..."></textarea>
                    </div>

                    <div id="alerta_saldo" class="alert alert-warning d-none mt-3" role="alert">
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