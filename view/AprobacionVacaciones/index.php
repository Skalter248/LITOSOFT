<?php
    // ARCHIVO: view/AprobacionVacaciones/index.php
    
    require_once("../../config/index.php"); 
    session_start();

    // Redirigir si no está logueado
    if (!isset($_SESSION['usu_id'])) { header("Location: ../../index.php"); exit(); }
    
    // Opcional: Validar que el rol sea de Jefe o Admin (Ajusta los IDs de rol según tu DB)
    // if ($_SESSION['rol_id'] != 2 && $_SESSION['rol_id'] != 1) { header("Location: ../Home/index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Aprobación de Vacaciones | LITOSOFT</title>
    <?php require_once("../headermodulos/headermodulos.php");?>
    <link rel="stylesheet" href="../../public/css/main.css">
    </head>
<body class="with-side-menu">
    
    <?php require_once("../headercapitalhumano/headercapitalhumano.php");?> 
    <?php require_once("../navcapitalhumano/nav.php");?> 

    <div class="page-content">
        <div class="container-fluid">
            <header class="section-header">
                <h2>Gestión de Solicitudes de Vacaciones Pendientes</h2>
            </header>

            <section class="box-typical">
                <div class="box-typical-body">
                    <table id="aprobacion_data" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                         <thead>
                            <tr>
                                <th>ID Sol.</th>
                                <th>Empleado</th>
                                <th>Puesto / Área</th>
                                <th>F. Inicio</th>
                                <th>F. Fin</th>
                                <th>Días Hábiles</th>
                                <th>F. Solicitud</th>
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
    <script type="text/javascript" src="mntaprobacion.js"></script>
</body>
</html>