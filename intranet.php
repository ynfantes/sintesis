<?php

include_once 'includes/constants.php';
$usuarios = new usuario();
$result = array();

if (isset($_POST['submit'])) {
    $result = $usuarios->login($_POST['usuario'], $_POST['password'], 0);
} else {
    if (isset($_POST['cedula'])) {
        //$result = $usuarios->recuperarContraSena($_POST['cedula']);
    }
}
echo $twig->render('intranet.html.twig', array("mensaje" => $result));
