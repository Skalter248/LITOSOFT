<?php
    // Incluye el archivo de configuración global y la conexión
    require_once("config/index.php"); 
    // Iniciar la sesión es CRUCIAL
    session_start();

    // Si ya existe una sesión, redirige al dashboard.
    if (isset($_SESSION['usu_id'])) {
        header("Location: view/home/"); 
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Inicio de Sesión | LITOSOFT</title>

    <link href="img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
    <link rel="stylesheet" href="public/css/separate/pages/login.min.css">
    <link rel="stylesheet" href="public/css/lib/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="public/css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/main.css">
</head>
<body>
    <div class="page-center">
        <div class="page-center-in">
            <div class="container-fluid">

                <form class="sign-box" action="controller/UsuarioControlador.php" method="post" id="login_form">

                    <input type="hidden" id="rol_id" name="rol_id" value="1">

                    <div class="sign-avatar">
                        <img src="public/Logo Lito.jpg" alt="Logo de LitoSoft" id="imgtipo" style="width: 120px; height: 120px; border-radius: 0;">
                    </div>

                    <br>

                    <header class="sign-title" id="lbltitulo">Personal Operativo</header>

                    <?php
                        // Manejo de errores de la URL
                        if (isset($_GET["m"])) {
                            switch ($_GET["m"]) {
                                case "1":
                                    ?>
                                    <div class="alert alert-warning alert-icon alert-close alert-dismissible fade in" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <i class="font-icon font-icon-warning"></i>
                                        El Usuario y/o Contraseña son incorrectos.
                                    </div>
                                    <?php
                                    break;

                                case "2":
                                    ?>
                                    <div class="alert alert-warning alert-icon alert-close alert-dismissible fade in" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <i class="font-icon font-icon-warning"></i>
                                        Verifica la información (Usuario y Contraseña requeridos).
                                    </div>
                                    <?php
                                    break;
                            }
                        }
                    ?>

                    <div class="form-group">
                        <input type="text" id="usu_usuario_inicio" name="usu_usuario_inicio" class="form-control" placeholder="Usuario" required/>
                    </div>
                    <div class="form-group">
                        <input type="password" id="usu_contraseña_inicio" name="usu_contraseña_inicio" class="form-control" placeholder="Contraseña" required/>
                    </div>
                    
                    <div class="form-group">
                        <div class="float-left reset">
                            <a href="view/accesadministrativo/">Administrativos</a>
                        </div>
                    </div>

                    <style>
                        .btn-custom {
                            background-color: #0a3678;
                            color: white;
                            border: none;
                        }
                        .btn-custom:hover {
                            background-color: #0a3678;
                        }
                    </style>

                    <input type="hidden" name="enviar" class="form-control" value="si">
                    <button type="submit" class="btn btn-rounded btn-custom">Acceder</button>
                </form>
            </div>
        </div>
    </div>

<script src="public/js/lib/jquery/jquery.min.js"></script>
<script src="public/js/lib/tether/tether.min.js"></script>
<script src="public/js/lib/bootstrap/bootstrap.min.js"></script>
<script src="public/js/plugins.js"></script>
<script type="text/javascript" src="public/js/lib/match-height/jquery.matchHeight.min.js"></script>
<script>
    $(function() {
        $('.page-center').matchHeight({
            target: $('html')
        });

        $(window).resize(function(){
            setTimeout(function(){
                $('.page-center').matchHeight({ remove: true });
                $('.page-center').matchHeight({
                    target: $('html')
                });
            },100);
        });
    });
</script>
<script src="public/js/app.js"></script>
<script type="text/javascript" src="datos.js"></script>

</body>
</html>