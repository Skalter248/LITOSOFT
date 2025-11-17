// ARCHIVO: view/Vacaciones/mntvacaciones.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var dias_disponibles_global = 0.0; // Almacenará el saldo disponible del usuario
var tablaVacaciones; 

// Función que se ejecuta al cargar la página
$(document).ready(function() {

    // ⭐ CRÍTICO: Inicializar saldo desde el HTML, ya que el PHP lo puso ahí.
    dias_disponibles_global = parseFloat($('#saldo_disponible_ui').text()) || 0.0;
    
    // Si necesitas recargar el saldo desde el backend:
    getResumenVacaciones();
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

    // 3. Apertura del modal y limpieza del estado
    $('#btnNuevaSolicitud').click(function() {
        $('#modalSolicitudVacacionesLabel').html('Solicitar Vacaciones'); 
        $('#solicitud_form')[0].reset();
        $('#vac_id').val('');
        
        // Limpieza CRÍTICA al abrir
        $('#vac_dias_habiles').val(''); 
        $('#dias_habiles_info').html('Selecciona un rango de fechas para calcular...'); 
        $('#alerta_saldo').addClass('d-none'); 

        $('#modalSolicitudVacaciones').modal('show');
    });
});


function calcularDiasHabiles() {
    // ... (Mantener la lógica anterior, ya que es correcta)
    const fecha_inicio = $('#vac_fecha_inicio').val();
    const fecha_fin = $('#vac_fecha_fin').val();
    
    // ... (Validación de fechas)
    if (!fecha_inicio || !fecha_fin || fecha_inicio > fecha_fin) {
        $('#vac_dias_habiles').val('');
        $('#dias_habiles_info').html('Selecciona un rango de fechas válido.');
        $('#alerta_saldo').addClass('d-none');
        return;
    }

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
                const dias_totales = parseFloat(response.dias_totales) || 0;
                
                $('#vac_dias_habiles').val(dias_habiles.toFixed(2));
                $('#dias_habiles_info').html(`Total de días solicitados: **${dias_totales}** (Hábiles: **${dias_habiles.toFixed(2)}**)`);

                if (dias_habiles > dias_disponibles_global) {
                    $('#alerta_saldo').removeClass('d-none');
                } else {
                    $('#alerta_saldo').addClass('d-none');
                }
            } else {
                $('#dias_habiles_info').html('Error: Respuesta de cálculo inválida del servidor.');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#vac_dias_habiles').val('Error');
            $('#dias_habiles_info').html(`Error del Servidor (Status ${jqXHR.status}). Revise la consola (F12)`);
            console.error("PHP Error:", jqXHR.responseText); 
        }
    });
}

// ... (El resto de funciones como guardarSolicitud, listarSolicitudes, y getResumenVacaciones deben funcionar igual)