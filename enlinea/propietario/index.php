<?php
include_once '../../includes/constants.php';
if (isset($_GET['logout'])) {
    die();
}
$propietario = new propietario();
$bitacora = new bitacora();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "ver";
$id = isset($_GET['id']) ? $_GET['id'] : "perfil";
if ($id!= 'sac' && !is_numeric($id)) {
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
}

switch ($accion) {
    
    // <editor-fold defaultstate="collapsed" desc="ver o actualizar">
    case "ver":
    case "actualizar":

        /* @var $datos_personales callable */
        $datos_personales = $propietario->ver($session['usuario']['id']);
        
        $bitacora->insertar(Array(
            "id_sesion"=>$session['id_sesion'],
            "id_accion"=> 3,
            "descripcion"=>$session['usuario']['nombre'],
        ));
        
        echo $twig->render('enlinea/propietario/formulario.html.twig', array(
            "session" => $session,
            "propietario" => $datos_personales['data'][0],
            "accion" => $accion,
            "id" => $id));


        break; 
// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="modificar">
    case "modificar":
        
        $data = $_POST;
        unset($data['actualizar']);
        
        if ($_GET['id'] == 'perfil') {
            $exito = $propietario->actualizar($session['usuario']['id'], $data);
            $mensaje = "Datos actualizados con éxito!";
            $bitacora->insertar(Array(
                "id_sesion"=>$session['id_sesion'],
                "id_accion"=> 14,
                "descripcion"=>'',
            ));
        } else {
            $exito = $propietario->ver($session['usuario']['id']);
            if ($exito['suceed'] && count($exito['data']) > 0) {
                if ($exito['data'][0]['clave'] == $data['clave_actual']) {
                    unset($data['clave_actual']);
                    $exito = $propietario->actualizar($session['usuario']['id'], $data);
                    //$exito = $propietario->cambioDeClave($session['usuario']['id'], $clave);
                    $mensaje = "Cambio de clave efectuado con éxito!.";
                    $bitacora->insertar(Array(
                        "id_sesion"=>$session['id_sesion'],
                        "id_accion"=> 7,
                        "descripcion"=>'',
                    ));
                } else {
                    $mensaje = "Clave actual no concuerda.";
                    $exito['suceed'] = false;
                }
            } else {
                $mensaje = "El cambio de clave no se pudo procesar.";
            }
        
        }
        if ($exito['suceed']) {
            $exito['mensaje'] = $mensaje;
        } else {
            if ($mensaje == "") {

                $mensaje = "Los cambios no puedieron guardarse.";
            }
            $exito['mensaje'] = $mensaje;
        }
        $datos_personales = $propietario->ver($session['usuario']['id']);
        echo $twig->render('enlinea/propietario/formulario.html.twig', array(
            "session" => $session,
            "propietario" => $datos_personales['data'][0],
            "accion" => "actualizar",
            "resultado" => $exito,
            "id" => $_GET['id']));
        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="claves actualizadas">
    case "clavesActualizadas":
        $listado = $propietario->listarPropietariosClavesActualizadas();


        if ($listado['suceed'] && count($listado['data'] > 0)) {
            foreach ($listado['data'] as $clave) {


                $propietario->actualizar($clave["id"], Array("cambio_clave" => 0));


                echo $clave["id_inmueble"] . "|";
                echo $clave["apto"] . "|";
                echo $clave["clave"] . "[EOL]";
            
                
            }
        }
        break; // </editor-fold>
       
    // <editor-fold defaultstate="collapsed" desc="propietarios actualizados">
    case "actualizados":
        $resultado = $propietario->obtenerPropietariosActualizados();
        
        if ($resultado['suceed'] && count($resultado['data']) > 0) {
            foreach ($resultado['data'] as $actualizado) {
                echo "|" . $actualizado['cedula'] . "|" . $actualizado['id_inmueble'];
                echo "|" . $actualizado['apto'] . "|" . $actualizado['clave'] . "|" . $actualizado['direccion'];
                echo "|" . $actualizado['telefono1'] . "|" . $actualizado['telefono2'];
                echo "|" . $actualizado['telefono3'] . "|" . $actualizado['email'] . "|" . $actualizado['email_alternativo'] . "[EOL]";
            }
        }
        break;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="enviar clave del servicio">

    case "clave-servicio":
        $propietario = new propietario();
        
        if (!is_numeric($id))
            $id = null;
        $propietario->envioMasivoEmail(NOMBRE_APLICACION, '../plantillas/clave-servicio.html', $id);
        break; // </editor-fold>
}
