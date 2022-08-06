<?php
include_once '../../includes/constants.php';
include_once '../../includes/file.php';

$propietario = new propietario();
$bitacora = new bitacora();

$propietario->esPropietarioLogueado();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "bandeja-entrada";
$session = $_SESSION;

$mensajes = new mensajes();

switch ($accion) {
    
    case "eliminar-mensaje":
        $resultado = $mensajes->actualizar($_GET['id'], Array("eliminado"=>TRUE));
    
    // <editor-fold defaultstate="collapsed" desc="bandeja de entrada">
    case "bandeja-entrada": default :
        $num = $mensajes->cantidadMensajesSinLeerPorPropietario($session['usuario']['cedula']);
        $bitacora->insertar(Array(
            "id_sesion"=>$session['id_sesion'],
            "id_accion"=> 5,
            "descripcion"=>"Bandeja de Entrada: (".$num.") mensaje(s) sin leer.",
        ));
        echo $twig->render('enlinea/mensajes/index.html.twig', array(
            "session" => $session,
            "cantidad" => $num
        ));
        break; 
// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="listar mensajes">
    case "listar":
        $resultado = null;
        if (isset($_GET['pagina'])) {
            $page = $_GET['pagina'];
        } else {
            $page = 1;
        }
        
        $start = ($page - 1) * 20;
        $inbox = $mensajes->mostrarMensajesPorPropietario($session['usuario']['cedula'],$start, 20);
        $limit = 0;
        $total = $mensajes->obtenerTotalMensajes();
        if ($inbox['suceed']) {
            $resultado = $inbox['data'];
            $limit = count($inbox['data']) * $page;
            $start++;
        }
        
        echo $twig->render('enlinea/mensajes/listar.html.twig', array(
            "session" => $session,
            "lista"   => $resultado,
            "pagina"  => $page,
            "start"   => $start,
            "limit"   => $limit,
            "total"   => $total
        ));
        break; // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="abrir mensaje">
    case "abrir-mensaje":
        $resultado = null;
        $mensaje = $mensajes->ver($_GET['id']);
        
        if ($mensaje['suceed']) {
            $resultado = $mensaje['data'];
            $mensajes->actualizar($_GET['id'], Array("leido"=>true));
        }


        echo $twig->render('enlinea/mensajes/ver.html.twig', array(
            "mensaje" => $resultado
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="eliminar mensaje ajax">
    case "eliminar":


        $resultado = $mensajes->actualizar($_GET['id'], Array("eliminado" => TRUE));
        echo $resultado['suceed'];
        break; // </editor-fold>

    case "notificaciones":
        $resultado = null;
        $inbox = $mensajes->mostrarMensajesPorPropietario($session['usuario']['cedula']);
        if ($inbox['suceed']) {
            $resultado = $inbox['data'];
        }

        echo $twig->render('enlinea/mensajes/notificaciones.html.twig', array(
            "session" => $session,
            "lista" => $resultado
        ));
        break;
        
    case "avisos":
        echo $twig->render('enlinea/mensajes/avisos.html.twig', array(
            "session" => $session
        ));
        break;
}