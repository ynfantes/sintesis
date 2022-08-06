<?php
include './constants.php';

$ini = parse_ini_file('./emails.ini');

if (isset($_GET['accion'])) {
    $result = Array();
    switch ($_GET['accion']) {
    
        // <editor-fold defaultstate="collapsed" desc="mensaje pagina contacto">
        case "email_contacto":
            $mail = new mailto(2);
            $mensaje = sprintf($ini['CUERPO_CONTACTO_SINTESIS'], $_POST['name'],$_POST['email'],$_POST['message']);
            $r = $mail->enviar_email($ini['ASUNTO_CONTACTO'], $mensaje, "", USER_MAIL);

            if ($r == "") {
                $mail = new mailto(2);
                $mensaje = $ini['CUERPO_CONTACTO'];

                $r = $mail->enviar_email($ini['ASUNTO_CONTACTO'], $mensaje, "", $_POST['email']);
                $result = Array("sent"=>"yes");
            } else {
                $result = Array("sent"=>"no");
            }
            echo json_encode($result);
            break; 
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="email soporte">
        case "email_soporte":
            session_start();
            if (isset($_POST['email']) && isset($_SESSION['usuario']['cedula'])) {
                $mail = new mailto(SMTP);
                $mensaje = sprintf($ini['CUERPO_SOPORTE'], $_POST['name'], NOMBRE_APLICACION,$_POST['email'], 
                        $_SESSION['usuario']['cedula'],$_POST['message']);
                $r = $mail->enviar_email("Soporte ".NOMBRE_APLICACION, $mensaje, "Síntesis en línea", "info@sintesisonline.com.ve");

                if ($r == "") {
                    $mail = new mailto(SMTP);
                    $mensaje = $ini['CUERPO_CONTACTO'];

                    $r = $mail->enviar_email("Soporte página web", $mensaje, "", $_POST['email']);
                    $result = Array("sent" => "yes");
                } else {
                    $result = Array("sent" => "no");
                }
            } else {
                $result = Array("sent" => "failure");
            }
            echo json_encode($result);
            break;
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="contacto_propiedad">
        case "contacto_propiedad":
            $mail = new mailto(SMTP);
            $mensaje = sprintf($ini['CUERPO_CONTACTO_PROPIEDAD'],
                    $_POST['nombre'],
                    $_POST['titulo'], 
                    $_POST['mensaje'], 
                    $_POST['email'], 
                    $_POST['telefono'],                     $_POST['url']);
            
            $r = $mail->enviar_email('Información ' . $_POST['titulo'], $mensaje, "", "bienesraices@mpinmuebles.com");

            if ($r == "") {
                $mail = new mailto();
                $mensaje = $ini['CUERPO_CONTACTO'];

                $r = $mail->enviar_email($ini['ASUNTO_CONTACTO'], $mensaje, "", $_POST['email']);

                echo '<h4>Menseje enviado con éxito!</h4>En breve estaremos 
                    contactando con usted.<br>Gracias por su interés.';
            } else {
                echo '<h4>Error al enviar el mensaje.</h4>
                    No se pudo enviar el mensaje. Por favor intente nuevamente.';
            }
            break; // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="compartir_propiedad">
        case "compartir_propiedad":
            $mail = new mailto(SMTP);
            if (isset($_POST['de']) && isset($_POST['email']) && isset($_POST['de']) && isset($_POST['url']) && isset($_POST['titulo'])) {
                $mensaje = $_POST['mensaje'] . "<br>" . $_POST['titulo'] . "<br>" . $_POST['url'];
                $r = $mail->enviar_email($_POST['de'] . " ha compartido contigo una propiedad de mpinmuebles.com", $mensaje, "", $_POST['email']);
                if ($r == "") {
                    echo '<h4>Menseje enviado con éxito!</h4>Gracias por compartir la información de mpinmuebles.com.';
                } else {
                    echo '<h4>Error al enviar el mensaje.</h4>No se pudo enviar el mensaje. Por favor intente nuevamente.';
                }
            }
            break;
        // </editor-fold>
    }

}