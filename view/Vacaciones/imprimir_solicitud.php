<?php 
// ARCHIVO: view/Vacaciones/imprimir_solicitud.php
?>

<!DOCTYPE html>
<html>
<head>
    <title>Solicitud de Vacaciones #<?php echo $datos['vac_id']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; font-size: 10pt; }
        .container { width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; }
        /* ... Estilos CSS ... */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .section-title { font-size: 12pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #ccc; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">SOLICITUD DE VACACIONES</h2>

    <div class="section-title">Datos del Solicitante</div>
    <table>
        <tr>
            <td>Solicitante:</td>
            <td><?php echo $datos['usu_nombre'] . ' ' . $datos['usu_apellido_paterno']; ?></td>
            <td>Fecha de Ingreso a Planta:</td>
            <td><?php echo date("d/m/Y", strtotime($datos['fecha_ingreso_planta'])); ?></td>
        </tr>
    </table>

    <div class="section-title">Acumulado LFT y Saldo</div>
    <table>
        <tr>
            <td>Antigüedad (Años):</td>
            <td><?php echo (int)($datos['antiguedad_anos'] ?? 0); ?></td>
            <td>Días Generados LFT (Acumulado):</td>
            <td><?php echo number_format($datos['dias_generados_lft'] ?? 0, 2); ?></td>
        </tr>
    </table>

    <div class="section-title">Periodo Solicitado</div>
    <table>
        <tr>
            <td>Inicio:</td>
            <td><?php echo date("d/m/Y", strtotime($datos['vac_fecha_inicio'])); ?></td>
            <td>Fin:</td>
            <td><?php echo date("d/m/Y", strtotime($datos['vac_fecha_fin'])); ?></td>
        </tr>
        <tr>
            <td>Días Hábiles Solicitados:</td>
            <td><?php echo $datos['vac_dias_habiles']; ?></td>
            <td>Días Naturales Solicitados:</td>
            <td><?php echo $datos['vac_dias_solicitados']; ?></td>
        </tr>
    </table>

    <div class="section-title">Resumen de Días</div>
    <table>
        <tr>
            <td>Días Disponibles Antes de Solicitud:</td>
            <td><?php 
                echo number_format($datos['dias_disponibles_antes'] ?? 0, 2); 
            ?></td>
        </tr>
        <tr>
            <td>Días Usados (Acumulado):</td>
            <td><?php 
                echo number_format($datos['dias_usados_acumulado'] ?? 0, 2); 
            ?></td>
        </tr>
        <tr>
            <td>Días Disponibles Después de Solicitud:</td>
            <td><?php 
                echo number_format($datos['dias_disponibles_despues'] ?? 0, 2); 
            ?></td>
        </tr>
    </table>
    
    <script>
    window.onload = function() {
        window.print();
    };
</script>
</body>
</html>