<?php
    // ARCHIVO: view/MntCapitalHumano/modalmantenimiento.php
?>

<div class="modal fade" id="modalDepartamento" tabindex="-1" role="dialog" aria-labelledby="modalDepartamentoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDepartamentoLabel">Mantenimiento de Departamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="departamento_form" role="form">
                <div class="modal-body">
                    <input type="hidden" name="dep_id" id="dep_id">
                    <div class="form-group">
                        <label for="dep_nombre">Nombre del Departamento</label>
                        <input type="text" class="form-control" id="dep_nombre" name="dep_nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="action" id="btnGuardarDepartamento" value="add" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalArea" tabindex="-1" role="dialog" aria-labelledby="modalAreaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAreaLabel">Mantenimiento de Área</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="area_form" role="form">
                <div class="modal-body">
                    <input type="hidden" name="area_id" id="area_id">
                    
                    <div class="form-group">
                        <label for="dep_id_area">Departamento</label>
                        <select class="form-control" id="dep_id_area" name="dep_id" required>
                            <option value="">Seleccione un Departamento</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="area_nombre">Nombre del Área</label>
                        <input type="text" class="form-control" id="area_nombre" name="area_nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="action" id="btnGuardarArea" value="add" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPuesto" tabindex="-1" role="dialog" aria-labelledby="modalPuestoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPuestoLabel">Mantenimiento de Puesto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="puesto_form" role="form">
                <div class="modal-body">
                    <input type="hidden" name="pue_id" id="pue_id">

                    <div class="form-group">
                        <label for="area_id_puesto">Área</label>
                        <select class="form-control" id="area_id_puesto" name="area_id" required>
                            <option value="">Seleccione un Área</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pue_nombre">Nombre del Puesto</label>
                        <input type="text" class="form-control" id="pue_nombre" name="pue_nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="action" id="btnGuardarPuesto" value="add" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>