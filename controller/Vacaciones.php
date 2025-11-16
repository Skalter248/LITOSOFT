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
            // 1. Validar y obtener datos del POST
            $usu_id = $_POST["usu_id"];
            $vac_fecha_inicio = $_POST["vac_fecha_inicio"];
            $vac_fecha_fin = $_POST["vac_fecha_fin"];
            $vac_dias_solicitados = (int)$_POST["vac_dias_solicitados"]; // Días naturales
            $vac_dias_habiles = (int)$_POST["vac_dias_habiles"];     // Días hábiles
            
            // 2. Validación de saldo mínima (aunque el JS ya lo hace, es bueno en servidor)
            $datos_usuario = $usuario->get_usuario_por_id($usu_id);
            $fecha_ingreso = $datos_usuario['fecha_ingreso_planta'];
            $resumen = $vacaciones->get_resumen_vacaciones($usu_id, $fecha_ingreso);
            
            if ($vac_dias_habiles <= 0) {
                echo "Error: El rango de fechas no incluye días hábiles.";
                exit();
            }
            if ($vac_dias_habiles > $resumen['dias_disponibles']) {
                 echo "Error: Los días solicitados (" . $vac_dias_habiles . ") exceden el saldo disponible (" . $resumen['dias_disponibles'] . ").";
                 exit();
            }

            // 3. Insertar en la base de datos
            $resultado = $vacaciones->insert_solicitud(
                $usu_id, 
                $vac_fecha_inicio, 
                $vac_fecha_fin, 
                $vac_dias_solicitados, 
                $vac_dias_habiles
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
            // NOTA: Aquí se debe aplicar lógica de permisos (solo Jefes/Admins ven esto)
            // Por ahora, listamos todo, pero se debe restringir por rol de usuario.
            
            $datos = $vacaciones->get_solicitudes_pendientes();
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


        case "listar_solicitudes_pendientes_por_jefe":
            // Lógica pendiente
            break;

    }