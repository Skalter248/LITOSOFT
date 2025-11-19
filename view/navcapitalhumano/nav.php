<!--
BackEnd desarrollado por [Ing. Ismael Martinez Velasco]
Plantilla Frontend StarUi.com 
© [2024] [Ismael Martinez Velasco]. Todos los derechos reservados.
-->

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Solo mostrar este menú si el usuario tiene un rol asignado
if (isset($_SESSION["rol_id"])) {

    // Si el rol es 1 (Administrador)
    if ($_SESSION["rol_id"] == 2) {
        ?>
            <nav class="side-menu">
                <ul class="side-menu-list">
                    <li class="blue-dirty">
                        <a href="..\CapitalHumano\">
                            <span class="glyphicon glyphicon-home"></span>
                            <span class="lbl">Inicio</span>
                        </a>
                    </li>
                    <li class="blue-dirty">
                        <a href="..\home\">
                            <span class="glyphicon glyphicon-th-large"></span>
                            <span class="lbl">Gestor de Módulos</span>
                        </a>
                    </li>
                    <li class="blue-dirty">
                        <a href="..\MntCapitalHumano\">
                            <span class="glyphicon glyphicon-object-align-right"></span>
                            <span class="lbl">Gestión de Organización</span>
                        </a>
                    </li>
                    <li class="blue-dirty">
                        <a href="..\SolicitudVacaciones\">
                            <span class="glyphicon glyphicon-send"></span>
                            <span class="lbl">Solicitud de Vacaciones</span>
                        </a>
                    </li>
                    <li class="blue-dirty">
                        <a href="..\AprobacionSolicitud\">
                            <span class="glyphicon glyphicon-ok"></span>
                            <span class="lbl">Aprobacion de Vacaciones</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php
    } else {
        // Si el rol NO es administrador, solo mostrar Inicio
        ?>
        <nav class="side-menu">
            <ul class="side-menu-list">
                <li class="blue-dirty">
                    <a href="..\CapitalHumano\">
                        <span class="glyphicon glyphicon-home"></span>
                        <span class="lbl">Inicio</span>
                    </a>
                </li>
                <li class="blue-dirty">
                    <a href="..\home\">
                        <span class="glyphicon glyphicon-th-large"></span>
                        <span class="lbl">Gestor de Módulos</span>
                    </a>
                </li>
                <li class="blue-dirty">
                    <a href="..\SolicitudVacaciones\">
                        <span class="glyphicon glyphicon-object-align-right"></span>
                        <span class="lbl">Solicitud de Vacaciones</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php
    }
}
?>

<style>
    .side-menu .glyphicon {
        color: #0a3678 !important;
    }
</style>
