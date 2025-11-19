// ARCHIVO: view/MntCapitalHumano/mntusuario.js

var tablaUsuarios;
var tablaFirmas;
// Ruta del controlador de usuarios
var ruta_controlador = '../../controller/MntUsuario.php?op='; 

$(document).ready(function() {
    listarUsuarios();
    cargarSelectsIniciales();
    listarFirmas();

    // 1. Manejar el evento submit del formulario de Usuarios
    $('#usuario_form').on('submit', function(e){
        guardarUsuario(e);
    });

    // 2. Manejar el evento submit del formulario de Firmas
    $('#firma_form').on('submit', function(e) {
        e.preventDefault();
        guardarFirma(e);
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

/* *************************************************************** */
/* FUNCIONES PARA GESTIÓN DE FIRMAS DIGITALES */
/* *************************************************************** */

/**
 * Inicializa y lista los usuarios para la gestión de firmas en su DataTable.
 */
function listarFirmas() {
    // NOTA: El .DataTable() se elimina al final de la inicialización para evitar el TypeError
    tablaFirmas = $('#firmas_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ],
        "ajax": {
            // Llama al case 'listar_usuarios_firmas' en MntUsuario.php
            url: '../../controller/MntUsuario.php?op=listar_usuarios_firmas', 
            type: "post",
            dataType: "json",
            error: function (e) {
                console.log(e.responseText);
            }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [[1, "asc"]], // Ordenar por Nombre Completo
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No hay datos disponibles en esta tabla",
            "sInfo": "Mostrando un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "columns": [
            // 1. ID
            { "data": "usu_id" }, 
            
            // 2. Nombre Completo
            { "data": "nombre_completo" }, 
            
            // 3. Puesto
            { "data": "pue_nombre" },
            
            /* 4. FIRMA DIGITAL (Muestra imagen o 'Sin Firma') */
            { 
                "data": "firma_nombre_archivo", 
                "render": function(data, type, row) {
                    if (data && data !== '') {
                        // Construye la ruta usando el nombre del archivo de la DB
                        var ruta_completa = '../../public/upload/firmas/' + data; 
                        return `<img src="${ruta_completa}" style="max-width: 100px; max-height: 40px; border: 1px solid #ccc;" alt="Firma Cargada">`;
                    } else {
                        return '<span class="label label-warning">Sin Firma</span>';
                    }
                }
            },
            
            /* 5. ACCIÓN (Botón Cargar/Editar Dinámico) */
            {
                "data": "usu_id",
                "render": function(data, type, row) {
                    // Si row.firma_nombre_archivo tiene valor, se considera que tiene firma
                    var tieneFirma = row.firma_nombre_archivo;
                    
                    var btnClase = tieneFirma ? 'btn-warning' : 'btn-primary';
                    var btnTexto = tieneFirma ? 'Editar Firma' : 'Cargar Firma';
                    
                    return `<button type="button" class="btn ${btnClase} btn-sm" 
                                onClick="gestionarFirma(${data});" 
                                title="${btnTexto}">
                                <i class="fa fa-pencil"></i> ${btnTexto}
                            </button>`;
                }
            }
        ]
    }); // <-- ¡Aquí termina la función! El .DataTable() extra fue eliminado.
}

/**
 * Abre el modal de carga de firma, precargando los datos del usuario.
 * @param {number} usu_id ID del usuario
 */
function gestionarFirma(usu_id) {
    // 1. Obtener los datos del usuario (incluye la firma actual gracias a la modificación del modelo)
    $.post(ruta_controlador + 'mostrar', { usu_id: usu_id }, function(data) {
        var datos = JSON.parse(data);
        
        $('#usu_id_firma').val(usu_id);
        $('#usu_nombre_firma').text(datos.usu_nombre + ' ' + datos.usu_apellido_paterno);
        
        // El campo usu_ruta_firma proviene del LEFT JOIN a LS_FIRMAS
        var firma_ruta_nombre = datos.usu_ruta_firma; 
        var previewHtml;
        
        if (firma_ruta_nombre && firma_ruta_nombre !== '') {
            // La ruta completa debe ser construida usando la convención del controlador
            var ruta_completa = '../../public/upload/firmas/' + firma_ruta_nombre; 
            previewHtml = '<img src="' + ruta_completa + '" style="max-width: 100%; max-height: 100px; display: block; margin: 0 auto;" alt="Firma Actual">';
        } else {
            previewHtml = '<small class="text-danger">Aún no hay firma digital cargada.</small>';
        }
        
        $('#firma_preview').html(previewHtml);
        $('#modalFirma').modal('show');
    });
}


/**
 * Maneja el submit del formulario de la firma, subiendo el archivo al servidor.
 */
function guardarFirma(e) {
    // El evento 'e' ya fue prevenido en $(document).ready()
    
    var formData = new FormData($('#firma_form')[0]);
    
    // Validar que se haya seleccionado un archivo
    var fileName = $('#firma_file').val();
    if (!fileName) {
        Swal.fire('Advertencia', 'Debe seleccionar un archivo de firma.', 'warning');
        return;
    }
    
    // Validar tipo de archivo
    var ext = fileName.split('.').pop().toLowerCase();
    if ($.inArray(ext, ['png', 'jpg', 'jpeg']) == -1) {
        Swal.fire('Error', 'Solo se permiten archivos JPG, JPEG y PNG.', 'error');
        return;
    }
    
    // Deshabilitar botón para evitar doble envío
    $('#btnGuardarFirma').prop('disabled', true);
    
    $.ajax({
        url: ruta_controlador + 'subir_firma', // case 'subir_firma' en MntUsuario.php
        type: "POST",
        data: formData,
        contentType: false, 
        processData: false, 
        dataType: "json",
        success: function(response) {
            $('#btnGuardarFirma').prop('disabled', false);
            
            if (response.status === 'ok') {
                Swal.fire('Guardado', response.message, 'success');
                $('#modalFirma').modal('hide');
                // Recargar SOLO la tabla de firmas
                tablaFirmas.ajax.reload(); 
            } else {
                Swal.fire('Error de Carga', response.message, 'error');
            }
        },
        error: function(jqXHR) {
            $('#btnGuardarFirma').prop('disabled', false);
            Swal.fire('Error de Conexión', 'No se pudo conectar con el servidor para subir la firma.', 'error');
            console.error(jqXHR.responseText);
        }
    });
}