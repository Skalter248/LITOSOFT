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


        case 'mostrar':
        // Verifica que el ID esté presente, si no, retorna error JSON.
        if (!isset($_POST["usu_id"]) || empty($_POST["usu_id"])) {
            die(json_encode(['error' => 'ID de usuario no proporcionado.']));
        }
        
        $usu_id = (int)$_POST["usu_id"];
        
        // El modelo mostrar() devuelve el array de datos o FALSE si no lo encuentra.
        $datos = $usuario->mostrar($usu_id); 
        
        if ($datos) {
            // Si hay datos, imprime el JSON del usuario.
            echo json_encode($datos);
        } else {
            // Si el usuario no existe o la consulta falla, imprime un objeto vacío.
            // Esto es JSON válido y evita el error de parseo.
            echo json_encode([]); 
        }
        break;    

        case 'subir_firma':
        // 1. VALIDACIÓN INICIAL DE DATOS
        if (!isset($_POST['usu_id_firma']) || empty($_POST['usu_id_firma']) || empty($_FILES['firma_file']['name'])) {
            die(json_encode(['status' => 'error', 'message' => 'Datos incompletos o archivo no seleccionado.']));
        }
        
        $usu_id = (int)$_POST['usu_id_firma'];
        $archivo = $_FILES['firma_file'];
        
        
        /* *************************************************************** */
        /* 2. DEFINICIÓN DE RUTA ABSOLUTA Y MANEJO DE PERMISOS (CRÍTICO)   */
        /* *************************************************************** */
        // dirname(__FILE__) = C:\xampp1\htdocs\LITOSOFT\controller
        // Subimos un nivel (..) y vamos a public/upload/firmas/
        $target_dir_base = dirname(__FILE__) . '/../public/upload/firmas/'; 
        // Usar DIRECTORY_SEPARATOR para compatibilidad con Windows/Linux
        $target_dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target_dir_base);


        // Verificar si el directorio existe y crearlo si es necesario
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) { 
                // Fallo al crear la carpeta (probablemente permisos)
                die(json_encode(['status' => 'error', 'message' => 'Error: No se pudo crear la carpeta. Permisos faltantes en: ' . $target_dir]));
            }
        }
        
        // Verificar permisos de escritura en la carpeta
        if (!is_writable($target_dir)) {
            die(json_encode(['status' => 'error', 'message' => 'Error: La carpeta de firmas NO tiene permisos de escritura. Asigne 777 a: ' . $target_dir]));
        }
        
        
        /* *************************************************************** */
        /* 3. OBTENER DATOS DE USUARIO PARA NOMBRAR ARCHIVO                */
        /* *************************************************************** */
        $datos_usuario = $usuario->mostrar($usu_id);
        if (!$datos_usuario || empty($datos_usuario['usu_usuario_inicio'])) {
            die(json_encode(['status' => 'error', 'message' => 'Usuario no encontrado o sin nombre de inicio de sesión.']));
        }
        $nombre_base = $datos_usuario['usu_usuario_inicio'];
        
        // 4. GENERAR NOMBRES DE ARCHIVO Y RUTAS
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        // Nombre: usu_usuario_inicio_firma_timestamp.ext
        $nombre_archivo = $nombre_base . '_firma_' . time() . '.' . strtolower($extension); 
        
        // Ruta a guardar en DB (debe ser relativa para la vista JS)
        $ruta_relativa_db = 'public/upload/firmas/' . $nombre_archivo; 
        
        // Ruta completa ABSOLUTA para move_uploaded_file()
        $target_file = $target_dir . $nombre_archivo;                   

        
        /* *************************************************************** */
        /* 5. INTENTAR MOVER EL ARCHIVO (CRÍTICO)                          */
        /* *************************************************************** */
        if (move_uploaded_file($archivo["tmp_name"], $target_file)) {
            
            // 6. ÉXITO EN EL MOVIMIENTO: ACTUALIZAR LA BASE DE DATOS
            $resultado_db = $usuario->guardar_o_actualizar_firma($usu_id, $nombre_archivo, $ruta_relativa_db);
            
            if ($resultado_db['success']) {
                // 7. Borrar firma física antigua (si existe y es diferente)
                $old_filename = $resultado_db['old_filename'];
                if ($old_filename && $old_filename != $nombre_archivo) {
                    @unlink($target_dir . $old_filename); 
                }
                
                echo json_encode(['status' => 'ok', 'message' => 'Firma cargada y guardada correctamente.']);
            } else {
                // Falló DB: Borrar el archivo físico que sí se movió para limpiar
                @unlink($target_file);
                die(json_encode(['status' => 'error', 'message' => 'Fallo al guardar la ruta en la base de datos. El archivo físico fue borrado.']));
            }
        } else {
            // 8. ERROR DE MOVIMIENTO: DEPURACIÓN
            $error_code = $archivo['error'];
            $error_msg = 'move_uploaded_file() falló. Código de error: ' . $error_code;
            
            // Mensajes de error de PHP (más comunes):
            if ($error_code == 1) {$error_msg .= ' (Tamaño de archivo excedido en php.ini)';}
            if ($error_code == 3) {$error_msg .= ' (Carga Parcial)';}
            if ($error_code == 4) {$error_msg .= ' (No se seleccionó archivo)';}
            if ($error_code == 6) {$error_msg .= ' (Falta carpeta temporal de PHP)';}
            
            echo json_encode(['status' => 'error', 'message' => 'ERROR DE ARCHIVOS: ' . $error_msg . '. RUTA DE INTENTO: ' . $target_file]);
        }
        break;

        case 'listar_usuarios_firmas':
        $datos = $usuario->get_usuarios_para_firmas();
        
        // Si $datos es falso o nulo (ej. error en modelo), lo forzamos a ser un array vacío.
        if (!is_array($datos)) {
            $datos = [];
        }
        
        // CRÍTICO: Envuelve el array de resultados ASOCIATIVOS dentro de la clave "data"
        // Esto es el formato estándar que el JS espera al usar "data": "clave"
        $results = array(
            // Nota: Cambiamos "aaData" a "data"
            "data" => $datos,
            "sEcho" => 1, 
            "iTotalRecords" => count($datos),
            "iTotalDisplayRecords" => count($datos)
        );
        
        // Retornar la respuesta JSON
        echo json_encode($results);
        break;
    }
?>