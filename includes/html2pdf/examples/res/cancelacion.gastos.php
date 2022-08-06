<page>
    <bookmark title="Lettre" level="0" ></bookmark>
    <page_footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center;    width: 100%"><br>"LA CANELACION DEL PRESENTE<br>
            MES NO IMPLICA LA CANCELACIÓN DE<br>
            LOS ANTERIORES"</td>
            </tr>
        </table>
    </page_footer>
    <br>
    <table id="encabezado" cellspacing="0" style="width: 90%; text-align: left; font-size: 11pt;" align="center">
        <tr>
            <td style="width:50%; text-align:center; font-size:16px;background: #E7E7E7;padding:5px;" colspan="2"><b>CANCELACION DE GASTOS</b></td>
            <td style="width:50%; text-align:center; font-size:10px;" rowspan="7"> 
                Calle Nueva, Qta Rimar, Planta Baja, Palmar Este.<br>
                Caraballeda, Estado Vargas.<br>
                Teléfonos: (0212).355 8583 - (0212).355 8477 <br> Fax: (0212).355 8605 <br>
                http:\\www.sintesisonline.com.ve<br>
                e-mail: info@sintesisonline.com.ve</td>
        </tr>
        <tr>
            <td style="width:50%;" colspan="2">
                <table cellspacing="0" style="width:100%">
                    <tr>
                        <td style="width:50%; text-align:center;">Nº Control: <?php echo $detalle['data'][0]['numero_factura'] ?></td>
                        <td style="width:50%; text-align:center;">Período: <?php echo date('m-Y',strtotime($detalle['data'][0]['periodo'])) ?></td>
                    </tr>
                </table>
            </td>
            
        </tr>
        <tr>
            <td style="width:14%;">Inmueble:</td>
            <td style="width:36%; "><?php echo $detalle['data'][0]['nombre_inmueble'] ?></td>
            
        </tr>
        <tr>
            <td style="width:14%;">Apartamento:</td>
            <td style="width:36%; "><?php echo $detalle['data'][0]['apto'] ?></td>
            
        </tr>
        <tr>
            <td style="width:14%;">Propietario:</td>
            <td style="width:36%; "><?php echo $detalle['data'][0]['nombre'] ?></td>
            
        </tr>
        <tr>
            <td style="width:14%;">Alícuota:</td>
            <td style="width:36%; "><?php echo $detalle['data'][0]['alicuota'] ?></td>
            
        </tr>
        <tr>
            <td style="width:50%; text-align:center;background: #E7E7E7; padding:5px" colspan="2">Fecha Facturación: <?php echo date('d-m-Y',strtotime($detalle['data'][0]['fecha'])) ?></td>
            
        </tr>
    </table>
    <br>
    <br>
    <table cellspacing="0" style="width: 90%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 12px;"  align="center">
        <tr>
            <th style="width: 12%; padding:8px;">Codigo</th>
            <th style="width: 62%; padding:8px;">Descripción Gasto de Condominio</th>
            <th style="width: 13%; padding:8px;">Monto</th>
            <th style="width: 13%; padding:8px;">Alícuota</th>
        </tr>
    
<?php
$total = 0;
$comun = 0;
$i=0;
foreach ($detalle['data'] as $registro) {
    $total+=$registro['monto'];
    $comun+=$registro['comun'];
    $i+=1;
?>
        <tr style="background: #F7F7F7;">
            <td style="width: 12%; text-align: center; font-size:11px;"><?php echo $registro['codigo_gasto']; ?></td>
            <td style="width: 62%; text-align: left; font-size:11px;"><?php echo $registro['detalle']; ?></td>
            <td style="width: 13%; text-align: right; font-size:11px;"><?php 
            if ($registro['comun']==0) {
                echo "";
            } else {
                echo number_format($registro['comun'], 2, ',', ' '); 
                }?> &nbsp;</td>
            <td style="width: 13%; text-align: right; font-size:11px;"><?php echo number_format($registro['monto'], 2, ',', ' '); ?> &nbsp;</td>
        </tr>
<?php
    }
    while($i < 40 ){ ?>
        <tr style="background: #F7F7F7;">
            <td style="width: 12%;">&nbsp;</td>
            <td style="width: 62%;">&nbsp;</td>
            <td style="width: 13%;">&nbsp;</td>
            <td style="width: 13%;">&nbsp;</td>
        </tr>
<?php    
    $i+=1;
    }
    
?>
    
    
        <tr style="background: #E7E7E7;">
            <th style="width: 74%; text-align: right; padding:4px;" colspan="2">Totales : </th>
            <th style="width: 13%; text-align: right; padding:4px;"><?php echo number_format($comun, 2, ',', ' '); ?> &nbsp;</th>
            <th style="width: 13%; text-align: right; padding:4px;"><?php echo number_format($total, 2, ',', ' '); ?> &nbsp;</th>
        </tr>
    </table>
    <br><br><br><br><br><br><br>
    <table cellspacing="0" style="width:90%;" align="center">
        <tr>
            <td style="width:100%;text-align:right; font-size:11px;">Firma Cajero:______________________________</td>
        </tr>
    </table>
</page>