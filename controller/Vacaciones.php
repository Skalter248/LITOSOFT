<?php
// ARCHIVO: controller/Vacaciones.php

// 1. GESTIÓN DE SESIÓN Y REQUIRES
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. REQUIRES CRÍTICOS (Ajusta la ruta si tu controlador está en otro directorio)
// Usamos require_once para asegurar la conexión y el modelo
require_once('../config/conexion.php'); 
require_once('../models/Vacaciones.php'); 


$vacaciones = new Vacaciones();
$op = isset($_GET["op"]) ? $_GET["op"] : "";

// Si no hay sesión, salir inmediatamente para evitar errores
if (!isset($_SESSION['usu_id']) && $op != '') {
    // Si la operación no es de lectura, devolvemos un error
    if ($op == 'guardar_solicitud') {
        echo json_encode(['success' => false, 'message' => 'Sesión expirada. Por favor, inicie sesión de nuevo.']);
    } else {
        // Para DataTables o cálculos simples, evitamos errores fatales
        echo json_encode(["data" => []]);
    }
    exit();
}

switch ($op) {

    // =================================================================
    // CASE 1: CALCULAR DÍAS HÁBILES
    // =================================================================
    case 'calcular_dias':
    // Verifica que ambas fechas hayan llegado
    if (empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
        echo json_encode(['error' => 'Debe seleccionar ambas fechas.']);
        exit();
    }
    
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Llama al método del modelo
    $resultado = $vacaciones->calcular_dias_habiles($fecha_inicio, $fecha_fin);
    
    // Devuelve días hábiles y naturales
    echo json_encode($resultado); 
    break;

    // =================================================================
    // CASE 2: GUARDAR NUEVA SOLICITUD
    // =================================================================
    case 'guardar_solicitud':
<<<<<<< HEAD
        $usu_id = $_SESSION['usu_id'];
        $fecha_inicio = $_POST['vac_fecha_inicio'];
        $fecha_fin = $_POST['vac_fecha_fin'];
        $dias_habiles = $_POST['vac_dias_habiles'];
        $observaciones = $_POST['vac_observaciones'] ?? '';

        // Llamar al modelo
        $respuesta = $vacaciones->guardar_solicitud($usu_id, $fecha_inicio, $fecha_fin, $dias_habiles, $observaciones);
        
        if ($respuesta['status']) {
            echo json_encode(['status' => 'ok', 'message' => $respuesta['message']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $respuesta['message']]);
        }
        break;
=======
    $usu_id = $_SESSION['usu_id']; 

    // 1. Obtener el ID del jefe
    $usu_jefe_id = $vacaciones->get_jefe_inmediato($usu_id);
    if ($usu_jefe_id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró un jefe inmediato asociado.']);
        exit();
    }
    
    // 2. Preparar los datos
    $datos_solicitud = [
        'usu_id' => $usu_id,
        'usu_jefe_id' => $usu_jefe_id,
        'vac_fecha_inicio' => $_POST['vac_fecha_inicio'],
        'vac_fecha_fin' => $_POST['vac_fecha_fin'],
        // Se asume que estos campos vienen calculados y validados desde JS
        'vac_dias_habiles' => (float)$_POST['vac_dias_habiles'],
        'vac_dias_naturales' => (int)$_POST['vac_dias_naturales'], 
        'vac_observaciones' => $_POST['vac_observaciones'] ?? ''
    ];

    // 3. Validar si hay días solicitados
    if ($datos_solicitud['vac_dias_habiles'] <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'El periodo no contiene días hábiles válidos para solicitar.']);
        exit();
    }

    // 4. Guardar la solicitud
    $resultado = $vacaciones->guardar_solicitud($datos_solicitud);
    
    if ($resultado === "ok") {
        echo json_encode(['status' => 'ok', 'message' => 'Solicitud enviada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $resultado]);
    }
    break;
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac

    case 'get_saldo':
        if (!isset($_SESSION['usu_id'])) {
            echo json_encode(['usu_dias_disponibles' => 0]);
            exit();
        }
        
        $usu_id = $_SESSION['usu_id'];
        // Reutilizamos la función que ya tienes en el modelo
        $saldo = $vacaciones->get_saldo_actual($usu_id);
        
        // Devolvemos el array completo en JSON
        echo json_encode($saldo);
        break;

    

    case 'calcular_dias_habiles':
    // Recibimos los datos del POST
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    // Llamamos al modelo
    $resultado = $vacaciones->calcular_dias_habiles($fecha_inicio, $fecha_fin);
    
    // Devolvemos JSON
    echo json_encode($resultado);
    break;

<<<<<<< HEAD
    case 'listar_solicitudes_jefe':
        $jefe_id = $_SESSION['usu_id'];
        $datos = $vacaciones->listar_solicitudes_por_jefe($jefe_id);
        $data = Array();

        foreach ($datos as $row) {
            $sub_array = array();
            
            // 1. Empleado
            $sub_array[] = '<div class="font-weight-bold">' . $row["nombre_empleado"] . '</div>';
            
            // 2. Fechas
            $inicio = date("d/m/Y", strtotime($row["vac_fecha_inicio"]));
            $fin = date("d/m/Y", strtotime($row["vac_fecha_fin"]));
            $sub_array[] = $inicio . ' al ' . $fin;
            
            // 3. Días y Tipo (Adelanto/Normal)
            $es_adelanto = strpos($row['vac_observaciones'], '[SOLICITUD DE ADELANTO]') !== false;
            $badge_dias = $es_adelanto ? 'badge-danger' : 'badge-info';
            $texto_extra = $es_adelanto ? '<br><small class="text-danger font-weight-bold">¡Adelanto!</small>' : '';
            
            $sub_array[] = '<span class="badge '.$badge_dias.' font-size-14">' . $row["vac_dias_habiles"] . ' días</span>' . $texto_extra;
            
            // 4. Observaciones
            $sub_array[] = $row["vac_observaciones"];

            // 5. Estado
            $estado = $row["vac_estado"];
            $class_estado = 'secondary';
            if($estado == 'Aprobada') $class_estado = 'success';
            if($estado == 'Rechazada') $class_estado = 'danger';
            if($estado == 'Pendiente') $class_estado = 'warning';
            
            $sub_array[] = '<span class="label label-pill label-'.$class_estado.'">'.$estado.'</span>';
            
            // 6. Acciones (Solo si está pendiente)
            $botones = '';
            if ($estado == 'Pendiente') {
                $botones .= '<button type="button" onClick="gestionar('.$row["vac_id"].', \'aprobar\');" class="btn btn-success btn-sm mr-1" title="Aprobar"><i class="fa fa-check"></i></button>';
                $botones .= '<button type="button" onClick="gestionar('.$row["vac_id"].', \'rechazar\');" class="btn btn-danger btn-sm" title="Rechazar"><i class="fa fa-times"></i></button>';
            } else if ($estado == 'Aprobada') {
                // APROBADA: Mostrar el botón de Imprimir
                $botones .= '<button type="button" onClick="imprimirSolicitud('.$row["vac_id"].');" class="btn btn-primary btn-sm" title="Imprimir Formato SGC001"><i class="fa fa-print"></i></button>';
            } else {
                // RECHAZADA/CANCELADA: Mostrar el mensaje de procesada
                $botones = '<span class="text-muted"><i class="fa fa-lock"></i> Procesada</span>';
            }

            $sub_array[] = $botones;
            
            $data[] = $sub_array;
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;


        case 'generar_formato_impresion':
        if (!isset($_POST['vac_id'])) {
            die(json_encode(['status' => 'error', 'message' => 'ID de solicitud no proporcionado.']));
        }
        
        $vac_id = $_POST['vac_id'];
        
        $datos = $vacaciones->get_solicitud_para_impresion($vac_id);

        if (!$datos) {
            die(json_encode(['status' => 'error', 'message' => 'Solicitud no encontrada.']));
        }
        
        // Manejar explícitamente el error de SQL
        if (isset($datos['error_sql'])) {
            // Este mensaje será visible en el SweetAlert, muy útil para el debug
            die(json_encode(['status' => 'error', 'message' => 'Error de Base de Datos al buscar datos de impresión: ' . $datos['error_sql']]));
        }

        // Si no hay error, procede a guardar en sesión y devolver la URL
        $_SESSION['datos_impresion_vacaciones'] = $datos;
        
        echo json_encode(['status' => 'ok', 'url' => '../AprobacionSolicitud/FormatoVacaciones.php']);
        break;

=======
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
    // =================================================================
    // CASE 3: LISTAR SOLICITUDES PARA DATATABLES
    // =================================================================
    case 'listar_mis_solicitudes':
<<<<<<< HEAD
        $usu_id = $_SESSION['usu_id'];
=======
        
        $usu_id = $_SESSION['usu_id']; // Usamos el ID de la sesión
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        $datos = $vacaciones->listar_mis_solicitudes($usu_id);
        $data = Array();

        foreach ($datos as $row) {
            $sub_array = array();
<<<<<<< HEAD
            
            // 1. ID Solicitud
            $sub_array[] = $row["vac_id"];
            
            // 2. Fecha Inicio
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_inicio"]));
            
            // 3. Fecha Fin
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_fin"]));
            
            // 4. Días Hábiles
            $sub_array[] = $row["vac_dias_habiles"];
            
            // 5. Estado (Con diseño)
            $estado = $row["vac_estado"];
            $badge_class = 'secondary';
            
            if ($estado == 'Aprobada') $badge_class = 'success';
            elseif ($estado == 'Rechazada') $badge_class = 'danger';
            elseif ($estado == 'Cancelada') $badge_class = 'secondary'; // Gris para cancelada
            elseif ($estado == 'Pendiente') $badge_class = 'warning';
            
            $sub_array[] = '<span class="label label-pill label-'.$badge_class.'">'.$estado.'</span>';
            
            // 6. Fecha Solicitud
            $sub_array[] = date("d/m/Y H:i", strtotime($row["vac_fecha_solicitud"]));
            
            // 7. Aprobador
            if ($row["nombre_aprobador"]) {
                $sub_array[] = $row["nombre_aprobador"];
            } else {
                $sub_array[] = '<span class="text-muted">---</span>';
            }
            
            // 8. Acciones
            $botones = '';
            
            // Botón Ver Detalle (siempre visible)
            // $botones .= '<button type="button" onClick="ver('.$row["vac_id"].');" class="btn btn-inline btn-primary btn-sm ladda-button"><i class="fa fa-eye"></i></button>';

            // Botón Cancelar (Solo si está Pendiente)
            if ($estado == 'Pendiente') {
                $botones .= '<button type="button" onClick="cancelarSolicitud('.$row["vac_id"].');" class="btn btn-inline btn-danger btn-sm ladda-button" title="Cancelar Solicitud"><i class="fa fa-trash"></i></button>';
            }
            
            $sub_array[] = $botones;
            
            $data[] = $sub_array;
        }

=======
            $sub_array[] = $row["vac_id"];
            // Formateo de fechas a d/m/Y
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_inicio"]));
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_fin"]));
            $sub_array[] = $row["vac_dias_habiles"];
            
            // Lógica para mostrar el estado con etiquetas de color
            switch ($row["vac_estado"]) {
                case 'Aprobada':
                    $sub_array[] = '<span class="label label-success">Aprobada</span>';
                    $acciones = '<button type="button" onClick="verDetalle('.$row["vac_id"].');" class="btn btn-info btn-sm">Ver</button>';
                    break;
                case 'Rechazada':
                    $sub_array[] = '<span class="label label-danger">Rechazada</span>';
                    $acciones = '<button type="button" onClick="verDetalle('.$row["vac_id"].');" class="btn btn-info btn-sm">Ver</button>';
                    break;
                case 'Pendiente':
                default:
                    $sub_array[] = '<span class="label label-warning">Pendiente</span>';
                    // Botón para cancelar (requiere función JS cancelarSolicitud)
                    $acciones = '<button type="button" onClick="cancelarSolicitud('.$row["vac_id"].');" class="btn btn-danger btn-sm">Cancelar</button>';
                    break;
            }
            
            $sub_array[] = $acciones;
            $data[] = $sub_array;
        }

        // Estructura de respuesta requerida por DataTables
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;
<<<<<<< HEAD


        case 'gestion_solicitud':
        // Este case lo usarás tanto para cancelar (empleado) como para aprobar (jefe)
        $vac_id = $_POST['vac_id'];
        $accion = $_POST['accion']; // 'aprobar', 'rechazar', 'cancelar'
        $aprobador_id = $_SESSION['usu_id']; // El usuario logueado hace la acción

        $respuesta = $vacaciones->gestionar_solicitud($vac_id, $accion, $aprobador_id);
        echo json_encode($respuesta);
        break;
=======
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        
    // =================================================================
    // OTROS CASES (Aquí irían aprobar_solicitud, rechazar_solicitud, etc.)
    // =================================================================

    // ... otros casos ...
}
?>