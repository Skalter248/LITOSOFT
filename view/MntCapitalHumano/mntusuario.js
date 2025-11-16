// ARCHIVO: view/MntCapitalHumano/mntusuario.js

var tablaUsuarios;
// Ruta del controlador de usuarios
var ruta_controlador = '../../controller/MntUsuario.php?op='; 

$(document).ready(function() {
    listarUsuarios();
    cargarSelectsIniciales();

    // 1. Manejar el evento submit del formulario de Usuarios
    $('#usuario_form').on('submit', function(e){
        guardarUsuario(e);
    });

    // 2. Función para ABRIR MODAL de nuevo usuario (Configuración de INSERT)
    $('#btnNuevoUsuario').click(function(){
        
        $('#modalUsuarioLabel').html('Nuevo Usuario');
        
        // Limpiar y resetear
        $('#usuario_form')[0].reset();
        $('#usu_id').val('');
        $('#usu_foto_actual').val('default.png');
        $('#foto_preview').html('');
        cargarSelectJefes();
        cargarSelectsIniciales();
        $('#usu_area').html('<option value="">Seleccione Área</option>');
        $('#usu_puesto').html('<option value="">Seleccione Puesto</option>');
        
        // Lógica de Contraseña para INSERT: Requerida y Placeholder
        $('#usu_contraseña_inicio').attr('required', true).attr('placeholder', 'Ingresa Contraseña');
        $('#labelContrasena').html('Contraseña <span style="color:red;">*</span>');
        
        $('#modalUsuario').modal('show');
    });
    
    // 3. EVENTO DE CÁLCULO DE EDAD AL CAMBIAR LA FECHA
    $('#usu_fecha_nacimiento').on('change', function() {
        calcularEdadAutomatica();
    });


    // 4. Lógica de SELECTS DEPENDIENTES
    $('#usu_departamento').on('change', function() {
        $('#usu_area').html('<option value="">Seleccione Área</option>');
        $('#usu_puesto').html('<option value="">Seleccione Puesto</option>');
        cargarSelectsDependientes();
    });
    
    $('#usu_area').on('change', function() {
        $('#usu_puesto').html('<option value="">Seleccione Puesto</option>');
        var area_id = $(this).val();
        cargarSelectPuestos(area_id);
    });
});


/* ========================================================================= */
/* FUNCIONES DE UTILIDAD                                                     */
/* ========================================================================= */

function calcularEdadAutomatica() {
    var fechaNac = $('#usu_fecha_nacimiento').val();
    
    if (fechaNac) {
        var hoy = new Date();
        var nacimiento = new Date(fechaNac);
        var edad = hoy.getFullYear() - nacimiento.getFullYear();
        var mes = hoy.getMonth() - nacimiento.getMonth();
        
        // Ajustar si aún no ha cumplido años este mes
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        $('#usu_edad').val(edad).attr('readonly', true);
    } else {
        $('#usu_edad').val('');
    }
}


/* ========================================================================= */
/* FUNCIONES DE LISTADO Y CRUD                                               */
/* ========================================================================= */

function listarUsuarios() {
    tablaUsuarios = $('#usuarios_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5'
        ],
        "ajax": {
            url: ruta_controlador + 'listar_usuarios',
            type: "post",
            dataType: "json",
            error: function(e){ console.log(e.responseText); }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [[1, "asc"]],
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" }
    });
}

function guardarUsuario(e) {
    e.preventDefault();
    
    var formData = new FormData($('#usuario_form')[0]);
    
    $.ajax({
        url: ruta_controlador + 'guardaryeditar_usuario',
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(datos) {
            if (datos.trim() == "ok") {
                $('#modalUsuario').modal('hide');
                tablaUsuarios.ajax.reload();
                swal.fire("Guardado", "El registro del usuario fue guardado con éxito.", "success");
            } else {
                swal.fire("Error", datos, "error");
            }
        }
    });
}

function editarUsuario(usu_id) {
    $('#modalUsuarioLabel').html('Editar Usuario');
    
    // Lógica de Contraseña para UPDATE: Opcional y Placeholder
    $('#usu_contraseña_inicio').removeAttr('required').attr('placeholder', 'Dejar en blanco para no cambiar');
    $('#labelContrasena').html('Contraseña'); // Quitar el asterisco de requerido

    
    $.post(ruta_controlador + 'get_usuario_por_id', {usu_id: usu_id}, function(data) {
        data = JSON.parse(data);
        
        // Cargar campos básicos
        $('#usu_id').val(data.usu_id);
        $('#usu_nombre').val(data.usu_nombre);
        $('#usu_apellido_paterno').val(data.usu_apellido_paterno);
        $('#usu_apellido_materno').val(data.usu_apellido_materno);
        $('#usu_usuario_inicio').val(data.usu_usuario_inicio);
        $('#usu_telefono').val(data.usu_telefono);
        $('#usu_RFC').val(data.usu_RFC);
        $('#usu_CURP').val(data.usu_CURP);
        $('#usu_NSS').val(data.usu_NSS);
        $('#usu_domicilio').val(data.usu_domicilio);
        
        // Campos de Edad y Fecha de Nacimiento
        $('#usu_edad').val(data.usu_edad);
        $('#usu_fecha_nacimiento').val(data.usu_fecha_nacimiento);
        $('#fecha_ingreso_planta').val(data.fecha_ingreso_planta);
        
        // Foto
        var foto_path = '../../public/upload/fotos/' + data.usu_foto;
        $('#usu_foto_actual').val(data.usu_foto);
        $('#foto_preview').html('<img src="' + foto_path + '" width="150" onerror="this.src=\'../../public/upload/fotos/default.png\'" />');
        
        // Carga de campos del Jefe Directo
        cargarSelectJefes(data.jefe_id);

        // Seleccionar Relaciones
        $('#rol_id').val(data.rol_id);
        $('#usu_departamento').val(data.usu_departamento);
        
        // Cargar Áreas y Puestos dependientes y seleccionar valores
        cargarSelectsDependientes(data.usu_area, data.usu_puesto);

        $('#modalUsuario').modal('show');
    });
}

