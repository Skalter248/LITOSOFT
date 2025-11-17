<?php
// ARCHIVO: controller/Vacaciones.php

// 1. CORRECCIÓN DE CONTEXTO Y SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. REQUIRES CRÍTICOS (Ajusta las rutas si son diferentes)
require_once('../config/conexion.php'); 
require_once('../models/Vacaciones.php'); 

$vacaciones = new Vacaciones();
$op = isset($_GET["op"]) ? $_GET["op"] : "";

switch ($op) {

    case 'get_resumen_vacaciones':
        // Carga el saldo disponible del usuario
        if (!isset($_SESSION['usu_id'])) {
            $saldo = ['usu_dias_disponibles' => 0.00];
        } else {
            $usu_id = $_SESSION['usu_id'];
            $saldo = $vacaciones->get_saldo_actual($usu_id);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['saldo' => $saldo['usu_dias_disponibles']]);
        break;

    case 'calcular_dias_habiles':
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        $resultado = $vacaciones->calcular_dias_habiles($fecha_inicio, $fecha_fin);
        
        header('Content-Type: application/json');
        echo json_encode($resultado);
        break;

    case 'guardar_solicitud':
        if (!isset($_SESSION['usu_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
            exit();
        }
        
        $usu_id = $_SESSION['usu_id'];
        $vac_fecha_inicio = $_POST['vac_fecha_inicio'];
        $vac_fecha_fin = $_POST['vac_fecha_fin'];
        $vac_dias_habiles = $_POST['vac_dias_habiles']; // Recibido como string/float
        $vac_observaciones = $_POST['vac_observaciones'];
        
        $resultado = $vacaciones->guardar_solicitud($usu_id, $vac_fecha_inicio, $vac_fecha_fin, $vac_dias_habiles, $vac_observaciones);

        header('Content-Type: application/json');
        echo json_encode($resultado); 
        break;

    case 'listar_mis_solicitudes':
        if (!isset($_SESSION['usu_id'])) {
             echo json_encode(["data" => []]);
             exit();
        }
        echo json_encode($vacaciones->listar_mis_solicitudes($_SESSION['usu_id'])); 
        break;
        
    // No hay otros casos por ahora
}