<?php
    // ARCHIVO: models/CapitalHumano.php
    
    // ¡IMPORTANTE! Se elimina el require_once, ya que config/index.php
    // debe ser incluido por el controlador, y es ese archivo quien carga
    // la clase Conectar (a través de config/conexion.php).

    class CapitalHumano {
        private $db; 

        public function __construct() {
            // CORRECCIÓN LÍNEA 14: Llama al método estático 'conectar()' de la clase 'Conexion'.
            // Este método devuelve la instancia PDO que usaremos para las consultas.
            $this->db = Conexion::conectar(); 
        }

        private function conectar() {
            // CORRECCIÓN: Este método solo necesita devolver la instancia PDO ($this->db).
            // (Anteriormente intentaba llamar a $this->con->Conexion(), que era incorrecto.)
            return $this->db;
        }
        /* ========================================================================= */
        /* MÉTODOS PARA DEPARTAMENTOS (LS_DEPARTAMENTOS)                             */
        /* ========================================================================= */

        public function get_departamentos() {
            $conectar = $this->conectar();
            $sql = "SELECT * FROM LS_DEPARTAMENTOS WHERE dep_estatus IN ('ACTIVO', 'INACTIVO') ORDER BY dep_nombre ASC";
            $stmt = $conectar->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_departamentos_activos() {
            $conectar = $this->conectar();
            $sql = "SELECT * FROM LS_DEPARTAMENTOS WHERE dep_estatus = 'ACTIVO' ORDER BY dep_nombre ASC";
            $stmt = $conectar->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_departamento_por_id($dep_id) {
            $conectar = $this->conectar();
            $sql = "SELECT * FROM LS_DEPARTAMENTOS WHERE dep_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function insert_departamento($dep_nombre) {
            $conectar = $this->conectar();
            $sql_check = "SELECT dep_id FROM LS_DEPARTAMENTOS WHERE dep_nombre = ? AND dep_estatus = 'ACTIVO'";
            $stmt_check = $conectar->prepare($sql_check);
            $stmt_check->bindValue(1, $dep_nombre);
            $stmt_check->execute();
            if ($stmt_check->rowCount() > 0) {
                return false; 
            }
            
            $sql = "INSERT INTO LS_DEPARTAMENTOS (dep_nombre, dep_estatus, dep_fecha_creacion) VALUES (?, 'ACTIVO', NOW())";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_nombre);
            return $stmt->execute();
        }

        public function update_departamento($dep_id, $dep_nombre) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_DEPARTAMENTOS SET dep_nombre = ? WHERE dep_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_nombre);
            $stmt->bindValue(2, $dep_id);
            return $stmt->execute();
        }

        public function delete_departamento($dep_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_DEPARTAMENTOS SET dep_estatus = 'INACTIVO' WHERE dep_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_id);
            return $stmt->execute();
        }


        /* ========================================================================= */
        /* MÉTODOS PARA ÁREAS (LS_AREAS)                                             */
        /* ========================================================================= */
        
        public function get_areas() {
            $conectar = $this->conectar();
            $sql = "SELECT 
                        A.*, 
                        D.dep_nombre
                    FROM 
                        LS_AREAS A
                    INNER JOIN 
                        LS_DEPARTAMENTOS D ON A.dep_id = D.dep_id
                    WHERE
                        A.area_estatus IN ('ACTIVO', 'INACTIVO')
                    ORDER BY 
                        D.dep_nombre ASC, A.area_nombre ASC";
            $stmt = $conectar->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        public function get_areas_activas() {
             $conectar = $this->conectar();
             $sql = "SELECT 
                        A.area_id, 
                        A.area_nombre, 
                        D.dep_nombre
                    FROM 
                        LS_AREAS A
                    INNER JOIN 
                        LS_DEPARTAMENTOS D ON A.dep_id = D.dep_id
                    WHERE
                        A.area_estatus = 'ACTIVO'
                    ORDER BY 
                        A.area_nombre ASC";
            $stmt = $conectar->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_area_por_id($area_id) {
            $conectar = $this->conectar();
            $sql = "SELECT * FROM LS_AREAS WHERE area_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $area_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function insert_area($dep_id, $area_nombre) {
            $conectar = $this->conectar();
            $sql_check = "SELECT area_id FROM LS_AREAS WHERE area_nombre = ? AND dep_id = ? AND area_estatus = 'ACTIVO'";
            $stmt_check = $conectar->prepare($sql_check);
            $stmt_check->bindValue(1, $area_nombre);
            $stmt_check->bindValue(2, $dep_id);
            $stmt_check->execute();
            if ($stmt_check->rowCount() > 0) {
                return false;
            }
            
            $sql = "INSERT INTO LS_AREAS (dep_id, area_nombre, area_estatus, area_fecha_creacion) VALUES (?, ?, 'ACTIVO', NOW())";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_id);
            $stmt->bindValue(2, $area_nombre);
            return $stmt->execute();
        }

        public function update_area($area_id, $dep_id, $area_nombre) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_AREAS SET dep_id = ?, area_nombre = ? WHERE area_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_id);
            $stmt->bindValue(2, $area_nombre);
            $stmt->bindValue(3, $area_id);
            return $stmt->execute();
        }

        public function delete_area($area_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_AREAS SET area_estatus = 'INACTIVO' WHERE area_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $area_id);
            return $stmt->execute();
        }
        
        /* ========================================================================= */
        /* MÉTODOS PARA PUESTOS (LS_PUESTOS)                                         */
        /* ========================================================================= */

        public function get_puestos() {
            $conectar = $this->conectar();
            $sql = "SELECT 
                        P.*, 
                        A.area_nombre
                    FROM 
                        LS_PUESTOS P
                    INNER JOIN 
                        LS_AREAS A ON P.area_id = A.area_id
                    WHERE
                        P.pue_estatus IN ('ACTIVO', 'INACTIVO')
                    ORDER BY 
                        A.area_nombre ASC, P.pue_nombre ASC";
            $stmt = $conectar->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_puesto_por_id($pue_id) {
            $conectar = $this->conectar();
            $sql = "SELECT * FROM LS_PUESTOS WHERE pue_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $pue_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        public function insert_puesto($area_id, $pue_nombre) {
            $conectar = $this->conectar();
            $sql_check = "SELECT pue_id FROM LS_PUESTOS WHERE pue_nombre = ? AND area_id = ? AND pue_estatus = 'ACTIVO'";
            $stmt_check = $conectar->prepare($sql_check);
            $stmt_check->bindValue(1, $pue_nombre);
            $stmt_check->bindValue(2, $area_id);
            $stmt_check->execute();
            if ($stmt_check->rowCount() > 0) {
                return false;
            }
            
            $sql = "INSERT INTO LS_PUESTOS (area_id, pue_nombre, pue_estatus, pue_fecha_creacion) VALUES (?, ?, 'ACTIVO', NOW())";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $area_id);
            $stmt->bindValue(2, $pue_nombre);
            return $stmt->execute();
        }

        public function update_puesto($pue_id, $area_id, $pue_nombre) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_PUESTOS SET area_id = ?, pue_nombre = ? WHERE pue_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $area_id);
            $stmt->bindValue(2, $pue_nombre);
            $stmt->bindValue(3, $pue_id);
            return $stmt->execute();
        }

        public function delete_departamento_permanente($dep_id) {
            $conectar = $this->conectar();
            // CAMBIO: Usamos DELETE FROM para eliminar el registro físicamente
            $sql = "DELETE FROM LS_DEPARTAMENTOS WHERE dep_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_id);
            return $stmt->execute();
        }

        public function activate_departamento($dep_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_DEPARTAMENTOS SET dep_estatus = 'ACTIVO' WHERE dep_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $dep_id);
            return $stmt->execute();
        }

        public function delete_puesto($pue_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_PUESTOS SET pue_estatus = 'INACTIVO' WHERE pue_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $pue_id);
            return $stmt->execute();
        }

        // NUEVO: Activar Puesto
        public function activate_puesto($pue_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_PUESTOS SET pue_estatus = 'ACTIVO' WHERE pue_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $pue_id);
            return $stmt->execute();
        }

        // NUEVO: Eliminación Permanente de Puesto
        public function delete_puesto_permanente($pue_id) {
            $conectar = $this->conectar();
            $sql = "DELETE FROM LS_PUESTOS WHERE pue_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $pue_id);
            return $stmt->execute();
        }

        // NUEVO: Activar Área
        public function activate_area($area_id) {
            $conectar = $this->conectar();
            $sql = "UPDATE LS_AREAS SET area_estatus = 'ACTIVO' WHERE area_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $area_id);
            return $stmt->execute();
        }

        // NUEVO: Eliminación Permanente de Área
        public function delete_area_permanente($area_id) {
            $conectar = $this->conectar();
            $sql = "DELETE FROM LS_AREAS WHERE area_id = ?";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $area_id);
            return $stmt->execute();
        }
    }
?>