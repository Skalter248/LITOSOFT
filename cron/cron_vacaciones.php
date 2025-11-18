<?php
// ARCHIVO: LITOSOFT/cron/cron_vacaciones.php

/**
 * Script de CRON DEDICADO para gestionar saldos de vacaciones.
 * Ejecutar una vez al día (ej: 00:05 AM).
 * * NOTA: La ruta '__DIR__ . '/../...' es necesaria porque este archivo está 
 * dentro de la carpeta 'cron/' y debe subir un nivel (..) para llegar a 'config/'.
 */

// 1. CONFIGURACIÓN CRÍTICA DE RUTAS
require_once(__DIR__ . '/../config/index.php'); 
require_once(__DIR__ . '/../config/conexion.php'); 
require_once(MODEL_PATH . '/Vacaciones.php'); 


$vacaciones = new Vacaciones();

$result_init = $vacaciones->sincronizar_nuevos_usuarios();

$result_update = $vacaciones->actualizar_saldos_por_aniversario();

$log_message = date('Y-m-d H:i:s') . 
               " | Sincronizados (Nuevos): {$result_init['count']} usuarios." .
               " | Actualizados (Aniversario): {$result_update['count']} saldos.";
               
error_log("[CRON VACACIONES] " . $log_message);

exit;