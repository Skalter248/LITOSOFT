<?php
// ARCHIVO: models/Vacaciones.php

class Vacaciones {
    
    private function conectar() {
        return Conexion::conectar();
    }

    public function insert_solicitud($usu_id, $vac_fecha_inicio, $vac_fecha_fin, $vac_dias_solicitados, $vac_dias_habiles) {
        $conectar = $this->conectar();
        
        $sql = "INSERT INTO LS_VACACIONES_SOLICITUDES 
                (usu_id, vac_fecha_inicio, vac_fecha_fin, vac_dias_solicitados, vac_dias_habiles, vac_estado, vac_fecha_solicitud) 
                VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $vac_fecha_inicio);
        $stmt->bindValue(3, $vac_fecha_fin);
        $stmt->bindValue(4, $vac_dias_solicitados, PDO::PARAM_INT);
        $stmt->bindValue(5, $vac_dias_habiles, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Obtiene el listado de solicitudes para un usuario específico.
     * Incluye el nombre del aprobador si existe. 
     */
    public function get_solicitudes_por_usuario($usu_id) {
        $conectar = $this->conectar();
        
        $sql = "SELECT 
                    v.*,
                    -- ¡VERIFICA ESTA LÍNEA EXACTA!
                    TRIM(CONCAT_WS(' ', 
                        COALESCE(u.usu_nombre, ''), 
                        COALESCE(u.usu_apellido_paterno, ''), 
                        COALESCE(u.usu_apellido_materno, '')
                    )) AS nombre_aprobador
                    -- --------------------------
                FROM LS_VACACIONES_SOLICITUDES v
                LEFT JOIN LS_USUARIOS u ON v.vac_jefe_id_aprobador = u.usu_id
                WHERE v.usu_id = ?
                ORDER BY v.vac_fecha_solicitud DESC";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve el mínimo de días de vacaciones pagadas según la LFT y la reforma de Vacaciones Dignas.
     * @param int $antiguedad_anos Antigüedad en años cumplidos.
     * @return int Días de vacaciones mínimos generados.
     */
    public function get_dias_generados_por_antiguedad(int $antiguedad_anos): int {
        if ($antiguedad_anos < 1) {
            return 0;
        }

        // Lógica de Vacaciones Dignas (Mínimos por ley)
        if ($antiguedad_anos == 1) return 12;
        if ($antiguedad_anos == 2) return 14;
        if ($antiguedad_anos == 3) return 16;
        if ($antiguedad_anos == 4) return 18;
        if ($antiguedad_anos == 5) return 20;

        // A partir del año 6, se suman 2 días por cada 5 años de servicio adicionales.
        if ($antiguedad_anos >= 6 && $antiguedad_anos <= 10) return 22;
        if ($antiguedad_anos >= 11 && $antiguedad_anos <= 15) return 24;
        if ($antiguedad_anos >= 16 && $antiguedad_anos <= 20) return 26;
        if ($antiguedad_anos >= 21 && $antiguedad_anos <= 25) return 28;
        if ($antiguedad_anos >= 26 && $antiguedad_anos <= 30) return 30;

        // Para antigüedad muy alta, se puede generalizar la fórmula:
        $dias_base = 20;
        $bloques_de_cinco = floor(($antiguedad_anos - 5) / 5);
        return $dias_base + ($bloques_de_cinco * 2);
    }


    /**
     * Obtiene el resumen de vacaciones (Generadas, Usadas, Disponibles) para un usuario.
     * Esta función requiere la fecha de ingreso a planta del usuario.
     * @param int $usu_id ID del usuario.
     * @param string $fecha_ingreso_planta Fecha de ingreso del usuario (formato 'YYYY-MM-DD').
     * @return array {dias_generados, dias_usados, dias_disponibles, antiguedad_anos}
     */
    public function get_resumen_vacaciones(int $usu_id, string $fecha_ingreso_planta): array {
        $conectar = $this->conectar();
        
        // 1. Calcular Antigüedad en Años
        $hoy = new DateTime();
        $ingreso = new DateTime($fecha_ingreso_planta);
        $antiguedad = $ingreso->diff($hoy);
        $antiguedad_anos = $antiguedad->y;

        // 2. Calcular Días Generados según LFT
        $dias_generados = $this->get_dias_generados_por_antiguedad($antiguedad_anos);

        // 3. Sumar Días Usados (Aprobados)
        // Solo sumamos los días hábiles de solicitudes APROBADAS
        $sql_usados = "SELECT SUM(vac_dias_habiles) AS dias_usados 
                       FROM LS_VACACIONES_SOLICITUDES 
                       WHERE usu_id = ? AND vac_estado = 'Aprobada'";
        $stmt_usados = $conectar->prepare($sql_usados);
        $stmt_usados->bindValue(1, $usu_id, PDO::PARAM_INT);
        $stmt_usados->execute();
        $resultado_usados = $stmt_usados->fetch(PDO::FETCH_ASSOC);
        
        $dias_usados = (int)($resultado_usados['dias_usados'] ?? 0);
        
        // 4. Calcular Días Disponibles
        $dias_disponibles = max(0, $dias_generados - $dias_usados);

        return [
            'antiguedad_anos' => $antiguedad_anos,
            'dias_generados' => $dias_generados,
            'dias_usados' => $dias_usados,
            'dias_disponibles' => $dias_disponibles
        ];
    }
    /**
     * Cancela una solicitud de vacaciones (solo si está pendiente).
     * @return PDOStatement|false Devuelve el objeto de la sentencia PDO si se ejecuta, o false si falla.
     */
    public function cancelar_solicitud($vac_id, $usu_id) {
        $conectar = $this->conectar();
        
        $sql = "UPDATE LS_VACACIONES_SOLICITUDES 
                SET vac_estado = 'Cancelada', vac_fecha_aprobacion = NOW() 
                WHERE vac_id = ? AND usu_id = ? AND vac_estado = 'Pendiente'";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $vac_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $usu_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
             return $stmt; // <-- NUEVO: Retorna el objeto de la sentencia (PDOStatement)
        } else {
             return false;
        }
    }

    public function get_solicitudes_pendientes() {
        $conectar = $this->conectar();
        
        $sql = "SELECT 
                    v.*,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido_paterno) AS nombre_empleado,
                    p.pue_nombre,
                    a.area_nombre
                FROM LS_VACACIONES_SOLICITUDES v
                JOIN LS_USUARIOS u ON v.usu_id = u.usu_id
                JOIN LS_PUESTOS p ON u.usu_puesto = p.pue_id       
                JOIN LS_AREAS a ON p.area_id = a.area_id
                WHERE v.vac_estado = 'Pendiente'
                ORDER BY v.vac_fecha_solicitud ASC";
        
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el listado de solicitudes pendientes para revisión POR UN JEFE ESPECÍFICO.
     * Filtra por el campo LS_USUARIOS.jefe_id. Si el jefe es Admin (usu_id=1), trae todas.
     */
    public function get_solicitudes_pendientes_por_jefe($jefe_id) {
        $conectar = $this->conectar();
        
        $where_parts = ["v.vac_estado = 'Pendiente'"];
        
        // Lógica para visibilidad: Si no es el Admin Global (usu_id=1), se aplica el filtro de jerarquía.
        if ($jefe_id != 1) { 
            // Si NO es el admin, solo ve a sus subordinados.
            $where_parts[] = "u.jefe_id = :jefe_id";
        } 
        // Si ES el admin global (ID 1), no se añade filtro de jefe_id (ve todas).

        // CLÁUSULA CLAVE: Evitar la auto-aprobación. El solicitante (v.usu_id) no debe ser el jefe logueado.
        $where_parts[] = "v.usu_id != :id_excluir";
        
        $where_clause = implode(' AND ', $where_parts);
        
        $sql = "SELECT 
                    v.*,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido_paterno) AS nombre_empleado,
                    p.pue_nombre,
                    a.area_nombre
                FROM LS_VACACIONES_SOLICITUDES v
                JOIN LS_USUARIOS u ON v.usu_id = u.usu_id
                JOIN LS_PUESTOS p ON u.usu_puesto = p.pue_id  
                JOIN LS_AREAS a ON p.area_id = a.area_id
                WHERE {$where_clause}
                ORDER BY v.vac_fecha_solicitud ASC";
        
        $stmt = $conectar->prepare($sql);

        // 1. Vinculación del filtro de subordinados (solo si no es el admin ID 1)
        if ($jefe_id != 1) {
            $stmt->bindValue(':jefe_id', $jefe_id, PDO::PARAM_INT); 
        }
        
        // 2. Vinculación de la exclusión (SIEMPRE se aplica)
        $stmt->bindValue(':id_excluir', $jefe_id, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar_estado_solicitud($vac_id, $vac_estado_nuevo, $vac_jefe_id_aprobador) {
        $conectar = $this->conectar();
        
        // La consulta SQL debe estar dentro de comillas
        $sql = "UPDATE LS_VACACIONES_SOLICITUDES 
                SET vac_estado = ?, 
                    vac_fecha_aprobacion = NOW(), 
                    vac_jefe_id_aprobador = ? 
                WHERE vac_id = ? AND vac_estado = 'Pendiente'";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $vac_estado_nuevo);
        $stmt->bindValue(2, $vac_jefe_id_aprobador, PDO::PARAM_INT);
        $stmt->bindValue(3, $vac_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
             // Solo devuelve el resultado si la ejecución fue exitosa
             return $stmt; 
        } else {
             return false;
        }
    }

}
