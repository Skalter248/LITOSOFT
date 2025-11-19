// ARCHIVO: view/SolicitudVacaciones/solicitudvacacions.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var dias_disponibles_global = 0.0; // Almacenará el saldo disponible del usuario
var tablaSolicitudes; // Variable global para el DataTable

// Función principal que se ejecuta al cargar la página
$(document).ready(function() {
    
    getResumenVacaciones(); // Carga el saldo disponible y lo guarda en dias_disponibles_global
    listarSolicitudes(); 
     
    // 1. Evento para calcular días hábiles y validar adelanto
    $('#vac_fecha_inicio, #vac_fecha_fin').on('change', function() {
        calcularDiasHabiles();
    });

    // 2. Submit del formulario
    $('#solicitud_form').on('submit', function(e) {
        e.preventDefault();
        guardarSolicitud();
    });

    $('#btnNuevaSolicitud').off('click'); 

    // ⭐ PASO 2: Vincular el evento click de forma directa y simple
    $('#btnNuevaSolicitud').on('click', function(e) {
        // e.preventDefault(); // Generalmente no es necesario, pero es un buen guardia
        abrirModalSolicitud();
    });
    
});

/* =============================================================== */
/* Lógica de Modal                                                 */
/* =============================================================== */

function abrirModalSolicitud() {
    $('#modalSolicitudVacacionesLabel').html('Solicitar Vacaciones'); 
    $('#solicitud_form')[0].reset();
    $('#vac_id').val('');
    
    // 1. Cargar el saldo actualizado al abrir el modal
    getResumenVacaciones(); // Esto llenará #saldo_disponible_modal

    // 2. Resetear cálculos
    $('#vac_dias_habiles').val('0.00');
    $('#vac_dias_naturales').val('0');
    $('#dias_habiles_info').html('Selecciona un rango de fechas para calcular los días.').removeClass('text-danger');
    $('#alerta_saldo').addClass('d-none');
    
    // 3. REINICIAR VISIBILIDAD DE OBSERVACIONES (Oculto y no requerido)
    $('#div_observaciones_container').addClass('d-none');
    $('#vac_observaciones').prop('required', false).val('');
    
    $('#modalSolicitudVacaciones').modal('show'); 
}
/**
 * Calcula los días hábiles entre dos fechas.
 * NOTA: Esta función debe ser replicada en el backend (Vacaciones.php) para validación segura.
 */
/* =============================================================== */
/* FUNCIONES DE LÓGICA DE DÍAS Y ADELANTO                          */
/* =============================================================== */

