// ARCHIVO: view/AprobacionVacaciones/mntaprobacion.js

var tablaAprobacion;
var ruta_controlador = '../../controller/Vacaciones.php?op=';

// Función que se ejecuta al cargar la página
$(document).ready(function() {
    listarSolicitudesPendientes();
});


/* =============================================================== */
/* FUNCIONES DE LISTADO (DataTable)                                */
/* =============================================================== */

function listarSolicitudesPendientes() {
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
            url: ruta_controlador + 'listar_solicitudes_pendientes',
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
        "order": [
            [6, "asc"]
        ], // Ordenar por fecha de solicitud ascendente
        "language": {
            // Configura aquí tus traducciones de DataTables
        }
    });
}


/* =============================================================== */
/* FUNCIONES DE ACCIÓN (Aprobar/Rechazar)                          */
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
                if (data === "ok") {
                    swal.fire("Aprobada", "La solicitud ha sido aprobada con éxito.", "success");
                } else {
                    swal.fire("Atención", data, "warning"); 
                }
                tablaAprobacion.ajax.reload(); // Recargar tabla
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
                if (data === "ok") {
                    swal.fire("Rechazada", "La solicitud ha sido rechazada.", "success");
                } else {
                    swal.fire("Atención", data, "warning");
                }
                tablaAprobacion.ajax.reload(); // Recargar tabla
            }).fail(function() {
                swal.fire("Error", "Ocurrió un error en la comunicación con el servidor.", "error");
            });
        }
    });
}