/* ========================================================================= */
/* FUNCIONES DE ACTIVACIÓN / DESACTIVACIÓN / ELIMINACIÓN                     */
/* ========================================================================= */

function desactivarUsuario(usu_id) {
    swal.fire({
        title: "¿Estás seguro?", text: "¡El usuario será marcado como INACTIVO!", icon: "warning",
        showCancelButton: true, confirmButtonText: "Sí, desactivar", cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            // Se usa 'eliminar_usuario' en el controlador para la desactivación (Soft Delete)
            $.post(ruta_controlador + 'eliminar_usuario', {usu_id: usu_id}, function(data){
                if (data.trim() === "ok") {
                    swal.fire("Desactivado", "El usuario ha sido desactivado.", "success");
                } else {
                    swal.fire("Error", "Error al desactivar: " + data, "error");
                }
                tablaUsuarios.ajax.reload();
            });
        }
    });
}

function activarUsuario(usu_id) {
    swal.fire({
        title: "¿Estás seguro?", text: "¡El usuario será marcado como ACTIVO!", icon: "info",
        showCancelButton: true, confirmButtonText: "Sí, activar", cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post(ruta_controlador + 'activar_usuario', {usu_id: usu_id}, function(data){
                if (data.trim() === "ok") {
                    swal.fire("Activado", "El usuario ha sido activado.", "success");
                } else {
                    swal.fire("Error", "Error al activar: " + data, "error");
                }
                tablaUsuarios.ajax.reload();
            });
        }
    });
}

function eliminarUsuarioPermanente(usu_id) {
    swal.fire({
        title: "¿Estás COMPLETAMENTE seguro?", text: "¡El usuario será ELIMINADO permanentemente y sus datos se perderán!", icon: "error",
        showCancelButton: true, confirmButtonText: "Sí, borrar definitivamente", cancelButtonText: "No, cancelar",
        dangerMode: true,
    }).then((result) => {
        if (result.value) {
            $.post(ruta_controlador + 'eliminar_usuario_permanente', {usu_id: usu_id}, function(data){
                swal.fire("Eliminado", "El usuario ha sido borrado permanentemente.", "success");
                tablaUsuarios.ajax.reload();
            });
        }
    });
}

/* ========================================================================= */
/* FUNCIONES DE SELECTS DEPENDIENTES (USANDO ID)                             */
/* ========================================================================= */

function cargarSelectsIniciales() {
    $.post(ruta_controlador + 'select_departamentos_usuarios', function(data) {
        $('#usu_departamento').html(data);
    });
    
    $.post(ruta_controlador + 'select_roles', function(data) {
         $('#rol_id').html(data);
    });
}

function cargarSelectsDependientes(selected_area_id = null, selected_puesto_id = null) {
    var dep_id = $('#usu_departamento').val();

    if (dep_id) {
        $.post(ruta_controlador + 'select_areas_usuarios', {dep_id: dep_id}, function(data) {
            $('#usu_area').html(data);
            
            if (selected_area_id) {
                $('#usu_area').val(selected_area_id);
            }
            
            var area_id_actual = selected_area_id || $('#usu_area').val();
            
            if (area_id_actual) {
                cargarSelectPuestos(area_id_actual, selected_puesto_id);
            } else {
                $('#usu_puesto').html('<option value="">Seleccione Puesto</option>');
            }
        });
    } else {
        $('#usu_area').html('<option value="">Seleccione Área</option>');
        $('#usu_puesto').html('<option value="">Seleccione Puesto</option>');
    }
}

function cargarSelectPuestos(area_id, selected_puesto_id = null) {
    if (area_id) {
        $.post(ruta_controlador + 'select_puestos_usuarios', {area_id: area_id}, function(data) {
            $('#usu_puesto').html(data);
            if (selected_puesto_id) {
                $('#usu_puesto').val(selected_puesto_id);
            }
        });
    } else {
        $('#usu_puesto').html('<option value="">Seleccione Puesto</option>');
    }
}

function cargarSelectJefes(jefe_id_seleccionado = '') {
    $.post(ruta_controlador + 'select_jefes', function(data){
        $('#jefe_id').html(data);
        
        // Si se proporcionó un ID, lo seleccionamos SOLO después de que el HTML haya sido cargado.
        if (jefe_id_seleccionado) {
            $('#jefe_id').val(jefe_id_seleccionado).trigger('change');
        }
    });
}