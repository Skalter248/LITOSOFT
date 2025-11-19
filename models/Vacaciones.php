<?php
// ARCHIVO: LITOSOFT/models/Vacaciones.php

// Asegúrate de que 'Conexion' ya esté cargado o definido

class Vacaciones {

    // =================================================================
    // 🛠️ LÓGICA AUXILIAR
    // =================================================================

    // =================================================================
    // 🛠️ LÓGICA AUXILIAR DE CÁLCULO LFT (Necesaria para todo el sistema)
    // =================================================================

    /**
     * Auxiliar: Devuelve los días base según la LFT (México).
     * @param int $antiguedad_anos Años de antigüedad cumplidos.
     * @return int Días de vacaciones base.
     */
    private function obtener_dias_por_antiguedad($antiguedad_anos) {
        if ($antiguedad_anos <= 0) return 0;
        
        // Nueva ley LFT (12, 14, 16, 18, 20 en los primeros 5 años)
        if ($antiguedad_anos >= 1 && $antiguedad_anos <= 5) {
            // 1 año -> 12, 2 años -> 14, etc.
            return 10 + ($antiguedad_anos * 2); 
        }
        
        // A partir del 6to año, el incremento es de 2 días cada 5 años.
        if ($antiguedad_anos >= 6) {
            $anos_sobre_cinco = $antiguedad_anos - 5;
            $incrementos = floor($anos_sobre_cinco / 5);
            // 20 días base (por los primeros 5 años) + incrementos
            return 20 + ($incrementos * 2);
        }
        return 0;
    }
    
    /**
     * Auxiliar: Devuelve la fecha del próximo aniversario (vencimiento del ciclo actual).
     * Se usa para establecer la fecha_vencimiento_ciclo en la tabla de saldos.
     * @param string $fecha_ingreso_planta Fecha de ingreso del usuario (Y-m-d).
     * @return string La fecha del próximo aniversario (Y-m-d).
     */
    private function obtener_fecha_vencimiento_ciclo($fecha_ingreso_planta) {
        $fecha_ingreso_dt = new DateTime($fecha_ingreso_planta);
        $hoy = new DateTime();
        
        // 1. Calcular el aniversario de este año (mismo mes y día de ingreso)
        $aniversario_este_ano = new DateTime($hoy->format('Y') . '-' . $fecha_ingreso_dt->format('m-d'));
        
        // 2. Si el aniversario de este año ya pasó o es hoy, el ciclo vence el próximo año.
        if ($aniversario_este_ano <= $hoy) {
            $aniversario_este_ano->modify('+1 year');
        } 
        
        return $aniversario_este_ano->format('Y-m-d');
    }

    // =================================================================
    // 1. FUNCIÓN DE INICIALIZACIÓN/RESPALDO
    // =================================================================
    
    /**
     * Inicializa o actualiza el registro de saldo del usuario.
     * @param string $fecha_ingreso_planta Se busca en LS_USUARIOS si es nula.
     */
    public function inicializar_saldo_usuario($usu_id, $fecha_ingreso_planta = null) {
        
        $conectar = Conexion::conectar();
        
        // Si no tenemos la fecha de ingreso (ej. al llamar desde el CRON), la buscamos.
        if (empty($fecha_ingreso_planta)) {
            $sql_fetch = "SELECT fecha_ingreso_planta FROM LS_USUARIOS WHERE usu_id = :usu_id";
            $stmt_fetch = $conectar->prepare($sql_fetch);
            $stmt_fetch->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt_fetch->execute();
            $data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);
            
            if (empty($data['fecha_ingreso_planta'])) {
                error_log("ERROR: No se encontró fecha_ingreso_planta para el usuario ID: $usu_id.");
                return ['success' => false, 'message' => 'No se pudo obtener la fecha de ingreso.'];
            }
            $fecha_ingreso_planta = $data['fecha_ingreso_planta'];
        }
        
        // Cálculo de Antigüedad
        $hoy = new DateTime();
        $ingreso_dt = new DateTime($fecha_ingreso_planta);
        $diferencia = $ingreso_dt->diff($hoy);
        $antiguedad_anos = $diferencia->y; 
        
        $dias_generados_lft = $this->obtener_dias_por_antiguedad($antiguedad_anos);
        $fecha_vencimiento_ciclo = $this->obtener_fecha_vencimiento_ciclo($fecha_ingreso_planta);
        $dias_disponibles_inicial = $dias_generados_lft; 

