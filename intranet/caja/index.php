<?php

include_once '../../includes/constants.php';
include_once "../../includes/usuario.php";
include_once '../../includes/file.php';

usuario::esUsuarioLogueado();
$accion = isset($_GET['accion']) ? $_GET['accion'] : "";
$id = isset($_GET['id']) ? $_GET['id'] : -1;
$session = $_SESSION;
switch ($accion) {

    // <editor-fold defaultstate="collapsed" desc="lista propietarios por inmueble">
    case "listarPropietariosPorInmueble" :
        if (isset($_POST['id_inmueble'])) {
            $pro = new propietario();
            $r = $pro->listarPropietariosPorInmueble($_POST['id_inmueble']);
            if ($r['suceed'] && count($r['data']) > 0) {
                $jsonTable = json_encode($r['data']);
                echo $jsonTable;
            }
          }
        break; // </editor-fold>
          
    // <editor-fold defaultstate="collapsed" desc="salir">
    case "salir":
        
        $user_logout = new usuario();
        $user_logout->logout();
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="guardar">
    case "guardar":
        $pago = new pago();
        $data = $_POST;
        if (count($data) > 0) {
            unset($data['procesar']);
            $exito = $pago->registrarPago($data);
            $exito['facturas'] = $_POST['facturas'];
            
        } else {
            header("location:".URL_INTRANET."/caja");
            return;
        }
        
        echo $twig->render('intranet/caja/index.html.twig', array("session" => $session,
            "resultado" => $exito,
            "accion" => "registrar"
        ));
        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="guardar cartelera">
    case "guardar-cartelera":
        $result = Array();
        $data = Array();
        if (strlen($_POST['contenido']) < 15) {
            $result['suceed'] = false;
            $result['mensaje'] = "Debe agregar el contenido a la publicación.";
        } else {
            $data = $_POST;
            $cartelera = new cartelera();
            if (isset($_POST['inmueble'])) {
                $cartelera->tabla = "cartelera_inmueble";
            }
            $data['fecha_hasta'] = Misc::format_mysql_date($data['fecha_hasta']);
            $result = $cartelera->insertar($data);
            $data['contenido'] = utf8_encode($data['contenido']);
            if ($result['suceed']) {
                $result['mensaje'] = "Publicación registrada con éxito";
            }
        }
        echo json_encode($result);
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="cartelera general">
    case "cartelera-general":
        $cartelera = new cartelera();
        $publicaciones = $cartelera->listar();


        echo $twig->render('intranet/caja/cartelera.general.html.twig', array(
            "session" => $session,
            "publicaciones" => $publicaciones['data'],
            "id" => $id
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="eliminar publicacion cartelera">
    case "eliminar-cartelera-general":
        $data = Array();
        $cartelera = new cartelera();
        if (is_numeric($id)) {
            $data['eliminar'] = 1;
            $cartelera->actualizar($id, $data);
        }
        $publicaciones = $cartelera->listar();
        echo $twig->render('intranet/caja/cartelera.general.html.twig', array(
            "session" => $session,
            "publicaciones" => $publicaciones['data'],
            "id" => 0
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="eliminar cartelera condominio">
    case "eliminar-cartelera-condominio":
        $data = Array();
        $cartelera = new cartelera();
        $cartelera->tabla = "cartelera_inmueble";
        if (is_numeric($id)) {
            $data['eliminar'] = 1;
            $cartelera->actualizar($id, $data);
        }
        $publicaciones = $cartelera->listar();
        echo $twig->render('intranet/caja/cartelera.condominio.html.twig', array(
            "session" => $session,
            "publicaciones" => $publicaciones['data'],
            "id" => 0
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="listar cartelera general">
    case "listar-cartelera-general":
        $cartelera = new cartelera();
        $publicaciones = $cartelera->listar();
        echo $twig->render('intranet/caja/listar-cartelera.general.html.twig', array(
            "session" => $session,
            "publicaciones" => $publicaciones['data']
        ));
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="cartelera condominio">
    case "cartelera-condominio":
        $cartelera = new cartelera();
        $inmueble = new inmueble();


        $cartelera->tabla = "cartelera_inmueble";
        $publicaciones = $cartelera->listar();


        $lista_inm = $inmueble->listarInmueblesAutorizados($session['usuario']['id']);


        echo $twig->render('intranet/caja/cartelera.condominio.html.twig', array(
            "session" => $session,
            "publicaciones" => $publicaciones['data'],
            "id" => $id,
            "inmuebles" => $lista_inm['data']
        ));
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="consultar">
    case "consultar":
        
        if (isset($_POST['inmueble']) && isset($_POST['apto'])) {
            $facturas = new factura();
            $inmueble = new inmueble();
            $propietario = new propietario();
            $propiedades  = new propiedades();
            $inm = $inmueble->ver($_POST['inmueble']);
            
            $propiedad = $propiedades->verPropiedad($_POST['inmueble'], $_POST['apto']);
            $lista_pro = $propietario->listarPropietariosPorInmueble($_POST['inmueble']);
            $prop = $propietario->obtenerPropietario($_POST['inmueble'], $_POST['apto']);
            
            $factura = $facturas->estadoDeCuenta($_POST['inmueble'], $_POST['apto']);
            
            if ($factura['suceed'] == true) {

                for ($index = 0; $index < count($factura['data']); $index++) {

                    $filename = "../../enlinea/avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                    
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
        $archivo = '../../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
        $fecha_actualizacion = JFile::read($archivo);
        
        echo $twig->render('intranet/caja/index.html.twig', array(
            "session"       => $session,
            "cuentas"       => $cuenta,
            "inmuebles"     => $lista_inm['data'],
            "propietarios"  => $lista_pro['data'],
            "actualizacion" => $fecha_actualizacion
        ));
        break; // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="default">
    default :
        
        $inmueble = new inmueble();
        $lista_inm = $inmueble->listarInmueblesAutorizados($session['usuario']['id']);

        echo $twig->render('intranet/caja/index.html.twig', array(
            "session" => $session,
            "inmuebles"=>$lista_inm['data']
        ));
        
        break; // </editor-fold>

}