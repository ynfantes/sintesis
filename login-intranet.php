<?php 
include_once 'includes/constants.php';

//echo $twig->render('mantenimiento.html.twig');
//die();

$result = array();
if (isset($_POST['nombre']) && isset($_POST['clave'])) {
$usuario = new usuario();
    $result = $usuario->login($_POST['nombre'],$_POST['clave']);
    echo json_encode($result);
}