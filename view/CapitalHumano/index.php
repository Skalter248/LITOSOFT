<?php
    
    // 1. Carga configuraciones (incluye config/conexion.php)
require_once("../../config/index.php"); 
// Nota: Si index.php ya incluye conexion.php, solo necesitas esta línea.

session_start();
if (!isset($_SESSION['usu_id'])) { 
    header("Location: ../../index.php"); 
    exit(); 
}

// 2. Incluir el MODELO (Vacaciones), el cual extiende la clase Conexion
require_once('../../models/Vacaciones.php'); 

// 3. Iniciar la lógica de Saldo LFT
// NO es necesario que Vacaciones extienda Conexion. La clase Vacaciones
// debería existir por sí misma e INSTANCIAR la conexión internamente.
// Pero si insistes en la herencia:
$vacaciones = new Vacaciones(); // ✅ Esta es la única nueva instancia que debe haber.
$vacaciones->ejecutar_logica_actualizacion_saldos();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Capital Humano</title>
    <?php require_once("../headermodulos/headermodulos.php");?>
    <link rel="stylesheet" href="../../public/css/main.css">
    </head>
<body class="with-side-menu">
    
    <?php require_once("../headercapitalhumano/headercapitalhumano.php");?> 
    <?php require_once("../navcapitalhumano/nav.php");?> 

    <div class="page-content">
        <div class="container-fluid">
            <header class="section-header">
                <h2>Dashboard Capital Humano</h2>
            </header>
        </div>    
    </div>

    <?php require_once("../mainjs/js.php");?>
    <script type="text/javascript" src="mntcapitalhumano.js"></script>
</body>
</html>