// ARCHIVO: view/AprobacionVacaciones/mntaprobacion.js (Código Completo y Corregido)

var tablaAprobacion;
var ruta_controlador = '../../controller/Vacaciones.php?op=';

// Función que se ejecuta al cargar la página
$(document).ready(function() {
    listarSolicitudesHistoricas(); 
});


/* =============================================================== */
/* FUNCIONES DE LISTADO (DataTable)                                */
/* =============================================================== */

function listarSolicitudesHistoricas() { 
    tablaAprobacion = $('#aprobacion_data').DataTable({
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
            url: ruta_controlador + 'listar_solicitudes_historicas', 
            type: "post",
            dataType: "json",
            error: function(e) {
                console.log(e.responseText);
            }
        },
        "columns": [
            { "data": 0 }, // 0: vac_id
            { "data": 1 }, // 1: nombre_empleado
            { "data": 2 }, // 2: puesto_area
            { "data": 3 }, // 3: vac_fecha_inicio
            { "data": 4 }, // 4: vac_fecha_fin
            { "data": 5 }, // 5: vac_dias_habiles
            { "data": 6 }, // 6: vac_fecha_solicitud
            
            // ⭐ COLUMNA 7: ESTADO (Corrección Final)
            { "data": 9, 
              "render": function(data, type, row) {
                  // Normalizamos el valor de la DB a MAYÚSCULAS y sin espacios (ej: "aprobada" -> "APROBADA")
                  const estado = data ? data.trim().toUpperCase() : ''; 
                  
                  switch(estado) {
                      case 'APROBADA': // ⭐ Agregamos el caso Femenino
                      case 'APROBADO': 
                      case 'A': 
                          return '<span class="label label-success">APROBADA</span>'; // ✅ VERDE
                      
                      case 'PENDIENTE':
                      case 'P': 
                          return '<span class="label label-warning">PENDIENTE</span>'; // ⚠️ AMARILLO
                      
                      case 'RECHAZADA': // ⭐ Agregamos el caso Femenino
                      case 'RECHAZADO':
                      case 'R': 
                          return '<span class="label label-danger">RECHAZADA</span>'; // ❌ ROJO
                      
                      default: 
                          // Muestra el valor crudo en el texto de la tabla si no hay coincidencia
                          return '<span class="label label-default">DESCONOCIDO (' + data + ')</span>'; 
                  }
              }
            }, 
            
            // ⭐ COLUMNA 8: Justificación
            { "data": 7, 
              "render": function(data, type, row) {
                  const esAdelanto = row[8] == 1; 
                  const justificacion = row[7]; 
                  let contenido = '';
                  
                  if (esAdelanto) {
                      contenido += '<span class="label label-warning">ADELANTO</span><br>';
                  }
                  
                  if (justificacion && justificacion.trim().length > 0) {
                      contenido += justificacion.length > 50 ? justificacion.substring(0, 50) + '...' : justificacion;
                  } else if (esAdelanto) {
                      contenido += 'Sin justificación';
                  } else {
                      contenido += 'Estándar';
                  }
                  return contenido;
              }
            },
            
            // ⭐ COLUMNA 9: Acciones (Botones)
            { "data": 0, 
              "orderable": false,
              "render": function(data, type, row) {
                  const vac_id = data;
                  const estado = row[9] ? row[9].trim().toUpperCase() : ''; 
                  let html = '';
                  
                  // Botón Imprimir (Siempre visible)
                  html += `<button class="btn btn-inline btn-primary btn-sm" title="Imprimir Solicitud" onclick="imprimirSolicitud(${vac_id});"><i class="fa fa-print"></i></button> `;
                  
                  // Muestra botones solo si el estado es PENDIENTE
                  if (estado === 'PENDIENTE' || estado === 'P') { 
                       html += `<button class="btn btn-inline btn-success btn-sm" title="Aprobar" onclick="aprobarSolicitud(${vac_id})"><i class="fa fa-check"></i></button> `;
                       html += `<button class="btn btn-inline btn-danger btn-sm" title="Rechazar" onclick="rechazarSolicitud(${vac_id})"><i class="fa fa-times"></i></button>`;
                  } else {
                       html += `<button class="btn btn-inline btn-secondary btn-sm" disabled>Finalizada</button>`;
                  }
                  
                  return html;
              }
            }
        ],
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [
            [6, "asc"]
        ], 
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No hay solicitudes de vacaciones.",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });
}


/* =============================================================== */
/* FUNCIONES DE ACCIÓN (Aprobar/Rechazar con SweetAlerts)          */
/* =============================================================== */

function aprobarSolicitud(vac_id) {
    swal.fire({
        title: "¿Aprobar Solicitud?",
        text: "¡Confirma que deseas aprobar las vacaciones del empleado!",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, Aprobar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            $.post(ruta_controlador + 'aprobar_solicitud', {vac_id: vac_id}, function(data) {
                if (data.trim() === "ok") {
                    swal.fire("Aprobada", "La solicitud ha sido aprobada con éxito.", "success");
                } else {
                    swal.fire("Atención", data, "warning"); 
                }
                tablaAprobacion.ajax.reload(null, false);
            }).fail(function() {
                swal.fire("Error", "Ocurrió un error en la comunicación con el servidor.", "error");
            });
        }
    });
}


function rechazarSolicitud(vac_id) {
    swal.fire({
        title: "¿Rechazar Solicitud?",
        text: "¡Confirma que deseas rechazar esta solicitud de vacaciones!",
        icon: "warning", 
        showCancelButton: true,
        confirmButtonText: "Sí, Rechazar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            $.post(ruta_controlador + 'rechazar_solicitud', {vac_id: vac_id}, function(data) {
                if (data.trim() === "ok") {
                    // SweetAlert de RECHAZO (Rojo)
                    swal.fire("Rechazada", "La solicitud ha sido rechazada.", "error"); 
                } else {
                    swal.fire("Atención", data, "warning");
                }
                tablaAprobacion.ajax.reload(null, false);
            }).fail(function() {
                swal.fire("Error", "Ocurrió un error en la comunicación con el servidor.", "error");
            });
        }
    });
}

function imprimirSolicitud(vac_id) {
    const url_impresion = ruta_controlador + 'imprimir_solicitud&vac_id=' + vac_id;
    window.open(url_impresion, '_blank');
}