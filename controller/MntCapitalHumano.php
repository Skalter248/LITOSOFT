<?php
    // ARCHIVO: controller/MntCapitalHumano.php

    require_once("../config/index.php"); 
    require_once("../models/CapitalHumano.php");

    $capital_humano = new CapitalHumano();
    $op = isset($_GET["op"]) ? $_GET["op"] : null;

    switch ($op) {
        
        /* ========================================================================= */
        /* DEPARTAMENTOS                                                             */
        /* ========================================================================= */

        case "guardaryeditar_departamento":
            $dep_id = isset($_POST["dep_id"]) ? $_POST["dep_id"] : "";
            $dep_nombre = isset($_POST["dep_nombre"]) ? $_POST["dep_nombre"] : "";

            if (empty($dep_nombre)) { echo "El nombre del departamento es obligatorio."; exit(); }

            if (empty($dep_id)) {
                $resultado = $capital_humano->insert_departamento($dep_nombre);
                echo ($resultado === false) ? "Error al guardar o el departamento ya existe." : "ok";
            } else {
                $capital_humano->update_departamento($dep_id, $dep_nombre);
                echo "ok";
            }
            break;

        case "listar_departamentos":
            $datos = $capital_humano->get_departamentos();
            $data = Array();
            foreach ($datos as $row) {
                $estatus_label = ($row["dep_estatus"] == 'ACTIVO') ? '<span class="label label-success">ACTIVO</span>' : '<span class="label label-danger">INACTIVO</span>';
                
                // Lógica de Botones Condicionales
                $botones = '<button type="button" onClick="editarDepartamento(' . $row["dep_id"] . ');" class="btn btn-warning btn-sm">Editar</button>';
                $botones .= '&nbsp;';

                if ($row["dep_estatus"] == 'ACTIVO') {
                    // Si está ACTIVO, se muestra botón para DESACTIVAR
                    $botones .= '<button type="button" onClick="desactivarDepartamento(' . $row["dep_id"] . ');" class="btn btn-danger btn-sm">Desactivar</button>';
                } else {
                    // Si está INACTIVO, se muestra botón para ACTIVAR y ELIMINAR PERMANENTE
                    $botones .= '<button type="button" onClick="activarDepartamento(' . $row["dep_id"] . ');" class="btn btn-success btn-sm">Activar</button>';
                    $botones .= '&nbsp;';
                    $botones .= '<button type="button" onClick="eliminarDepartamentoPermanente(' . $row["dep_id"] . ');" class="btn btn-dark btn-sm">Borrar</button>';
                }
                
                $data[] = array(
                    $row["dep_nombre"],
                    $estatus_label,
                    $row["dep_fecha_creacion"],
                    $botones // Usa la variable $botones
                );
            }

            echo json_encode(array("sEcho" => 1, "iTotalRecords" => count($data), "iTotalDisplayRecords" => count($data), "aaData" => $data));
            break;

        case "mostrar_departamento":
            $datos = $capital_humano->get_departamento_por_id($_POST["dep_id"]);
            if (is_array($datos) && count($datos) > 0) {
                echo json_encode($datos);
            }
            break;

        case "eliminar_departamento": // Renombrado a desactivar (opcional)
            $capital_humano->delete_departamento($_POST["dep_id"]); // Cambia el estado a INACTIVO
            echo "ok";
            break;

        case "activar_departamento":
            $capital_humano->activate_departamento($_POST["dep_id"]);
            echo "ok";
            break;    
            
        case "eliminar_departamento_permanente":
            $capital_humano->delete_departamento_permanente($_POST["dep_id"]);
            echo "ok";
            break;    

        /* ========================================================================= */
        /* ÁREAS                                                                     */
        /* ========================================================================= */

        case "guardaryeditar_area":
            $area_id = isset($_POST["area_id"]) ? $_POST["area_id"] : "";
            $dep_id = isset($_POST["dep_id"]) ? $_POST["dep_id"] : "";
            $area_nombre = isset($_POST["area_nombre"]) ? $_POST["area_nombre"] : "";

            if (empty($dep_id) || empty($area_nombre)) { echo "Todos los campos son obligatorios."; exit(); }

            if (empty($area_id)) {
                $resultado = $capital_humano->insert_area($dep_id, $area_nombre);
                echo ($resultado === false) ? "Error al guardar o el área ya existe en este departamento." : "ok";
            } else {
                $capital_humano->update_area($area_id, $dep_id, $area_nombre);
                echo "ok";
            }
            break;

        case "listar_areas":
            $datos = $capital_humano->get_areas();
            $data = Array();
            foreach ($datos as $row) {
                $estatus_label = ($row["area_estatus"] == 'ACTIVO') ? '<span class="label label-success">ACTIVO</span>' : '<span class="label label-danger">INACTIVO</span>';
                $botones = '<button type="button" onClick="editarArea(' . $row["area_id"] . ');" class="btn btn-warning btn-sm">Editar</button>&nbsp;';
                
                if ($row["area_estatus"] == 'ACTIVO') {
                    $botones .= '<button type="button" onClick="desactivarArea(' . $row["area_id"] . ');" class="btn btn-danger btn-sm">Desactivar</button>';
                } else {
                    $botones .= '<button type="button" onClick="activarArea(' . $row["area_id"] . ');" class="btn btn-success btn-sm">Activar</button>&nbsp;';
                    $botones .= '<button type="button" onClick="eliminarAreaPermanente(' . $row["area_id"] . ');" class="btn btn-dark btn-sm">Borrar</button>';
                }
                
                $data[] = array($row["dep_nombre"], $row["area_nombre"], $estatus_label, $row["area_fecha_creacion"], $botones);
            }
            echo json_encode(array("sEcho" => 1, "iTotalRecords" => count($data), "iTotalDisplayRecords" => count($data), "aaData" => $data));
            break;
        
        case "eliminar_area": // Desactivar
            $capital_humano->delete_area($_POST["area_id"]); echo "ok"; break;
        case "activar_area":
            $capital_humano->activate_area($_POST["area_id"]); echo "ok"; break;
        case "eliminar_area_permanente":
            $capital_humano->delete_area_permanente($_POST["area_id"]); echo "ok"; break;

        case "mostrar_area":
            $datos = $capital_humano->get_area_por_id($_POST["area_id"]);
            if (is_array($datos) && count($datos) > 0) {
                echo json_encode($datos);
            }
            break;

        case "eliminar_area":
            $capital_humano->delete_area($_POST["area_id"]);
            echo "ok";
            break;
            
        /* ========================================================================= */
        /* PUESTOS                                                                   */
        /* ========================================================================= */

        case "guardaryeditar_puesto":
            $pue_id = isset($_POST["pue_id"]) ? $_POST["pue_id"] : "";
            $area_id = isset($_POST["area_id"]) ? $_POST["area_id"] : "";
            $pue_nombre = isset($_POST["pue_nombre"]) ? $_POST["pue_nombre"] : "";

            if (empty($area_id) || empty($pue_nombre)) { echo "Todos los campos son obligatorios."; exit(); }

            if (empty($pue_id)) {
                $resultado = $capital_humano->insert_puesto($area_id, $pue_nombre);
                echo ($resultado === false) ? "Error al guardar o el puesto ya existe en esta área." : "ok";
            } else {
                $capital_humano->update_puesto($pue_id, $area_id, $pue_nombre);
                echo "ok";
            }
            break;

        case "listar_puestos":
            $datos = $capital_humano->get_puestos();
            $data = Array();
            foreach ($datos as $row) {
                $estatus_label = ($row["pue_estatus"] == 'ACTIVO') ? '<span class="label label-success">ACTIVO</span>' : '<span class="label label-danger">INACTIVO</span>';
                $botones = '<button type="button" onClick="editarPuesto(' . $row["pue_id"] . ');" class="btn btn-warning btn-sm">Editar</button>&nbsp;';
                
                if ($row["pue_estatus"] == 'ACTIVO') {
                    $botones .= '<button type="button" onClick="desactivarPuesto(' . $row["pue_id"] . ');" class="btn btn-danger btn-sm">Desactivar</button>';
                } else {
                    $botones .= '<button type="button" onClick="activarPuesto(' . $row["pue_id"] . ');" class="btn btn-success btn-sm">Activar</button>&nbsp;';
                    $botones .= '<button type="button" onClick="eliminarPuestoPermanente(' . $row["pue_id"] . ');" class="btn btn-dark btn-sm">Borrar</button>';
                }
                
                $data[] = array($row["area_nombre"], $row["pue_nombre"], $estatus_label, $row["pue_fecha_creacion"], $botones);
            }
            echo json_encode(array("sEcho" => 1, "iTotalRecords" => count($data), "iTotalDisplayRecords" => count($data), "aaData" => $data));
            break;

        case "eliminar_puesto": // Desactivar
            $capital_humano->delete_puesto($_POST["pue_id"]); echo "ok"; break;
        case "activar_puesto":
            $capital_humano->activate_puesto($_POST["pue_id"]); echo "ok"; break;
        case "eliminar_puesto_permanente":
            $capital_humano->delete_puesto_permanente($_POST["pue_id"]); echo "ok"; break;    

        case "mostrar_puesto":
            $datos = $capital_humano->get_puesto_por_id($_POST["pue_id"]);
            if (is_array($datos) && count($datos) > 0) {
                echo json_encode($datos);
            }
            break;

        case "eliminar_puesto":
            $capital_humano->delete_puesto($_POST["pue_id"]);
            echo "ok";
            break;

        /* ========================================================================= */
        /* SELECTS DEPENDIENTES                                                      */
        /* ========================================================================= */
        
        case "select_departamentos":
            $datos = $capital_humano->get_departamentos_activos();
            echo '<option value="">Seleccione un Departamento</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    echo '<option value="' . $row["dep_id"] . '">' . $row["dep_nombre"] . '</option>';
                }
            }
            break;

        case "select_areas":
            $datos = $capital_humano->get_areas_activas();
            echo '<option value="">Seleccione un Área</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    echo '<option value="' . $row["area_id"] . '">' . $row["area_nombre"] . ' (Dep: ' . $row["dep_nombre"] . ')</option>';
                }
            }
            break;
    }
?>