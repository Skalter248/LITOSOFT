<?php
    // ARCHIVO: models/Usuario.php
    
    // Asumimos que config/index.php ya cargó la clase Conexion
    
    class Usuario {
        private $db;

        public function __construct() {
            // Utilizamos el método estático conectar() de la clase Conexion
            $this->db = Conexion::conectar(); 
        }

        private function conectar() {
            return $this->db;
        }

        /* ========================================================================= */
        /* MÉTODOS CRUD DE USUARIOS                                                  */
        /* ========================================================================= */

        public function get_usuarios() {
            $conectar = $this->conectar();
            // JOINs usando los IDs (INT) para obtener los NOMBRES en el listado
            $sql = "SELECT U.*, D.dep_nombre, A.area_nombre, P.pue_nombre, R.rol_nombre
                    FROM LS_USUARIOS U
                    LEFT JOIN LS_DEPARTAMENTOS D ON D.dep_id = U.usu_departamento
                    LEFT JOIN LS_AREAS A ON A.area_id = U.usu_area
                    LEFT JOIN LS_PUESTOS P ON P.pue_id = U.usu_puesto
                    LEFT JOIN LS_ROLES R ON R.rol_id = U.rol_id 
                    ORDER BY U.usu_nombre ASC";
            $stmt = $conectar->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_usuario_por_id($usu_id) {
            $conectar = $this->conectar();
            $sql = "SELECT * FROM LS_USUARIOS WHERE usu_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function insert_usuario($usu_nombre, $usu_apellido_paterno, $usu_apellido_materno, $rol_id, $jefe_id, $dep_id, $area_id, $pue_id, $usu_usuario_inicio, $usu_contraseña_inicio, $usu_telefono, $usu_RFC, $usu_CURP, $usu_NSS, $usu_domicilio, $usu_edad, $usu_fecha_nacimiento, $fecha_ingreso_planta, $usu_foto) {
            $conectar = $this->conectar();
            // 1. Verificar unicidad (sin cambios)
            $sql_check = "SELECT usu_id FROM LS_USUARIOS WHERE usu_usuario_inicio = ? OR usu_RFC = ? OR usu_CURP = ? OR usu_NSS = ?";
            $stmt_check = $conectar->prepare($sql_check);
            $stmt_check->bindValue(1, $usu_usuario_inicio);
            $stmt_check->bindValue(2, $usu_RFC);
            $stmt_check->bindValue(3, $usu_CURP);
            $stmt_check->bindValue(4, $usu_NSS);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                return false;
            }

            // 2. Insertar - Se añade fecha_ingreso_planta
            $sql = "INSERT INTO LS_USUARIOS (usu_nombre, usu_apellido_paterno, usu_apellido_materno, rol_id, jefe_id, usu_departamento, usu_area, usu_puesto, usu_usuario_inicio, usu_contraseña_inicio, usu_telefono, usu_RFC, usu_CURP, usu_NSS, usu_domicilio, usu_edad, usu_fecha_nacimiento, fecha_ingreso_planta, usu_foto, usu_estado, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_nombre);
            $stmt->bindValue(2, $usu_apellido_paterno);
            $stmt->bindValue(3, $usu_apellido_materno);
            $stmt->bindValue(4, $rol_id, PDO::PARAM_INT);
            $stmt->bindValue(5, $jefe_id, PDO::PARAM_INT);
            $stmt->bindValue(6, $dep_id, PDO::PARAM_INT);
            $stmt->bindValue(7, $area_id, PDO::PARAM_INT);
            $stmt->bindValue(8, $pue_id, PDO::PARAM_INT);
            $stmt->bindValue(9, $usu_usuario_inicio);
            $stmt->bindValue(10, password_hash($usu_contraseña_inicio, PASSWORD_DEFAULT)); 
            $stmt->bindValue(11, $usu_telefono);
            $stmt->bindValue(12, $usu_RFC);
            $stmt->bindValue(13, $usu_CURP);
            $stmt->bindValue(14, $usu_NSS);
            $stmt->bindValue(15, $usu_domicilio);
            $stmt->bindValue(16, $usu_edad, PDO::PARAM_INT);
            $stmt->bindValue(17, $usu_fecha_nacimiento);
            $stmt->bindValue(18, $fecha_ingreso_planta); // NUEVO
            $stmt->bindValue(19, $usu_foto);
            
            return $stmt->execute();
        }

        public function update_usuario($usu_id, $usu_nombre, $usu_apellido_paterno, $usu_apellido_materno, $rol_id, $jefe_id, $dep_id, $area_id, $pue_id, $usu_usuario_inicio, $usu_telefono, $usu_RFC, $usu_CURP, $usu_NSS, $usu_domicilio, $usu_edad, $usu_fecha_nacimiento, $fecha_ingreso_planta, $usu_foto) {
            $conectar = $this->conectar(); 
            
            // Se añade fecha_ingreso_planta al UPDATE
            $sql = "UPDATE LS_USUARIOS SET 
                        usu_nombre = ?, usu_apellido_paterno = ?, usu_apellido_materno = ?, rol_id = ?, 
                        jefe_id = ?, usu_departamento = ?, usu_area = ?, usu_puesto = ?, usu_usuario_inicio = ?, 
                        usu_telefono = ?, usu_RFC = ?, usu_CURP = ?, usu_NSS = ?, usu_domicilio = ?, 
                        usu_edad = ?, usu_fecha_nacimiento = ?, fecha_ingreso_planta = ?, usu_foto = ?, fecha_modificacion = NOW() 
                    WHERE usu_id = ?";
            
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_nombre);
            $stmt->bindValue(2, $usu_apellido_paterno);
            $stmt->bindValue(3, $usu_apellido_materno);
            $stmt->bindValue(4, $rol_id, PDO::PARAM_INT);
            $stmt->bindValue(5, $jefe_id, PDO::PARAM_INT);
            $stmt->bindValue(6, $dep_id, PDO::PARAM_INT);
            $stmt->bindValue(7, $area_id, PDO::PARAM_INT);
            $stmt->bindValue(8, $pue_id, PDO::PARAM_INT);
            $stmt->bindValue(9, $usu_usuario_inicio);
            $stmt->bindValue(10, $usu_telefono);
            $stmt->bindValue(11, $usu_RFC);
            $stmt->bindValue(12, $usu_CURP);
            $stmt->bindValue(13, $usu_NSS);
            $stmt->bindValue(14, $usu_domicilio);
            $stmt->bindValue(15, $usu_edad, PDO::PARAM_INT);
            $stmt->bindValue(16, $usu_fecha_nacimiento);
            $stmt->bindValue(17, $fecha_ingreso_planta); // NUEVO
            $stmt->bindValue(18, $usu_foto);
            $stmt->bindValue(19, $usu_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
        
        // 1. Desactivación (Soft Delete)
        public function delete_usuario($usu_id) {
            $conectar = $this->conectar();
            // Aseguramos que se actualice la columna de fecha de inactividad con la fecha y hora actual
            $sql = "UPDATE LS_USUARIOS SET usu_estado = 0, fecha_puesto_inactivo = NOW() WHERE usu_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        }

        // 2. Activación
        public function activate_usuario($usu_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_USUARIOS SET usu_estado = 1, fecha_puesto_inactivo = NULL WHERE usu_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        }

        // 3. Eliminación Permanente
        public function delete_usuario_permanente($usu_id) {
            $conectar = $this->conectar();
            $sql = "DELETE FROM LS_USUARIOS WHERE usu_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        
        // Combo Jefes Directos
        public function get_jefes_disponibles(): array {
        $conectar = $this->conectar();
        // Excluimos al usuario que está logueado y solo usuarios activos
        $sql = "SELECT usu_id, usu_nombre, usu_apellido_paterno, usu_apellido_materno 
                FROM LS_USUARIOS 
                WHERE usu_estado = 1 
                ORDER BY usu_apellido_paterno ASC";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    }
?>