function calcularDiasHabiles() {
    const fecha_inicio = $('#vac_fecha_inicio').val();
    const fecha_fin = $('#vac_fecha_fin').val();
    
    // 1. Validar que ambos campos tengan datos
    if (!fecha_inicio || !fecha_fin) {
        $('#vac_dias_habiles').val('0.00');
        $('#vac_dias_naturales').val('0');
        $('#dias_habiles_info').html('Selecciona ambas fechas.').removeClass('text-danger');
        $('#alerta_saldo').addClass('d-none');
        
        // OCULTAR Y QUITAR REQUERIMIENTO
        $('#div_observaciones_container').addClass('d-none');
        $('#vac_observaciones').prop('required', false).val('');
        return;
    }

    // 2. Validar que Inicio NO sea mayor que Fin
    // Usamos new Date() para una comparación robusta
    const dateInicio = new Date(fecha_inicio.replace(/-/g, '/'));
    const dateFin = new Date(fecha_fin.replace(/-/g, '/'));

    if (dateInicio > dateFin) {
        $('#vac_dias_habiles').val('0.00');
        $('#vac_dias_naturales').val('0');
        $('#dias_habiles_info').html('La fecha de inicio no puede ser mayor a la final.').addClass('text-danger');
        $('#alerta_saldo').addClass('d-none');
        
        // OCULTAR Y QUITAR REQUERIMIENTO
        $('#div_observaciones_container').addClass('d-none');
        $('#vac_observaciones').prop('required', false).val('');
        return;
    }

    // 3. Si pasa las validaciones, llamar al servidor
    $.ajax({
        url: ruta_controlador + 'calcular_dias_habiles',
        type: "POST",
        data: { 
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin
        },
        dataType: "json",
        success: function(response) {
            if (response && response.dias_habiles !== undefined) { 
                const dias_habiles = parseFloat(response.dias_habiles) || 0;
                const dias_naturales = parseFloat(response.dias_naturales) || 0;
                
                // Actualizar inputs y la información del cálculo
                $('#vac_dias_habiles').val(dias_habiles.toFixed(2));
                $('#vac_dias_naturales').val(dias_naturales);
                $('#dias_habiles_info').html(`Total de días naturales: <strong>${dias_naturales}</strong>. Días hábiles válidos: <strong>${dias_habiles.toFixed(2)}</strong>.`);
                $('#dias_habiles_info').removeClass('text-danger');

                // --- Lógica de Saldo y Justificación de Adelanto ---
                if (dias_habiles > dias_disponibles_global) {
                    // CASO: ADELANTO (Saldo insuficiente)
                    const saldo_restante = dias_disponibles_global - dias_habiles;
                    
                    $('#alerta_saldo').removeClass('d-none');
                    $('#alerta_saldo').html(`<strong>¡Advertencia!</strong> Saldo insuficiente (${dias_disponibles_global.toFixed(2)} días). Quedarás con **${saldo_restante.toFixed(2)}** días (Adelanto).`);
                    
                    // MOSTRAR OBSERVACIONES Y HACERLO OBLIGATORIO
                    $('#div_observaciones_container').removeClass('d-none');
                    $('#vac_observaciones').prop('required', true).removeClass('border-danger').addClass('border-danger'); // Asegura el borde rojo

                } else {
                    // CASO: NORMAL (Saldo suficiente)
                    $('#alerta_saldo').addClass('d-none');
                    
                    // OCULTAR OBSERVACIONES Y QUITAR OBLIGATORIEDAD
                    $('#div_observaciones_container').addClass('d-none');
                    $('#vac_observaciones').prop('required', false).removeClass('border-danger');
                    $('#vac_observaciones').val(''); // Limpiar el contenido si cambió de Adelanto a Normal
                }
                
            } else {
                $('#vac_dias_habiles').val('0.00');
                $('#vac_dias_naturales').val('0');
                $('#dias_habiles_info').html('Error al calcular.').addClass('text-danger');
            }
        },
        error: function(jqXHR) {
            console.error("Error PHP:", jqXHR.responseText);
            $('#vac_dias_habiles').val('0.00');
            $('#vac_dias_naturales').val('0');
            $('#dias_habiles_info').html('Error de conexión con el servidor.').addClass('text-danger');
        }
    });
}

