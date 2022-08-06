<?php
include_once '../../includes/constants.php';
include_once '../../includes/mailto.php';
$propietario = new propietario();

if (!isset($_GET['id']) || $_GET['accion']=='autorizar') {
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
    
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";

$prerecibo = new prerecibo();
$mensaje='';

switch ($accion) {
    
    // <editor-fold defaultstate="collapsed" desc="autorizar">
    case "autorizar":


        $actualizar = $prerecibo->actualizar($_GET['id'], Array("aprobado" => "-1",
            "aprobado_por" => $session['usuario']['nombre'],
            "fecha_aprobado" => date("Y-m-d")));


        if ($actualizar['suceed']) {
            $documento = $prerecibo->ver($_GET['id']);


            if ($documento['suceed'] && count($documento['data'] > 0)) {


                // enviamos un email de notificación a la administradora
                $ini = parse_ini_file('../../includes/emails.ini');


                $mail = new mailto(PROGRAMA_CORREO);


                $destinatario = $ini['CUENTA_FACTURACION'];


                $subject = sprintf($ini['ASUNTO_MENSAJE_CONFIRMACION_AUTORIZACION_PRERECIBO'], 
                        $session['junta'],                         date('m-Y', strtotime($documento['data'][0]['periodo']))
                );
                $mensaje = sprintf($ini['CUERPO_MENSAJE_CONFIRMACION_AUTORIZACION_PRERECIBO'], 
                        $session['junta'],                         date('m-Y', strtotime($documento['data'][0]['periodo'])),                         $session['usuario']['nombre']);
            
            }


            $r = $mail->enviar_email($subject, $mensaje, "", $destinatario);
            if ($r == "") {
                $prerecibo->actualizar($_GET['id'], Array("notificacion" => '-1'));
            }
            $mensaje = "Prerecibo autorizado con éxito!";
        
            
        } else {
            $mensaje = "No se pudo autorizar el prerecibo.
                Inténtelo nuevamente. Si el problema persiste póngase en contacto
                con la Administradora";
        }
        unset($actualizar['query']);
        $actualizar['mensaje'] = $mensaje;
        echo json_encode($actualizar);
        break;
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="soportes">
    case "soportes":
        $propiedad = new propiedades();
        $resultado = Array();


        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);


        if ($propiedades['suceed'] == true) {


            foreach ($propiedades['data'] as $propiedad) {


                $prerecibos = $prerecibo->listarPorInmueble($propiedad['id_inmueble']);


                if ($prerecibos['suceed']) {

                    if (!count($prerecibos['data']) > 0) {
                        $resultado['suceed'] = true;
                        $resultado['mensaje'] = "No se ha publicado ningún pre-recibo hasta ahora.";
                    } else {

                        for ($index = 0; $index < count($prerecibos['data']); $index++) {
                            $filename = $prerecibos['data'][$index]['documento'];
                            $pos = strpos($filename, '_');

                            $filename = str_replace(substr($filename, 0, $pos), "Soporte", $filename);
                            $prerecibos['data'][$index]['publicado'] = file_exists($filename);
                            $prerecibos['data'][$index]['soporte'] = file_exists($filename) ? $filename : "";
                                                }
                        if (!$mensaje == "") {
                            $resultado['suceed'] = $actualizar['suceed'];
                            $resultado['mensaje'] = $mensaje;
                        }
                    }
                } else {
                    $resultado['suceed'] = False;
                    $resultado['mensaje'] = "Ha ocurrido un error, no se puede recuperar la información.";
                }
            }
        } else {
            $resultado['suceed'] = False;
            $resultado['mensaje'] = "No se puede recuperar la información.";
        }
        echo $twig->render('enlinea/prerecibo/soporte.html.twig', array(
            "session" => $session,
            "resultado" => $resultado,
            "prerecibos" => $prerecibos));
        break; 
    // </editor-fold>
            
    // <editor-fold defaultstate="collapsed" desc="listar">
    case "listar":

        $resultado = Array();
        // mostramos los últimos 5 periodos facturados, lo pagamos como parámetro a esta funcion
        $prerecibos = $prerecibo->listarPorInmueble($session['junta'],5);
        
        if ($prerecibos['suceed']) {
            
            if (!count($prerecibos['data']) > 0) {
                $resultado['suceed'] = true;
                $resultado['mensaje'] = "No se ha publicado ningún pre-recibo hasta ahora.";
            } else {
                for ($index = 0; $index < count($prerecibos['data']); $index++) {
                    $filename = $prerecibos['data'][$index]['documento'];
                    //echo $filename.'<br>';
                    $prerecibos['data'][$index]['publicado'] = file_exists($filename);
                    $pos = strpos($filename,'_');  
                    $filename = str_replace(substr($filename, 0,$pos), "Soporte", $filename);
                    //echo $filename.'<br>';
                    $prerecibos['data'][$index]['soporte'] = file_exists($filename)? $filename: "";
                    
                    
                }
                if (!$mensaje=="") {
                    $resultado['suceed'] = $actualizar['suceed'];
                    $resultado['mensaje'] = $mensaje;
                }
            }
        } else {
            $resultado['suceed'] = False;
            $resultado['mensaje'] = "Ha ocurrido un error, no se puede recuperar la información.";
        }
    
        echo $twig->render('enlinea/prerecibo/formulario.html.twig', array(
            "session" => $session,
            "resultado" => $resultado,
            "prerecibos" => $prerecibos));
        break; // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="publicar">
    case "publicar":
        $prerecibos = new prerecibo();


        $data = Array("id_inmueble" => $_GET['id_inmueble'],
            "documento" => $_GET['documento'],
            "periodo" => Misc::format_mysql_date($_GET['periodo']));


        //if ($prerecibos->prereciboYaRegistrado($_GET['id_inmueble'], $_GET['periodo'])) {
        
        //} else {
        $resultado = $prerecibos->insertar($data);
        //}
        if ($resultado['suceed']) {
            // enviamos un email de notificación a los miembros de la junta

            echo "Prerecibo registrado con éxito.";
        } else {
            if ($resultado['stats']['errno'] == 1062) {
                echo $resultado['stats']['errno'];
            } else {
                echo $resultado['stats']['error'];
            }
        }
        break; 
// </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="ver">
    case "ver":
        
        $titulo = $_GET['id'];
        $content = 'Content-type: application/pdf';
        $url = URL_SISTEMA . "/prerecibo/" . $_GET['id'];
        header('Content-Disposition: attachment; filename="' . $titulo . '"');
        header($content);
        readfile($url);
        break; // </editor-fold>
        
    default:
        break;
}
