<?php
include_once 'includes/constants.php';

//echo $twig->render('mantenimiento.html.twig');
//die();

$result = array();
$password = '';
$apto = '';
if (isset($_POST['apto']) && isset($_POST['password'])) {
$propietario = new propietario();

    $apto = $_POST['apto'];
    $password = $_POST['password'];
    $result = $propietario->login($apto,$password, 0);
        echo json_encode($result);

}
