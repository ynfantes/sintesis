<?php
/**
 * HTML2PDF Librairy - example
 *
 * HTML => PDF convertor
 * distributed under the LGPL License
 *
 * @author      Laurent MINGUET <webmaster@html2pdf.fr>
 *
 * isset($_GET['vuehtml']) is not mandatory
 * it allow to display the result in the HTML format
 */

    // get the HTML
    ob_start();
?>
<?php
    $id= $_GET['id'];
    
    include_once '../../constants.php';
    $pagos = new pago();
    $detalle = $pagos->detalleCancelacionDeGastos($id);
    if (!$detalle['suceed'] && !$detalle['data']>0) {
        die("No se encuentra la información solicitada");
    }
    include(dirname(__FILE__).'/res/cancelacion.gastos.php');
    $content = ob_get_clean();
    
    // convert to PDF
    require_once(dirname(__FILE__).'/../html2pdf.class.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'LETTER', 'fr');
        $html2pdf->pdf->IncludeJS("print(true);");
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->Output('cancelacion.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
