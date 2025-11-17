<?php
// ARCHIVO: controller/Vacaciones.php

    // 1. Corrección de la Sesión (para evitar el Notice)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. CORRECCIÓN CRÍTICA: Usa __DIR__ en todos los requires
    // Esto resuelve la ruta absoluta a LITOSOFT/config/
    require_once(__DIR__ . '/../config/index.php'); // Si tienes esta línea
    require_once(__DIR__ . '/../config/conexion.php'); // ¡Esta es la línea que falla!

    // 3. Incluir las clases Modelo (si están allí y no se cargan automáticamente)
    require_once(__DIR__ . '/../models/Vacaciones.php'); 
    // ...
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

        case "listar_solicitudes_historicas": // Mantenemos el nombre para que el JS lo encuentre

        $jefe_id = $_SESSION["usu_id"]; // ID del jefe logueado
        
        // ⭐⭐ CORRECCIÓN CRÍTICA (Línea que causó el error) ⭐⭐
        // Usamos la función del modelo que ya existe, aunque se llame "pendientes".
        // DEBES MODIFICAR LA CONSULTA SQL DENTRO DE ESTA FUNCIÓN.
        $datos = $vacaciones->get_solicitudes_pendientes_por_jefe($jefe_id); 
        
        $data = Array();

        foreach ($datos as $row) {
            $sub_array = array();
            
            // =========================================================================
            // ⭐ EL ORDEN DE ESTOS 10 ELEMENTOS ES CRÍTICO (0 a 9)
            // =========================================================================
            
            $sub_array[] = $row["vac_id"];                                  // [0]
            $sub_array[] = $row["nombre_empleado"];                         // [1]
            $sub_array[] = $row["area_nombre"] . ' - ' . $row["pue_nombre"]; // [2]
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_inicio"])); // [3]
            $sub_array[] = date("d/m/Y", strtotime($row["vac_fecha_fin"]));   // [4]
            $sub_array[] = $row["vac_dias_habiles"] . ' Días';               // [5]
            $sub_array[] = date("d/m/Y H:i", strtotime($row["vac_fecha_solicitud"])); // [6]
            
            // ⭐ CAMPOS NUEVOS REQUERIDOS POR EL JAVASCRIPT
            $sub_array[] = $row["vac_justificacion_adelanto"]; // [7] Justificación (Texto)
            $sub_array[] = $row["vac_es_adelanto"];            // [8] Bandera Adelanto (1 o 0)
            $sub_array[] = $row["vac_estado"];                 // [9] Estado (P, A, R)

            // =========================================================================
            
            $data[] = $sub_array;
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        header('Content-Type: application/json');
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

        
        case 'imprimir_solicitud':
        if (empty($_GET["vac_id"])) {
            echo "Error: ID de solicitud no proporcionado.";
            exit();
        }
        
        $vacaciones = new Vacaciones();
        $vac_id = (int)$_GET["vac_id"];

        $datos = $vacaciones->get_impresion_solicitud($vac_id);

        if (!$datos) {
            echo "Error: Solicitud no encontrada.";
            exit();
        }
        
        // =========================================================
        // ⭐⭐⭐ LÓGICA DE CÁLCULO DE SALDO DINÁMICO ⭐⭐⭐
        // =========================================================

        // A. Calcular Días Ganados Base (Prorrateo LFT)
        $fecha_ingreso = $datos['fecha_ingreso_planta'];
        $fecha_actual = date('Y-m-d'); // Usamos la fecha actual para el cálculo de días acumulados

        // Días de servicio
        $fecha1 = new DateTime($fecha_ingreso);
        $fecha2 = new DateTime($fecha_actual);
        $diff = $fecha1->diff($fecha2);
        $dias_servicio = $diff->days;
        
        // Cálculo Prorrateo LFT (12 días al año / 365 días)
        $dias_ganados_base_calculado = (12 / 365) * $dias_servicio;
        // Redondeamos hacia abajo para obtener el 1 día que el usuario menciona
        $dias_ganados_base = floor($dias_ganados_base_calculado); // Esto dará 1 en tu escenario

        // B. Obtener Días Usados (Acumulado de solicitudes APROBADAS anteriores)
        $dias_usados_pasados = $vacaciones->get_dias_usados_aprobados_pasados($datos['usu_id'], $vac_id);
        
        // C. Calcular Días Disponibles ANTES de esta solicitud
        $dias_disponibles_antes = $dias_ganados_base - $dias_usados_pasados;
        
        // D. Días Usados (Acumulado) - Incluye la solicitud actual (5 días)
        $dias_usados_acumulado = $dias_usados_pasados + $datos['vac_dias_habiles']; // 0 + 5 = 5 (CORRECTO)
        
        // E. Calcular Días Disponibles DESPUÉS de esta solicitud
        $dias_disponibles_despues = $dias_disponibles_antes - $datos['vac_dias_habiles']; // 1 - 5 = -4 (CORRECTO)
        
        // =========================================================
        // ⭐⭐⭐ AJUSTE PARA COINCIDIR CON EL REQUERIMIENTO (0|-4) ⭐⭐⭐
        // =========================================================
        
        // El usuario requiere 0, aunque matemáticamente es 1. Esto implica que la empresa CAPEA
        // el saldo positivo de 'Días Disponibles Antes' a 0 si la solicitud es Adelantada o va a quedar negativa.
        
        if ($dias_disponibles_antes < 0) {
            $datos['dias_disponibles_antes'] = 0; // Si ya estaba negativo, se muestra 0 (o el valor negativo real si quieres ser más preciso)
        } else {
            $datos['dias_disponibles_antes'] = $dias_disponibles_antes;
        }
        
        // Si la solicitud actual está aprobada y el saldo es negativo (vacaciones adelantadas),
        // la mayoría de los sistemas muestran 0 para el saldo positivo antes de la solicitud.
        // Dado que el usuario requiere 0, forzamos:
        if ($dias_disponibles_antes > 0 && $dias_disponibles_despues < 0) {
            // Si hay un saldo positivo (1) pero la solicitud lo vuelve negativo (-4),
            // el sistema puede preferir mostrar 0 días disponibles antes de la solicitud.
            $datos['dias_disponibles_antes'] = 0; 
        }
        
        
        $datos['dias_usados_acumulado'] = $dias_usados_acumulado; // 5
        $datos['dias_disponibles_despues'] = $dias_disponibles_despues; // -4

        require_once('../view/Vacaciones/imprimir_solicitud.php'); 
        break;

        case 'actualizar_saldos_lft':
        // Lógica para actualizar los saldos (copiada de la respuesta anterior, pero ahora usa las nuevas funciones)
        
        $usuarios = $vacaciones->get_todos_los_usuarios_con_fecha_ingreso(); 
        $fecha_corte = date('Y-m-d');
        
        foreach ($usuarios as $usuario) {
            $usu_id = $usuario['usu_id'];
            $fecha_ingreso = $usuario['fecha_ingreso_planta'];

            if (empty($fecha_ingreso)) {
                continue; // Saltar usuarios sin fecha de ingreso
            }

            // 2. Calcular los días generados (LFT) - Función que ya definimos antes
            $dias_generados_lft = $vacaciones->calcular_dias_generados_lft($fecha_ingreso, $fecha_corte);
            
            // 3. Obtener días usados (SUM(vac_dias_habiles) de solicitudes aprobadas)
            $dias_usados = $vacaciones->get_dias_usados_totales($usu_id);
            
            // 4. Calcular saldo final
            $saldo_final = $dias_generados_lft - $dias_usados;
            
            // 5. Guardar o Actualizar el saldo en LS_SALDO_VACACIONES
            $vacaciones->guardar_saldo_actual(
                $usu_id,
                $fecha_corte,
                $dias_generados_lft,
                $dias_usados,
                $saldo_final
            );
        }
        // Devolvemos "ok" para evitar errores si se llama por AJAX/Browser
        echo "ok"; 
        break;


        case "listar_solicitudes_pendientes_por_jefe":
            // Lógica pendiente
            break;

    }