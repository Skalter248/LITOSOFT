<?php
// ARCHIVO: view/AprobacionSolicitud/index.php
require_once("../../config/conexion.php"); 
session_start();

if (!isset($_SESSION['usu_id'])) { 
    header("Location: ../../index.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aprobación de Vacaciones</title>
    <?php require_once("../headermodulos/headermodulos.php");?>
    <link rel="stylesheet" href="../../public/css/main.css">
</head>
<body class="with-side-menu">
    
    <?php require_once("../headercapitalhumano/headercapitalhumano.php");?> 
    <?php require_once("../navcapitalhumano/nav.php");?> 

    <div class="page-content">
        <div class="container-fluid">
            <header class="section-header">
                <div class="tbl">
                    <div class="tbl-row">
                        <div class="tbl-cell">
                            <h3>Aprobación de Solicitudes</h3>
                            <ol class="breadcrumb breadcrumb-simple">
                                <li><a href="#">Vacaciones</a></li>
                                <li class="active">Aprobación</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </header>

            <section class="card">
                <div class="card-block">
                    <table id="tabla_aprobacion" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Fechas</th>
                                <th>Días</th>
                                <th>Observaciones / Motivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>

    <?php require_once("../MainJs/js.php");?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="aprobacion.js"></script> 
</body>
</html>