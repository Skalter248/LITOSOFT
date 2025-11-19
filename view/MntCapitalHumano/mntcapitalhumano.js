// ARCHIVO: view/MntCapitalHumano/mntcapitalhumano.js

var tablaDepartamentos;
var tablaAreas;
var tablaPuestos;

// Función que se ejecuta al cargar la página
$(document).ready(function() {
    // 1. Inicializar DataTables para las tres entidades
    listarDepartamentos();
    listarAreas();
    listarPuestos();

    // 2. Cargar los SELECTs necesarios para los modales de Áreas y Puestos
    cargarSelectDepartamentos();
    cargarSelectAreas();

    // 3. Manejar el evento submit de los formularios
    $('#departamento_form').on('submit', function(e){
        guardar(e, 'Departamento');
    });

    $('#area_form').on('submit', function(e){
        guardar(e, 'Area');
    });

    $('#puesto_form').on('submit', function(e){
        guardar(e, 'Puesto');
    });

    // 4. Funciones para ABRIR MODALES (Controladas solo por JS)
    $('#btnNuevoDepartamento').click(function(){
        $('#modalDepartamentoLabel').html('Nuevo Departamento');
        $('#departamento_form')[0].reset();
        $('#dep_id').val('');
        $('#modalDepartamento').modal('show');
    });

    $('#btnNuevaArea').click(function(){
        $('#modalAreaLabel').html('Nueva Área');
        $('#area_form')[0].reset();
        $('#area_id').val('');
        $('#modalArea').modal('show');
    });

    $('#btnNuevoPuesto').click(function(){
        $('#modalPuestoLabel').html('Nuevo Puesto');
        $('#puesto_form')[0].reset();
        $('#pue_id').val('');
        $('#modalPuesto').modal('show');
    });
});

/* *************************************************************** */
/* FUNCIONES DE LISTADO (DataTables) */
/* *************************************************************** */

function listarDepartamentos(){
    tablaDepartamentos = $('#departamentos_data').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [ 'copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5' ],
        "ajax":{
            url: '../../controller/MntCapitalHumano.php?op=listar_departamentos',
            type: "post",
            dataType: "json",
            error: function(e){ console.log(e.responseText); }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/Spanish.json"
        }
    }).DataTable();
}

function listarAreas(){
    tablaAreas = $('#areas_data').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [ 'copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5' ],
        "ajax":{
            url: '../../controller/MntCapitalHumano.php?op=listar_areas',
            type: "post",
            dataType: "json",
            error: function(e){ console.log(e.responseText); }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "autoWidth": false,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json" }
    }).DataTable();
}

function listarPuestos(){
    tablaPuestos = $('#puestos_data').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [ 'copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5' ],
        "ajax":{
            url: '../../controller/MntCapitalHumano.php?op=listar_puestos',
            type: "post",
            dataType: "json",
            error: function(e){ console.log(e.responseText); }
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "autoWidth": false,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json" }
    }).DataTable();
}


/* *************************************************************** */
/* FUNCIONES DE MANTENIMIENTO (CRUD) */
/* *************************************************************** */

// Función central para guardar/editar
function guardar(e, entidad) {
    e.preventDefault();
    var formData;
    var url;

    switch (entidad) {
        case 'Departamento':
            formData = new FormData($('#departamento_form')[0]);
            url = '../../controller/MntCapitalHumano.php?op=guardaryeditar_departamento';
            break;
        case 'Area':
            formData = new FormData($('#area_form')[0]);
            url = '../../controller/MntCapitalHumano.php?op=guardaryeditar_area';
            break;
        case 'Puesto':
            formData = new FormData($('#puesto_form')[0]);
            url = '../../controller/MntCapitalHumano.php?op=guardaryeditar_puesto';
            break;
    }

    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(datos){
            if (datos === "ok") {
                swal.fire({ title: "¡Éxito!", text: entidad + " guardado correctamente.", icon: "success" });
            } else {
                 swal.fire({ title: "Error", text: datos, icon: "error" });
            }
            
            $('#modal' + entidad).modal('hide');
            eval('tabla' + entidad + 's').ajax.reload();
            // ...
            
            if (entidad === 'Departamento') {
                cargarSelectDepartamentos();
                listarAreas();
            } else if (entidad === 'Area') {
                cargarSelectAreas();
                listarPuestos();
            }
        }
    });
}

