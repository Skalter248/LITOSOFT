// ARCHIVO: view/Vacaciones/mntvacaciones.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var dias_disponibles_global = 0; // Variable global para almacenar los días disponibles
var tablaVacaciones; // Variable global para el DataTable
var calendar;
var modo_adelanto_activo = false;


$(document).ready(function() {
    // 1. Inicializar la vista del usuario logueado y obtener saldo
    getResumenVacaciones();
    
    // 2. Calcular días hábiles automáticamente al cambiar las fechas en el modal
    $('#vac_fecha_inicio, #vac_fecha_fin').on('change', function() {
        calcularDiasHabiles();
    });

    // 3. Abrir modal de solicitud
    $('#btnNuevaSolicitud').click(function() {
        $('#modalSolicitudVacacionesLabel').html('Solicitar Vacaciones'); 
        $('#solicitud_form')[0].reset();
        $('#vac_id').val('');

        // 🔥 CRÍTICO: REINICIAR BANDERA Y LIMPIAR ESTADO
        modo_adelanto_activo = false; 
        
        $('#vac_dias_habiles').val(0); 
        $('#vac_dias_solicitados').val(0); 
        
        // LIMPIEZA VISUAL ABSOLUTA AL ABRIR (Usando .hide() para forzar display: none)
        $('#alerta_adelanto_dias').hide();
        $('#adelanto_justificacion_container').hide();
        $('#vac_justificacion_adelanto').prop('required', false);
        
        // Limpieza de otras alertas
        $('#alerta_restriccion_antiguedad').hide().empty();
        $('#alerta_dias_insuficientes').hide().empty();

        // Mostrar los días disponibles en el modal
        $('#dias_disponibles_modal').text(dias_disponibles_global);
        
        // --- LÓGICA DE BLOQUEO Y ADELANTO ---
        const saldo_cero_o_negativo = dias_disponibles_global <= 0;
        
        // Habilitar/Deshabilitar campos de fecha
        $('#vac_fecha_inicio, #vac_fecha_fin').prop('disabled', saldo_cero_o_negativo);

        if (saldo_cero_o_negativo) {
            // Saldo cero o negativo: Bloqueado al inicio
            $('#btnGuardarSolicitud').prop('disabled', true); 
            
            // Insertar el botón "Quiero Adelantar"
            if ($('#btnHabilitarAdelanto').length === 0) {
                 $('#modalSolicitudVacacionesLabel').after('<button type="button" class="btn btn-sm btn-warning ml-3" id="btnHabilitarAdelanto">Quiero Adelantar Días</button>');
            }

            $('#alerta_info_disponibles').hide();
        } else {
        // Saldo positivo: Solicitud estándar
        $('#btnGuardarSolicitud').prop('disabled', true); 
        $('#alerta_info_disponibles').show();
        
        // CLAVE: Asegurar que el botón de adelanto se elimina si hay saldo.
        $('#btnHabilitarAdelanto').remove(); 
        
        validarDias(); // Se llama para inicializar el estado del botón Guardar
    }

        // ⭐ LÍNEA CRÍTICA 1: Desactivar el manejo de teclado de Bootstrap
        $('#modalSolicitudVacaciones').modal({ keyboard: false });
        
        // Mostrar modal (se llama show después de la inicialización con keyboard: false)
        $('#modalSolicitudVacaciones').modal('show');

    });
    
    // ⭐⭐⭐ SOLUCIÓN CRÍTICA FINAL: GARANTÍA DE FOCO Y Z-INDEX (Triple-Layer + tabindex) ⭐⭐⭐


        // --- EVENTO CLAVE: CLICK EN "Quiero Adelantar Días" ---
        $(document).on('click', '#btnHabilitarAdelanto', function() {
        modo_adelanto_activo = true; 
        $('#vac_fecha_inicio, #vac_fecha_fin').prop('disabled', false);
        
        $(this).hide().prop('disabled', true); // Oculta el botón

        $('#alerta_adelanto_dias').show(); 
        $('#adelanto_justificacion_container').show();
        
        // ⭐⭐⭐ REEMPLAZO CRÍTICO DEL CAMPO ⭐⭐⭐
        const justContainer = $('#adelanto_justificacion_container');
        
        // 1. Crear el HTML de reemplazo con estilos inline definitivos
        const newHtml = `
            <label for="vac_justificacion_adelanto">Justificación Adelanto:</label>
            <textarea 
                class="form-control"
                id="vac_justificacion_adelanto" 
                name="vac_justificacion_adelanto" 
                rows="3" 
                required 
                style="z-index: 999999 !important; pointer-events: auto !important; position: relative !important; background-color: #ffffff;"
            ></textarea>
        `;
        
        // 2. Reemplazar el contenido del contenedor
        justContainer.html(newHtml);
        
        // 3. Obtener la nueva referencia al campo
        const justInput = $('#vac_justificacion_adelanto');

        // 4. Aplicar el Focus Trap (solo por si acaso)
        justInput.attr('tabindex', '0');
        justInput.focus();
        
        // 5. Aplicar la anulación de propagación inmediata al nuevo elemento
        justInput.off('keydown.forcewrite').on('keydown.forcewrite', function(e) {
            e.stopImmediatePropagation();
            if (e.which === 27) { // 27 es ESC
               e.preventDefault(); 
            }
        });

        calcularDiasHabiles(); 
    });

    // ⭐⭐ SOLUCIÓN DEFINITIVA: Desconexión de Datepicker y Anulación de Propagación ⭐⭐
   $('#modalSolicitudVacaciones').on('shown.bs.modal', function() {
        $('.swal2-container').remove(); 
        $(this).off('keydown.bs.modal keyup.bs.modal');
        
        if (modo_adelanto_activo) {
            const justInput = $('#vac_justificacion_adelanto');
            
            // Forzar foco dos veces
            justInput.focus(); 
            setTimeout(() => { justInput.focus(); }, 150); 
            
            // Focus Trap (Se mantiene como respaldo)
            $(this).on('focusout.focustrap', function(e) {
                setTimeout(() => {
                    if (document.activeElement !== justInput[0]) {
                         justInput.focus();
                    }
                }, 10); 
            });

        } else {
            // Limpieza al cerrar
            $(this).off('focusout.focustrap');
            $('#vac_justificacion_adelanto').off('keydown.forcewrite');
        }
    });

    // 4. Botón para abrir el Calendario/Modal
    $('#btnVerCalendario').click(function() {
        $('#modalCalendario').modal('show');
    });

    // 5. Manejar el evento submit del formulario de solicitud
    $('#solicitud_form').on('submit', function(e){
        e.preventDefault(); // ⭐ CLAVE 1: Detiene el envío nativo del formulario (la recarga de página)
        guardar_solicitud(); // ⭐ CLAVE 2: Llama a la función con el nombre correcto y sin el argumento 'e'
    });

    // 6. Inicializar el DataTable para listar solicitudes
    listarSolicitudes();

    // 7. Evento para inicializar FullCalendar CUANDO EL MODAL YA ES VISIBLE
    $('#modalCalendario').on('shown.bs.modal', function() {
        // Solo inicializamos FullCalendar la primera vez que se abre el modal
        if (!calendar) {
            inicializarFullCalendar();
        }
        // Forzamos a FullCalendar a recalcular sus dimensiones después de que el modal se muestra
        if (calendar) {
             calendar.updateSize(); 
        }
        cargarDetalleFechasTabla(); // Cargar la tabla de detalle
    });
    // 8. Botón para imprimir detalles
    $('#btnImprimirDetalles').click(function() {
        imprimirDetalles(); 
    });

    // 9. Adelantar vacaciones
    $('#vac_fecha_inicio, #vac_fecha_fin').on('change', function() {
        calcularDiasHabiles();
        validarDias();
    });
    
    // NUEVO: Validar justificación al escribir en ella
    $('#vac_justificacion_adelanto').on('input', validarDias);

});

