<?php
    // ARCHIVO: view/Vacaciones/modalvacaciones.php
?>

<style>
.force-writable {
    z-index: 9999 !important; 
    pointer-events: auto !important; 
    position: relative !important; 
    background-color: #f7f7f7 !important; /* Ayuda a ver que la clase está activa */
}
</style>

<div class="modal fade" id="modalSolicitudVacaciones" tabindex="-1" role="dialog" aria-labelledby="modalSolicitudVacacionesLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSolicitudVacacionesLabel">Solicitar Vacaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="solicitud_form" role="form">
                <div class="modal-body">
                    <input type="hidden" name="vac_id" id="vac_id">
                    <input type="hidden" name="usu_id" id="usu_id" value="<?= $_SESSION['usu_id'] ?>">
                    
                    <div class="alert alert-info" role="alert" id="alerta_info_disponibles">
                        Días disponibles: <strong id="dias_disponibles_modal">0</strong> días hábiles.
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="vac_fecha_inicio">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="vac_fecha_inicio" name="vac_fecha_inicio" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="vac_fecha_fin">Fecha de Fin</label>
                                <input type="date" class="form-control" id="vac_fecha_fin" name="vac_fecha_fin" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="vac_dias_solicitados">Días Naturales (Calculado)</label>
                                <input type="number" class="form-control" id="vac_dias_solicitados" name="vac_dias_solicitados" readonly required value="0">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="vac_dias_habiles">Días Hábiles (Calculado)</label>
                                <input type="number" class="form-control" id="vac_dias_habiles" name="vac_dias_habiles" readonly required value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="alerta_dias_insuficientes" role="alert">
                        <strong>Error:</strong> La solicitud excede los días disponibles en su saldo.
                    </div>
                    
                    <div class="alert alert-warning d-none" id="alerta_adelanto_dias" role="alert">
                        <strong>¡Atención!</strong> Su solicitud excede el saldo disponible. La diferencia de días se registrará como un **adelanto** y requerirá una justificación para ser procesada.
                    </div>

                    <div id="adelanto_justificacion_container" style="display:none;" >
                        <label for="vac_justificacion_adelanto">Justificación Adelanto:</label>
                        <textarea id="vac_justificacion_adelanto" ></textarea>
                    </div>
     
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="action" id="btnGuardarSolicitud" value="add" class="btn btn-primary">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>