        // Insertar/Actualizar el registro
        $sql = "INSERT INTO LS_USUARIOS_SALDOS 
                (usu_id, fecha_ingreso_planta, fecha_vencimiento_ciclo, usu_dias_disponibles, usu_dias_generados_lft, usu_dias_usados, ultima_actualizacion) 
                VALUES 
                (:usu_id, :fecha_ingreso, :fecha_vencimiento, :dias_disponibles, :dias_lft, 0.00, NOW())
                ON DUPLICATE KEY UPDATE
                    fecha_ingreso_planta = VALUES(fecha_ingreso_planta),
                    fecha_vencimiento_ciclo = VALUES(fecha_vencimiento_ciclo),
                    usu_dias_disponibles = VALUES(usu_dias_disponibles),
                    usu_dias_generados_lft = VALUES(usu_dias_generados_lft),
                    ultima_actualizacion = NOW()";
                    
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_ingreso', $fecha_ingreso_planta, PDO::PARAM_STR);
        $stmt->bindValue(':fecha_vencimiento', $fecha_vencimiento_ciclo, PDO::PARAM_STR);
        $stmt->bindValue(':dias_disponibles', $dias_disponibles_inicial, PDO::PARAM_STR);
        $stmt->bindValue(':dias_lft', $dias_generados_lft, PDO::PARAM_STR);

        try {
            return ['success' => $stmt->execute(), 'dias_lft' => $dias_generados_lft, 'count' => 1];
        } catch (PDOException $e) {
            error_log("Error de BD al inicializar saldo para ID $usu_id: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de BD al inicializar saldo.'];
        }
    }
    
    // =================================================================
    // 2. FUNCIÓN CRON: SINCRONIZAR NUEVOS EMPLEADOS
    // =================================================================
    
