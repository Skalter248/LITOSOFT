<?php
    // ARCHIVO: LITOSOFT/view/home/index.php
    
    // 1. Incluir la configuración y la sesión
    require_once("../../config/index.php"); 
    session_start();

    // 2. Control de Acceso (Guardrail de Seguridad)
    if (!isset($_SESSION['usu_id'])) {
        header("Location: ../../index.php"); 
        exit();
    }
    
    
    // NOTA: Si necesitas variables como $nombre_usuario, debes definirlas aquí
    // $nombre_usuario = $_SESSION['usu_nombre'] . ' ' . $_SESSION['usu_apellido_paterno'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>LITOSOFT</title>
    
    <?php require_once("../headermodulos/headermodulos.php");?>
    
    <link rel="stylesheet" href="../../public/css/main.css"> 
    <link rel="stylesheet" href="../../public/css/home.css"> 
</head>
<body>  
    <div class="page-content">
        <div class="container-fluid">     
                <header class="section-header text-center" style="margin-top: 50px;">
                <div class="tbl">
                    <div class="tbl-row">
                        <div class="tbl-cell">
                            <h2>Bienvenido a LITOSOFT</h2>
                            <div class="subtitle">Seleccione el módulo al que desea acceder.</div>
                        </div>
                    </div>
                </div>
            </header>
            <section class="module-access-grid">
                <div class="row justify-content-center">   
                    <div class="col-xl-3 col-md-6 module-item-col">
                        <a href="../Produccion/" class="module-card module-blue">
                            <i class="font-icon font-icon-cogwheel"></i> 
                            <span class="module-title">Producción</span>
                            <span class="module-desc">Programación de turnos y visualización de reportes de producción.</span>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 module-item-col">
                        <a href="../CapitalHumano/" class="module-card module-green">
                            <i class="font-icon font-icon-users"></i>
                            <span class="module-title">Capital Humano</span>
                            <span class="module-desc">Gestión de vacaciones, permisos, faltas y personal.</span>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 module-item-col">
                        <a href="../Pre-prensa/" class="module-card module-red">
                            <i class="font-icon font-icon-picture"></i>
                            <span class="module-title">Pre-prensa</span>
                            <span class="module-desc">Visualización de fichas técnicas y archivos de diseño.</span>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 module-item-col">
                        <a href="../SGC/" class="module-card module-purple">
                            <i class="font-icon font-icon-notebook"></i>
                            <span class="module-title">SGC</span>
                            <span class="module-desc">Visualización de procedimientos y descarga de formatos controlados.</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <?php require_once("../MainJs/js.php");?>
</body>

<script src="../../public/js/lib/jquery/jquery.min.js"></script>
<script src="../../public/js/lib/tether/tether.min.js"></script>
<script src="../../public/js/lib/bootstrap/bootstrap.min.js"></script>
<script src="../../public/js/plugins.js"></script>
<script type="text/javascript" src="../../public/js/lib/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../public/js/lib/lobipanel/lobipanel.min.js"></script>
<script type="text/javascript" src="../../public/js/lib/match-height/jquery.matchHeight.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="../../public/js/app.js"></script>

</html>