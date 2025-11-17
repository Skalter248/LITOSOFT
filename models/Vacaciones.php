<?php
// ARCHIVO: models/Vacaciones.php

// Asegúrate de que tu archivo 'conexion.php' ya haya sido incluido en el controlador y en index.php.

class Vacaciones {

    /**
     * Temporal: Devuelve un saldo por defecto para EVITAR ERRORES FATALES
     * hasta que la tabla LS_USUARIOS_SALDOS esté creada y accesible.
     */
    public function get_saldo_actual($usu_id) {
        // ⭐ TEMPORAL: Retornamos un saldo fijo (10 días) para que la página cargue correctamente. ⭐
        return ['usu_dias_disponibles' => 10.00]; 
    }

    /**
     * Calcula los días hábiles (Lunes a Viernes) entre dos fechas.
     */
    public function calcular_dias_habiles($fecha_inicio, $fecha_fin) {
        
        try {
            $fecha_inicio_dt = new DateTime($fecha_inicio);
            $fecha_fin_dt = new DateTime($fecha_fin);
        } catch (Exception $e) {
            return ['dias_habiles' => 0.00, 'dias_totales' => 0.00, 'error' => 'Fechas inválidas.'];
        }
        
        if ($fecha_inicio_dt > $fecha_fin_dt) {
            return ['dias_habiles' => 0.00, 'dias_totales' => 0.00];
        }

        $dias_habiles = 0;
        $dias_totales = 0;
        $intervalo = new DateInterval('P1D');
        
        $fecha_fin_incluida = clone $fecha_fin_dt;
        $fecha_fin_incluida->modify('+1 day'); 

        $periodo = new DatePeriod($fecha_inicio_dt, $intervalo, $fecha_fin_incluida); 

        foreach ($periodo as $dia) {
            $dias_totales++;
            $dia_semana = $dia->format('N'); // 1 (Lun) a 7 (Dom)
            
            if ($dia_semana >= 1 && $dia_semana <= 5) {
                $dias_habiles++;
            }
        }
        
        return [
            'dias_habiles' => number_format($dias_habiles, 2, '.', ''), 
            'dias_totales' => number_format($dias_totales, 2, '.', '')
        ];
    }
    
    /**
     * Guarda la solicitud de vacaciones en la base de datos.
     */
    public function guardar_solicitud($usu_id, $f_inicio, $f_fin, $dias_habiles, $observaciones) {
        // ⭐ CRÍTICO: Conexión estática forzada para asegurar que no sea null. ⭐
        $conectar = Conexion::conectar(); 
        
        $sql = "INSERT INTO LS_VACACIONES_SOLICITUDES 
                (usu_id, vac_fecha_inicio, vac_fecha_fin, vac_dias_habiles, vac_observaciones, vac_estado) 
                VALUES 
                (:usu_id, :f_inicio, :f_fin, :dias_habiles, :observaciones, 'Pendiente')";
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(':f_inicio', $f_inicio, PDO::PARAM_STR);
        $stmt->bindValue(':f_fin', $f_fin, PDO::PARAM_STR);
        $stmt->bindValue(':dias_habiles', $dias_habiles, PDO::PARAM_STR);
        $stmt->bindValue(':observaciones', $observaciones, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Solicitud guardada con éxito.'];
        } else {
            return ['success' => false, 'message' => 'Error al guardar la solicitud en la base de datos.'];
        }
    }

    // Funciones stub
    public function ejecutar_logica_actualizacion_saldos() { /* Sin acción por ahora */ return true; }
    public function listar_mis_solicitudes($usu_id) { return ["data" => []]; } 
}