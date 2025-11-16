<?php
/**
 * Archivo de Configuración General de LITOSOFT
 * * Este archivo define constantes esenciales para el funcionamiento
 * del sistema, como rutas base y configuraciones de entorno.
 */

// 1. Definición de Rutas Absolutas

// Define la ruta absoluta al directorio raíz del proyecto (LITOSOFT/).
// dirname(__DIR__) te lleva un nivel arriba del directorio actual (config), 
// llegando a la raíz del proyecto.
define('ROOT_PATH', dirname(__DIR__));

// Define rutas clave usando la ruta raíz
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CONTROLLER_PATH', ROOT_PATH . '/controller');
define('MODEL_PATH', ROOT_PATH . '/models');
define('VIEW_PATH', ROOT_PATH . '/view');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('INCLUDE_PATH', ROOT_PATH . '/include');


// 2. Constantes de la Aplicación

// Puedes definir constantes como el nombre de la aplicación
define('APP_NAME', 'LITOSOFT');

// Define el estado del entorno (útil para mostrar o no errores)
// Cambiar a 'production' al subir al servidor real
define('APP_ENVIRONMENT', 'development'); 

// 3. Configuración de Errores (Basado en el entorno)

if (APP_ENVIRONMENT === 'development') {
    // En desarrollo, muestra todos los errores
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // En producción, oculta los errores al usuario por seguridad
    ini_set('display_errors', 0);
    error_reporting(0);
}

// 4. Inclusión de la Conexión a la DB
// Es común incluir la conexión aquí para que esté disponible en todo el proyecto
require_once CONFIG_PATH . '/conexion.php';

?>