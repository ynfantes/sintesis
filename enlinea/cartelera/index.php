<?php
include_once '../../includes/constants.php';
include_once '../../includes/propietario.php';
include_once '../../includes/file.php';

propietario::esPropietarioLogueado();


$archivo = '../../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
$fecha_actualizacion = JFile::read($archivo);

$session = $_SESSION;
switch ($accion) {
    default :
        $bitacora = new bitacora();
        
        $bitacora->insertar(Array(
            "id_sesion"=>$session['id_sesion'],
            "id_accion"=>11,
            "descripcion"=>$propiedad['id_inmueble']." - ".$propiedad['apto'],
        ));
        
        echo $twig->render('enlinea/cartelera.html.twig', array(
            "session" => $session,
            "fecha_actualizacion" => $fecha_actualizacion
            ));
        break;
}