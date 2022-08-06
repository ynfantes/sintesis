<?php
//if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
    ob_start();            
}
elseif (strpos(' ' . $_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') == false) {
    if (strpos(' ' . $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') == false) {
        ob_start();
    }
    elseif(!ob_start("ob_gzhandler")) {
        ob_start();
    }   
}
elseif(!ob_start("ob_gzhandler")) {
    ob_start();
}
include_once 'includes/constants.php';
$result = array();
$mantenimiento = false;

$avance = 0;
$propietarios = new propietario();
$propiedades = new propiedades();
$facturas = new factura();
$result = $propietarios->listar();

if (!$result['suceed'] || empty($result['data'])) {
    $mantenimiento=TRUE;
} else {
    $result = $propiedades->listar();
    if (!$result['suceed'] || empty($result['data'])) {
        $mantenimiento=TRUE;

    } else {
        $result = $facturas->listar();
        if (!$result['suceed'] || empty($result['data'])) {
            $mantenimiento=TRUE;
        }
    }    
}
//|| MOROSO
if ($mantenimiento) {
    //$mantenimiento = TRUE;
    $mail = new mailto(SMTP);
    $min = date("i");
    $avance = $min * 100 / 60;
    if ($_SERVER['SERVER_NAME'] == "www.sintesisonline.com.ve" | $_SERVER['SERVER_NAME'] == "sintesisonline.com.ve") {
        $mail->enviar_email(TITULO.' [Mantenimiento]','Sincronice la página web','','ynfantes@gmail.com','Valoriza2');
    }
}
echo $twig->render('index.html.twig',Array(
                "mantenimiento" => $mantenimiento,
                "avance"        => $avance
                ));