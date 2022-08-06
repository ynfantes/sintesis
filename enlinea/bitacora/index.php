<?php
include_once '../../includes/constants.php';

$propietario = new propietario();

$propietario->esPropietarioLogueado();

$accion = isset(filter_input(INPUT_GET, 'accion')) ? filter_input(INPUT_GET, 'accion') : "listar";
$session = $_SESSION;

$bitacora = new bitacora();

switch ($accion) {
    case  "listar":
        $resultado = null;
        if (isset(filter_input(INPUT_GET, 'pagina'))) {
            $page = filter_input(INPUT_GET, 'pagina');
        } else {
            $page = 1;
        }
        $start = ($page - 1) * 10;
        
        $historico = $bitacora->obtenerBitacoraPorPropietario($session['usuario']['cedula'], $start, 10);
        $limit = 0;
        $total = $bitacora->totalRegistrosBitacora($session['usuario']['cedula']);
        
        if ($historico['suceed']) {
            $resultado = $historico['data'];
            $limit = 10 * $page;
            $start++;
        }
        //echo $limit.'<br>'.$total;
        if ($page==1) {
            
            $bitacora->insertar(Array(
                "id_sesion"=>$session['id_sesion'],
                "id_accion"=> 13,
                "descripcion"=>'',
            ));
        }
        echo $twig->render('enlinea/bitacora/listar.html.twig', array(
            "session"  => $session,
            "historico"=> $resultado,
            "pagina"   => $page,
            "start"    => $start,
            "limit"    => $limit,
            "total"   => $total
        ));
        break;
}

?>
