// ARCHIVO: view/SolicitudVacaciones/solicitudvacacions.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var dias_disponibles_global = 0.0; // Almacenará el saldo disponible del usuario
var tablaSolicitudes; // Variable global para el DataTable
<<<<<<< HEAD
var tablaVacaciones; // Variable global
=======
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac

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
<<<<<<< HEAD
    $('#solicitud_form')[0].reset();
    $('#vac_id').val('');
    
    // 1. Cargar saldo
    getResumenVacaciones(); 
    
    // 2. FORZAR OCULTAMIENTO DE JUSTIFICACIÓN Y ALERTA
    $('#vac_dias_habiles').val('0.00');
    $('#vac_dias_naturales').val('0');
    
    // Esto asegura que empiece limpio SIEMPRE
    activarModoNormal(); 
    
    $('#modalSolicitudVacaciones').modal('show'); 
}
 
=======
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
/* =============================================================== */
/* FUNCIONES DE LÓGICA DE DÍAS Y ADELANTO                          */
/* =============================================================== */

<<<<<<< HEAD

=======
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
function calcularDiasHabiles() {
    const fecha_inicio = $('#vac_fecha_inicio').val();
    const fecha_fin = $('#vac_fecha_fin').val();
    
<<<<<<< HEAD
    // Validaciones básicas
    if (!fecha_inicio || !fecha_fin) return resetearCalculo();
=======
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
    const dateInicio = new Date(fecha_inicio.replace(/-/g, '/'));
    const dateFin = new Date(fecha_fin.replace(/-/g, '/'));

    if (dateInicio > dateFin) {
<<<<<<< HEAD
        swal.fire("Error", "La fecha de inicio no puede ser mayor a la final.", "error");
        return resetearCalculo();
    }

    // Petición al Servidor
    $.ajax({
        url: ruta_controlador + 'calcular_dias_habiles',
        type: "POST",
        data: { fecha_inicio: fecha_inicio, fecha_fin: fecha_fin },
=======
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        dataType: "json",
        success: function(response) {
            if (response && response.dias_habiles !== undefined) { 
                const dias_habiles = parseFloat(response.dias_habiles) || 0;
                const dias_naturales = parseFloat(response.dias_naturales) || 0;
<<<<<<< HEAD

                // 1. LEER EL SALDO VISUAL DEL MODAL (Lo que ve el usuario)
                let texto_saldo = $('#saldo_disponible_modal').text().trim();
                let saldo_actual = parseFloat(texto_saldo);
                if (isNaN(saldo_actual)) saldo_actual = 0;

                // 2. LLENAR INPUTS
                $('#vac_dias_habiles').val(dias_habiles.toFixed(2));
                $('#vac_dias_naturales').val(dias_naturales);

                // =================================================
                // 🧠 LÓGICA VISUAL (Semáforo)
                // =================================================
                if (dias_habiles > saldo_actual) {
                    // CASO B: ADELANTO (Insuficiente) -> MOSTRAR EXTRAS
                    activarModoAdelanto(dias_habiles, saldo_actual);
                } else {
                    // CASO A: NORMAL (Suficiente) -> OCULTAR EXTRAS
                    activarModoNormal();
                }
            } else {
                resetearCalculo();
            }
        },
        error: function() { resetearCalculo(); }
    });
}

// --- FUNCIONES VISUALES ---

function activarModoAdelanto(dias_solicitados, saldo_actual) {
    const saldo_final = saldo_actual - dias_solicitados;
    
    // 1. Poner Modal en Rojo
    $('#modal_body_container').addClass('es-adelanto');

    // 2. MOSTRAR JUSTIFICACIÓN
    $('#div_observaciones_container').removeClass('d-none'); // Quita clase ocultar
    $('#div_observaciones_container').show(); // jQuery show forzoso
    
    $('#vac_observaciones').prop('required', true);

    // 3. Alerta
    $('#alerta_saldo').removeClass('d-none');
    $('#alerta_saldo').html(
        `<i class="fa fa-warning"></i> Atención: Tu saldo final será negativo: ${saldo_final.toFixed(2)} días.`
    );
}

function activarModoNormal() {
    // 1. Restaurar colores si tiene saldo positivo
    if (dias_disponibles_global > 0) {
        $('#modal_body_container').removeClass('es-adelanto');
    }

    // 2. OCULTAR ESTRICTAMENTE LA JUSTIFICACIÓN
    $('#div_observaciones_container').addClass('d-none'); // Agrega clase ocultar
    $('#div_observaciones_container').hide(); // jQuery hide forzoso por si acaso
    
    $('#vac_observaciones').prop('required', false); 
    $('#vac_observaciones').val(''); 

    // 3. Ocultar Alerta de saldo final
    $('#alerta_saldo').addClass('d-none');
    $('#alerta_saldo').html('');
}