/* ========================================================================= */
/* FUNCIONES CRÍTICAS */
/* ========================================================================= */

/**
 * Valida que los días hábiles solicitados no excedan los días disponibles, 
 * controlando la visibilidad del modo Adelanto con la bandera global.
 */
function validarDias() {
    const dias_solicitados = parseInt($('#vac_dias_habiles').val()) || 0;
    const dias_disponibles = dias_disponibles_global;
    
    const btnGuardar = $('#btnGuardarSolicitud');
    const alerta_insuficientes = $('#alerta_dias_insuficientes'); 
    const alerta_adelanto = $('#alerta_adelanto_dias');           
    const justificacionContainer = $('#adelanto_justificacion_container'); 
    const justificacionInput = $('#vac_justificacion_adelanto');

    // --- 1. Limpieza total (Estado por defecto) ---
    alerta_insuficientes.hide(); 
    alerta_adelanto.hide();      
    justificacionContainer.hide(); 
    
    justificacionInput.prop({
        'required': false,
        'disabled': false, 
        'readonly': false 
    }).val('');
    
    btnGuardar.prop('disabled', false); 

    if (dias_solicitados === 0) {
        btnGuardar.prop('disabled', true);
        return;
    }
    
    // ⭐⭐⭐ 2. VALIDACIÓN: DÍAS INSUFICIENTES (Bloqueo Estricto) ⭐⭐⭐
    if (dias_solicitados > dias_disponibles) {
        
        // Bloqueo estricto solo si NO estamos en modo ADELANTO
        if (!modo_adelanto_activo) {
            
            alerta_insuficientes.show(); 
            btnGuardar.prop('disabled', true);
            justificacionInput.prop('disabled', true); 
            
            swal.fire({
                title: "🚫 Días Insuficientes",
                text: `No puedes solicitar ${dias_solicitados} días hábiles. Tu saldo disponible es de solo ${dias_disponibles} días. Si deseas solicitar un adelanto, debes usar la opción 'Solicitar Adelanto'.`,
                icon: "error"
            });
            return; 
        }
        
        // --- 2b. MODO ADELANTO ACTIVO ---
        if (modo_adelanto_activo) {
            alerta_adelanto.show(); 
            justificacionContainer.show(); 
            
            // Habilitación total para escribir (con inyección de CSS)
            justificacionInput.prop({
                'required': true,
                'disabled': false, 
                'readonly': false 
            }).css({
                'z-index': '999999',
                'pointer-events': 'auto',
                'position': 'relative'
            });
            btnGuardar.prop('disabled', false);
            return;
        }
    }
    
    // --- 3. CONDICIÓN NORMAL (OK) ---
    btnGuardar.prop('disabled', false);
}
/* ========================================================================= */
/* FUNCIONES DE DATA Y RESUMEN                                               */
/* ========================================================================= */



