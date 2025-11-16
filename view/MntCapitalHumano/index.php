<?php
    // ARCHIVO: view/MntCapitalHumano/index.php
    
    require_once("../../config/index.php"); 
    session_start();
    if (!isset($_SESSION['usu_id'])) { header("Location: ../../index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Gestión de Organización</title>
    <?php require_once("../headermodulos/headermodulos.php");?>
    <link rel="stylesheet" href="../../public/css/main.css">
    </head>
<body class="with-side-menu">
    
    <?php require_once("../mainhead/head.php");?> 
    <?php require_once("../headercapitalhumano/headercapitalhumano.php");?> 
    <?php require_once("../navcapitalhumano/nav.php");?> 

    <div class="page-content">
        <div class="container-fluid">
            <header class="section-header">
                <h2>Gestión de Organización</h2>
            </header>

            <section class="tabs-section">
                <div class="tabs-section-nav tabs-section-nav-inline">
                    <ul class="nav" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#tab-departamentos" role="tab" data-toggle="tab">Departamentos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab-areas" role="tab" data-toggle="tab">Áreas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab-puestos" role="tab" data-toggle="tab">Puestos</a>
                        </li>
                    </ul>
                </div><div class="tab-content">
                    
                    <div role="tabpanel" class="tab-pane fade in active" id="tab-departamentos">
                        <button type="button" id="btnNuevoDepartamento" class="btn btn-primary">Nuevo Departamento</button>
                        <table id="departamentos_data" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="tab-areas">
                        <button type="button" id="btnNuevaArea" class="btn btn-primary">Nueva Área</button>
                        <table id="areas_data" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                             <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th>Nombre Área</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="tab-puestos">
                        <button type="button" id="btnNuevoPuesto" class="btn btn-primary">Nuevo Puesto</button>
                        <table id="puestos_data" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                             <thead>
                                <tr>
                                    <th>Área</th>
                                    <th>Nombre Puesto</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div></section></div></div><?php require_once("modalmantenimiento.php");?>

    <?php require_once("../MainJs/js.php");?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="mntcapitalhumano.js"></script>
</body>
</html>