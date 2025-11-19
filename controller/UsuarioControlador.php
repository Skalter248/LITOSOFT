<?php
/**
 * UsuarioControlador - CONTROLADOR
 * Maneja la petición de login, valida, llama al Modelo, gestiona la sesión y redirige.
 */
// 1. Inclusión de configuración y sesión
require_once('../config/index.php'); 
session_start();

// 2. Definición del modelo
require_once(MODEL_PATH . '/UsuarioModel.php');
$usuarioModel = new Usuario(); 

// 3. Verificación de la petición (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ***** CORRECCIÓN CLAVE: Captura usando los nombres de columna exactos *****
    $usuario_inicio = isset($_POST['usu_usuario_inicio']) ? trim($_POST['usu_usuario_inicio']) : '';
    $contraseña_inicio = isset($_POST['usu_contraseña_inicio']) ? $_POST['usu_contraseña_inicio'] : '';
    $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 0;
    
    $redirect_error_path = ($rol_id == 2) ? '../view/accesadministrativo/index.php' : '../index.php';
    
    // Validación de campos vacíos (Error m=2)
    if (empty($usuario_inicio) || empty($contraseña_inicio) || $rol_id === 0) {
        header("Location: " . $redirect_error_path . "?m=2");
        exit();
    }

    // 4. Llamada al Modelo para verificar credenciales
    $datos_usuario = $usuarioModel->login_user($usuario_inicio, $contraseña_inicio, $rol_id);

    // 5. Gestión de la respuesta del Modelo
    if ($datos_usuario) {
        
        // Autenticación Exitosa: Guardar en sesión

        $_SESSION['usu_id'] = $datos_usuario['usu_id'];
        $_SESSION['usu_nombre'] = $datos_usuario['usu_nombre']; 
        $_SESSION['usu_apellido_paterno'] = $datos_usuario['usu_apellido_paterno'];
        $_SESSION['usu_apellido_materno'] = $datos_usuario['usu_apellido_materno'];
        $_SESSION['rol_id'] = $datos_usuario['rol_id'];
        $_SESSION['usu_area'] = $datos_usuario['usu_area'];
        $_SESSION['usu_puesto'] = $datos_usuario['usu_puesto'];
        $_SESSION['usu_departamento'] = $datos_usuario['usu_departamento'];
        $_SESSION['usu_usuario_inicio'] = $datos_usuario['usu_usuario_inicio'];
        $_SESSION['usu_contraseña_inicio'] = $datos_usuario['usu_contraseña_inicio'];
        $_SESSION['usu_telefono'] = $datos_usuario['usu_telefono'];
        $_SESSION['usu_RFC'] = $datos_usuario['usu_RFC'];
        $_SESSION['usu_CURP'] = $datos_usuario['usu_CURP'];
        $_SESSION['usu_NSS'] = $datos_usuario['usu_NSS'];
        $_SESSION['usu_domicilio'] = $datos_usuario['usu_domicilio'];
        $_SESSION['usu_edad'] = $datos_usuario['usu_edad'];
        $_SESSION['usu_foto'] = $datos_usuario['usu_foto'];
        $_SESSION['fecha_creacion'] = $datos_usuario['fecha_creacion'];
        $_SESSION['fecha_modificacion'] = $datos_usuario['fecha_modificacion'];
        $_SESSION['fecha_ingreso_planta'] = $datos_usuario['fecha_ingreso_planta'];
        $_SESSION['fecha_puesto_inactivo'] = $datos_usuario['fecha_puesto_inactivo'];
        $_SESSION['usu_estado'] = $datos_usuario['usu_estado'];

        header("Location: ../view/home/"); 
        exit();

    } else {
        
        // Autenticación Fallida (Error m=1)
        header("Location: " . $redirect_error_path . "?m=1");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}