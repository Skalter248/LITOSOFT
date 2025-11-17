<?php 
// Esta vista es incluida desde el controlador, tiene acceso a la variable $datos
?>

<!DOCTYPE html>
<html>
<head>
    <title>Solicitud de Vacaciones #<?php echo $datos['vac_id']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; font-size: 10pt; }
        .container { width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header img { height: 60px; object-fit: contain; }
        .header-info { text-align: right; font-size: 8pt; }
        .header-info p { margin: 2px 0; }
        .section-title { font-size: 12pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .status-box { background-color: #f0f0f0; padding: 10px; text-align: center; font-weight: bold; }
        .signature-area { display: flex; justify-content: space-around; margin-top: 50px; }
        .signature-area div { width: 45%; border-top: 1px solid #000; padding-top: 5px; text-align: center; }
        .text-center { text-align: center; }

        @media print {
            .container { border: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="../../public/Logo Lito.jpg" alt="Logo Lito">
        <div class="header-info">
            <p>CÓDIGO DE DOCUMENTO: **SGC001**</p>
            <p>NIVEL DE REVISIÓN: **00154**</p>
        </div>
    </div>
    
    <h2 class="text-center">SOLICITUD DE VACACIONES</h2>

    <table>
        <tr>
            <td colspan="2">Estatus de la Solicitud:</td>
            <td colspan="2" class="status-box">
                <?php 
                    $estado_display = strtoupper(trim($datos['vac_estado']));
                    if ($estado_display === 'A') $estado_display = 'APROBADA';
                    if ($estado_display === 'P') $estado_display = 'PENDIENTE';
                    if ($estado_display === 'R') $estado_display = 'RECHAZADA';
                    echo $estado_display; 
                ?>
            </td>
        </tr>
        <tr>
            <td>Fecha de Solicitud:</td>
            <td><?php echo date("d/m/Y", strtotime($datos['vac_fecha_solicitud'])); ?></td>
            <td>Fecha de Aprobación:</td>
            <td><?php echo $datos['vac_fecha_aprobacion'] ? date("d/m/Y", strtotime($datos['vac_fecha_aprobacion'])) : 'PENDIENTE'; ?></td>
        </tr>
    </table>

    <div class="section-title">Datos del Solicitante</div>
    <table>
        <tr>
            <td>Solicitante:</td>
            <td><?php echo $datos['solicitante_nombre'] . ' ' . $datos['solicitante_apellido']; ?></td>
            
            <td>Fecha de Ingreso a Planta:</td>
            <td>
                <?php 
                echo $datos['fecha_ingreso_planta'] 
                    ? date("d/m/Y", strtotime($datos['fecha_ingreso_planta'])) 
                    : 'N/A'; 
                ?>
            </td>
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
                // Muestra 0 o el valor positivo (ya ajustado en el controlador)
                echo is_numeric($datos['dias_disponibles_antes']) ? $datos['dias_disponibles_antes'] : 'N/A'; 
            ?></td>
        </tr>
        <tr>
            <td>Días Usados (Acumulado):</td>
            <td><?php 
                echo is_numeric($datos['dias_usados_acumulado']) ? $datos['dias_usados_acumulado'] : 'N/A'; 
            ?></td>
        </tr>
        <tr>
            <td>Días Disponibles Después de Solicitud:</td>
            <td><?php 
                // Muestra el valor real, que puede ser negativo (-4)
                echo is_numeric($datos['dias_disponibles_despues']) ? $datos['dias_disponibles_despues'] : 'N/A'; 
            ?></td>
        </tr>
    </table>

    <div class="section-title">Aprobación</div>
    <table>
        <tr>
            <td>Aprobado por:</td>
            <td><?php echo $datos['jefe_nombre'] ? $datos['jefe_nombre'] . ' ' . $datos['jefe_apellido'] : 'Pendiente / No Aplica'; ?></td>
        </tr>
    </table>
    
    <div class="signature-area">
        <div>
            Firma del Solicitante
        </div>
        <div>
            Firma del Aprobador
        </div>
    </div>
    
    <br><br>
    <div class="text-center" style="font-size: 8pt;">
        <p>Documento generado el: <?php echo date("d/m/Y H:i:s"); ?></p>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    };
</script>
</body>
</html>