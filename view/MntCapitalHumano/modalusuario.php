<?php
    // ARCHIVO: view/MntCapitalHumano/modalusuario.php
?>

<div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioLabel">Mantenimiento de Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="usuario_form" role="form" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="usu_id" id="usu_id">
                    <input type="hidden" name="usu_foto_actual" id="usu_foto_actual">

                    <div class="row">
                        <div class="col-lg-4"><div class="form-group"><label>Nombre</label>
                            <input type="text" class="form-control" id="usu_nombre" name="usu_nombre" placeholder="Primer Nombre" required></div></div>
                        <div class="col-lg-4"><div class="form-group"><label>Apellido Paterno</label>
                            <input type="text" class="form-control" id="usu_apellido_paterno" name="usu_apellido_paterno" placeholder="Apellido Paterno" required></div></div>
                        <div class="col-lg-4"><div class="form-group"><label>Apellido Materno</label>
                            <input type="text" class="form-control" id="usu_apellido_materno" name="usu_apellido_materno" placeholder="Apellido Materno"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4"><div class="form-group"><label>Usuario de Inicio (Login)</label>
                            <input type="text" class="form-control" id="usu_usuario_inicio" name="usu_usuario_inicio" placeholder="Ej: jlopez" required></div></div>
                        
                        <div class="col-lg-4"><div class="form-group"><label id="labelContrasena">Contraseña</label>
                            <input type="password" class="form-control" id="usu_contraseña_inicio" name="usu_contraseña_inicio" placeholder="Ingresa Contraseña"></div></div>
                        
                        <div class="col-lg-4"><div class="form-group"><label>Teléfono</label>
                            <input type="text" class="form-control" id="usu_telefono" name="usu_telefono" placeholder="Ej: 5512345678"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4"><div class="form-group"><label>Departamento</label>
                            <select class="form-control" id="usu_departamento" name="usu_departamento" required>
                                <option value="">Seleccione Depto.</option>
                            </select></div></div>
                        <div class="col-lg-4"><div class="form-group"><label>Área</label>
                            <select class="form-control" id="usu_area" name="usu_area" required>
                                <option value="">Seleccione Área</option>
                            </select></div></div>
                        <div class="col-lg-4"><div class="form-group"><label>Puesto</label>
                            <select class="form-control" id="usu_puesto" name="usu_puesto" required>
                                <option value="">Seleccione Puesto</option>
                            </select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12"> 
                            <div class="form-group">
                                <label>Jefe Directo</label>
                                <select class="form-control" id="jefe_id" name="jefe_id"></select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-3"><div class="form-group"><label>Rol</label>
                            <select class="form-control" id="rol_id" name="rol_id" required></select></div></div>    
                        <div class="col-lg-3"><div class="form-group"><label>Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="usu_fecha_nacimiento" name="usu_fecha_nacimiento"></div></div>
                        <div class="col-lg-3"><div class="form-group"><label>Edad</label>
                            <input type="number" class="form-control" id="usu_edad" name="usu_edad" placeholder="Edad Calculada" readonly></div></div>
                        <div class="col-lg-3"><div class="form-group"><label>Fecha Ingreso a Planta</label>
                            <input type="date" class="form-control" id="fecha_ingreso_planta" name="fecha_ingreso_planta"></div></div>    
                    </div>
                    <div class="row">
                         <div class="col-lg-12"><div class="form-group"><label>Foto</label>
                            <input type="file" class="form-control" id="usu_foto" name="usu_foto"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4"><div class="form-group"><label>RFC</label>
                            <input type="text" class="form-control" id="usu_RFC" name="usu_RFC" placeholder="Ej: ABCD123456EFG"></div></div>
                        <div class="col-lg-4"><div class="form-group"><label>CURP</label>
                            <input type="text" class="form-control" id="usu_CURP" name="usu_CURP" placeholder="Ej: ABCDEFGHIJ01234567"></div></div>
                        <div class="col-lg-4"><div class="form-group"><label>NSS</label>
                            <input type="text" class="form-control" id="usu_NSS" name="usu_NSS" placeholder="Ej: 12345678901"></div></div>
                    </div>

                    <div class="form-group">
                        <label>Domicilio</label>
                        <input type="text" class="form-control" id="usu_domicilio" name="usu_domicilio" placeholder="Calle, Número, Colonia, Ciudad">
                    </div>

                    <div id="foto_preview" style="text-align:center; margin-top:15px;"></div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" name="action" id="btnGuardarUsuario" value="add" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>