/* --- Funciones de EDICIÓN (Mostrar por ID) --- */

function editarDepartamento(dep_id) {
    $('#modalDepartamentoLabel').html('Editar Departamento');
    $.post("../../controller/MntCapitalHumano.php?op=mostrar_departamento", {dep_id: dep_id}, function(data){
        data = JSON.parse(data);
        $('#dep_id').val(data.dep_id);
        $('#dep_nombre').val(data.dep_nombre);
        $('#modalDepartamento').modal('show');
    });
}

function editarArea(area_id) {
    $('#modalAreaLabel').html('Editar Área');
    $.post("../../controller/MntCapitalHumano.php?op=mostrar_area", {area_id: area_id}, function(data){
        data = JSON.parse(data);
        $('#area_id').val(data.area_id);
        $('#dep_id_area').val(data.dep_id); 
        $('#area_nombre').val(data.area_nombre);
        $('#modalArea').modal('show');
    });
}

function editarPuesto(pue_id) {
    $('#modalPuestoLabel').html('Editar Puesto');
    $.post("../../controller/MntCapitalHumano.php?op=mostrar_puesto", {pue_id: pue_id}, function(data){
        data = JSON.parse(data);
        $('#pue_id').val(data.pue_id);
        $('#area_id_puesto').val(data.area_id); 
        $('#pue_nombre').val(data.pue_nombre);
        $('#modalPuesto').modal('show');
    });
}


/* --- Funciones de ELIMINACIÓN (Lógica) --- */

function eliminarDepartamento(dep_id) {
    swal.fire({
        title: "¿Estás seguro?",
        text: "¡El departamento será marcado como INACTIVO!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
        cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_departamento", {dep_id: dep_id}, function(data){
                swal.fire("Desactivado", "El departamento ha sido desactivado.", "success");
                tablaDepartamentos.ajax.reload();
                cargarSelectDepartamentos(); 
                listarAreas();
            });
        }
    });
}

function desactivarDepartamento(dep_id) {
    swal.fire({
        title: "¿Estás seguro?",
        text: "¡El departamento será marcado como INACTIVO!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
        cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            // Usa el caso existente que cambia el estado a INACTIVO
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_departamento", {dep_id: dep_id}, function(data){
                swal.fire("Desactivado", "El departamento ha sido desactivado.", "success");
                tablaDepartamentos.ajax.reload();
                cargarSelectDepartamentos(); 
                listarAreas();
            });
        }
    });
}

// 2. ACTIVAR (Nuevo)
function activarDepartamento(dep_id) {
    swal.fire({
        title: "¿Estás seguro?",
        text: "¡El departamento será marcado como ACTIVO!",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Sí, activar",
        cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            // Usa el nuevo caso para activar
            $.post("../../controller/MntCapitalHumano.php?op=activar_departamento", {dep_id: dep_id}, function(data){
                swal.fire("Activado", "El departamento ha sido activado.", "success");
                tablaDepartamentos.ajax.reload();
                cargarSelectDepartamentos(); 
                listarAreas();
            });
        }
    });
}

// 3. ELIMINACIÓN PERMANENTE (Nuevo)
function eliminarDepartamentoPermanente(dep_id) {
    swal.fire({
        title: "¿Estás COMPLETAMENTE seguro?",
        text: "¡El departamento será ELIMINADO permanentemente de la base de datos!",
        icon: "error",
        showCancelButton: true,
        confirmButtonText: "Sí, borrar definitivamente",
        cancelButtonText: "No, cancelar",
        dangerMode: true,
    }).then((result) => {
        if (result.value) {
            // Usa el nuevo caso para eliminar permanentemente
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_departamento_permanente", {dep_id: dep_id}, function(data){
                swal.fire("Eliminado", "El departamento ha sido borrado permanentemente.", "success");
                tablaDepartamentos.ajax.reload();
                cargarSelectDepartamentos(); 
                listarAreas();
            });
        }
    });
}

function eliminarArea(area_id) {
    swal.fire({
        title: "¿Estás seguro?",
        text: "¡El área será marcada como INACTIVA!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
        cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_area", {area_id: area_id}, function(data){
                swal.fire("Desactivada", "El área ha sido desactivada.", "success");
                tablaAreas.ajax.reload();
                cargarSelectAreas(); 
                listarPuestos();
            });
        }
    });
}

