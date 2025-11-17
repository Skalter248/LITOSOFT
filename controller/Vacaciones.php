<?php
// ARCHIVO: controller/Vacaciones.php

    session_start();

    require_once("../config/index.php"); 
    require_once("../config/conexion.php"); // NECESARIO para que la clase 'Conexion' sea visible
    require_once("../models/Vacaciones.php");
    require_once("../models/Usuario.php"); 

    $vacaciones = new Vacaciones();
    $usuario = new Usuario();
    
    
    $op = isset($_GET["op"]) ? $_GET["op"] : null;

    switch ($op) {
        
        case "get_resumen_dias":
            // Asegúrate de que la sesión tenga el usu_id logueado
            $usu_id = $_POST["usu_id"] ?? $_SESSION["usu_id"]; 

            // 1. Obtener la fecha de ingreso a planta
            $datos_usuario = $usuario->get_usuario_por_id($usu_id);
            if (empty($datos_usuario) || empty($datos_usuario['fecha_ingreso_planta'])) {
                echo json_encode(["error" => "No se encontró el usuario o la fecha de ingreso."]);
                exit();
            }

            $fecha_ingreso = $datos_usuario['fecha_ingreso_planta'];

            // 2. Calcular resumen de vacaciones
            $resumen = $vacaciones->get_resumen_vacaciones($usu_id, $fecha_ingreso);
            
            // Retornamos la fecha de ingreso y el resumen (antiguedad, generados, usados, disponibles)
            echo json_encode(array_merge($resumen, ["fecha_ingreso_planta" => $fecha_ingreso]));
            break;

        /* ========================================================================= */
        /* CRUD DE SOLICITUDES (Se hará en el siguiente paso)                         */
        /* ========================================================================= */
            
        case "guardar_solicitud":
        // 1. Obtener datos del POST
        $usu_id = $_POST["usu_id"];
        $vac_fecha_inicio = $_POST["vac_fecha_inicio"];
        $vac_fecha_fin = $_POST["vac_fecha_fin"];
        $vac_dias_solicitados = (int)$_POST["vac_dias_solicitados"]; 
        $vac_dias_habiles = (int)$_POST["vac_dias_habiles"];     

        // OBTENER LA JUSTIFICACIÓN
        $vac_justificacion_adelanto = $_POST['vac_justificacion_adelanto'] ?? null;
        
        // 2. Validación de días hábiles mínimos
        if ($vac_dias_habiles <= 0) {
            // Dejamos este error de días hábiles, que es correcto
            echo "Error: El rango de fechas seleccionado no incluye días hábiles o los días solicitados son cero.";
            exit();
        }
        
        // *** EL BLOQUE DE VALIDACIÓN DE SALDO HA SIDO ELIMINADO AQUÍ ***

        // 3. Insertar en la base de datos (con el nuevo campo)
        $resultado = $vacaciones->insert_solicitud(
            $usu_id, 
            $vac_fecha_inicio, 
            $vac_fecha_fin, 
            $vac_dias_solicitados, 
            $vac_dias_habiles,
            $vac_justificacion_adelanto // <-- ¡Nuevo campo!
        );

        echo ($resultado) ? "ok" : "Error al registrar la solicitud en la base de datos.";
        break;    

        case "listar_solicitudes_por_usuario":
            $usu_id = $_SESSION["usu_id"]; 
            $datos = $vacaciones->get_solicitudes_por_usuario($usu_id);
            $data = Array();

            foreach ($datos as $row) {
                $sub_array = array();
                
                $sub_array[] = $row["vac_id"];
                $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_inicio"]));
                $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_fin"]));
                $sub_array[] = $row["vac_dias_habiles"] . ' Días';
                
                // Formato del Estado
                $estado = $row["vac_estado"];
                $badge = "";
                switch ($estado) {
                    case 'Aprobada': $badge = '<span class="label label-success">Aprobada</span>'; break;
                    case 'Rechazada': $badge = '<span class="label label-danger">Rechazada</span>'; break;
                    case 'Pendiente': $badge = '<span class="label label-warning">Pendiente</span>'; break;
                    case 'Cancelada': $badge = '<span class="label label-default">Cancelada</span>'; break;
                }
                $sub_array[] = $badge;
                
                $sub_array[] = date("d/m/Y H:i", strtotime($row["vac_fecha_solicitud"]));
                
                // Nombre del Aprobador
                $nombre_aprobador = trim($row["nombre_aprobador"] ?? '');
                
                // Muestra el nombre solo si el estado NO es 'Pendiente' Y el nombre no es una cadena vacía.
                if ($row["vac_estado"] !== 'Pendiente') {
                    error_log("Solicitud ID: " . $row["vac_id"] . " | Nombre devuelto: '" . $nombre_aprobador . "'");
                }
                // LÓGICA DE VISUALIZACIÓN
                $sub_array[] = ($row["vac_estado"] === 'Pendiente') 
                    ? 'Pendiente' 
                    : (($nombre_aprobador) ? $nombre_aprobador : 'Administrador');
                    
                $botones = "";
                if ($row["vac_estado"] === 'Pendiente') {
                    // Botón de cancelar
                    $botones .= '<button type="button" onClick="cancelarSolicitud(' . $row["vac_id"] . ');" class="btn btn-danger btn-sm">Cancelar</button>';
                } else {
                    // Botón de detalle
                    $botones .= '<button type="button" onClick="verDetalle(' . $row["vac_id"] . ');" class="btn btn-info btn-sm">Ver</button>';
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

        case "cancelar_solicitud":
            $vac_id = $_POST["vac_id"];
            $usu_id = $_SESSION["usu_id"]; // El usuario logueado es quien cancela
            
            // Renombramos la variable a $stmt para reflejar que recibimos el objeto de la sentencia
            $stmt = $vacaciones->cancelar_solicitud($vac_id, $usu_id);
            
            // Verificamos si la ejecución fue exitosa Y si se modificó alguna fila (rowCount > 0)
            if ($stmt !== false && $stmt->rowCount() > 0) {
                 echo "ok";
            } 
            // Verificamos si la ejecución fue exitosa PERO no se modificaron filas (ya estaba cancelada/aprobada)
            elseif ($stmt !== false && $stmt->rowCount() === 0) {
                 echo "La solicitud no se pudo cancelar. Podría haber sido ya aprobada, rechazada o no existe.";
            } 
            // Si $stmt es false, hubo un error de ejecución en la base de datos
            else {
                 echo "Error al intentar cancelar la solicitud.";
            }
            break;

        case "listar_solicitudes_pendientes":
        // NOTA: Se ha corregido la lógica para usar el filtro por jefe.

        $jefe_id = $_SESSION["usu_id"]; // Obtenemos el ID del jefe logueado
        
        // Llamamos a la función CORRECTA, que filtra por jefe y excluye la auto-aprobación.
        $datos = $vacaciones->get_solicitudes_pendientes_por_jefe($jefe_id); 
        
        $data = Array();

        foreach ($datos as $row) {
            $sub_array = array();
            
            $sub_array[] = $row["vac_id"];
            $sub_array[] = $row["nombre_empleado"];
            $sub_array[] = $row["area_nombre"] . ' - ' . $row["pue_nombre"]; // Área y Puesto
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_inicio"]));
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_fin"]));
            $sub_array[] = $row["vac_dias_habiles"] . ' Días';
            $sub_array[] = date("d/m/Y H:i", strtotime($row["vac_fecha_solicitud"]));
            
            // Acciones (Aprobar/Rechazar)
            $botones = '';
            $botones .= '<button type="button" onClick="aprobarSolicitud(' . $row["vac_id"] . ');" class="btn btn-success btn-sm">Aprobar</button> ';
            $botones .= '<button type="button" onClick="rechazarSolicitud(' . $row["vac_id"] . ');" class="btn btn-danger btn-sm">Rechazar</button>';
            
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
         
        case "aprobar_solicitud":
        case "rechazar_solicitud":
            
            $vac_id = $_POST["vac_id"];
            $vac_jefe_id_aprobador = $_SESSION["usu_id"];
            
            // Determinar el estado basado en la operación
            $estado_nuevo = ($op === "aprobar_solicitud") ? 'Aprobada' : 'Rechazada';

            $stmt = $vacaciones->actualizar_estado_solicitud($vac_id, $estado_nuevo, $vac_jefe_id_aprobador);

            if ($stmt !== false && $stmt->rowCount() > 0) {
                 echo "ok";
            } elseif ($stmt !== false && $stmt->rowCount() === 0) {
                 echo "La solicitud ya ha sido procesada o no existe.";
            } else {
                 echo "Error al intentar actualizar el estado de la solicitud.";
            }
            break;   
            
        case "get_eventos_calendario":
        $usu_id = $_SESSION["usu_id"]; 
        $datos = $vacaciones->get_solicitudes_por_usuario($usu_id); // Reutilizamos la función de listado
        
        $eventos = [];
        
        foreach ($datos as $row) {
        $color = '#C0C0C0'; // Gris por defecto (para el estado Cancelada)
        $title = 'Días: ' . $row["vac_dias_habiles"];

        // Definición de colores basada en el estado
        switch ($row["vac_estado"]) {
            case 'Aprobada':
                $color = '#28a745'; // Verde
                $title = 'APROBADA | ' . $title;
                break;
            case 'Pendiente':
                $color = '#ffc107'; // Amarillo
                $title = 'PENDIENTE | ' . $title;
                break;
            case 'Rechazada':
                $color = '#dc3545'; // Rojo (Solo para rechazos)
                $title = 'RECHAZADA | ' . $title;
                break;
            case 'Cancelada': // Nuevo caso específico para Canceladas (Gris)
                $color = '#6c757d'; // Gris oscuro (Bootstrap secondary)
                $title = 'CANCELADA | ' . $title;
                break;
        }

            $eventos[] = [
                'title' => $title,
                // FullCalendar usa 'start' y 'end'. Añadimos un día extra a 'end' para que la fecha final sea inclusiva.
                'start' => $row["vac_fecha_inicio"],
                'end' => date('Y-m-d', strtotime($row["vac_fecha_fin"] . ' +1 day')),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [ // Datos adicionales que pueden ser útiles
                    'estado' => $row["vac_estado"],
                    'vac_id' => $row["vac_id"]
                ]
            ];
        }

        echo json_encode($eventos);
        break;
        
       case "get_detalles_solicitud":
        if (!isset($_POST["vac_id"])) {
            header('Content-Type: application/json');
            echo json_encode(["error" => "ID de solicitud no proporcionado."]);
            exit();
        }
        
        $vac_id = $_POST["vac_id"];
        $detalles = $vacaciones->get_detalles_solicitud($vac_id); 

        if ($detalles) {
            
            // --- 1. Obtener los datos de resumen de vacaciones (Días Disponibles/Ocupados) ---
            $usu_id = $detalles['usu_id'];
            
            // **IMPORTANTE:** Verificar si la función existe. Si no existe, causa un error 500 fatal.
            if (method_exists($vacaciones, 'get_resumen_vacaciones')) {
                
                $fecha_ingreso = $detalles['fecha_ingreso_planta'];
                $resumen_dias = $vacaciones->get_resumen_vacaciones($usu_id, $fecha_ingreso); 

                if (is_array($resumen_dias)) {
                    $detalles = array_merge($detalles, $resumen_dias);
                } else {
                    $detalles['dias_disponibles'] = 'ERROR DE RESUMEN';
                    $detalles['dias_ocupados'] = 'ERROR DE RESUMEN';
                }
            } else {
                // Si la función get_resumen_vacaciones no existe, añadimos N/A y registramos un error.
                error_log("FATAL: El método get_resumen_vacaciones no existe en la clase Vacaciones.");
                $detalles['dias_disponibles'] = 'N/A';
                $detalles['dias_ocupados'] = 'N/A';
            }
            
            // --- 2. Formatear fechas ---
            $detalles['vac_fecha_solicitud_f'] = date("d/m/Y H:i", strtotime($detalles['vac_fecha_solicitud']));
            $detalles['vac_fecha_aprobacion_f'] = !empty($detalles['vac_fecha_aprobacion']) ? date("d/m/Y H:i", strtotime($detalles['vac_fecha_aprobacion'])) : 'N/A';
            $detalles['vac_fecha_inicio_f'] = date("d/m/Y", strtotime($detalles['vac_fecha_inicio']));
            $detalles['vac_fecha_fin_f'] = date("d/m/Y", strtotime($detalles['vac_fecha_fin']));
            
            header('Content-Type: application/json');
            echo json_encode($detalles);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["error" => "Solicitud no encontrada en la base de datos."]);
        }
        break;


        case "listar_solicitudes_pendientes_por_jefe":
            // Lógica pendiente
            break;

    }