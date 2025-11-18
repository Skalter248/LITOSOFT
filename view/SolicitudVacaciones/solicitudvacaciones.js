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
    // 1. Limpiar y resetear el formulario
    $('#solicitud_form')[0].reset();
    $('#modalSolicitudVacacionesLabel').html('Solicitar Vacaciones');
    
    // 2. Limpiar campos de cálculo y alerta
    $('#vac_dias_habiles').val('0.00');
    $('#dias_habiles_info').text('Selecciona un rango de fechas para calcular los días.').removeClass('text-danger');
    $('#alerta_saldo').addClass('d-none');
    
    // 3. Abrir el modal de Bootstrap
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
    
    if (!fecha_inicio || !fecha_fin || fecha_inicio > fecha_fin) {
        $('#vac_dias_habiles').val('0.00');
        $('#dias_habiles_info').html('Selecciona un rango de fechas válido.').addClass('text-danger');
        $('#alerta_saldo').addClass('d-none');
        return;
    }

    $.ajax({
        // ⭐ AJUSTAR: Se asume que el case en el controlador será 'calcular_dias' o 'calcular_dias_habiles'
        // Si usas el modelo directamente, sería 'calcular_dias_habiles'
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
                const dias_totales = parseFloat(response.dias_totales) || 0;
                
                $('#vac_dias_habiles').val(dias_habiles.toFixed(2));
                $('#dias_habiles_info').html(`Total de días solicitados: **${dias_totales}** (Hábiles: **${dias_habiles.toFixed(2)}**)`);
                $('#dias_habiles_info').removeClass('text-danger');

                // ⭐ Lógica de Saldo/Adelanto (Usando la variable global) ⭐
                if (dias_habiles > dias_disponibles_global) {
                    const saldo_restante = (dias_disponibles_global - dias_habiles).toFixed(2);
                    $('#alerta_saldo').html(`<strong>¡Advertencia!</strong> Los días solicitados (${dias_habiles.toFixed(2)}) exceden tu saldo (${dias_disponibles_global.toFixed(2)}). De aprobarse, tu saldo será **${saldo_restante} días** (Adelanto).`).removeClass('d-none');
                } else {
                    $('#alerta_saldo').addClass('d-none');
                }
            } else {
                $('#vac_dias_habiles').val('0.00');
                $('#dias_habiles_info').html('Error: Respuesta de cálculo inválida.').addClass('text-danger');
            }
        },
        error: function(jqXHR) {
            $('#vac_dias_habiles').val('0.00');
            $('#dias_habiles_info').html(`Error del Servidor (Status ${jqXHR.status}).`).addClass('text-danger');
            console.error("Respuesta del Servidor (PHP Error):", jqXHR.responseText);
        }
    });
}

function getResumenVacaciones() {
    $.post(ruta_controlador + 'get_saldo', { usu_id: $('#usu_id').val() }, function(data) {
        try {
            var saldo = JSON.parse(data);
            
            // 1. Asignar el valor disponible a la variable global
            dias_disponibles_global = parseFloat(saldo.usu_dias_disponibles) || 0.0;
            
            // 2. Actualizar la UI del dashboard
            $('#dias_disponibles').text(dias_disponibles_global.toFixed(2));
            // Asegúrate de que los otros campos de la UI también se actualicen (opcional)

        } catch (e) {
            console.error("Error al parsear el saldo:", e);
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