    public function sincronizar_nuevos_usuarios() {
        $conectar = Conexion::conectar();
        $count = 0;
        
        // Busca IDs en LS_USUARIOS que no están en LS_USUARIOS_SALDOS
        $sql = "SELECT u.usu_id, u.fecha_ingreso_planta
                FROM LS_USUARIOS u
                LEFT JOIN LS_USUARIOS_SALDOS s ON u.usu_id = s.usu_id
                WHERE s.usu_id IS NULL AND u.fecha_ingreso_planta IS NOT NULL";
                
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $usuarios_nuevos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usuarios_nuevos as $usuario) {
            // Llama a inicializar_saldo_usuario, que hará el cálculo y la inserción.
            $result = $this->inicializar_saldo_usuario($usuario['usu_id'], $usuario['fecha_ingreso_planta']);
            if ($result['success']) {
                $count++;
            }
        }
        return ['success' => true, 'count' => $count];
    }

    // =================================================================
    // 3. FUNCIÓN CRON: ACTUALIZACIÓN POR ANIVERSARIO
    // =================================================================

    public function actualizar_saldos_por_aniversario() {
        $conectar = Conexion::conectar();
        $actualizaciones_exitosas = 0;

        // Obtener usuarios cuyo ciclo ha vencido (hoy o en el pasado)
        $sql = "SELECT usu_id, fecha_ingreso_planta, usu_dias_usados 
                FROM LS_USUARIOS_SALDOS 
                WHERE fecha_vencimiento_ciclo <= CURDATE()";
        
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $usuarios_a_actualizar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usuarios_a_actualizar as $usuario) {
            $usu_id = $usuario['usu_id'];
            $fecha_ingreso = $usuario['fecha_ingreso_planta'];
            $dias_usados_acumulado = (float)$usuario['usu_dias_usados'];

            // Recalcular la nueva antigüedad
            $hoy = new DateTime();
            $ingreso_dt = new DateTime($fecha_ingreso);
            $diferencia = $ingreso_dt->diff($hoy);
            $antiguedad_anos = $diferencia->y;
            
            $nuevos_dias_lft = $this->obtener_dias_por_antiguedad($antiguedad_anos);
            $nuevo_saldo_disponible = $nuevos_dias_lft - $dias_usados_acumulado;

            // Obtener la nueva fecha de vencimiento (el aniversario del próximo año)
            $nueva_fecha_vencimiento = $this->obtener_fecha_vencimiento_ciclo($fecha_ingreso);
            
            // Actualizar la base de datos
            $sql_update = "UPDATE LS_USUARIOS_SALDOS SET 
                            fecha_vencimiento_ciclo = :nueva_vencimiento,
                            usu_dias_disponibles = :nuevo_saldo, 
                            usu_dias_generados_lft = :nuevos_dias_lft,
                            usu_dias_usados = 0.00, -- REINICIO DEL CONTADOR DE USADOS
                            ultima_actualizacion = NOW()
                            WHERE usu_id = :usu_id";
                            
            $stmt_update = $conectar->prepare($sql_update);
            $stmt_update->bindValue(':nueva_vencimiento', $nueva_fecha_vencimiento, PDO::PARAM_STR);
            $stmt_update->bindValue(':nuevo_saldo', number_format($nuevo_saldo_disponible, 2, '.', ''), PDO::PARAM_STR);
            $stmt_update->bindValue(':nuevos_dias_lft', number_format($nuevos_dias_lft, 2, '.', ''), PDO::PARAM_STR);
            $stmt_update->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
            
            if ($stmt_update->execute()) {
                $actualizaciones_exitosas++;
            } else {
                 error_log("Fallo al actualizar saldo por aniversario para ID: $usu_id");
            }
        }
        return ['success' => true, 'count' => $actualizaciones_exitosas];
    }

    /**
     * Calcula los días de vacaciones que corresponden por ley (LFT 2023)
     * según la antigüedad en años cumplidos.
     * * @param int $antiguedad_anos Antigüedad del empleado en años.
     * @return float Días de vacaciones que corresponden por ley.
     */
    public function calcular_dias_lft_segun_antiguedad($antiguedad_anos) {
        if ($antiguedad_anos <= 0) {
            return 0.00;
        }

        // Lógica LFT post-2023: +2 días por año hasta el 5to año.
        if ($antiguedad_anos >= 1 && $antiguedad_anos <= 5) {
            // Fórmula: 12 días base + (Años - 1) * 2
            return 12.00 + (($antiguedad_anos - 1) * 2);
        } 
        
        // De 6 a 10 años
        else if ($antiguedad_anos >= 6 && $antiguedad_anos <= 10) {
            return 22.00;
        } 
        
        // De 11 a 15 años
        else if ($antiguedad_anos >= 11 && $antiguedad_anos <= 15) {
            return 24.00;
        }
        
        // De 16 a 20 años
        else if ($antiguedad_anos >= 16 && $antiguedad_anos <= 20) {
            return 26.00;
        }
        
        // De 21 a 25 años
        else if ($antiguedad_anos >= 21 && $antiguedad_anos <= 25) {
            return 28.00;
        }
        
        // Más de 25 años (ej. 26 a 30)
        else if ($antiguedad_anos >= 26 && $antiguedad_anos <= 30) {
            return 30.00;
        }
        
        // Para cualquier antigüedad mayor (solo si tu empresa lo aplica)
        else {
            return 32.00;
        }
    }

    /**
     * Suma los días hábiles de las solicitudes de vacaciones APROBADAS para un usuario.
     * @param int $usu_id ID del usuario.
     * @return float Total de días usados.
     */
    public function get_dias_usados_aprobados($usu_id) {
        $conectar = Conexion::conectar();
        
        $sql = "SELECT SUM(vac_dias_habiles) AS total_usados 
                FROM LS_VACACIONES_SOLICITUDES
                WHERE usu_id = :usu_id 
                AND vac_estado = 'Aprobada'"; // Solo contamos las APROBADAS
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la suma es NULL (no hay solicitudes), devolvemos 0.00
        return (float)($data['total_usados'] ?? 0.00);
    }

    public function ejecutar_logica_actualizacion_saldos() {
        $conectar = Conexion::conectar();
        
        // 1. Obtener todos los usuarios con su fecha de ingreso
        $sql_usuarios = "SELECT usu_id, fecha_ingreso_planta FROM LS_USUARIOS";
        $stmt_usuarios = $conectar->query($sql_usuarios);
        $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

        // 2. Preparar el statement de actualización de saldos
        // NOTA: Asegúrate de incluir y bindear todas tus variables de saldo (días LFT, días usados, etc.)
        $sql_update = "INSERT INTO LS_USUARIOS_SALDOS 
               (usu_id, fecha_ingreso_planta, usu_dias_generados_lft, usu_dias_usados, usu_dias_disponibles, usu_antiguedad_anos, fecha_ultima_actualizacion) 
               VALUES (:usu_id, :fecha_ingreso, :dias_lft, :dias_usados, :dias_disponibles, :antiguedad_anos, NOW())
               ON DUPLICATE KEY UPDATE 
               fecha_ingreso_planta = VALUES(fecha_ingreso_planta),  /* <-- LÍNEA AÑADIDA */
               usu_dias_generados_lft = VALUES(usu_dias_generados_lft), 
               usu_dias_usados = VALUES(usu_dias_usados), 
               usu_dias_disponibles = VALUES(usu_dias_disponibles),
               usu_antiguedad_anos = VALUES(usu_antiguedad_anos), 
               fecha_ultima_actualizacion = NOW()";
                        // ... columna que faltaba
            $stmt_update = $conectar->prepare($sql_update);

        foreach ($usuarios as $usuario) {
            $usu_id = $usuario['usu_id'];
            $fecha_ingreso = $usuario['fecha_ingreso_planta'];

            if (empty($fecha_ingreso)) {
                // 1. Advertencia: Este usuario no tiene fecha de ingreso en LS_USUARIOS
                // Puedes agregar un log aquí si es necesario.

                // 2. Usar una fecha segura (ej. 1900-01-01) para que el campo NOT NULL no falle.
                // Esto también asegura que los días LFT y la antigüedad sean 0.
                $fecha_ingreso = '1900-01-01'; 
            }
            
            // **********************************************
            // ******* CÁLCULO Y ALMACENAMIENTO DE ANTIGÜEDAD *******
            // **********************************************
            $antiguedad_anos = $this->calcular_antiguedad_en_anos($fecha_ingreso);
            
            // NOTA: Aquí iría la lógica compleja para calcular $dias_lft y $dias_usados actualizados.
            // Simularemos valores (Tú debes poner tu lógica real aquí)
            $dias_lft = $this->calcular_dias_lft_segun_antiguedad($antiguedad_anos); // Ejemplo de función que debes tener
            $dias_usados = $this->get_dias_usados_aprobados($usu_id); // Ejemplo de función que debes tener
            $dias_disponibles = $dias_lft - $dias_usados;
            
            // Ejecutar la actualización/inserción
            $stmt_update->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
            $stmt_update->bindValue(':fecha_ingreso', $fecha_ingreso, PDO::PARAM_STR);
            $stmt_update->bindValue(':dias_lft', number_format($dias_lft, 2, '.', ''), PDO::PARAM_STR);
            $stmt_update->bindValue(':dias_usados', number_format($dias_usados, 2, '.', ''), PDO::PARAM_STR);
            $stmt_update->bindValue(':dias_disponibles', number_format($dias_disponibles, 2, '.', ''), PDO::PARAM_STR);
            // Bindear el nuevo campo calculado
            $stmt_update->bindValue(':antiguedad_anos', $antiguedad_anos, PDO::PARAM_INT); 
            
            $stmt_update->execute();
        }
    }

