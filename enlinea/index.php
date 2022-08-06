<?php
include_once '../includes/constants.php';
include_once '../includes/file.php';

propietario::esPropietarioLogueado();
$archivo = '../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
$fecha_actualizacion = JFile::read($archivo);

$session = $_SESSION;
// <editor-fold defaultstate="collapsed" desc="cantidad recibos pendientes">
$f = new factura();
$p = new pago();

$cuenta = $f->numeroRecibosPendientesPropitario($session['usuario']['cedula']);
$recibos = 0;
$cancela = 0;
if ($cuenta['suceed']) {
    if (count($cuenta['data']) > 0) {
        $recibos = $cuenta['data'][0]['cantidad'];
    }
}
$pagos = $p->numeroRecibosCanceladosPorPropitario($session['usuario']['cedula']);
if ($pagos['suceed']) {
    if (count($pagos['data']) > 0) {
        $cancela = $pagos['data'][0]['cantidad'];
    }
}
// </editor-fold>

$mensajes = new mensajes();
$m = $mensajes->cantidadMensajesSinLeerPorPropietario($session['usuario']['cedula'],$session['id_sesion']);

$sesiones = propietario::obtenerInfoUltimasSesiones($session['usuario']['cedula'],$session['id_sesion']);
$historico = null;

if ($sesiones['suceed']) {
    $historico = $sesiones['data'];
}

$cartelera = new cartelera();
$propiedad = new propiedades();
$cartelera_general = $cartelera->listar();

$propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);

if ($propiedades['suceed'] == true) {
    $cartelera_inmueble = Array();
    $cartelera->tabla="cartelera_inmueble";
    foreach ($propiedades['data'] as $propiedad) {
        $resultado = $cartelera->listarCarteleraInmueble($propiedad['id_inmueble']);
        array_push($cartelera_inmueble, $resultado['data']);
    }
}

echo $twig->render('enlinea/index.html.twig', array(
    "session" => $session,
    "fecha_actualizacion" => $fecha_actualizacion,
    "recibos"=> $recibos,
    "cancelacion"=>$cancela,
    "historico" => $historico,
    "mensajes" => $m,
    "cartelera_general" => $cartelera_general['data'],
    "cartelera_inmueble"=> $cartelera_inmueble
    ));