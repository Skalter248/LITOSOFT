// ARCHIVO: view/AprobacionSolicitud/aprobacion.js

var ruta_controlador = '../../controller/Vacaciones.php?op=';
var tabla;

$(document).ready(function() {
    listarSolicitudesEquipo();
});

function listarSolicitudesEquipo() {
    tabla = $('#tabla_aprobacion').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "dom": 'Bfrtip',
        "buttons": [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5' // Se corrige a pdfHtml5 para que funcionen los botones de exportación
        ],
        "ajax": {
            url: ruta_controlador + 'listar_solicitudes_jefe',
            type: "post",
            dataType: "json",
            error: function(e) { console.log(e.responseText); }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "order": [[ 4, "desc" ]], // Ordenar por la columna de Estado (índice 4) para que las "Pendientes" salgan primero.
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron solicitudes pendientes",
            "sEmptyTable":     "Ningún dato disponible",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
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
    });
}


function imprimirSolicitud(vac_id) {
    $.ajax({
        url: ruta_controlador + 'generar_formato_impresion',
        type: "POST",
        data: { vac_id: vac_id },
        dataType: "json",
        success: function(response) {
            if (response.status === 'ok') {
                // Abrir la nueva URL en una nueva pestaña
                window.open(response.url, '_blank');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo conectar para generar el formato.', 'error');
        }
    });
}

function gestionar(vac_id, accion) {
    // Configurar textos según acción
    let titulo = accion === 'aprobar' ? '¿Aprobar Solicitud?' : '¿Rechazar Solicitud?';
    let texto = accion === 'aprobar' 
        ? 'Se descontarán los días del saldo del empleado.' 
        : 'La solicitud será marcada como rechazada.';
    let colorBtn = accion === 'aprobar' ? '#28a745' : '#d33';

    swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: colorBtn,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: ruta_controlador + 'gestion_solicitud',
                type: "POST",
                data: { vac_id: vac_id, accion: accion },
                dataType: "json",
                success: function(datos) {
                    if (datos.status) {
                        swal.fire('Procesado', datos.message, 'success');
                        tabla.ajax.reload();
                    } else {
                        swal.fire('Error', datos.message, 'error');
                    }
                },
                error: function(e) {
                    swal.fire('Error', 'Error de conexión con el servidor', 'error');
                    console.log(e.responseText);
                }
            });
        }
    });
}