<<<<<<< HEAD
    public function get_solicitud_para_impresion($vac_id) {
        $conectar = Conexion::conectar();
        
        $sql = "SELECT 
                    s.*,
                    -- Solicitante - Información Personal
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido_paterno, ' ', u.usu_apellido_materno) as nombre_solicitante,
                    
                    -- Solicitante - Mapeo de IDs a Nombres
                    ps.pue_nombre AS solicitante_puesto_nombre, 
                    pa.area_nombre AS solicitante_area_nombre,
                    pd.dep_nombre AS solicitante_depto_nombre, 
                    
                    -- Solicitante - FIRMA
                    fs.firma_ruta_completa AS solicitante_firma_ruta,
                    fs.firma_nombre_archivo AS solicitante_firma_nombre,
                    
                    -- Aprobador - Información Personal (CRÍTICO: Usar COALESCE)
                    COALESCE(CONCAT(a.usu_nombre, ' ', a.usu_apellido_paterno, ' ', a.usu_apellido_materno), 'N/A') as nombre_aprobador,
                    
                    -- Aprobador - Mapeo de IDs a Nombres (Usar COALESCE)
                    COALESCE(pas.pue_nombre, 'N/A') AS aprobador_puesto_nombre, 
                    COALESCE(paa.area_nombre, 'N/A') AS aprobador_area_nombre,
                    COALESCE(pad.dep_nombre, 'N/A') AS aprobador_depto_nombre, 
                    
                    -- Aprobador - FIRMA (CRÍTICO: Usar COALESCE para asegurar un string vacío en lugar de NULL)
                    COALESCE(fa.firma_ruta_completa, '') AS aprobador_firma_ruta,
                    COALESCE(fa.firma_nombre_archivo, '') AS aprobador_firma_nombre,

                    -- Saldos
                    us.usu_dias_disponibles AS saldo_actual_antes, 
                    us.usu_dias_generados_lft 
                FROM ls_vacaciones_solicitudes s
                INNER JOIN ls_usuarios u ON s.usu_id = u.usu_id 
                
                /* JOINs para Solicitante (u) y su firma (fs) */
                LEFT JOIN LS_PUESTOS ps ON u.usu_puesto = ps.pue_id 
                LEFT JOIN ls_areas pa ON u.usu_area = pa.area_id 
                LEFT JOIN ls_departamentos pd ON u.usu_departamento = pd.dep_id
                LEFT JOIN LS_FIRMAS fs ON u.usu_id = fs.usu_id
                
                /* JOINs para Aprobador (a) y su firma (fa) */
                LEFT JOIN ls_usuarios a ON s.usu_aprobador_id = a.usu_id
                LEFT JOIN LS_PUESTOS pas ON a.usu_puesto = pas.pue_id 
                LEFT JOIN ls_areas paa ON a.usu_area = paa.area_id 
                LEFT JOIN ls_departamentos pad ON a.usu_departamento = pad.dep_id
                LEFT JOIN LS_FIRMAS fa ON a.usu_id = fa.usu_id
                
                LEFT JOIN ls_usuarios_saldos us ON s.usu_id = us.usu_id
                WHERE s.vac_id = :vac_id";
                
        try {
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(':vac_id', $vac_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener datos para impresión: " . $e->getMessage());
            return ['error_sql' => $e->getMessage()];
        }
    }

    public function gestionar_solicitud($vac_id, $accion, $aprobador_id) {
        $conectar = Conexion::conectar();
        
        try {
            // INICIAR TRANSACCIÓN (Todo o nada)
            $conectar->beginTransaction();

            // 1. Obtener datos de la solicitud actual (para validar y obtener días)
            $sql_sol = "SELECT usu_id, vac_dias_habiles, vac_estado FROM ls_vacaciones_solicitudes WHERE vac_id = :vac_id";
            $stmt_sol = $conectar->prepare($sql_sol);
            // Usamos execute con array para un binding limpio
            $stmt_sol->execute([':vac_id' => $vac_id]);
            $solicitud = $stmt_sol->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                throw new Exception("Solicitud no encontrada.");
            }

            if ($solicitud['vac_estado'] != 'Pendiente') {
                throw new Exception("Esta solicitud ya fue procesada anteriormente.");
            }

            $usu_id_solicitante = $solicitud['usu_id'];
            $dias_a_descontar = floatval($solicitud['vac_dias_habiles']);
            $nuevo_estado = '';

            // 2. Ejecutar la acción
            if ($accion == 'aprobar') {
                $nuevo_estado = 'Aprobada';
                
                // --- ACTUALIZAR SALDOS (SOLO SI SE APRUEBA) ---
                // Usamos nombres de parámetros únicos para el mismo valor (:dias_disp y :dias_usados)
                $sql_update_saldo = "UPDATE ls_usuarios_saldos 
                                    SET usu_dias_disponibles = usu_dias_disponibles - :dias_disp, /* <-- ÚNICO */
                                        usu_dias_usados = usu_dias_usados + :dias_usados,     /* <-- ÚNICO */
                                        fecha_ultima_actualizacion = NOW()
                                    WHERE usu_id = :usu_id";
                $stmt_saldo = $conectar->prepare($sql_update_saldo);
                
                // Binding seguro usando execute(array)
                if (!$stmt_saldo->execute([
                    // Asignamos el mismo valor a los dos parámetros únicos
                    ':dias_disp' => $dias_a_descontar,
                    ':dias_usados' => $dias_a_descontar,
                    ':usu_id' => $usu_id_solicitante
                ])) {
                    throw new Exception("Error al actualizar saldos.");
                }

            } elseif ($accion == 'rechazar') {
                $nuevo_estado = 'Rechazada';
            } elseif ($accion == 'cancelar') {
                $nuevo_estado = 'Cancelada';
            } else {
                throw new Exception("Acción no válida.");
            }

            // 3. Actualizar Estado de la Solicitud (también corregido a execute(array))
            $sql_update_sol = "UPDATE ls_vacaciones_solicitudes 
                            SET vac_estado = :estado, 
                                usu_aprobador_id = :aprobador, 
                                vac_fecha_aprobacion = NOW() 
                            WHERE vac_id = :vac_id";
            $stmt_update = $conectar->prepare($sql_update_sol);

            // Binding seguro usando execute(array)
            if (!$stmt_update->execute([
                ':estado' => $nuevo_estado,
                ':aprobador' => $aprobador_id,
                ':vac_id' => $vac_id
            ])) {
                throw new Exception("Error al actualizar el estado de la solicitud.");
            }

            // CONFIRMAR TRANSACCIÓN
            $conectar->commit();
            return ['status' => true, 'message' => "Solicitud $nuevo_estado correctamente."];

        } catch (Exception $e) {
            // SI ALGO FALLA, REVERTIR TODO
            $conectar->rollBack();
            return ['status' => false, 'message' => "Error de Transacción: " . $e->getMessage()];
        }
    }
    public function listar_solicitudes_por_jefe($jefe_id) {
        $conectar = Conexion::conectar();
        $sql = "SELECT 
                    s.vac_id, 
                    s.vac_fecha_inicio, 
                    s.vac_fecha_fin, 
                    s.vac_dias_habiles, 
                    s.vac_estado, 
                    s.vac_fecha_solicitud,
                    s.vac_observaciones,
                    -- Nombre del empleado solicitante
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido_paterno) as nombre_empleado,
                    u.usu_foto
                FROM ls_vacaciones_solicitudes s
                INNER JOIN ls_usuarios u ON s.usu_id = u.usu_id
                WHERE u.jefe_id = :jefe_id  /* <-- ¡CORRECCIÓN CLAVE AQUÍ! */
                ORDER BY FIELD(s.vac_estado, 'Pendiente', 'Aprobada', 'Rechazada', 'Cancelada'), s.vac_id DESC";
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':jefe_id', $jefe_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

