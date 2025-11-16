<?php
/**
 * Clase Conexion (PDO)
 * Utiliza el patrón Singleton para asegurar una única instancia de la conexión PDO.
 */
class Conexion {
    
    private static $conexion = null;

    // Credenciales de la base de datos (Ajusta estos valores)
    private $db_host = "localhost";    // Servidor de la DB
    private $db_user = "root";         // Tu usuario de phpMyAdmin/MySQL
    private $db_pass = "";             // Tu contraseña
    private $db_name = "LITOSOFT";     // Nombre de la base de datos
    private $db_charset = "utf8";

    // Constructor PRIVADO: Evita que se pueda usar 'new Conexion()' fuera de esta clase.
    private function __construct() {}

    /**
     * Método estático para obtener la instancia de la conexión PDO.
     * @return PDO Retorna la conexión PDO activa.
     */
    public static function conectar() {
        if (self::$conexion === null) {
            try {
                $instance = new self(); 
                
                $dsn = "mysql:host=" . $instance->db_host . ";dbname=" . $instance->db_name . ";charset=" . $instance->db_charset;

                $opciones = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$conexion = new PDO($dsn, $instance->db_user, $instance->db_pass, $opciones);
                
                return self::$conexion;

            } catch (PDOException $e) {
                // Si la conexión falla, se detiene la aplicación.
                die("Error de Conexión a la Base de Datos: " . $e->getMessage());
            }
        }
        
        return self::$conexion;
    }
}