function resetearCalculo() {
    $('#vac_dias_habiles').val('0.00');
    $('#vac_dias_naturales').val('0');
    activarModoNormal(); // Al resetear, volvemos al estado limpio
}

function abrirModalSolicitud() {
    $('#solicitud_form')[0].reset();
    $('#vac_id').val('');
    
    // Cargar saldo y resetear visuales
    getResumenVacaciones(); 
    resetearCalculo();
    
    $('#modalSolicitudVacaciones').modal('show'); 
}

function getResumenVacaciones() {
=======
                
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
    $('#saldo_disponible_modal').text('...'); 
    
    $.ajax({
        url: ruta_controlador + 'get_saldo',
        type: "POST",
<<<<<<< HEAD
        dataType: "json",
        success: function(saldo) {
            if (saldo && saldo.usu_dias_disponibles !== undefined) {
                dias_disponibles_global = parseFloat(saldo.usu_dias_disponibles) || 0.0;
                
                // Actualizar UI
                $('#dias_disponibles_ui').text(dias_disponibles_global.toFixed(2));
                $('#saldo_disponible_modal').text(dias_disponibles_global.toFixed(2)); 
                
                // --- LÓGICA VISUAL DEL SALDO INICIAL ---
                // Si el saldo es 0 o negativo, lo ponemos ROJO. Si es positivo, VERDE.
                if (dias_disponibles_global <= 0) {
                    $('#modal_body_container').addClass('es-adelanto'); // Rojo
                    $('#saldo_disponible_modal').html(dias_disponibles_global.toFixed(2) + ' <br><small style="font-size:0.5em">(Sin días disponibles)</small>');
                } else {
                    $('#modal_body_container').removeClass('es-adelanto'); // Verde
                }

                console.log("Saldo cargado:", dias_disponibles_global);
            }
        },
        error: function() {
            $('#saldo_disponible_modal').text('Error');
=======
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
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
<<<<<<< HEAD
    // Validaciones previas...
    var dias_habiles = parseFloat($('#vac_dias_habiles').val());
    if (dias_habiles <= 0) {
        swal.fire("Error", "Cálculo de días inválido.", "error");
        return;
    }

    var formData = $('#solicitud_form').serialize();

    $.ajax({
        url: ruta_controlador + 'guardar_solicitud',
=======
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(datos) {
<<<<<<< HEAD
            if (datos.status == 'ok') {
                $('#modalSolicitudVacaciones').modal('hide');
                swal.fire("¡Enviado!", datos.message, "success");
                
                // Recargar tabla y saldos
                $('#tabla_solicitudes').DataTable().ajax.reload();
                getResumenVacaciones(); // Recargar saldo visual
            } else {
                swal.fire("Error", datos.message, "error");
            }
        }
    });
}

function cancelarSolicitud(vac_id) {
    swal.fire({
        title: '¿Cancelar solicitud?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(ruta_controlador + 'gestion_solicitud', { vac_id: vac_id, accion: 'cancelar' }, function(data) {
                var datos = JSON.parse(data);
                if (datos.status) {
                    swal.fire('Cancelada', datos.message, 'success');
                    $('#tabla_solicitudes').DataTable().ajax.reload();
                } else {
                    swal.fire('Error', datos.message, 'error');
                }
            });
=======
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
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        }
    });
}

/**
 * Inicializa y lista las solicitudes del usuario logueado en DataTable.
 */
function listarSolicitudes() {
<<<<<<< HEAD
    tablaVacaciones = $('#vacaciones_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdf'
        ],
        "ajax": {
            url: ruta_controlador + 'listar_mis_solicitudes',
            type: "post",
            dataType: "json",
            error: function(e) {
                console.log(e.responseText);
            }
=======
    tablaSolicitudes = $('#tabla_solicitudes').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ajax": {
            url: ruta_controlador + 'listar_mis_solicitudes', // Debes crear este case en el controlador
            type: "post",
            dataType: "json",
            data: { usu_id: $('#usu_id').val() }, // Pasa el ID del usuario logueado (desde el modal)
            error: function(e) { console.log(e.responseText); }
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
<<<<<<< HEAD
        "order": [[ 0, "desc" ]], // Ordenar por ID descendente (lo más reciente primero)
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
=======
        "order": [[0, "desc"]],
        "columns": [
            { "data": "vac_id" },
            { "data": "vac_fecha_inicio" },
            { "data": "vac_fecha_fin" },
            { "data": "vac_dias_habiles" },
            { "data": "vac_estado" },
            { "data": "acciones" } // Columna para botones de acción (ej. Cancelar, Ver)
        ]
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
    });
}