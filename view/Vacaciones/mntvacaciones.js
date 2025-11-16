// ARCHIVO: view/Vacaciones/mntvacaciones.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var dias_disponibles_global = 0; // Variable global para almacenar los días disponibles
var tablaVacaciones; // Variable global para el DataTable

$(document).ready(function() {
    // 1. Inicializar la vista del usuario logueado y obtener saldo
    getResumenVacaciones();
    
    // 2. Calcular días hábiles automáticamente al cambiar las fechas en el modal
    $('#vac_fecha_inicio, #vac_fecha_fin').on('change', function() {
        calcularDiasHabiles();
        validarDias();
    });
    
    // 3. Abrir modal
    $('#btnNuevaSolicitud').click(function() {
        $('#modalSolicitudVacacionesLabel').html('Solicitar Vacaciones');
        $('#solicitud_form')[0].reset();
        $('#vac_id').val('');
        // Mostrar los días disponibles en el modal
        $('#dias_disponibles_modal').text(dias_disponibles_global);
        $('#max_dias_permitidos').text(dias_disponibles_global);
        $('#alerta_dias_insuficientes').addClass('d-none'); // Ocultar alerta
        $('#modalSolicitudVacaciones').modal('show');
    });
    
    // 4. Manejar el evento submit del formulario de solicitud
    $('#solicitud_form').on('submit', function(e){
        guardarSolicitud(e);
    });
    
    // 5. Inicializar el DataTable para listar solicitudes (NUEVO)
    listarSolicitudes();
});


/* ========================================================================= */
/* FUNCIONES DE DATA Y RESUMEN                                               */
/* ========================================================================= */

function getResumenVacaciones() {
    $.post(ruta_controlador + 'get_resumen_dias', function(data) {
        const resumen = JSON.parse(data);
        
        if (resumen.error) {
            console.error(resumen.error);
            return;
        }

        // Almacenamos el valor globalmente
        dias_disponibles_global = parseInt(resumen.dias_disponibles);

        // Mostrar datos en el dashboard
        $('#antiguedad_anos').text(resumen.antiguedad_anos + ' años');
        $('#dias_generados').text(resumen.dias_generados + ' días');
        $('#dias_usados').text(resumen.dias_usados + ' días');
        $('#dias_disponibles').text(resumen.dias_disponibles + ' días');
        $('#fecha_ingreso_planta_info').text(resumen.fecha_ingreso_planta);
    }).fail(function() {
        // Manejo de error de la conexión
        swal.fire("Error", "No se pudo obtener el resumen de vacaciones.", "error");
    });
}

/* ========================================================================= */
/* LÓGICA DE VALIDACIÓN Y CÁLCULO DE DÍAS HÁBILES                             */
/* ========================================================================= */

/**
 * Valida que los días hábiles solicitados no excedan los días disponibles.
 */
function validarDias() {
    const dias_solicitados_habiles = parseInt($('#vac_dias_habiles').val());

    if (dias_solicitados_habiles > dias_disponibles_global) {
        $('#alerta_dias_insuficientes').removeClass('d-none');
        $('#btnGuardarSolicitud').prop('disabled', true);
    } else {
        $('#alerta_dias_insuficientes').addClass('d-none');
        $('#btnGuardarSolicitud').prop('disabled', false);
    }
}

/**
 * Calcula el número de días naturales y hábiles (Lunes a Viernes) entre dos fechas.
 */
