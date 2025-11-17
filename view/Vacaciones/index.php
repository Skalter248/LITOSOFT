<?php
// ARCHIVO: view/Vacaciones/index.php

// 1. GESTIÓN DE SESIÓN Y REQUIRES (Ajusta las rutas según sea necesario)
require_once("../../config/conexion.php"); // Usar ruta relativa aquí es común si este archivo lo incluye un archivo superior
session_start();
require_once("../../models/Vacaciones.php"); 


if (!isset($_SESSION['usu_id'])) { 
    header("Location: ../../index.php"); 
    exit(); 
}

$rol_id = $_SESSION['rol_id'];

$vacaciones = new Vacaciones();
$vacaciones->ejecutar_logica_actualizacion_saldos(); 


// 3. OBTENER SALDO DEL USUARIO LOGUEADO
$usu_id = $_SESSION['usu_id']; 
$saldo = $vacaciones->get_saldo_actual($usu_id); // Obtiene el saldo recién actualizado

$saldo_disponible = number_format($saldo['usu_dias_disponibles'] ?? 0, 2);
$dias_generados_lft = number_format($saldo['usu_dias_generados_lft'] ?? 0, 2);
$dias_usados = number_format($saldo['usu_dias_usados'] ?? 0, 2);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Vacaciones</title>
    <?php require_once("../headermodulos/headermodulos.php");?>
    <link rel="stylesheet" href="../../public/css/main.css">
    
    <link href='../../public/lib/fullcalendar/main.min.css' rel='stylesheet' />
</head>
<body class="with-side-menu">
    
    <?php require_once("../headercapitalhumano/headercapitalhumano.php");?>
    <?php require_once("../navcapitalhumano/nav.php");?>

    <div class="page-content">
    <div class="container-fluid">
        <header class="section-header">
            <h2>Gestión de Vacaciones</h2>
        </header>

        <section class="card">
            <div class="card-header">
                <h3 class="card-title">Resumen de Saldo y Ciclo Vacacional</h3>
            </div>
            
            <div class="card-block">
                <div class="row">
                    
                    <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-primary text-white p-3 mb-3 text-center">
                            <p class="m-0">Fecha de Ingreso</p>
                            <h4 class="m-0" id="fecha_ingreso_planta_info">Calculando...</h4>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-info text-white p-3 mb-3 text-center">
                            <p class="m-0">Antigüedad (Años LFT)</p>
                            <h4 class="m-0" id="antiguedad_anos">0</h4>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-warning text-dark p-3 mb-3 text-center">
                            <p class="m-0">Fin del Ciclo (Vencimiento)</p>
                            <h4 class="m-0" id="fecha_vencimiento_ui">--/--/----</h4>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-secondary text-white p-3 mb-3 text-center">
                            <p class="m-0">Días Generados (LFT)</p>
                            <h4 class="m-0" id="dias_generados">0.00</h4>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-danger text-white p-3 mb-3 text-center">
                            <p class="m-0">Días Ocupados</p>
                            <h4 class="m-0" id="dias_usados">0.00</h4>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-success text-white p-3 mb-3 text-center">
                            <p class="m-0">Días Disponibles</p>
                            <h4 class="m-0" id="dias_disponibles">0.00</h4>
                            <p class="m-0 text-white" id="dias_adelantados_span_dashboard"></p>
                        </div>
                    </div>
                    
                    <div class="col-xl-6 col-md-6 col-sm-12 mb-3">
                        <div class="color-card bg-info text-white p-3 mb-3 text-center" style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
                            <p class="m-0">Acciones Rápidas</p>
                            <div class="d-flex justify-content-center mt-2">
                                <button type="button" class="btn btn-primary" onclick="abrirModalSolicitud()">
                                    <i class="fa fa-plane"></i> Crear Solicitud
                                </button>
                                <button type="button" class="btn btn-light mx-2" id="btnVerCalendario">
                                    <i class="fa fa-calendar-alt"></i> Ver Calendario
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
        
        <section class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de Solicitudes</h3>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table id="tabla_solicitudes" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Días Hábiles</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div></div>```
    
    
    <?php require_once("modalvacaciones.php");?>
    <script src='../../public/lib/fullcalendar/main.min.js'></script>
    <script type="text/javascript" src="mntvacaciones.js"></script>
</body>
</html>