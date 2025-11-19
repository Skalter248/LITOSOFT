<?php
/**
 * Clase Usuario - MODELO
 * Gestiona la interacción con la tabla LS_USUARIOS de la base de datos.
 */
require_once('Vacaciones.php');

<<<<<<< HEAD

=======
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
class Usuario { 
    
    /**
     * Obtiene los datos del usuario si las credenciales son válidas.
     * @param string $usuario_ingresado Nombre de usuario ingresado.
     * @param string $pass_ingresada Contraseña ingresada.
     * @param int $rol_id Rol buscado.
     * @return array|false Retorna el array con los datos del usuario si es correcto, o false si falla.
     */
    public function login_user($usuario_ingresado, $pass_ingresada, $rol_id) {
        
        if (empty($usuario_ingresado) || empty($pass_ingresada) || empty($rol_id)) {
            return false;
        }

        try {
            $db = Conexion::conectar(); 

            // Consulta SQL con nombres de columna EXACTOS
            $sql = "SELECT * FROM LS_USUARIOS 
                    WHERE usu_usuario_inicio = :usuario_db_col 
                    AND rol_id = :rol 
                    AND usu_estado = 1";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':usuario_db_col', $usuario_ingresado, PDO::PARAM_STR); 
            $stmt->bindParam(':rol', $rol_id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // 1. Verificar si el usuario existe
            if (!$resultado) {
                return false;
            }

            // 2. Verificar la Contraseña Hasheada con la columna exacta
            if (password_verify($pass_ingresada, $resultado['usu_contraseña_inicio'])) {
                // Credenciales correctas
                return $resultado;
            } else {
                // Contraseña incorrecta
                return false;
            }

        } catch (PDOException $e) {
            error_log("Error de DB en login: " . $e->getMessage());
            return false;
        }
    }

    public function guardar_usuario($datos_usuario) {
        
        $conectar = Conexion::conectar();
                $sql_insert = "INSERT INTO LS_USUARIOS (..., fecha_ingreso_planta) 
                    VALUES (..., :fecha_ingreso_planta)";
        $stmt = $conectar->prepare($sql_insert);
        $stmt->bindValue(':fecha_ingreso_planta', $datos_usuario['fecha_ingreso_planta'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            
            $nuevo_usu_id = $conectar->lastInsertId(); // OBTENER EL ID GENERADO
            $fecha_ingreso = $datos_usuario['fecha_ingreso_planta'];

            $vacaciones_model = new Vacaciones();
            $vacaciones_model->inicializar_saldo_usuario($nuevo_usu_id, $fecha_ingreso);
            
            return ['success' => true, 'id' => $nuevo_usu_id];
        }
        
        return ['success' => false, 'message' => 'Error al crear el usuario.'];
    }
}