function getResumenVacaciones() {
    const usu_id = $('#usu_id').val(); 
    
    // Usamos $.post que es un atajo para $.ajax con método POST
    $.post(ruta_controlador + 'get_resumen_dias', { usu_id: usu_id }, function(data) {
        let resumen;
        try {
            resumen = JSON.parse(data);
        } catch (e) {
            console.error("Error al parsear el resumen de vacaciones:", data);
            return;
        }

        if (resumen.error) {
            console.error(resumen.error);
            return;
        }

        // 1. Almacenamos los valores clave del modelo
        dias_disponibles_global = parseInt(resumen.dias_disponibles) || 0;
        const dias_generados_total = parseInt(resumen.dias_generados) || 0;
        const dias_usados_total = parseInt(resumen.dias_usados) || 0;
        const antiguedad_anos_valor = resumen.antiguedad_anos || 0; 
        const motivo_restriccion = resumen.motivo_restriccion || ''; 

        // 2. Mostrar datos en el dashboard
        $('#antiguedad_anos').text(antiguedad_anos_valor + ' años');
        $('#dias_generados').text(dias_generados_total + ' días');
        $('#dias_usados').text(dias_usados_total + ' días');
        $('#fecha_ingreso_planta_info').text(resumen.fecha_ingreso_planta || 'Calculando...');

        // 3. Mostrar Saldo Disponible
        $('#dias_disponibles').text(dias_disponibles_global);
        $('#dias_disponibles_modal').text(dias_disponibles_global);
        $('#max_dias_permitidos').text(dias_disponibles_global);


        // --- Lógica para Días Adelantados (Deuda PENDIENTE) ---
        // CLAVE: La deuda pendiente es el saldo negativo BRUTO (Generados - Usados)
        const saldo_bruto = dias_generados_total - dias_usados_total;
        const saldo_deuda_pendiente = saldo_bruto < 0 ? Math.abs(saldo_bruto) : 0;
        
        if (saldo_deuda_pendiente > 0) {
            // Si el saldo bruto es negativo, significa que hay deuda pendiente.
            $('#dias_adelantados_span_dashboard').html(`(-${saldo_deuda_pendiente} Adelantados)`).removeClass('d-none').addClass('text-danger font-weight-bold');
            $('#dias_adelantados_modal').text(saldo_deuda_pendiente);
        } else {
             // Si el saldo bruto es cero o positivo, la deuda está compensada o no existe.
             $('#dias_adelantados_span_dashboard').empty().addClass('d-none'); 
             $('#dias_adelantados_modal').text('0');
        }

        // 4. Resaltar saldo disponible si es negativo
        if (dias_disponibles_global < 0) {
            $('#dias_disponibles').addClass('text-danger');
            $('#dias_disponibles_modal').addClass('text-danger');
        } else {
            $('#dias_disponibles').removeClass('text-danger');
            $('#dias_disponibles_modal').removeClass('text-danger');
        }
        
        // 5. LÓGICA DE RESTRICCIÓN POR ANTIGÜEDAD
        const alertaRestriccion = $('#alerta_restriccion_antiguedad');
        
        if (motivo_restriccion.length > 0) {
            alertaRestriccion.removeClass('d-none alert-success').addClass('alert-warning').html(`<strong>Advertencia:</strong> ${motivo_restriccion}`);
        } else {
            alertaRestriccion.addClass('d-none').empty();
        }
        // 6. LÓGICA DE BLOQUEO DE SOLICITUD ESTÁNDAR
        const btnNuevaSolicitud = $('#btnNuevaSolicitud');

        if (dias_disponibles_global <= 0) {
            // Si el saldo es cero o negativo, forzamos al modo "Adelanto"
            btnNuevaSolicitud.text('Solicitar Adelanto de Días').removeClass('btn-success').addClass('btn-warning');
        } else {
            // Saldo positivo, modo "Solicitud Normal"
            btnNuevaSolicitud.text('Nueva Solicitud').removeClass('btn-warning').addClass('btn-success');
        }
        
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Fallo la llamada a get_resumen_dias:", textStatus, errorThrown, jqXHR.responseText);
        swal.fire("Error", "No se pudo obtener el resumen de vacaciones.", "error");
    });
}
/* ========================================================================= */
/* LÓGICA DE VALIDACIÓN Y CÁLCULO DE DÍAS HÁBILES                             */
/* ========================================================================= */

