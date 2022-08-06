<?php
date_default_timezone_set("America/La_Paz");

$debug = true;
$sistema = "/sintesis/";
$email_error = true;
$mostrar_error = false;

if ($_SERVER['SERVER_NAME'] == "www.sintesisonline.com.ve" | $_SERVER['SERVER_NAME'] == "sintesisonline.com.ve") {
    $user = "";
    $password = "";
    $db = "";
    $email_error = true;
    $mostrar_error = false;
    $debug = false;
    $sistema = "/";
    $entorno = 'PRODUCCION';
} else {
    $user = "";
    $password = "";
    $db = "";
    $entorno = 'DESARROLLO';
}

define("HOST", "localhost");
define("USER", $user);
define("PASSWORD", $password);
define("DB", $db);
define("SISTEMA", $sistema);
define("EMAIL_ERROR", $email_error);
define("EMAIL_CONTACTO", "ynfantes@gmail.com");
define("EMAIL_TITULO", "[Error] Sintesisonline.com.ve");
define("MOSTRAR_ERROR", $mostrar_error);
define("DEBUG", $debug);
define("ENTORNO", $entorno);
define("TITULO", "Sintesis Administración Outsourcing");
/**
 * para las urls
 */
define("ROOT", 'http://' . $_SERVER['SERVER_NAME'] . SISTEMA);
define("URL_SISTEMA", ROOT . "enlinea");
define("URL_INTRANET", ROOT . "intranet");
/**
 * para los includes
 */
define("SERVER_ROOT", $_SERVER['DOCUMENT_ROOT'] . SISTEMA);

/*set_include_path(SERVER_ROOT . "/site/");*/
define("TEMPLATE", SERVER_ROOT . "/template/");
define("mailPHP",0);
define("sendMail",1);
define("SMTP",2);
define("PROGRAMA_CORREO",SMTP);

include_once dirname(dirname(dirname(__FILE__))).'/framework/twig/lib/Twig/Autoloader.php';
include_once SERVER_ROOT . 'includes/extensiones.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(SERVER_ROOT . 'template');
$twig = new Twig_Environment($loader, array(
            'debug' => true,
            //'cache' => SERVER_ROOT . 'cache',
            'cache' => false,
            "auto_reload" => true)
);
if (isset($_SESSION))
    $twig->addGlobal("session", $_SESSION);

$twig->addExtension(new extensiones());
$twig->addExtension(new Twig_Extension_Debug());

function __autoload($clase) {
    include_once SERVER_ROOT . "/includes/" . $clase . ".php";
}

spl_autoload_register("__autoload", false);

if (isset($_GET['logout']) && $_GET['logout'] == true) {
    $user_logout = new propietario();
    $user_logout->logout();
}

define("NOMBRE_APLICACION","Síntesis en Línea");
define("ACTUALIZ","data/");
define("ARCHIVO_INMUEBLE","INMUEBLE.txt");
define("ARCHIVO_CUENTAS","CUENTAS.txt");
define("ARCHIVO_FACTURA","FACTURA.txt");
define("ARCHIVO_FACTURA_DETALLE","FACTURA_DETALLE.txt");
define("ARCHIVO_JUNTA_CONDOMINIO","JUNTA_CONDOMINIO.txt");
define("ARCHIVO_PROPIEDADES","PROPIEDADES.txt");
define("ARCHIVO_PROPIETARIOS","PROPIETARIOS.txt");
define("ARCHIVO_EDO_CTA_INM","EDO_CUENTA_INMUEBLE.txt");
define("ARCHIVO_ACTUALIZACION","ACTUALIZACION.txt");
define("SMTP_SERVER","");  
define("PORT",25);
define("USER_MAIL","");
define("PASS_MAIL","");
define("MESES_COBRANZA",100);
define("GRAFICO_FACTURACION",0);
define("GRAFICO_COBRANZA",0);
define("DEMO",0);
/* en caso de moroso modifique los siguientes valores */
define("MOROSO",false);
define("RECIBOS_PENDIENTES",1);
define("DEUDA",7500);