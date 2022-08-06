<?php
header ('Content-type: text/html; charset=utf-8');
include_once '../../includes/constants.php';
include_once "../../includes/usuario.php";
include_once '../../includes/file.php';


$accion = isset($_GET['accion']) ? $_GET['accion'] : "";
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!isset($_GET['id']) || !$_GET['id']=='sac' || !$accion=='actualizar-estatus') {
    usuario::esUsuarioLogueado();
    $session = $_SESSION;
}
switch ($accion) {

    // <editor-fold defaultstate="collapsed" desc="lista propietarios por inmueble">
    case "listarPropietariosMorososPorInmueble" :
        if (isset($_POST['id_inmueble'])) {
            $pro = new propietario();
            $r = $pro->listarPropietariosMorososPorInmueble($_POST['id_inmueble']);
            if ($r['suceed']) {
                $jsonTable = json_encode($r['data']);
                echo $jsonTable;
            }
          }
        break; 
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="guardar">
    case "guardar":
        $cobranza = new cobranza();
        $data = $_POST;
        $data['usuario'] = strtoupper($data['usuario']);
        $exito = array();
        if (count($data) > 0) {
            unset($data['procesar']);
            $exito = $cobranza->insertar($data);
            
        } else {
            $exito['suceed'] = false;
            $exito['mensaje'] = 'No se puedo guardar la gestión. No se recibieron los parámetros mínimos requeridos';
            
        }
        echo json_encode($exito);
        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="actualizar estatus">
    case "actualizar-estatus":
        $cobranza = new cobranza();
        if (is_numeric($id)) {
            $r = $cobranza->actualizar($id, array("sincronizado" => 1));
            echo $r['suceed'];
        } else {
            echo '--';
        }


        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="gestiones por sincronizar">
    case "por-sincronizar":
        $cobranza = new cobranza();
        $resultado = $cobranza->listarGestionesPorSincronizar();
        if ($resultado['suceed'] && count($resultado['data']) > 0) {
            foreach ($resultado['data'] as $gestion) {
                echo "|" . $gestion['id'] . "|" . $gestion['id_inmueble'];
                echo "|" . $gestion['apto'] . "|" . $gestion['telefono'] . "|" . $gestion['contacto'];
                echo "|" . utf8_decode($gestion['resultado']);
                echo "|" . $gestion['fecha_hora'] . "|" . $gestion['usuario'];
                echo "|" . $gestion['tipo'] . "<fin>";
            }
        }
        break; 
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="consultar">
    case "consultar":
        
        if (isset($_POST['inmueble']) && isset($_POST['apto'])) {
            $facturas = new factura();
            $inmueble = new inmueble();
            $propietario = new propietario();
            $propiedades  = new propiedades();
            $cobranza = new cobranza();
            $inm = $inmueble->ver($_POST['inmueble']);
            
            $propiedad = $propiedades->verPropiedad($_POST['inmueble'], $_POST['apto']);
            $lista_pro = $propietario->listarPropietariosMorososPorInmueble($_POST['inmueble']);
            $prop = $propietario->obtenerPropietario($_POST['inmueble'], $_POST['apto']);
            
            $factura = $facturas->estadoDeCuenta($_POST['inmueble'], $_POST['apto']);
            
            if ($factura['suceed'] == true) {

                for ($index = 0; $index < count($factura['data']); $index++) {

                    $filename = "../avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                    $factura['data'][$index]['aviso'] = file_exists($filename);
                }

                $cuenta = Array(
                    "codinm"        => $_POST['inmueble'],
                    "apto"          => $_POST['apto'],
                    "inmueble"      => $inm['data'][0],
                    "propietario"   => $prop,
                    "propiedad"     => $propiedad['row'],
                    "recibos"       => $factura['data']);
            }
        }
        $lista_inm = $inmueble->listar();
        $gestiones = $cobranza->listarGestionesPorPropiedad($_POST['inmueble'], $_POST['apto']);
        
        echo $twig->render('intranet/cobranza/index.html.twig', array(
            "session"       => $session,
            "cuentas"       => $cuenta,
            "inmuebles"     => $lista_inm['data'],
            "propietarios"  => $lista_pro['data'],
            "gestiones"     => $gestiones['data']
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="default">
    default :
    case "gestiones":    
        $inmueble = new inmueble();
        $lista_inm = $inmueble->listarInmueblesAutorizados($session['usuario']['id']);
        
        echo $twig->render('intranet/cobranza/index.html.twig', array(
            "session"   => $session,
            "inmuebles" => $lista_inm['data'],
            "accion"    => $accion
        ));
        
        break; 
    // </editor-fold>

}