function calcularDiasHabiles() {
    const fechaInicio = $('#vac_fecha_inicio').val();
    const fechaFin = $('#vac_fecha_fin').val();
    
    // ... (El resto de la función calcularDiasHabiles es la misma que definimos en el paso anterior) ...
    if (!fechaInicio || !fechaFin) {
        $('#vac_dias_habiles').val(0);
        $('#vac_dias_solicitados').val(0);
        return;
    }

    let inicio = new Date(fechaInicio + 'T00:00:00'); // Aseguramos zona horaria para precisión
    let fin = new Date(fechaFin + 'T00:00:00');
    
    if (inicio > fin) {
        $('#vac_dias_habiles').val(0);
        $('#vac_dias_solicitados').val(0);
        return;
    }
    
    let diasHabiles = 0;
    let diasNaturales = 0;
    
    let fechaActual = inicio;
    // Iteramos día por día (Ajuste para iterar correctamente en JS)
    while (fechaActual <= fin) {
        let diaDeLaSemana = fechaActual.getDay(); 
        
        // Días hábiles: Lunes(1) a Viernes(5)
        if (diaDeLaSemana !== 0 && diaDeLaSemana !== 6) {
            diasHabiles++;
        }
        
        diasNaturales++;
        
        // Avanzar un día
        fechaActual = new Date(fechaActual.setDate(fechaActual.getDate() + 1));
    }
    
    $('#vac_dias_habiles').val(diasHabiles);
    $('#vac_dias_solicitados').val(diasNaturales);
}

/* ========================================================================= */
/* FUNCIONES DE LISTADO Y DATATABLE                                          */
/* ========================================================================= */

function listarSolicitudes() {
    tablaVacaciones = $('#vacaciones_data').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [ 'copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5' ],
        "ajax": {
            url: ruta_controlador + 'listar_solicitudes_por_usuario',
            type: "post",
            dataType: "json",
            error: function(e) {
                console.log(e.responseText);
            }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "autoWidth": false,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No hay solicitudes registradas",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
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
        }
    }).DataTable();
}

function cancelarSolicitud(vac_id) {
    // Implementación pendiente
    swal.fire("En proceso", "Función para cancelar la solicitud " + vac_id + " pendiente de implementar.", "info");
}

function verDetalle(vac_id) {
    // Implementación pendiente
    swal.fire("En proceso", "Función para ver el detalle de la solicitud " + vac_id + " pendiente de implementar.", "info");
}

/* ========================================================================= */
/* LÓGICA DE GUARDADO DE SOLICITUD                                            */
/* ========================================================================= */

function guardarSolicitud(e) {
    e.preventDefault();
    
    const dias_solicitados_habiles = parseInt($('#vac_dias_habiles').val());

    if (dias_solicitados_habiles <= 0) {
         swal.fire("Advertencia", "Debe seleccionar un rango de fechas válido con días hábiles.", "warning");
         return;
    }

    if (dias_solicitados_habiles > dias_disponibles_global) {
        // La validación ya debería haber deshabilitado el botón, pero es un buen control de seguridad
         swal.fire("Error", "No tiene días disponibles suficientes para esta solicitud.", "error");
         return;
    }
    
    // Si la validación pasa, serializamos y enviamos
    var formData = new FormData($('#solicitud_form')[0]);
    formData.append('op', 'guardar_solicitud'); 

    $.ajax({
        url: '../../controller/Vacaciones.php?op=guardar_solicitud',
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(datos) {
            if (datos === "ok") {
                $('#modalSolicitudVacaciones').modal('hide');
                swal.fire("¡Éxito!", "Solicitud enviada para aprobación.", "success");
                getResumenVacaciones(); // Recarga el resumen para actualizar el saldo
                tablaVacaciones.ajax.reload(); // <--- AÑADIR ESTA LÍNEA
            } else {
                swal.fire("Error", datos, "error"); // Mostrar errores del servidor
            }
        },
        error: function() {
            swal.fire("Error", "Ocurrió un error en la comunicación con el servidor.", "error");
        }
    });
}

function cancelarSolicitud(vac_id) {
    swal.fire({
        title: "¿Estás seguro?",
        text: "¡La solicitud de vacaciones será CANCELADA y no podrá ser reactivada!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No, mantener",
    }).then((result) => {
        if (result.value) {
            $.post(ruta_controlador + 'cancelar_solicitud', {vac_id: vac_id}, function(data) {
                if (data === "ok") {
                    swal.fire("Cancelada", "La solicitud ha sido cancelada.", "success");
                    tablaVacaciones.ajax.reload(); // Recargar tabla
                    getResumenVacaciones(); // Recalcular saldo
                } else {
                    swal.fire("Error", data, "error");
                }
            }).fail(function() {
                swal.fire("Error", "Ocurrió un error en la comunicación con el servidor.", "error");
            });
        }
    });
}