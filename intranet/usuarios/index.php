<?php
header ('Content-type: text/html; charset=utf-8');
include_once '../../includes/constants.php';
include_once "../../includes/usuario.php";
include_once '../../includes/file.php';


$accion = isset($_GET['accion']) ? $_GET['accion'] : "";
$id = isset($_GET['id']) ? $_GET['id'] : -1;
usuario::esUsuarioLogueado();
$session = $_SESSION;
switch ($accion) {
    
    // <editor-fold defaultstate="collapsed" desc="guardar">
    case "guardar":
        $usuarios = new usuario();
        $usuario = array();
        $data = $_POST;
        if (isset($_POST['id'])) {
            unset($data['id']);
            $id = $_POST['id'];
            $resultado = $usuarios->actualizar($id, $data);
            $accion = 'editar';
        } else {
            $resultado = $usuarios->seleccionarUsuario(Array("nombre"=> "'".$data['nombre']."'"));
            if ($resultado['suceed'] && count($resultado['data'])>0) {
                $resultado['suceed'] = FALSE;
                $resultado['mensaje'] = 'Ya existe un usuario registrado con este nombre: '.$data['nombre'];
            } else {
                $resultado = $usuarios->insertar($data);
                if ($resultado['suceed']) {
                    $id = $resultado['insert_id'];
                    $accion = 'editar';
                }
            }
        }
        if (!isset($resultado['mensaje'])) {
            if ($resultado['suceed']) {
                $resultado['mensaje'] = 'Cambios registrados con éxito!';
            } else {
                $resultado['mensaje'] = 'No se pudieron guardar los cambios.';
            }
        }
        if ($id > 0) {
            $r = $usuarios->ver($id);
            
            if ($r['suceed'] && count($r['data'])>0) {
                $usuario = $r['data'][0];
            } else {
                $usuario = $_POST;
            }
        } else {
            $usuario = $_POST;
        }

        echo $twig->render('intranet/usuarios/gestion.html.twig', array(
            "session"   => $session,
            "usuario"   => $usuario,
            "accion"    => $accion,
            "resultado" => $resultado
        ));

        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="editar">
    case "editar":
        $usuarios = new usuario();
        $usuario = $usuarios->ver($id);
        
        echo $twig->render('intranet/usuarios/gestion.html.twig', array(
            "session"   => $session,
            "usuario"   => $usuario['data'][0],
            "accion"    => $accion
        ));
        break; 
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="registrar">
    case "registrar":
        $usuario = array();
        
        echo $twig->render('intranet/usuarios/gestion.html.twig', array(
            "session"   => $session,
            "usuario"   => $usuario,
            "accion"    => $accion
        ));
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

    // <editor-fold defaultstate="collapsed" desc="default, listar">
    default :
    case "listar":    
        $usuarios = new usuario();
        $lista = $usuarios->listar();
        echo $twig->render('intranet/usuarios/index.html.twig', array(
            "session"   => $session,
            "usuarios"  => $lista['data']
        ));
        
        break; 
    // </editor-fold>

}