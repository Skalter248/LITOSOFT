<div class="modal fade" id="modalCalendario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calendario de Mis Solicitudes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="calendar_container">
                    <p class="text-info">Las solicitudes aprobadas se mostrarán en verde y las pendientes en amarillo.</p>
                </div>
                
                <hr>
                
                <h4>Detalle de Fechas:</h4>
                <table class="table table-bordered table-sm" id="tabla_detalle_fechas">
                    <thead>
                        <tr>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Días Hábiles</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>