<?php
    // ARCHIVO: controller/MntUsuario.php

    // NOTA: Asegúrate de que index.php cargue la clase Conexion (ej. a través de require_once("../config/conexion.php"))
    require_once("../config/index.php"); 
    require_once("../models/Usuario.php");
    require_once("../models/CapitalHumano.php"); 
    // Si la conexión está en otro lado, ajústala
    // require_once("../config/conexion.php"); 
    
    $usuario = new Usuario();
    $capital_humano = new CapitalHumano(); 
    $op = isset($_GET["op"]) ? $_GET["op"] : null;

    switch ($op) {
        
        /* ========================================================================= */
        /* GUARDAR Y EDITAR (Con manejo de Foto)                                     */
        /* ========================================================================= */

        case "guardaryeditar_usuario":
            $usu_id = isset($_POST["usu_id"]) ? $_POST["usu_id"] : "";
            
            // Recibir IDs (INT) de los combos
            $dep_id = isset($_POST["usu_departamento"]) ? (int)$_POST["usu_departamento"] : 0; 
            $area_id = isset($_POST["usu_area"]) ? (int)$_POST["usu_area"] : 0;
            $pue_id = isset($_POST["usu_puesto"]) ? (int)$_POST["usu_puesto"] : 0;

            // Resto de campos
            $usu_nombre = $_POST["usu_nombre"];
            $usu_apellido_paterno = $_POST["usu_apellido_paterno"];
            $usu_apellido_materno = $_POST["usu_apellido_materno"];
            $rol_id = (int)$_POST["rol_id"]; 
            $jefe_id = isset($_POST["jefe_id"]) && !empty($_POST["jefe_id"]) ? (int)$_POST["jefe_id"] : NULL; // NUEVO CAMPO: Puede ser NULL si no tiene jefe
            $usu_usuario_inicio = $_POST["usu_usuario_inicio"];
            $usu_contraseña_inicio = $_POST["usu_contraseña_inicio"]; // Solo para INSERT
            $usu_telefono = $_POST["usu_telefono"];
            $usu_RFC = $_POST["usu_RFC"];
            $usu_CURP = $_POST["usu_CURP"];
            $usu_NSS = $_POST["usu_NSS"];
            $usu_domicilio = $_POST["usu_domicilio"];
            $usu_edad = (int)$_POST["usu_edad"];
            $usu_fecha_nacimiento = $_POST["usu_fecha_nacimiento"];
            $fecha_ingreso_planta = $_POST["fecha_ingreso_planta"];

            // Lógica de Foto (SIN CAMBIOS)
            // Nombre del archivo: nombre_apellido_timestamp.extension
            $nombre_base = preg_replace('/[^A-Za-z0-9\-]/', '_', $usu_nombre . '_' . $usu_apellido_paterno);
            $archivo_foto = isset($_FILES["usu_foto"]) ? $_FILES["usu_foto"] : null;
            $usu_foto_bd = isset($_POST["usu_foto_actual"]) ? $_POST["usu_foto_actual"] : "default.png";

            if ($archivo_foto && $archivo_foto["error"] == 0 && $archivo_foto["size"] > 0) {
                // Generar el nombre y ruta de guardado
                $extension = pathinfo($archivo_foto["name"], PATHINFO_EXTENSION);
                $usu_foto_bd = $nombre_base . '_' . time() . '.' . $extension;
                $ruta_guardado = __DIR__ . '/../public/upload/fotos/' . $usu_foto_bd;
                
                // Mover el archivo subido
                if (!move_uploaded_file($archivo_foto["tmp_name"], $ruta_guardado)) {
                    echo "Error al subir el archivo. Verifique permisos de carpeta en: /public/upload/fotos"; exit();
                }
                
                // Si es edición, eliminar la foto antigua (si no es la default)
                if (!empty($usu_id) && !empty($_POST["usu_foto_actual"]) && $_POST["usu_foto_actual"] != "default.png") {
                    @unlink(__DIR__ . '/../public/upload/fotos/' . $_POST["usu_foto_actual"]);
                }
            }
            // Si es edición y no se subió foto, $usu_foto_bd mantiene el valor de la foto_actual

            if (empty($usu_id)) {
                // INSERT
                if (empty($usu_contraseña_inicio)) { echo "La contraseña es obligatoria para nuevos usuarios."; exit(); }
                
                $resultado = $usuario->insert_usuario(
                    $usu_nombre, $usu_apellido_paterno, $usu_apellido_materno, $rol_id, $jefe_id, $dep_id, $area_id, $pue_id, // jefe_id añadido aquí
                    $usu_usuario_inicio, $usu_contraseña_inicio, $usu_telefono, $usu_RFC, $usu_CURP, $usu_NSS, 
                    $usu_domicilio, $usu_edad, $usu_fecha_nacimiento, $fecha_ingreso_planta, $usu_foto_bd
                );

                echo ($resultado === false) ? "Error al guardar o el usuario/documento ya existe (Inicio, RFC, CURP o NSS)." : "ok";
            } else {
                // UPDATE
                $usuario->update_usuario(
                    $usu_id, $usu_nombre, $usu_apellido_paterno, $usu_apellido_materno, $rol_id, $jefe_id, $dep_id, $area_id, $pue_id, // jefe_id añadido aquí
                    $usu_usuario_inicio, $usu_telefono, $usu_RFC, $usu_CURP, $usu_NSS, $usu_domicilio, $usu_edad, 
                    $usu_fecha_nacimiento, $fecha_ingreso_planta, $usu_foto_bd
                );
                echo "ok";
            }
            break;

            case "select_jefes":
            $datos = $usuario->get_jefes_disponibles();
            echo '<option value="">Seleccione un Jefe Directo (Opcional)</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    $nombre_completo = $row["usu_apellido_paterno"] . ' ' . $row["usu_apellido_materno"] . ', ' . $row["usu_nombre"];
                    echo '<option value="' . $row["usu_id"] . '">' . $nombre_completo . '</option>';
                }
            }
            break;

        /* ========================================================================= */
        /* LISTADO Y OBTENER POR ID                                                  */
        /* ========================================================================= */

        case "listar_usuarios":
            $datos = $usuario->get_usuarios();
            $data = Array();
            foreach ($datos as $row) {
                $estatus_label = ($row["usu_estado"] == 1) ? '<span class="label label-success">ACTIVO</span>' : '<span class="label label-danger">INACTIVO</span>';
                
                // Botones Condicionales
                $botones = '<button type="button" onClick="editarUsuario(' . $row["usu_id"] . ');" class="btn btn-warning btn-sm">Editar</button>&nbsp;';
                
                if ($row["usu_estado"] == 1) { // ACTIVO
                    $botones .= '<button type="button" onClick="desactivarUsuario(' . $row["usu_id"] . ');" class="btn btn-danger btn-sm">Desactivar</button>';
                } else { // INACTIVO
                    $botones .= '<button type="button" onClick="activarUsuario(' . $row["usu_id"] . ');" class="btn btn-success btn-sm">Activar</button>&nbsp;';
                    $botones .= '<button type="button" onClick="eliminarUsuarioPermanente(' . $row["usu_id"] . ');" class="btn btn-dark btn-sm">Borrar</button>';
                }
                
                // Mostrar nombres de las relaciones y la foto
                $data[] = array(
                    // La ruta de la imagen debe ser relativa a la VISTA (index.php)
                    '<img src="../../public/upload/fotos/' . $row["usu_foto"] . '" class="img-thumbnail" width="50px" height="50px" onerror="this.src=\'../../public/upload/fotos/default.png\'" />', 
                    $row["usu_nombre"] . ' ' . $row["usu_apellido_paterno"] . ' ' . $row["usu_apellido_materno"], 
                    $row["usu_usuario_inicio"],
                    $row["dep_nombre"], 
                    $row["area_nombre"], 
                    $row["pue_nombre"], 
                    $row["rol_nombre"], // Mostrar nombre del Rol
                    $row["usu_telefono"],
                    $estatus_label,
                    $row["fecha_creacion"],
                    $botones
                );
            }
            echo json_encode(array("sEcho" => 1, "iTotalRecords" => count($data), "iTotalDisplayRecords" => count($data), "aaData" => $data));
            break;
            
        case "get_usuario_por_id":
            $datos = $usuario->get_usuario_por_id($_POST["usu_id"]);
            if (is_array($datos) && count($datos) > 0) {
                echo json_encode($datos);
            }
            break;
            
        /* ========================================================================= */
        /* ACTIVACIÓN / DESACTIVACIÓN / ELIMINACIÓN PERMANENTE                       */
        /* ========================================================================= */
        case "eliminar_usuario": 
            // Ahora verificamos el resultado del modelo.
            $resultado = $usuario->delete_usuario($_POST["usu_id"]); 
            if ($resultado) {
                echo "ok";
            } else {
                // Mensaje de error más específico para ayudar a depurar
                echo "Error al desactivar el usuario. Verifique si el campo 'fecha_puesto_inactivo' existe en LS_USUARIOS."; 
            }
            break;
            
        case "activar_usuario":
            $resultado = $usuario->activate_usuario($_POST["usu_id"]);
            echo ($resultado) ? "ok" : "Error al activar el usuario.";
            break;

        case "eliminar_usuario_permanente":
            $usuario->delete_usuario_permanente($_POST["usu_id"]); echo "ok"; break;
            
        /* ========================================================================= */
        /* SELECTS DEPENDIENTES PARA MODAL DE USUARIOS (USANDO ID)                   */
        /* ========================================================================= */

        case "select_departamentos_usuarios": // Para cargar el primer combo
            $datos = $capital_humano->get_departamentos_activos();
            echo '<option value="">Seleccione un Departamento</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    echo '<option value="' . $row["dep_id"] . '">' . $row["dep_nombre"] . '</option>';
                }
            }
            break;

        case "select_areas_usuarios": // Se filtra por el ID del Departamento
            $dep_id = (int)$_POST["dep_id"];
            $datos = $capital_humano->get_areas_activas_por_departamento($dep_id);
            echo '<option value="">Seleccione un Área</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    echo '<option value="' . $row["area_id"] . '">' . $row["area_nombre"] . '</option>';
                }
            }
            break;

        case "select_puestos_usuarios": // Se filtra por el ID del Área
            $area_id = (int)$_POST["area_id"];
            $datos = $capital_humano->get_puestos_activos_por_area($area_id);
            echo '<option value="">Seleccione un Puesto</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    echo '<option value="' . $row["pue_id"] . '">' . $row["pue_nombre"] . '</option>';
                }
            }
            break;
            
        case "select_roles": // Carga de Roles
            $datos = $capital_humano->get_roles();
            echo '<option value="">Seleccione un Rol</option>';
            if (is_array($datos) && count($datos) > 0) {
                foreach ($datos as $row) {
                    echo '<option value="' . $row["rol_id"] . '">' . $row["rol_nombre"] . '</option>';
                }
            }
            break;
    }
?>