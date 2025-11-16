<?php
    // ARCHIVO: view/Vacaciones/index.php
    
    require_once("../../config/index.php"); 
    session_start();
    // Requerir el modelo de Vacaciones para el título
    require_once("../../models/Vacaciones.php"); 
    
    if (!isset($_SESSION['usu_id'])) { 
        header("Location: ../../index.php"); 
        exit(); 
    }
    
    $rol_id = $_SESSION['rol_id']; // Usaremos esto para definir la vista de Administrador/Jefe/Empleado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Solicitud de Vacaciones | LITOSOFT</title>
    <?php require_once("../headermodulos/headermodulos.php");?>
    <link rel="stylesheet" href="../../public/css/main.css">
</head>
<body class="with-side-menu">
    
    <?php require_once("../headercapitalhumano/headercapitalhumano.php");?> 
    <?php require_once("../navcapitalhumano/nav.php");?> 

    <div class="page-content">
        <div class="container-fluid">
            <header class="section-header">
                <h2>Gestión de Vacaciones</h2>
                <?php if ($rol_id == 1 || $rol_id == 2) : // 1: Admin, 2: Jefe (asumiendo roles) ?>
                    <p class="text-info">Estás viendo la vista de administrador/jefe. Puedes aprobar solicitudes y ver el resumen.</p>
                <?php endif; ?>
            </header>

            <section class="card">
                <div class="card-header">
                    <h3 class="card-title">📅 Resumen de Días de Vacaciones</h3>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="color-card bg-primary text-white p-3 mb-3 text-center">
                                <p class="m-0">Fecha de Ingreso</p>
                                <h4 class="m-0" id="fecha_ingreso_planta_info">Calculando...</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="color-card bg-info text-white p-3 mb-3 text-center">
                                <p class="m-0">Antigüedad (Años)</p>
                                <h4 class="m-0" id="antiguedad_anos">0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="color-card bg-success text-white p-3 mb-3 text-center">
                                <p class="m-0">Días Generados (LFT)</p>
                                <h4 class="m-0" id="dias_generados">0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="color-card bg-warning text-white p-3 mb-3 text-center">
                                <p class="m-0">Días Disponibles</p>
                                <h4 class="m-0" id="dias_disponibles">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <section class="card">
                <div class="card-header">
                    <h3 class="card-title">📝 Historial de Solicitudes</h3>
                </div>
                <div class="card-block">
                    <button type="button" id="btnNuevaSolicitud" class="btn btn-success mb-3">
                        <i class="fa fa-calendar-plus-o"></i> Nueva Solicitud
                    </button>
                    
                    <table id="vacaciones_data" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                         <thead>
                            <tr>
                                <th>ID Solicitud</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Días Hábiles</th>
                                <th>Estado</th>
                                <th>Fecha Solicitud</th>
                                <th>Aprobador</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>

    <?php 
        // 2. Incluir el modal de nueva solicitud (Se crea en el siguiente paso)
        require_once("modalvacaciones.php");
    ?>

    <?php require_once("../MainJs/js.php");?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="mntvacaciones.js"></script>
</body>
</html>