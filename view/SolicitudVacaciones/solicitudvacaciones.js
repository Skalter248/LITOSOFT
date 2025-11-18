// ARCHIVO: view/SolicitudVacaciones/solicitudvacacions.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var dias_disponibles_global = 0.0; // Almacenará el saldo disponible del usuario
var tablaSolicitudes; // Variable global para el DataTable

// Función principal que se ejecuta al cargar la página
$(document).ready(function() {
    
    // 1. Inicializar la tabla de historial
    listarSolicitudes(); 
    
    // 2. Obtener el saldo de la UI y guardarlo globalmente
    dias_disponibles_global = parseFloat($('#saldo_disponible_ui').text()) || 0.0; 
    
    // 3. Evento para calcular días hábiles y validar saldo
    $('#vac_fecha_inicio, #vac_fecha_fin').on('change', function() {
        calcularDiasHabiles();
    });

    // 4. Submit del formulario
    $('#solicitud_form').on('submit', function(e) {
        e.preventDefault();
        guardarSolicitud();
    });

    // 5. Abrir modal
    $('#btnNuevaSolicitud').click(function() {
        abrirModalSolicitud();
    });
});

// =================================================================
// CRUD y Lógica de Negocio
// =================================================================

function abrirModalSolicitud() {
    $('#modalSolicitudVacacionesLabel').html('Solicitar Vacaciones'); 
    $('#solicitud_form')[0].reset();
    $('#vac_id').val('');
    $('#vac_dias_habiles').val('');
    $('#modalSolicitudVacaciones').modal('show'); 
    // Ocultar alerta de saldo si estaba visible
    $('#alerta_saldo').addClass('d-none');
}

/**
 * Calcula los días hábiles entre dos fechas.
 * NOTA: Esta función debe ser replicada en el backend (Vacaciones.php) para validación segura.
 */
function calcularDiasHabiles() {
    var inicio = $('#vac_fecha_inicio').val();
    var fin = $('#vac_fecha_fin').val();

    if (!inicio || !fin) {
        $('#vac_dias_habiles').val(0);
        $('#dias_habiles_info').text('Selecciona ambas fechas.');
        return;
    }
    
    // Llama al controlador para obtener el cálculo seguro desde el backend
    $.post(ruta_controlador + 'calcular_dias_habiles', { vac_fecha_inicio: inicio, vac_fecha_fin: fin }, function(data) {
        var dias = parseFloat(data);
        
        $('#vac_dias_habiles').val(dias.toFixed(2));
        $('#dias_habiles_info').text(dias + ' días hábiles solicitados.');

        // Validación visual de adelanto
        if (dias > dias_disponibles_global) {
            $('#alerta_saldo').removeClass('d-none');
        } else {
            $('#alerta_saldo').addClass('d-none');
        }
    }).fail(function() {
        $('#dias_habiles_info').text('Error al calcular días.');
        $('#vac_dias_habiles').val(0);
    });
}

/**
 * Envía la solicitud de vacaciones al controlador.
 */
function guardarSolicitud() {
    // Validación básica en el frontend
    if (!$('#vac_fecha_inicio').val() || !$('#vac_fecha_fin').val() || parseFloat($('#vac_dias_habiles').val()) <= 0) {
        swal.fire("Advertencia", "Debes seleccionar las fechas y el cálculo de días debe ser positivo.", "warning");
        return;
    }

    var formData = new FormData($('#solicitud_form')[0]);
    formData.append("op", "guardar_solicitud");

    $.ajax({
        url: ruta_controlador + 'guardar_solicitud', // La op ya está en formData
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(datos) {
            datos = JSON.parse(datos);
            if (datos.success) {
                $('#modalSolicitudVacaciones').modal('hide');
                swal.fire("Éxito", "Solicitud enviada para aprobación.", "success");
                
                // Actualizar UI
                listarSolicitudes(); 
                // Aquí podrías hacer una llamada AJAX para actualizar los saldos de los cards
            } else {
                swal.fire("Error", datos.message || "Error desconocido del servidor.", "error"); 
            }
        },
        error: function(jqXHR) {
             swal.fire("Error de Conexión", "No se pudo conectar con el servidor.", "error");
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