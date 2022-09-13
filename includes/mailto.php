<?php
include 'phpmailer/phpmailer.php';
include 'phpmailer/smtp.php';
//include_once 'constants.php';
/**
 * Description of mailto
 *
 * @author emessia
 */
class mailto {
    /**
     * host
     * @var string host
     */
    private $host = SMTP_SERVER;
    
    /**
     * port
     * @var int port
     */
    private $port = PORT;
    
    /**
     * user_email
     * @var string user_mail
     */
    private $user = USER_MAIL;
    
    /**
     * pass_mail
     * @var string pass_mail
     */
    private $pass = PASS_MAIL;
    
    /**
     * Instancia de phpmailer
     * @var phpmailer mail
     */
    private $mail = null;
    
    
    //put your code here
    public function __construct($progamaCorreo=null) {
        
        $this->mail = new PHPMailer();
        switch($progamaCorreo) {
          case SMTP:
                $this->mail->IsSMTP();
                $this->mail->Host = $this->host;
                $this->mail->Port = $this->port;
                $this->mail->SMTPSecure = "SSL";
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $this->user;
                $this->mail->Password = $this->pass;
//                echo "Usuario: ".  $this->user."<br>";
//                echo "Pass: ".  $this->pass."<br>";
//                echo "Port: ". $this->port."<br>";
//                echo "Host: ".  $this->host."<br>";
                break;
          case sendMail:
              $this->mail->IsSendmail();
              break;
          default:
              $this->mail->IsMail();
        }
        $this->mail->SMTPDebug = true; 
        $this->mail->SMTPKeepAlive = true;
    }
    /**
     * Envía el email
     * @param string $asunto  Asunto del mensaje
     * @param string $mensaje Cuerpo del Mensaje
     * @param string $textoAlternativo Texto alternativo sin formato HTMLK
     * @param string $emailDestinatario   Correo del destinatario
     * @param string $nombreDestinatario  Nombre del Destinatario
     * @param Array $attach  archivos adjuntos
     * @return string  
     */
    public function enviar_email($asunto,$mensaje, $textoAlternativo, $emailDestinatario, $nombreDestinatario ="",$attach=null) {
        
        $this->mail->From = $this->user;
        $this->mail->FromName = NOMBRE_APLICACION;
        $this->mail->Subject = $asunto;
        $this->mail->AltBody = $textoAlternativo;
        $this->mail->MsgHTML($mensaje);
        //var_dump($attach);
        if ($attach != null ) {
            foreach ($attach as $value) {
                //echo $value;
                $this->mail->AddAttachment($value);
            }
        }
        if (DEMO == 1) {
            $this->mail->AddAddress('ynfantes@gmail.com','Edgar Messia');
        } else {
            $this->mail->AddAddress($emailDestinatario, $nombreDestinatario);
            //$this->mail->AddBCC("ynfantes@gmail.com","Edgar Messia");
        }
        
        $this->mail->IsHTML(true);
        
        if(!$this->mail->Send()) {
          $result = "Error: " . $this->mail->Host."<br>".$this->mail->Port;
        } else {
          $result = "";
        }
        return $result;
    }
}
