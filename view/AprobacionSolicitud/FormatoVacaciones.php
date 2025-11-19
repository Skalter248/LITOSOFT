<?php
// ARCHIVO: view/AprobacionSolicitud/FormatoVacaciones.php
session_start();
if (!isset($_SESSION['datos_impresion_vacaciones'])) {
    die("Acceso denegado o datos de solicitud no encontrados.");
}

$data = $_SESSION['datos_impresion_vacaciones'];


// Cálculo del saldo
$saldo_antes = floatval($data['saldo_actual_antes']);
$dias_solicitados = floatval($data['vac_dias_habiles']);
$saldo_despues = $saldo_antes - $dias_solicitados; 

$es_adelanto = strpos($data['vac_observaciones'], '[SOLICITUD DE ADELANTO]') !== false;
$justificacion = $data['vac_observaciones'];
if($es_adelanto) {
    $justificacion = str_replace('[SOLICITUD DE ADELANTO]', '', $justificacion);
    $justificacion = trim($justificacion);
}

// Helper para formato de fecha
function format_date($date) {
    return date("d/m/Y", strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato SGC001 - Solicitud de Vacaciones</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; margin: 0; padding: 20px; color: #333; }
        .page { width: 210mm; /* A4 vertical */ min-height: 297mm; margin: 0 auto; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { padding: 5px; vertical-align: middle; }
        .logo { width: 120px; height: auto; display: block; margin: 0 auto; }
        .title { text-align: center; font-size: 14pt; font-weight: 700; color: #0056b3; text-transform: uppercase; }
        .metadata { font-size: 8pt; text-align: right; line-height: 1.4; border: 1px solid #ccc; padding: 5px; }
        
        .section-title { font-size: 11pt; font-weight: bold; background-color: #f0f8ff; padding: 8px 10px; border-left: 5px solid #0056b3; margin-top: 20px; margin-bottom: 5px; }
        
        .data-table, .balance-table, .adelanto-table, .signature-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table td, .balance-table td, .adelanto-table td, .signature-table td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .data-table th, .balance-table th { background-color: #f7f7f7; font-weight: bold; text-align: left; }
        
        .label { font-weight: bold; width: 20%; background-color: #fafafa; } /* Reducimos el label */
        .value { width: 30%; } /* Aumentamos el valor para compensar */
        
        .status-badge { 
            display: inline-block; 
            padding: 4px 8px; 
            background-color: #28a745; /* Green */
            color: white; 
            border-radius: 3px; 
            font-weight: 600; 
            font-size: 9pt;
        }
        
        /* Ajuste de estilo para manejar la imagen de firma */
        .signature-table td { height: 100px; border: 1px solid #ddd; text-align: center; font-size: 9pt; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #000; display: block; width: 80%; margin: 5px auto 0; }
        .small-text { font-size: 8pt; color: #666; }
    </style>
</head>
<body onload="window.print()">
    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 20%; text-align: center;">
                    <img src="../../public/Logo Lito.jpg" alt="Logo Empresa" class="logo">
                </td>
                <td style="width: 55%;" class="title">
                    SOLICITUD Y AUTORIZACIÓN DE VACACIONES
                </td>
                <td style="width: 25%;">
                    <div class="metadata">
                        **Código:** SGC001<br>
                        **Revisión:** 002<br>
                        **Fecha Emisión:** <?php echo format_date($data['vac_fecha_solicitud']); ?>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-title">Información del Solicitante y Aprobador</div>
        <table class="data-table">
            <tr>
                <th colspan="2" style="width: 50%;">Solicitante</th> 
                <th colspan="2" style="width: 50%;">Aprobador</th>
            </tr>
            <tr>
                <td class="label">Nombre:</td>
                <td class="value"><?php echo htmlspecialchars($data['nombre_solicitante']); ?></td>
                <td class="label">Nombre:</td>
                <td class="value"><?php echo htmlspecialchars($data['nombre_aprobador'] ?? 'Pendiente'); ?></td>
            </tr>
            <tr>
                <td class="label">Puesto:</td>
                <td class="value"><?php echo htmlspecialchars($data['solicitante_puesto_nombre'] ?? 'ID No Encontrado'); ?></td>
                <td class="label">Puesto:</td>
                <td class="value"><?php echo htmlspecialchars($data['aprobador_puesto_nombre'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="label">Área/Depto:</td>
                <td class="value"><?php 
                    $area_s = htmlspecialchars($data['solicitante_area_nombre'] ?? 'ID No Encontrado');
                    $depto_s = htmlspecialchars($data['solicitante_depto_nombre'] ?? 'ID No Encontrado');
                    echo $area_s . ' / ' . $depto_s; 
                ?></td>
                <td class="label">Área/Depto:</td>
                <td class="value"><?php 
                    $area_a = htmlspecialchars($data['aprobador_area_nombre'] ?? 'N/A');
                    $depto_a = htmlspecialchars($data['aprobador_depto_nombre'] ?? 'N/A');
                    echo $area_a . ' / ' . $depto_a; 
                ?></td>
            </tr>
        </table>
        
        <div class="section-title">Detalle y Fechas de la Solicitud</div>
        <table class="data-table">
            <tr>
                <td class="label">Fecha de Inicio:</td>
                <td class="value"><?php echo format_date($data['vac_fecha_inicio']); ?></td>
                <td class="label">Fecha de Fin:</td>
                <td class="value"><?php echo format_date($data['vac_fecha_fin']); ?></td>
            </tr>
            <tr>
                <td class="label">Días Hábiles Solicitados:</td>
                <td class="value" style="font-size: 12pt; font-weight: bold;"><?php echo $dias_solicitados; ?></td>
                <td class="label">Estado Actual:</td>
                <td class="value"><span class="status-badge">APROBADA</span></td>
            </tr>
            <tr>
                <td class="label">Observaciones:</td>
                <td class="value" colspan="3"><?php echo htmlspecialchars($data['vac_observaciones']); ?></td>
            </tr>
        </table>
        
        <div class="section-title">Resumen de Días y Saldos</div>
        <table class="balance-table">
            <tr>
                <th>Días Disponibles Antes de la Aprobación</th>
                <th>Días Generados LFT (Acumulados)</th>
                <th>Días Solicitados</th>
                <th>**Saldo Restante Después de la Aprobación**</th>
            </tr>
            <tr>
                <td><?php echo number_format($saldo_antes + $dias_solicitados, 2); ?></td>
                <td><?php echo number_format(floatval($data['usu_dias_generados_lft']), 2); ?></td>
                <td><?php echo number_format($dias_solicitados, 2); ?></td>
                <td style="font-weight: bold; font-size: 12pt; color: <?php echo ($saldo_despues < 0) ? '#dc3545' : '#198754'; ?>">
                    <?php echo number_format($saldo_despues, 2); ?>
                </td>
            </tr>
        </table>

        <?php if ($es_adelanto): ?>
        <div class="adelanto-table" style="border: 2px solid #dc3545; background-color: #fff0f3; margin-top: 20px;">
            <div style="padding: 10px; font-weight: bold; color: #dc3545; font-size: 11pt;">
                🚨 NOTA: ESTA SOLICITUD IMPLICA UN ADELANTO DE DÍAS DE VACACIONES.
            </div>
            <div style="padding: 0 10px 10px;">
                <span style="font-weight: bold;">Justificación del Adelanto:</span> 
                <?php echo htmlspecialchars($justificacion); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section-title" style="margin-top: 40px;">Autorización y Conformidad</div>
        <table class="signature-table">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <?php
                    // La ruta en la DB debe ser (ej: public/upload/firmas/user_1.png)
                    $ruta_firma_solicitante = $data['solicitante_firma_ruta'] ?? null;
                    $img_tag_solicitante = '';
                    $line_margin_solicitante = 'margin-top: 5px;';
                    
                    if (!empty($ruta_firma_solicitante)) {
                        // *** 🔑 LÍNEA CRÍTICA: Ajuste la ruta relativa al directorio raíz. ***
                        $ruta_img_solicitante = '../../' . htmlspecialchars($ruta_firma_solicitante);
                        
                        $img_tag_solicitante = '<img src="' . $ruta_img_solicitante . '" 
                                alt="Firma Solicitante" 
                                style="max-width: 150px; max-height: 50px; margin: 0 auto; display: block;">';
                        $line_margin_solicitante = 'margin-top: 5px;';
                    } else {
                        $line_margin_solicitante = 'margin-top: 60px;';
                    }
                    
                    echo '<div style="height: 60px; text-align: center;">' . $img_tag_solicitante . '</div>';
                    echo '<div class="signature-line" style="' . $line_margin_solicitante . '"></div>';
                    ?>
                    <span style="font-weight: bold;"><?php echo htmlspecialchars($data['nombre_solicitante']); ?></span><br>
                    <span class="small-text">Firma y Nombre del Solicitante</span>
                </td>
                
                <td style="width: 50%; vertical-align: bottom;">
                    <?php
                    $ruta_firma_aprobador = $data['aprobador_firma_ruta'] ?? null;
                    
                    // Convertir el estado a mayúsculas para asegurar que la comparación sea correcta
                    $estado_solicitud_upper = strtoupper($data['vac_estado'] ?? 'N/A');

                    // La condición es: ¿El estado es APROBADA? Y ¿La ruta de la firma existe?
                    $mostrar_aprobador_firma = ($estado_solicitud_upper === 'APROBADA' && !empty($ruta_firma_aprobador));
                    
                    $img_tag_aprobador = '';
                    $line_margin_aprobador = 'margin-top: 5px;';

                    if ($mostrar_aprobador_firma) {
                        // La ruta ya la confirmamos: ../../ para subir dos niveles (view/AprobacionSolicitud/ -> RAIZ)
                        $ruta_img_aprobador = '../../' . htmlspecialchars($ruta_firma_aprobador);
                        
                        $img_tag_aprobador = '<img src="' . $ruta_img_aprobador . '" 
                                alt="Firma Aprobador" 
                                style="max-width: 150px; max-height: 50px; margin: 0 auto; display: block;">';
                        $line_margin_aprobador = 'margin-top: 5px;';
                    } else {
                        $line_margin_aprobador = 'margin-top: 60px;';
                    }

                    echo '<div style="height: 60px; text-align: center;">' . $img_tag_aprobador . '</div>';
                    echo '<div class="signature-line" style="' . $line_margin_aprobador . '"></div>';
                    ?>
                    <span style="font-weight: bold;"><?php echo htmlspecialchars($data['nombre_aprobador'] ?? 'N/A'); ?></span><br>
                    <span class="small-text">Firma y Nombre del Aprobador</span>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 30px; font-size: 8pt; color: #999;">
            Documento generado electrónicamente el <?php echo date("d/m/Y H:i:s"); ?>. Para fines de control interno.
        </div>
    </div>
</body>
</html>