<?php
    // ARCHIVO: LITOSOFT/view/home/index.php (Inicio)
    
    require_once("../../config/index.php"); 

    if (!isset($_SESSION['usu_id'])) {
        header("Location: ../../index.php"); 
        exit();
    }
    
    // Almacena el nombre completo (Nombre, Apellido Paterno, Apellido Materno)
    // NOTA: En el HTML estás usando $_SESSION["usu_ape"], asegurémonos que esta variable exista.
    $nombre_completo = $_SESSION['usu_nombre'] . ' ' . $_SESSION['usu_apellido_paterno'] . ' ' . $_SESSION['usu_apellido_materno'];
    
    // Lógica para la foto de perfil (RUTA DINÁMICA):
    $foto_perfil = (isset($_SESSION['usu_foto']) && !empty($_SESSION['usu_foto'])) 
                    ? '../../public/upload/fotos/' . $_SESSION['usu_foto'] 
                    : '../../public/img/avatar-2-64.png';
    
    // NOTA: Si usas la ruta absoluta '/Litosite/...' es porque estás en el entorno raíz.
    // Usaremos la ruta relativa segura ('../../...') que definiste, a menos que se requiera la ruta absoluta:
    // $foto_perfil_absoluta = '/litosoft/public/upload/fotos/' . (isset($_SESSION['usu_foto']) ? $_SESSION['usu_foto'] : 'avatar-2-64.png');
?>

<link href="../../public/img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
<link href="../../public/img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
<link href="../../public/img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
<link href="../../public/img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
<link href="../../public/img/favicon.png" rel="icon" type="image/png">
<link href="../../public/img/favicon.ico" rel="shortcut icon">

<link rel="stylesheet" href="../../public/css/lib/lobipanel/lobipanel.min.css">
<link rel="stylesheet" href="../../public/css/separate/vendor/lobipanel.min.css">
<link rel="stylesheet" href="../../public/css/lib/jqueryui/jquery-ui.min.css">
<link rel="stylesheet" href="../../public/css/separate/pages/widgets.min.css">
<link rel="stylesheet" href="../../public/css/lib/font-awesome/font-awesome.min.css">
<link rel="stylesheet" href="../../public/css/lib/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="../../public/css/main.css">


<header class="site-header">
    <div class="container-fluid">
        <div class="site-header-content">
				<div class="dropdown dropdown-typical">
                    <a href="../MntPerfil/" class="dropdown-toggle no-arr">
                        <span class="font-icon font-icon-user"></span>
                        <span class="lblcontactonomx" href="#"><?php echo htmlspecialchars($_SESSION["usu_nombre"]) ?> <?php echo htmlspecialchars($_SESSION["usu_apellido_paterno"]) ?> <?php echo htmlspecialchars($_SESSION["usu_apellido_materno"]) ?> </span> 
                    </a>
                </div>
            <div class="site-header-content-in">
                <div class="site-header-shown">
                    <div class="dropdown user-menu">
                        <button class="dropdown-toggle" id="dd-user-menu" type="button" 
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
                                style="border: none; background: transparent; padding: 0;"> <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de usuario">     
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd-user-menu">
                            <a class="dropdown-item" href="../../logout.php"><span class="font-icon glyphicon glyphicon-log-out"></span>Cerrar Sesion</a>
                        </div>
                    </div>
                </div><div class="mobile-menu-right-overlay"></div> 
                <input type="hidden" id="user_idx" value="<?php echo $_SESSION["usu_id"] ?>">
                <input type="hidden" id="rol_idx" value="<?php echo $_SESSION["rol_id"] ?>">  
            </div>
		</div>
	</div>
</header>

<script src="../../public/js/lib/jquery/jquery.min.js"></script>
<script src="../../public/js/lib/tether/tether.min.js"></script>
<script src="../../public/js/lib/bootstrap/bootstrap.min.js"></script>
<script src="../../public/js/plugins.js"></script>
<script type="text/javascript" src="../../public/js/lib/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../public/js/lib/lobipanel/lobipanel.min.js"></script>
<script type="text/javascript" src="../../public/js/lib/match-height/jquery.matchHeight.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="../../public/js/app.js"></script>