function eliminarPuesto(pue_id) {
    swal.fire({
        title: "¿Estás seguro?",
        text: "¡El puesto será marcado como INACTIVO!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
        cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_puesto", {pue_id: pue_id}, function(data){
                swal.fire("Desactivado", "El puesto ha sido desactivado.", "success");
                tablaPuestos.ajax.reload();
            });
        }
    });
}

function desactivarArea(area_id) {
    swal.fire({
        title: "¿Estás seguro?", text: "¡El área será marcada como INACTIVA!", icon: "warning",
        showCancelButton: true, confirmButtonText: "Sí, desactivar", cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_area", {area_id: area_id}, function(data){
                swal.fire("Desactivada", "El área ha sido desactivada.", "success");
                tablaAreas.ajax.reload();
                cargarSelectAreas(); 
                listarPuestos();
            });
        }
    });
}

function activarArea(area_id) {
    swal.fire({
        title: "¿Estás seguro?", text: "¡El área será marcada como ACTIVA!", icon: "info",
        showCancelButton: true, confirmButtonText: "Sí, activar", cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=activar_area", {area_id: area_id}, function(data){
                swal.fire("Activada", "El área ha sido activada.", "success");
                tablaAreas.ajax.reload();
                cargarSelectAreas(); 
                listarPuestos();
            });
        }
    });
}

function eliminarAreaPermanente(area_id) {
    swal.fire({
        title: "¿Estás COMPLETAMENTE seguro?", text: "¡El área será ELIMINADA permanentemente!", icon: "error",
        showCancelButton: true, confirmButtonText: "Sí, borrar definitivamente", cancelButtonText: "No, cancelar",
        dangerMode: true,
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_area_permanente", {area_id: area_id}, function(data){
                swal.fire("Eliminada", "El área ha sido borrada permanentemente.", "success");
                tablaAreas.ajax.reload();
                cargarSelectAreas(); 
                listarPuestos();
            });
        }
    });
}

// 1. DESACTIVAR (Antiguo eliminarPuesto)
function desactivarPuesto(pue_id) {
    swal.fire({
        title: "¿Estás seguro?", text: "¡El puesto será marcado como INACTIVO!", icon: "warning",
        showCancelButton: true, confirmButtonText: "Sí, desactivar", cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_puesto", {pue_id: pue_id}, function(data){
                swal.fire("Desactivado", "El puesto ha sido desactivado.", "success");
                tablaPuestos.ajax.reload();
            });
        }
    });
}

// 2. ACTIVAR PUESTO (Nuevo)
function activarPuesto(pue_id) {
    swal.fire({
        title: "¿Estás seguro?", text: "¡El puesto será marcado como ACTIVO!", icon: "info",
        showCancelButton: true, confirmButtonText: "Sí, activar", cancelButtonText: "No, cancelar",
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=activar_puesto", {pue_id: pue_id}, function(data){
                swal.fire("Activado", "El puesto ha sido activado.", "success");
                tablaPuestos.ajax.reload();
            });
        }
    });
}

// 3. ELIMINACIÓN PERMANENTE DE PUESTO (Nuevo)
function eliminarPuestoPermanente(pue_id) {
    swal.fire({
        title: "¿Estás COMPLETAMENTE seguro?", text: "¡El puesto será ELIMINADO permanentemente!", icon: "error",
        showCancelButton: true, confirmButtonText: "Sí, borrar definitivamente", cancelButtonText: "No, cancelar",
        dangerMode: true,
    }).then((result) => {
        if (result.value) {
            $.post("../../controller/MntCapitalHumano.php?op=eliminar_puesto_permanente", {pue_id: pue_id}, function(data){
                swal.fire("Eliminado", "El puesto ha sido borrado permanentemente.", "success");
                tablaPuestos.ajax.reload();
            });
        }
    });
}


/* *************************************************************** */
/* FUNCIONES DE SELECTS (Carga de datos) */
/* *************************************************************** */

function cargarSelectDepartamentos() {
    $.post("../../controller/MntCapitalHumano.php?op=select_departamentos", function(data){
        $('#dep_id_area').html(data);
    });
}

function cargarSelectAreas() {
    $.post("../../controller/MntCapitalHumano.php?op=select_areas", function(data){
        $('#area_id_puesto').html(data);
    });
}