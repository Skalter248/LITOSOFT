<?php
// ARCHIVO: models/Vacaciones.php

class Vacaciones {
    
    private function conectar() {
        return Conexion::conectar();
    }

    public function insert_solicitud($usu_id, $vac_fecha_inicio, $vac_fecha_fin, $vac_dias_solicitados, $vac_dias_habiles, $vac_justificacion_adelanto) {
        $conectar = $this->conectar();
        
        // Se añade vac_justificacion_adelanto a la lista de columnas y al VALUES
        $sql = "INSERT INTO ls_vacaciones_solicitudes 
                (usu_id, vac_fecha_inicio, vac_fecha_fin, vac_dias_solicitados, vac_dias_habiles, vac_estado, vac_justificacion_adelanto, vac_fecha_solicitud) 
                VALUES (?, ?, ?, ?, ?, 'Pendiente', ?, NOW())";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $vac_fecha_inicio);
        $stmt->bindValue(3, $vac_fecha_fin);
        $stmt->bindValue(4, $vac_dias_solicitados, PDO::PARAM_INT);
        $stmt->bindValue(5, $vac_dias_habiles, PDO::PARAM_INT);
        $stmt->bindValue(6, $vac_justificacion_adelanto); // Nuevo parámetro
        
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
    public function get_resumen_vacaciones($usu_id, $fecha_ingreso) {
    $conectar = $this->conectar(); 
    
    // 1. CÁLCULO DE ANTIGÜEDAD y DÍAS GENERADOS (LFT 2023)
    $fecha_actual = new \DateTime(); 
    try {
        $fecha_ingreso_dt = new \DateTime($fecha_ingreso); 
    } catch (\Exception $e) {
        return [
            'antiguedad_anos' => 0, 'dias_generados' => 0, 'dias_usados' => 0, 
            'dias_disponibles' => 0, 'dias_adelantados_usados' => 0, 
            'motivo_restriccion' => 'Error: Fecha de ingreso inválida o no encontrada.'
        ];
    }

    $diff = $fecha_ingreso_dt->diff($fecha_actual);
    $antiguedad_anos = $diff->y; 
    $dias_generados = 0;
    
    // Lógica de Días Generados LFT 2023
    if ($antiguedad_anos >= 1) {
        // Lógica para 1 año o más (Días por año completo)
        if ($antiguedad_anos <= 5) {
            $dias_generados = 10 + (2 * $antiguedad_anos); 
        } elseif ($antiguedad_anos >= 6 && $antiguedad_anos <= 10) { 
            $dias_generados = 22;
        } elseif ($antiguedad_anos >= 11 && $antiguedad_anos <= 15) { 
            $dias_generados = 24;
        } elseif ($antiguedad_anos >= 16 && $antiguedad_anos <= 20) { 
            $dias_generados = 26;
        } elseif ($antiguedad_anos >= 21 && $antiguedad_anos <= 25) { 
            $dias_generados = 28;
        } elseif ($antiguedad_anos >= 26 && $antiguedad_anos <= 30) { 
            $dias_generados = 30;
        } else {
            $dias_generados = 32; 
        }
    } elseif ($antiguedad_anos == 0) {
        // Lógica de DÍAS PROPORCIONALES (Menos de 1 año)
        // Calcula meses completos para asignar 1 día generado por mes (12 días / 12 meses)
        $fecha_ingreso_temp = clone $fecha_ingreso_dt;
        $meses_completos = 0;
        // Contar el número de aniversarios mensuales completos
        while ($fecha_ingreso_temp->modify('+1 month') <= $fecha_actual) {
            $meses_completos++;
        }
        
        // Se generan 1 día por cada mes completo
        $dias_generados = $meses_completos;
        
    }
    
    // 2. CÁLCULO DE DÍAS USADOS Y ADELANTADOS
    $sql_ocupados = "SELECT 
                        SUM(vac_dias_habiles) as dias_ocupados,
                        SUM(CASE 
                                WHEN vac_justificacion_adelanto IS NOT NULL AND vac_estado = 'Aprobada' THEN vac_dias_habiles 
                                ELSE 0 
                             END) AS dias_adelantados_usados
                     FROM 
                        ls_vacaciones_solicitudes
                     WHERE 
                        usu_id = :usu_id AND vac_estado = 'Aprobada'";

    $stmt_ocupados = $conectar->prepare($sql_ocupados);
    $stmt_ocupados->bindValue(':usu_id', $usu_id, PDO::PARAM_INT); 
    $stmt_ocupados->execute();
    $dias_ocupados_data = $stmt_ocupados->fetch(PDO::FETCH_ASSOC);
    
    // Compatibilidad PHP 5.x
    $dias_ocupados = (int)(isset($dias_ocupados_data['dias_ocupados']) ? $dias_ocupados_data['dias_ocupados'] : 0);
    $dias_adelantados_usados = (int)(isset($dias_ocupados_data['dias_adelantados_usados']) ? $dias_ocupados_data['dias_adelantados_usados'] : 0);
    
    
    // 3. CÁLCULO FINAL DE SALDO DISPONIBLE Y RESTRICCIÓN
    // El saldo bruto se calcula con los días generados (proporcional o completo)
    $dias_disponibles_bruto = $dias_generados - $dias_ocupados;
    $dias_disponibles_uso = $dias_disponibles_bruto; // Saldo de la cuenta (Deuda o Generados)
    $motivo_restriccion = "";

    // Restricción LFT: El usuario no puede usar el saldo si tiene 0 años completos
    if ($antiguedad_anos == 0) {
        $fecha_primer_aniversario = (new \DateTime($fecha_ingreso))->modify('+1 year');
        $motivo_restriccion = "Días restringidos: Requiere 1 año de antigüedad. Disponibles después de {$fecha_primer_aniversario->format('d/m/Y')}.";
        
        // Si el saldo es positivo (no tiene deuda), se fuerza a 0 por la LFT.
        if ($dias_disponibles_bruto > 0) {
            $dias_disponibles_uso = 0;
        }
        // Si el saldo es negativo (deuda por adelanto), se mantiene el valor negativo para el dashboard.
        // Esto significa que si tiene -3, el dashboard mostrará -3.
    }
    
    // 4. Devolver datos
    return [
        'antiguedad_anos' => $antiguedad_anos,
        'dias_generados' => $dias_generados, // Muestra 6 días generados
        'dias_usados' => $dias_ocupados, 
        'dias_disponibles' => $dias_disponibles_uso, // Muestra 0 días disponibles (por la restricción)
        'dias_adelantados_usados' => $dias_adelantados_usados,
        'motivo_restriccion' => $motivo_restriccion,
        'fecha_ingreso_planta' => date('d/m/Y', $fecha_ingreso_dt->getTimestamp())
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
                    v.*, /* <--- Deja el asterisco, incluye vac_justificacion_adelanto */
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
        // Hemos mantenido el nombre de la función existente.
        $conectar = $this->conectar();
        
        // Eliminamos el filtro de estado para traer todas las solicitudes (Históricas)
        $where_parts = [];
        
        // Lógica para visibilidad: Si no es el Admin Global (usu_id=1), se aplica el filtro de jerarquía.
        if ($jefe_id != 1) { 
            $where_parts[] = "u.jefe_id = :jefe_id"; 
        } 
        
        // CLÁUSULA CLAVE DE EXCLUSIÓN: Siempre se excluye la solicitud del propio jefe
        $where_parts[] = "v.usu_id != :id_excluir";
        
        $where_clause = implode(' AND ', $where_parts);
        
        // ⭐⭐ CORRECCIÓN CRÍTICA DE SQL AQUÍ ⭐⭐
        // Reemplazamos 'v.vac_es_adelanto' por una cláusula CASE.
        $sql = "SELECT 
                    v.*, 
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido_paterno) AS nombre_empleado,
                    p.pue_nombre,
                    a.area_nombre,
                    UPPER(v.vac_estado) AS vac_estado,
                    v.vac_justificacion_adelanto,
                    
                    -- ⭐⭐ NUEVA COLUMNA CALCULADA (Sustituye a vac_es_adelanto) ⭐⭐
                    -- Si la justificación NO está vacía, asumimos que ES ADELANTO (1).
                    (CASE WHEN v.vac_justificacion_adelanto IS NOT NULL AND v.vac_justificacion_adelanto != '' THEN 1 ELSE 0 END) AS vac_es_adelanto
                    -- ⭐⭐ ESTA COLUMNA ES EL ÍNDICE [8] QUE ESPERA EL JS ⭐⭐

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
    /**
     * Obtiene todos los detalles de una solicitud, incluyendo datos del solicitante y aprobador.
     */
    public function get_detalles_solicitud($vac_id) {
        $conectar = $this->conectar();
        
        $sql = "SELECT 
                    v.*, 
                    us.usu_nombre AS nombre_solicitante,
                    us.usu_apellido_paterno AS apellido_solicitante, /* Usamos apellido_paterno para el apellido */
                    ua.usu_nombre AS nombre_aprobador,
                    ua.usu_apellido_paterno AS apellido_aprobador,  /* Usamos apellido_paterno para el apellido */
                    us.fecha_ingreso_planta
                FROM 
                    LS_VACACIONES_SOLICITUDES v
                JOIN 
                    ls_usuarios us ON v.usu_id = us.usu_id      /* <--- CORRECCIÓN CLAVE: ls_usuarios */
                LEFT JOIN 
                    ls_usuarios ua ON v.vac_jefe_id_aprobador = ua.usu_id /* <--- CORRECCIÓN CLAVE: ls_usuarios */
                WHERE 
                    v.vac_id = :vac_id";
                    
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':vac_id', $vac_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_dias_usados_aprobados_pasados($usu_id, $vac_id) {
        $conectar = $this->conectar();
        
        $sql = "SELECT 
                    SUM(vac_dias_habiles) AS dias_usados_pasados
                FROM LS_VACACIONES_SOLICITUDES
                WHERE usu_id = :usu_id 
                AND vac_estado = 'Aprobada'
                AND vac_id != :vac_id"; // <-- CLAVE: Excluir la solicitud actual
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(':vac_id', $vac_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Devolvemos 0 si es NULL, garantizando un número
        return (int)($resultado['dias_usados_pasados'] ?? 0);
    }

    // ⭐ get_impresion_solicitud se mantiene limpia y corregida (sin columnas de saldo de usuario)
    public function get_impresion_solicitud($vac_id) {
        $conectar = $this->conectar();
        
        $sql = "SELECT 
                    v.*, 
                    u.usu_nombre AS solicitante_nombre, 
                    u.usu_apellido_paterno AS solicitante_apellido,
                    u.fecha_ingreso_planta AS fecha_ingreso_planta,
                    
                    jefe.usu_nombre AS jefe_nombre,
                    jefe.usu_apellido_paterno AS jefe_apellido
                    
                FROM LS_VACACIONES_SOLICITUDES v
                JOIN LS_USUARIOS u ON v.usu_id = u.usu_id
                LEFT JOIN LS_USUARIOS jefe ON v.vac_jefe_id_aprobador = jefe.usu_id
                
                WHERE v.vac_id = :vac_id";
        
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':vac_id', $vac_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
 * Calcula los días de vacaciones generados por LFT hasta la fecha de corte.
 * Asume las reglas de LFT de México (12 días el primer año, +2 por los siguientes 4, etc.).
 * Nota: El resultado NO está truncado (ej: puede dar 1.5 días).
 *
 * @param string $fecha_ingreso Fecha de ingreso del empleado ('YYYY-MM-DD').
 * @param string $fecha_corte Fecha hasta la que se calcula el saldo ('YYYY-MM-DD').
 * @return float Días LFT generados.
 */
    public function calcular_dias_generados_lft($fecha_ingreso, $fecha_corte = null) {
        if (empty($fecha_corte)) {
            $fecha_corte = date('Y-m-d');
        }

        $fecha_inicio = new DateTime($fecha_ingreso);
        $fecha_fin = new DateTime($fecha_corte);
        
        // Si la fecha de corte es anterior a la de ingreso, devuelve 0
        if ($fecha_fin < $fecha_inicio) {
            return 0.0;
        }

        $diff = $fecha_inicio->diff($fecha_fin);
        $anos_servicio = $diff->y;
        $dias_servicio_del_ano = $diff->days % 365;
        
        $dias_base = 0;
        
        // 1. Cálculo de Días por Años Completos
        if ($anos_servicio >= 1) {
            // Lógica de Antigüedad LFT (días completos)
            if ($anos_servicio == 1) $dias_base = 12;
            else if ($anos_servicio == 2) $dias_base = 14;
            else if ($anos_servicio == 3) $dias_base = 16;
            else if ($anos_servicio == 4) $dias_base = 18;
            else if ($anos_servicio == 5) $dias_base = 20;
            else if ($anos_servicio >= 6) {
                // Lógica para años 6-10, 11-15, etc. (Incremento de 2 días cada 5 años)
                $anos_sobre_cinco = $anos_servicio - 5;
                $incrementos = floor($anos_sobre_cinco / 5);
                $dias_base = 20 + ($incrementos * 2);
            }
        }
        
        // 2. Cálculo de Días por Prorrateo del Año Actual (LFT dice 12 días por el primer año)
        // El prorrateo se hace sobre el derecho base del año en curso. 
        $dias_derecho_anual = 12; // Base de 12 para el primer año completo

        if ($anos_servicio >= 1) {
            // Si ya cumplió un año, el derecho anual se basa en los años que CUMPLIRÁ
            // Aquí necesitarías una lógica más precisa o guardar el derecho anual.
            // Por simplicidad, usaremos 12 días como base para el prorrateo.
            // Si ya cumplió un año, usa el derecho que le toca por el año que está corriendo:
            $dias_derecho_anual = $this->obtener_dias_por_antiguedad($anos_servicio + 1);
        }
        
        $dias_prorrateados = ($dias_derecho_anual / 365) * $dias_servicio_del_ano;

        // Sumar días base (completos) más los días prorrateados del año actual.
        return $dias_base + $dias_prorrateados;
    }


    // (Función auxiliar que te será útil)
    public function obtener_dias_por_antiguedad($antiguedad_anos) {
        if ($antiguedad_anos <= 0) return 0;
        if ($antiguedad_anos == 1) return 12;
        if ($antiguedad_anos == 2) return 14;
        if ($antiguedad_anos == 3) return 16;
        if ($antiguedad_anos == 4) return 18;
        if ($antiguedad_anos == 5) return 20;
        
        // De 6 años en adelante
        if ($antiguedad_anos >= 6) {
            $anos_sobre_cinco = $antiguedad_anos - 5;
            $incrementos = floor($anos_sobre_cinco / 5);
            return 20 + ($incrementos * 2);
        }
        return 0; // Por defecto
    }

    public function get_todos_los_usuarios_con_fecha_ingreso() {
        $conectar = $this->conectar();
        
        $sql = "SELECT usu_id, fecha_ingreso_planta FROM LS_USUARIOS WHERE est = 1"; // Solo usuarios activos
        
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 2. Obtiene el total de días usados y aprobados por un usuario.
     */
    public function get_dias_usados_totales($usu_id) {
        $conectar = $this->conectar();
        
        $sql = "SELECT 
                    SUM(vac_dias_habiles) AS dias_usados_totales
                FROM LS_VACACIONES_SOLICITUDES
                WHERE usu_id = :usu_id 
                AND vac_estado = 'Aprobada'"; 
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float)($resultado['dias_usados_totales'] ?? 0);
    }

    /**
     * 3. Guarda o actualiza el saldo de vacaciones en la nueva tabla LS_SALDO_VACACIONES.
     * Usa INSERT ... ON DUPLICATE KEY UPDATE para ser idempotente (seguro de correr varias veces).
     */
    public function guardar_saldo_actual($usu_id, $fecha_corte, $dias_generados_lft, $dias_usados, $saldo_final) {
        $conectar = $this->conectar();
        
        $sql = "INSERT INTO LS_SALDO_VACACIONES 
                (usu_id, fecha_corte, usu_dias_generados_lft, usu_dias_usados, usu_dias_disponibles)
                VALUES (:usu_id, :fecha_corte, :dias_lft, :dias_usados, :saldo_final)
                ON DUPLICATE KEY UPDATE
                    usu_dias_generados_lft = :dias_lft,
                    usu_dias_usados = :dias_usados,
                    usu_dias_disponibles = :saldo_final";
                    
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_corte', $fecha_corte, PDO::PARAM_STR);
        $stmt->bindValue(':dias_lft', $dias_generados_lft, PDO::PARAM_STR);
        $stmt->bindValue(':dias_usados', $dias_usados, PDO::PARAM_STR);
        $stmt->bindValue(':saldo_final', $saldo_final, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function get_ultima_fecha_actualizacion_saldos() {
        $conectar = $this->conectar();
        
        // Busca la fecha de corte más reciente que se haya guardado
        $sql = "SELECT MAX(fecha_corte) AS ultima_fecha FROM LS_SALDO_VACACIONES";
        
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['ultima_fecha'];
    }

    public function ejecutar_logica_actualizacion_saldos() {
        // 1. Obtener la última fecha de actualización
        $ultima_actualizacion = $this->get_ultima_fecha_actualizacion_saldos(); // Asume que esta función existe
        $fecha_hoy = date('Y-m-d');

        // 2. Solo ejecutar si nunca se ha corrido o si la última corrida fue ayer o antes.
        if (!$ultima_actualizacion || $ultima_actualizacion < $fecha_hoy) {
            
            $usuarios = $this->get_todos_los_usuarios_con_fecha_ingreso(); 
            $fecha_corte = $fecha_hoy;
            
            foreach ($usuarios as $usuario) {
                $usu_id = $usuario['usu_id'];
                $fecha_ingreso = $usuario['fecha_ingreso_planta'];

                if (empty($fecha_ingreso)) {
                    continue; 
                }

                // Cálculo LFT (usas la función que ya definimos antes)
                $dias_generados_lft = $this->calcular_dias_generados_lft($fecha_ingreso, $fecha_corte);
                
                // Días usados (usas la función que ya definimos antes)
                $dias_usados = $this->get_dias_usados_totales($usu_id);
                
                $saldo_final = $dias_generados_lft - $dias_usados;
                
                // Guardar Saldo (usas la función que ya definimos antes)
                $this->guardar_saldo_actual(
                    $usu_id,
                    $fecha_corte,
                    $dias_generados_lft,
                    $dias_usados,
                    $saldo_final
                );
            }
            return true; // Ejecución completada
        }
        return false; // No se requería ejecución
    }

}