=======
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
    /**
     * Calcula los días hábiles (lunes a viernes) entre dos fechas (inclusivo).
     */

    public function listar_mis_solicitudes($usu_id) {
        $conectar = Conexion::conectar();
<<<<<<< HEAD
        $sql = "SELECT 
                    s.vac_id, 
                    s.vac_fecha_inicio, 
                    s.vac_fecha_fin, 
                    s.vac_dias_habiles, 
                    s.vac_estado, 
                    s.vac_fecha_solicitud,
                    s.vac_observaciones,
                    -- Obtenemos el nombre del aprobador si existe
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido_paterno) as nombre_aprobador
                FROM ls_vacaciones_solicitudes s
                LEFT JOIN ls_usuarios u ON s.usu_aprobador_id = u.usu_id
                WHERE s.usu_id = :usu_id
                ORDER BY s.vac_id DESC";
=======
        
        $sql = "SELECT vac_id, vac_fecha_inicio, vac_fecha_fin, vac_dias_habiles, vac_estado
                FROM LS_VACACIONES_SOLICITUDES
                WHERE usu_id = :usu_id
                ORDER BY vac_id DESC";
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcula la antigüedad de un usuario en años cumplidos.
     * @param string $fecha_ingreso Fecha de ingreso del usuario (Y-m-d).
     * @param string $fecha_referencia Fecha de referencia (hoy por defecto).
     * @return int Antigüedad en años.
     */
    public function calcular_antiguedad_en_anos($fecha_ingreso, $fecha_referencia = null) {
        if (empty($fecha_referencia)) { 
            $fecha_referencia = date('Y-m-d'); 
        }

        if (empty($fecha_ingreso) || $fecha_ingreso === 'N/A') { 
            return 0; 
        }

        try {
            $ingreso_dt = new DateTime($fecha_ingreso);
            $hoy_dt = new DateTime($fecha_referencia);
            $diferencia = $ingreso_dt->diff($hoy_dt);
            return $diferencia->y; // Devolvemos la diferencia en años
        } catch (Exception $e) {
            error_log("Error al calcular antigüedad: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene los datos del saldo actual del usuario desde la tabla LS_USUARIOS_SALDOS,
     * y calcula la antigüedad en años.
     * @param int $usu_id ID del usuario
     * @return array Datos del saldo, incluida la antigüedad.
     */
    public function get_saldo_actual($usu_id) {
        $conectar = Conexion::conectar();
        
        // *** CAMBIO CLAVE: Incluir usu_antiguedad_anos en el SELECT ***
        $sql = "SELECT 
                    fecha_ingreso_planta, 
                    fecha_vencimiento_ciclo, 
                    usu_dias_disponibles, 
                    usu_dias_generados_lft, 
                    usu_dias_usados, 
                    usu_antiguedad_anos 
                FROM LS_USUARIOS_SALDOS
                WHERE usu_id = :usu_id";
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no hay datos, inicializar a 0 y N/A
        if (!$data) {
            return [
                'fecha_ingreso_planta' => 'N/A',
                'antiguedad_anos' => 0, // Clave que la vista espera
                'fecha_vencimiento_ciclo' => 'N/A',
                'usu_dias_disponibles' => 0.00,
                'usu_dias_generados_lft' => 0.00,
                'usu_dias_usados' => 0.00
            ];
        }
        
        // *** MAPEO CLAVE: Asignar el valor del campo de la BD a la clave 'antiguedad_anos' ***
        $data['antiguedad_anos'] = $data['usu_antiguedad_anos'] ?? 0;
        
        return $data;
    }

    /**
     * Obtiene el ID del jefe inmediato del usuario.
     * @param int $usu_id ID del usuario.
     * @return int ID del jefe o 0 si no tiene.
     */
    public function get_jefe_inmediato($usu_id) {
        $conectar = Conexion::conectar();
        
        $sql = "SELECT jefe_id FROM ls_usuarios WHERE usu_id = :usu_id";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no tiene jefe o el ID es nulo, devuelve 0 para evitar errores.
        return (int)($data['jefe_id'] ?? 0);
    }

    /**
     * Calcula días hábiles y naturales entre dos fechas, excluyendo fines de semana (Sáb/Dom) y festivos.
     * @param string $fecha_inicio Fecha de inicio (Y-m-d).
     * @param string $fecha_fin Fecha de fin (Y-m-d).
     * @return array ['dias_habiles' => float, 'dias_naturales' => int]
     */
    public function calcular_dias_habiles($fecha_inicio, $fecha_fin) {
        $dias_habiles = 0;
        $dias_naturales = 0;

        // Validaciones básicas
        if (empty($fecha_inicio) || empty($fecha_fin)) return ['dias_habiles' => 0, 'dias_naturales' => 0];
        if ($fecha_inicio > $fecha_fin) return ['dias_habiles' => 0, 'dias_naturales' => 0];

        try {
            $conectar = Conexion::conectar();
            $festivos_db = [];

            // Intentamos obtener festivos, si la tabla no existe, usamos array vacío
            try {
                $sql_festivos = "SELECT dia_fecha FROM LS_DIAS_FESTIVOS";
                $stmt_festivos = $conectar->query($sql_festivos);
                if ($stmt_festivos) {
                    $festivos_db = $stmt_festivos->fetchAll(PDO::FETCH_COLUMN, 0);
                }
            } catch (Exception $e) {
                // Si la tabla no existe, ignoramos los festivos por ahora
                $festivos_db = [];
            }

            $inicio = new DateTime($fecha_inicio);
            $fin = new DateTime($fecha_fin);
            $fin->modify('+1 day'); // Incluir el día final

            $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);

            foreach ($periodo as $dia) {
                $dias_naturales++;
                $dia_semana = $dia->format('N'); // 1 (Lunes) a 7 (Domingo)
                $fecha_actual = $dia->format('Y-m-d');

                // Si es Lunes(1) a Viernes(5) Y no es festivo
                if ($dia_semana >= 1 && $dia_semana <= 5 && !in_array($fecha_actual, $festivos_db)) {
                    $dias_habiles++;
                }
            }
        } catch (Exception $e) {
            return ['dias_habiles' => 0, 'dias_naturales' => 0];
        }

        return [
            'dias_habiles' => number_format($dias_habiles, 2, '.', ''),
            'dias_naturales' => $dias_naturales
        ];
    }
    /**
     * Guarda una nueva solicitud de vacaciones en estado PENDIENTE.
     * @param array $datos_solicitud Todos los datos de la solicitud.
     * @return string 'ok' si es exitoso, o mensaje de error.
     */
<<<<<<< HEAD
    public function guardar_solicitud($usu_id, $fecha_inicio, $fecha_fin, $dias_habiles, $observaciones) {
        $conectar = Conexion::conectar();
        
        // 1. Obtener datos del usuario (Jefe y Saldo actual para marcar si es adelanto)
        $sql_user = "SELECT u.jefe_id, s.usu_dias_disponibles 
                    FROM ls_usuarios u
                    LEFT JOIN ls_usuarios_saldos s ON u.usu_id = s.usu_id
                    WHERE u.usu_id = :usu_id";
        $stmt_user = $conectar->prepare($sql_user);
        $stmt_user->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt_user->execute();
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

        $jefe_id = $user_data['jefe_id']; // Aunque no se guarda en solicitud, sirve para notificaciones futuras
        $saldo_actual = floatval($user_data['usu_dias_disponibles']);

        // 2. Lógica para marcar Adelanto en Observaciones
        // Como no tenemos columna 'es_adelanto', lo inyectamos en el texto para que el jefe lo vea.
        if ($dias_habiles > $saldo_actual) {
            $observaciones = "[SOLICITUD DE ADELANTO] " . $observaciones;
        }

        // 3. Insertar Solicitud
        $sql = "INSERT INTO ls_vacaciones_solicitudes 
                (usu_id, vac_fecha_inicio, vac_fecha_fin, vac_dias_habiles, vac_observaciones, vac_estado, vac_fecha_solicitud) 
                VALUES 
                (:usu_id, :inicio, :fin, :habiles, :obs, 'Pendiente', NOW())";
                
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(':usu_id', $usu_id, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $fecha_inicio, PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fecha_fin, PDO::PARAM_STR);
        $stmt->bindValue(':habiles', $dias_habiles, PDO::PARAM_STR);
        $stmt->bindValue(':obs', $observaciones, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Solicitud enviada correctamente.'];
            } else {
                return ['status' => false, 'message' => 'Error BD al guardar.'];
            }
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
=======
    public function guardar_solicitud($datos_solicitud) {
        $conectar = Conexion::conectar();
        
        $sql = "INSERT INTO LS_VACACIONES_SOLICITUDES 
                (usu_id, usu_jefe_id, vac_fecha_inicio, vac_fecha_fin, vac_dias_naturales, vac_dias_habiles, vac_observaciones, vac_estado, vac_fecha_solicitud) 
                VALUES 
                (:usu_id, :jefe_id, :inicio, :fin, :naturales, :habiles, :obs, 'PENDIENTE', NOW())";
                
        $stmt = $conectar->prepare($sql);
        
        $stmt->bindValue(':usu_id', $datos_solicitud['usu_id'], PDO::PARAM_INT);
        $stmt->bindValue(':jefe_id', $datos_solicitud['usu_jefe_id'], PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $datos_solicitud['vac_fecha_inicio'], PDO::PARAM_STR);
        $stmt->bindValue(':fin', $datos_solicitud['vac_fecha_fin'], PDO::PARAM_STR);
        $stmt->bindValue(':naturales', $datos_solicitud['vac_dias_naturales'], PDO::PARAM_INT);
        $stmt->bindValue(':habiles', $datos_solicitud['vac_dias_habiles'], PDO::PARAM_STR);
        $stmt->bindValue(':obs', $datos_solicitud['vac_observaciones'], PDO::PARAM_STR);

        try {
            return $stmt->execute() ? "ok" : "Error al ejecutar la inserción.";
        } catch (PDOException $e) {
            return "Error PDO al guardar la solicitud: " . $e->getMessage();
>>>>>>> fa224ba21b5c5d01405e4102bb20c3f3077f62ac
        }
    }

        
    // ... [Aquí van otras funciones de tu modelo, como get_saldo_actual, etc.]
}