function getResumenVacaciones() {
    // 1. Colocamos un indicador de carga mientras llega la respuesta
    $('#saldo_disponible_modal').text('...'); 
    
    $.ajax({
        url: ruta_controlador + 'get_saldo',
        type: "POST",
        dataType: "json", // Le decimos a jQuery que espere JSON
        success: function(saldo) { // 'saldo' YA es un objeto
            
            // 2. Comprobación de que el objeto tiene la propiedad
            if (saldo && saldo.usu_dias_disponibles !== undefined) {
                // ACTUALIZACIÓN DE LA VARIABLE GLOBAL
                dias_disponibles_global = parseFloat(saldo.usu_dias_disponibles) || 0.0;
                
                // ACTUALIZACIÓN CRÍTICA DEL MODAL (Usamos .text() en el <div>)
                $('#saldo_disponible_modal').text(dias_disponibles_global.toFixed(2)); 
                
                // Actualizar la UI del Dashboard (si existe)
                $('#dias_disponibles_ui').text(dias_disponibles_global.toFixed(2));
                
                console.log("Saldo sincronizado (ÉXITO):", dias_disponibles_global);
            } else {
                $('#saldo_disponible_modal').text('Error: Saldo inválido.'); 
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // 3. Manejo de Fallo
            $('#saldo_disponible_modal').text('Error (Revisar)');
            console.error("Error AJAX get_saldo:", jqXHR.responseText);
        }
    });
}

function validarAdelanto(dias_solicitados) {
    // Tomamos el saldo disponible de la variable global (que debe inicializarse al cargar la vista)
    if (dias_disponibles_global < dias_solicitados) {
        // Se requiere adelanto (saldo quedará negativo)
        $('#alerta_saldo').removeClass('d-none');
        var saldo_restante = dias_disponibles_global - dias_solicitados;
        $('#alerta_saldo').html('<strong>¡Advertencia!</strong> Los días solicitados (' + dias_solicitados.toFixed(2) + ') exceden tu saldo (' + dias_disponibles_global.toFixed(2) + '). El saldo quedará en **' + saldo_restante.toFixed(2) + ' días** (Adelanto).');
    } else {
        // Saldo suficiente
        $('#alerta_saldo').addClass('d-none');
    }
}

/**
 * Envía la solicitud de vacaciones al controlador.
 */
function guardarSolicitud() {
    // 1. Validaciones básicas antes de enviar
    var dias_habiles = parseFloat($('#vac_dias_habiles').val());
    if (dias_habiles <= 0 || isNaN(dias_habiles)) {
        swal.fire("Atención", "El período seleccionado no contiene días hábiles válidos.", "warning");
        return;
    }
    
    // Deshabilitar botón para evitar doble click
    $('#btnGuardar').prop('disabled', true); 

    // 2. Crear FormData con todos los campos
    var formData = $('#solicitud_form').serialize();
    
    // 3. Enviar solicitud AJAX
    $.ajax({
        url: ruta_controlador + 'guardar_solicitud', // Case 'guardar_solicitud' en controller
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(datos) {
            $('#btnGuardar').prop('disabled', false); 
            if (datos.status === "ok") {
                // Éxito: Cerrar modal y mostrar mensaje
                $('#modalSolicitudVacaciones').modal('hide');
                swal.fire("Enviada", datos.message || "Solicitud enviada a tu jefe inmediato.", "success");
                
                // Recargar información y tabla de historial
                getResumenVacaciones(); // <-- Fundamental para que el saldo refleje la solicitud (aunque el cambio real sea al aprobar)
                if (typeof tablaVacaciones !== 'undefined' && tablaVacaciones) {
                    tablaVacaciones.ajax.reload(); 
                }
            } else {
                // Muestra el mensaje de error del controlador
                swal.fire("Error", datos.message || "Error desconocido del servidor.", "error"); 
            }
        },
        error: function(jqXHR) {
             $('#btnGuardar').prop('disabled', false); 
             swal.fire("Error de Conexión", "No se pudo conectar con el servidor.", "error");
             console.error(jqXHR.responseText);
        }
    });
}

/**
 * Inicializa y lista las solicitudes del usuario logueado en DataTable.
 */
function listarSolicitudes() {
    tablaSolicitudes = $('#tabla_solicitudes').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ajax": {
            url: ruta_controlador + 'listar_mis_solicitudes', // Debes crear este case en el controlador
            type: "post",
            dataType: "json",
            data: { usu_id: $('#usu_id').val() }, // Pasa el ID del usuario logueado (desde el modal)
            error: function(e) { console.log(e.responseText); }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [[0, "desc"]],
        "columns": [
            { "data": "vac_id" },
            { "data": "vac_fecha_inicio" },
            { "data": "vac_fecha_fin" },
            { "data": "vac_dias_habiles" },
            { "data": "vac_estado" },
            { "data": "acciones" } // Columna para botones de acción (ej. Cancelar, Ver)
        ]
    });
}