/**
 * Calcula el número de días naturales y hábiles (Lunes a Viernes) entre dos fechas.
 */
function calcularDiasHabiles() {
    const fechaInicioStr = $('#vac_fecha_inicio').val();
    const fechaFinStr = $('#vac_fecha_fin').val();
    const diasSolicitadosInput = $('#vac_dias_solicitados');
    const diasHabilesInput = $('#vac_dias_habiles');

    if (!fechaInicioStr || !fechaFinStr) {
        diasSolicitadosInput.val(0);
        diasHabilesInput.val(0);
        $('#dias_solicitados_info').text('0');
        validarDias(); // ⬅️ Llamada para limpiar estado inicial
        return;
    }

    const fechaInicio = new Date(fechaInicioStr + 'T00:00:00');
    const fechaFin = new Date(fechaFinStr + 'T00:00:00');
    
    // Aseguramos que la fecha de fin sea posterior o igual a la de inicio
    if (fechaInicio > fechaFin) {
        diasSolicitadosInput.val(0);
        diasHabilesInput.val(0);
        $('#dias_solicitados_info').text('Fechas inválidas');
        validarDias(); // ⬅️ Llamada para bloquear con fechas inválidas
        return;
    }

    let totalDias = 0;
    let diasHabiles = 0;
    let currentDate = new Date(fechaInicio.getTime());
    
    // Iterar día por día
    while (currentDate <= fechaFin) {
        totalDias++;
        const dia = currentDate.getDay(); // 0 = Domingo, 6 = Sábado
        
        if (dia !== 0 && dia !== 6) { 
            diasHabiles++;
        }
        
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // 1. ACTUALIZAR LOS CAMPOS
    $('#vac_dias_solicitados').val(totalDias);
    $('#vac_dias_habiles').val(diasHabiles);
    $('#dias_solicitados_info').text(diasHabiles);
    
    // 2. ⭐ LLAMADA CRÍTICA: Se dispara la validación.
    validarDias();
}

/* ========================================================================= */
/* FUNCIONES DE LISTADO Y DATATABLE                                          */
/* ========================================================================= */

function listarSolicitudes() {
    // 1. Obtener el rol_id. Lo usamos para la restricción de permisos.
    // **NOTA**: Asumo que tienes un input hidden con id="rol_id" en tu HTML.
    const rol_id = $('#rol_id').val() ?? '3'; // Usamos '3' (Empleado) si no se define.
    
    tablaVacaciones = $('#vacaciones_data').DataTable({
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
        // ¡¡AÑADIR ESTA DEFINICIÓN DE COLUMNAS!! Es la clave para el botón.
        "columns": [
            { data: 0 }, // Índice 0: ID Solicitud (vac_id)
            { data: 1 }, // Índice 1: Fecha Inicio
            { data: 2 }, // Índice 2: Fecha Fin
            { data: 3 }, // Índice 3: Días Hábiles
            { data: 4 }, // Índice 4: Estado (vac_estado)
            { data: 5 }, // Índice 5: Fecha Solicitud
            { data: 6 }, // Índice 6: Aprobador
            { // Índice 7: Columna de Acciones (Botones)
                data: 7, 
                // Usamos 'render' para modificar el contenido de esta columna
                render: function(data, type, row) {
                    let botones = data; // Botones existentes (Ver o Cancelar)
                    const estado = row[4].includes('Aprobada') ? 'Aprobada' : row[4]; // Obtenemos el estado (row[4])

                    // CONDICIÓN 1: Solo si el usuario es Rol ID 2 (Jefe/Admin)
                    if (rol_id === '2') {
                        // CONDICIÓN 2: Solo si el estado es Aprobada (Necesario para impresión de documento final)
                        // NOTA: El estado row[4] puede contener el badge HTML, así que buscamos la palabra.
                        if (row[4].includes('Aprobada')) { 
                            // row[0] contiene el vac_id para la función de impresión
                            botones += `
                                <button type="button" 
                                    onClick="imprimirSolicitudDetalle(${row[0]});" 
                                    class="btn btn-info btn-sm ml-1"
                                    title="Imprimir Reporte Aprobado">
                                    <i class="fa fa-print"></i>
                                </button>`;
                        }
                    }
                    return botones;
                }
            }
        ],
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
    });
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

function guardar_solicitud() {
    var justificacionInput = $('#vac_justificacion_adelanto');
    var justificacionContainer = $('#adelanto_justificacion_container');
    
    // 1. Manejo del atributo 'required' para evitar el error 'not focusable'
    // Si el contenedor de justificación está oculto, quitamos el atributo 'required' 
    // en caso de que validarDias() no lo haya hecho a tiempo.
    if (justificacionContainer.is(':hidden') && justificacionInput.prop('required')) {
         justificacionInput.prop('required', false);
    }
    
    // 2. Validación manual: Si el campo es requerido Y está vacío (Adelanto sin justificación)
    if (justificacionInput.prop('required') && justificacionInput.val().trim() === '') {
        swal.fire("Atención", "Debe ingresar una justificación para solicitar el adelanto de días.", "warning");
        // Aseguramos que el campo sea enfocable si no lo estaba
        justificacionInput.focus();
        return; // Detener el envío
    }
    
    // 3. Preparar datos para el envío AJAX
    var formData = new FormData($("#solicitud_form")[0]);

    // Asegurarse de que el campo vacío (en modo NO-adelanto) se envíe como cadena vacía, no como NULL
    if (formData.get('vac_justificacion_adelanto') === '') {
        formData.set('vac_justificacion_adelanto', '');
    }

    // 4. Enviar AJAX
    $.ajax({
        url: ruta_controlador + 'guardar_solicitud',
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(datos) {
            // El servidor devuelve "ok" o el mensaje de error en texto plano
            if (datos.trim() === "ok") { 
                $('#modalSolicitudVacaciones').modal('hide');
                swal.fire("Éxito", "Solicitud enviada para aprobación.", "success");
                getResumenVacaciones(); // Actualizar el dashboard
                tablaVacaciones.ajax.reload();
            } else {
                // Muestra el texto exacto devuelto por el servidor
                swal.fire("Error del Servidor", datos, "error"); 
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
             swal.fire("Error de Conexión", "No se pudo conectar con el servidor.", "error"); 
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

/* ========================================================================= */
/* FUNCIONES DEL CALENDARIO Y DETALLE DE FECHAS (DEBEN SER GLOBALES)         */
/* ========================================================================= */

// 1. FUNCIÓN PARA INICIALIZAR EL CALENDARIO (Global)
function inicializarFullCalendar() {
    const calendarEl = document.getElementById('calendar_container');
    
    // Si calendar ya existe, no hacemos nada (para evitar inicializaciones múltiples)
    if (calendar) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 600, 
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        events: {
            url: ruta_controlador + 'get_eventos_calendario', // Llama al nuevo endpoint del controlador
            method: 'POST',
            failure: function() {
                console.error('Error al cargar eventos del calendario.');
            }
        },
        eventClick: function(info) {
            swal.fire({
                title: info.event.title,
                text: "Estado: " + info.event.extendedProps.estado + 
                      "\nInicio: " + info.event.start.toLocaleDateString('es-ES') +
                      "\nFin: " + (info.event.end ? (new Date(info.event.end.getTime() - 86400000)).toLocaleDateString('es-ES') : info.event.start.toLocaleDateString('es-ES')),
                icon: 'info'
            });
        }
    });
    calendar.render();
}

// 2. FUNCIÓN PARA CARGAR LA TABLA DE DETALLE (Global)
function cargarDetalleFechasTabla() { 
    $.ajax({
        url: ruta_controlador + 'listar_solicitudes_por_usuario', 
        type: "post",
        dataType: "json",
        success: function(response) {
            let html = '';
            $('#tabla_detalle_fechas tbody').empty(); 

            response.aaData.forEach(item => {
                const fecha_inicio = item[1]; 
                const fecha_fin = item[2];
                const dias_habiles = item[3];
                const estado_badge = item[4]; 

                html += `<tr>
                            <td>${fecha_inicio}</td>
                            <td>${fecha_fin}</td>
                            <td>${dias_habiles}</td>
                            <td>${estado_badge}</td>
                        </tr>`;
            });

            $('#tabla_detalle_fechas tbody').html(html);
        },
        error: function(e) {
            console.error("Error al cargar detalle de fechas:", e.responseText);
        }
    });
}

    /* ========================================================================= */
    /* FUNCIÓN DE IMPRESIÓN POR SOLICITUD                                        */
    /* ========================================================================= */

    function imprimirSolicitudDetalle(vac_id) {
    // Usamos $.post con 'json' como dataType
    $.post(ruta_controlador + 'get_detalles_solicitud', { vac_id: vac_id }, function(detalles) {
        
        if (detalles.error) {
            swal.fire("Error del Servidor", detalles.error, "error");
            return;
        }
        
        // --- Variables auxiliares ---
        const estado_solicitud = detalles.vac_estado;
        const nombre_completo_solicitante = `${detalles.nombre_solicitante} ${detalles.apellido_solicitante}`;
        const nombre_completo_aprobador = detalles.nombre_aprobador 
            ? `${detalles.nombre_aprobador} ${detalles.apellido_aprobador}` 
            : 'Pendiente / N/A';
        const fecha_aprobacion = detalles.vac_fecha_aprobacion_f || 'N/A';
        const dias_disponibles = detalles.dias_disponibles || 0;
        const dias_usados = detalles.dias_usados || 0;
        
        // --- Constantes del Documento ---
        const CODIGO_DOCUMENTO = 'SGC001';
        const NIVEL_REVISION = '154';
        const RUTA_LOGO = '../../public/Logo Lito.jpg'; // Ruta relativa desde la vista/Vacaciones


        // --- Generación del Contenido Imprimible (Plantilla Corporativa) ---
        const contenidoImprimible = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Reporte de Vacaciones - Solicitud N°${vac_id}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; font-size: 13px; }
                    .header-doc { 
                        display: flex; 
                        justify-content: space-between; 
                        align-items: center; 
                        border: 1px solid #000; 
                        padding: 5px; 
                        margin-bottom: 20px;
                        font-size: 11px;
                    }
                    .header-doc div { width: 33%; text-align: center; }
                    .header-doc .logo img { max-height: 40px; float: left; }
                    .header-doc .title { font-weight: bold; font-size: 14px; }
                    
                    h3 { color: #555; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 20px; }
                    .data-container { display: table; width: 100%; border-collapse: collapse; }
                    .data-row { display: table-row; }
                    .data-label, .data-value { display: table-cell; padding: 4px 10px 4px 0; border-bottom: 1px dotted #eee; }
                    .data-label { font-weight: bold; width: 250px; }
                    
                    .badge-estado { padding: 4px 8px; border-radius: 4px; color: #fff; font-weight: bold; font-size: 12px; }
                    .badge-Aprobada { background-color: #28a745; }
                    .badge-Rechazada { background-color: #dc3545; }
                    .badge-Pendiente { background-color: #ffc107; color: #333; }
                    .badge-Cancelada { background-color: #6c757d; }

                    .signatures { display: flex; justify-content: space-around; margin-top: 60px; width: 100%; }
                    .signature-box { 
                        width: 40%; 
                        text-align: center; 
                        border-top: 1px solid #000; 
                        padding-top: 5px; 
                        font-size: 12px;
                    }
                </style>
            </head>
            <body>
                
                <div class="header-doc">
                    <div class="logo"><img src="${RUTA_LOGO}" alt="Logo Empresa"></div>
                    <div class="title">SOLICITUD DE VACACIONES</div>
                    <div class="code-rev">
                        <div>**CÓDIGO:** ${CODIGO_DOCUMENTO}</div>
                        <div>**REVISIÓN:** ${NIVEL_REVISION}</div>
                    </div>
                </div>

                <h3>Información de la Solicitud (ID N°${detalles.vac_id})</h3>
                <div class="data-container">
                    <div class="data-row"><span class="data-label">Estado de Solicitud:</span> <span class="data-value"><span class="badge-estado badge-${estado_solicitud}">${estado_solicitud}</span></span></div>
                    <div class="data-row"><span class="data-label">Fecha de Solicitud:</span> <span class="data-value">${detalles.vac_fecha_solicitud_f}</span></div>
                    <div class="data-row"><span class="data-label">Solicitante:</span> <span class="data-value">${nombre_completo_solicitante}</span></div>
                    <div class="data-row"><span class="data-label">Fecha de Ingreso a Planta:</span> <span class="data-value">${detalles.fecha_ingreso_planta}</span></div>
                </div>

                <h3>Detalle de Fechas y Días</h3>
                <div class="data-container">
                    <div class="data-row"><span class="data-label">Período Solicitado:</span> <span class="data-value">Del **${detalles.vac_fecha_inicio_f}** al **${detalles.vac_fecha_fin_f}**</span></div>
                    <div class="data-row"><span class="data-label">Días Hábiles Solicitados:</span> <span class="data-value">${detalles.vac_dias_habiles} días</span></div>
                    <div class="data-row"><span class="data-label">Días Naturales (Total):</span> <span class="data-value">${detalles.vac_dias_solicitados} días</span></div>
                    <div class="data-row"><span class="data-label">Días Disponibles (Previo):</span> <span class="data-value">${dias_disponibles} días</span></div>
                    <div class="data-row"><span class="data-label">Días Usados (Acumulado):</span> <span class="data-value">${dias_usados} días</span></div>
                </div>
                
                <h3>Proceso de Aprobación</h3>
                <div class="data-container">
                    <div class="data-row"><span class="data-label">Aprobado por:</span> <span class="data-value">${nombre_completo_aprobador}</span></div>
                    <div class="data-row"><span class="data-label">Fecha de Proceso:</span> <span class="data-value">${fecha_aprobacion}</span></div>
                    ${detalles.vac_motivo_rechazo ? `<div class="data-row"><span class="data-label">Motivo de Rechazo:</span> <span class="data-value" style="color:red;">${detalles.vac_motivo_rechazo}</span></div>` : ''}
                </div>

                <div class="signatures">
                    <div class="signature-box">
                        ${nombre_completo_solicitante}<br>
                        **Firma del Solicitante**
                    </div>
                    <div class="signature-box">
                        ${nombre_completo_aprobador}<br>
                        **Firma del Aprobador**
                    </div>
                </div>

            </body>
            </html>
        `;

        const ventanaImpresion = window.open('', '', 'height=600,width=800');
        ventanaImpresion.document.write(contenidoImprimible);
        ventanaImpresion.document.close();
        ventanaImpresion.print();
        
    }, 'json').fail(function() {
        swal.fire("Error", "Ocurrió un error de red al contactar al servidor. Revise F12.", "error");
    });
}


function guardar_solicitud() {
    console.log("GUARDAR: 1. Inicio de la función guardar_solicitud.");
    var justificacionInput = $('#vac_justificacion_adelanto');
    var justificacionContainer = $('#adelanto_justificacion_container');
    var btnGuardar = $('#btnGuardarSolicitud');
    
    // 1. Manejo del atributo 'required' para evitar el error 'not focusable'
    if (justificacionContainer.is(':hidden') && justificacionInput.prop('required')) {
         justificacionInput.prop('required', false);
         console.log("GUARDAR: Campo de justificación oculto, required deshabilitado.");
    }
    
    // 2. Validación manual: Si el campo es requerido Y está vacío (Adelanto sin justificación)
    if (justificacionInput.prop('required') && justificacionInput.val().trim() === '') {
        console.warn("GUARDAR: 2. Validación fallida - Justificación requerida y vacía.");
        swal.fire("Atención", "Debe ingresar una justificación para solicitar el adelanto de días.", "warning");
        justificacionInput.focus();
        return; // Detener el envío
    }
    
    console.log("GUARDAR: 3. Validación de Justificación pasada. Preparando AJAX.");

    var formData = new FormData($("#solicitud_form")[0]);

    // Asegurarse de que el campo vacío (en modo NO-adelanto) se envíe como cadena vacía
    if (formData.get('vac_justificacion_adelanto') === '') {
        formData.set('vac_justificacion_adelanto', '');
    }

    // 3. Enviar AJAX
    $.ajax({
        url: ruta_controlador + 'guardar_solicitud',
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function() {
            console.log("AJAX: 4. Enviando solicitud a " + this.url);
            // Mostrar estado de carga en el botón
            btnGuardar.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
        },
        success: function(datos) {
            console.log("AJAX: 5. Respuesta recibida. Datos:", datos);
            btnGuardar.prop('disabled', false).html('Enviar Solicitud');

            // CLAVE: El servidor DEBE devolver la cadena "ok" y nada más para el éxito
            if (datos && datos.trim() === "ok") { 
                $('#modalSolicitudVacaciones').modal('hide');
                swal.fire("Éxito", "Solicitud enviada para aprobación.", "success");
                getResumenVacaciones();
                if (typeof tablaVacaciones !== 'undefined' && tablaVacaciones) {
                    tablaVacaciones.ajax.reload();
                }
            } else {
                var error_msg = datos ? datos : "Error desconocido del servidor o respuesta vacía.";
                swal.fire("Error del Servidor", error_msg, "error"); 
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
             console.error("AJAX: 6. Error de conexión o servidor.", textStatus, errorThrown, jqXHR);
             btnGuardar.prop('disabled', false).html('Enviar Solicitud');
             swal.fire("Error de Conexión", "No se pudo conectar con el servidor. Revise la consola para más detalles.", "error"); 
        }
    });
}

