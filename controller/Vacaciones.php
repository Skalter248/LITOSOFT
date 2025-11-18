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
    case 'calcular_dias_habiles':
        // Llama a la función del modelo para la precisión del cálculo.
        $fecha_inicio = $_POST['vac_fecha_inicio'] ?? '';
        $fecha_fin = $_POST['vac_fecha_fin'] ?? '';
        
        $dias_habiles = $vacaciones->calcular_dias_habiles($fecha_inicio, $fecha_fin);
        // Retorna el número de días directamente al JS
        echo $dias_habiles; 
        break;

    // =================================================================
    // CASE 2: GUARDAR NUEVA SOLICITUD
    // =================================================================
    case 'guardar_solicitud':
        
        // Validación mínima
        if (empty($_POST['vac_fecha_inicio']) || empty($_POST['vac_fecha_fin'])) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar ambas fechas.']);
            exit();
        }

        $usu_id = $_SESSION['usu_id']; 
        $fecha_inicio = $_POST['vac_fecha_inicio'];
        $fecha_fin = $_POST['vac_fecha_fin'];
        $dias_habiles = $_POST['vac_dias_habiles'] ?? 0.00; // Viene del cálculo JS/backend
        $observaciones = $_POST['vac_observaciones'] ?? '';

        $resultado = $vacaciones->guardar_solicitud($usu_id, $fecha_inicio, $fecha_fin, $dias_habiles, $observaciones);
        // El modelo devuelve una respuesta JSON {"success": true} o {"success": false, "message": "..."}
        echo $resultado; 
        break;

    // =================================================================
    // CASE 3: LISTAR SOLICITUDES PARA DATATABLES
    // =================================================================
    case 'listar_mis_solicitudes':
        
        $usu_id = $_SESSION['usu_id']; // Usamos el ID de la sesión
        $datos = $vacaciones->listar_mis_solicitudes($usu_id);
        $data = Array();

        foreach ($datos as $row) {
            $sub_array = array();
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
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;
        
    // =================================================================
    // OTROS CASES (Aquí irían aprobar_solicitud, rechazar_solicitud, etc.)
    // =================================================================

    // ... otros casos ...
}
?>