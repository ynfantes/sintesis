<?php
ini_set('max_execution_time', 600);
header ('Content-type: text/html; charset=utf-8');

include_once 'includes/db.php';
include_once 'includes/file.php';
include_once 'includes/inmueble.php';
include_once 'includes/junta_condominio.php';
include_once 'includes/propietario.php';
include_once 'includes/propiedades.php';
include_once 'includes/factura.php';
include_once 'includes/cobranza.php';

$db = new db();

$tablas = array("DetFact", "facturas", "propiedades", "propietarios", "junta_condominio", "inmueble", "inmueble_deuda_confidencial","cancelacion_gastos");

if (isset($_GET['codinm'])) {
    $codinm = $_GET['codinm'];
    $db->exec_query("delete from factura_detalle where id_factura in (select numero_factura from facturas wher id_inmueble='".$codinm."')");
    $db->exec_query("delete from facturas where id_inmueble='".$codinm."'");
    $db->exec_query("delete from propietarios where cedula in (select cedula from propiedades where id_inmueble='".$codinm."')");
    $db->exec_query("delete from junta_condominio where id_inmueble='".$codinm."'");
    $db->exec_query("delete from propiedades where id_inmueble='".$codinm."'");
    $db->exec_query("delete from inmueble where id='".$codinm."'");
    $db->exec_query("delete from inmueble_deuda_confidencial where id_inmueble='".$codinm."'");
    $db->exec_query("delete from cancelacion_gastos where id_inmueble='".$codinm."'");
    $mensaje = "Actualización inmueble ".$codinm."<br>";
} else {
    $mensaje = "Proceso de Actualización Ejecutado<br />";
    foreach ($tablas as $tabla) {
        $r = $db->exec_query("truncate table " . $tabla);
        if (!MOROSO) {
            echo "limpiar tabla: " .$tabla."<br />";
        }
        
    }
    $archivo = ACTUALIZ . "GESTIONES.txt";
    if (file_exists($archivo)) {
        $db->exec_query("delete from gestion_cobranza where sincronizado=1");
        echo "limpiar tabla: gestion_cobranza<br />";
    }
}

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo inmueble">
$archivo = ACTUALIZ . ARCHIVO_INMUEBLE;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
if (!MOROSO) {
    $mensaje.= "procesar archivo inmueble (".count($lineas).")<br />";
    echo "procesar archivo inmueble (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {
    
    $registro = explode("\t", $linea);
    if ($registro[0] != "") {
        $registro = Array(
            "id"                => $registro[0],            
            "nombre_inmueble"   => $registro[1],
            "deuda"             => $registro[2],
            "fondo_reserva"     => $registro[3],
            "beneficiario"      => $registro[4],
            "banco"             => '',
            "numero_cuenta"     => '',
            "supervision"       => '0',
            "RIF"               => $registro[5],
            "meses_mora"        => $registro[6],
            "porc_mora"         => $registro[7],
            "moneda"            => $registro[8],
            "unidad"            => $registro[9],
            "facturacion_usd"   => $registro[10],
            'tasa_cambio'       => $registro[11],
            'redondea_usd'      => $registro[12]
            );
        
        $r = $inmueble->insertar($registro);
        
        if($r["suceed"]==FALSE){
            if (!MOROSO) {
                echo ARCHIVO_INMUEBLE."<br/>".$r['stats']['errno']." ".'<br/>'.$r['query'].'<br/>';
            }
        }   
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="procesamos el archivo cuentas">
$archivo = ACTUALIZ . ARCHIVO_CUENTAS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
if (!MOROSO) {
    $mensaje.= "actulizando cuentas inmuebles (" . count($lineas) . ")<br />";
    echo "actualizando cuentas inmuebles (" . count($lineas) . ")<br />";
}
foreach ($lineas as $linea) {
    $id = '';
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        $id=$registro[0];
        $registro = Array(
            "numero_cuenta" => $registro[1],
            "banco" => $registro[2]);


        $r = $inmueble->actualizar("'".$id."'",$registro);
        if ($r["suceed"] == FALSE) {
            //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            if (!MOROSO) {
                echo $r['query'];
                die();
            }
        }

    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Junta_Condominio">
$archivo = ACTUALIZ . ARCHIVO_JUNTA_CONDOMINIO;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$junta_condominio = new junta_condominio();
if (!MOROSO) {
    $mensaje.= "procesar archivo Junta Condominio (".count($lineas).")<br />";
    echo "procesar archivo Junta Condominio (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        $registro = Array("id_cargo" => $registro[1],
            "id_inmueble" => $registro[0],
            "cedula" => $registro[2]);
        $r = $junta_condominio->insertar($registro);
        
        if($r["suceed"]==FALSE){
            if (!MOROSO) {
                echo ARCHIVO_JUNTA_CONDOMINIO."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            }
        }
    }
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Propietarios">
$archivo = ACTUALIZ . ARCHIVO_PROPIETARIOS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propietario = new propietario();
if (!MOROSO) {
    $mensaje.= "procesar archivo Propietarios (".count($lineas).")<br />";
    echo "procesar archivo Propietarios (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);

    if ($registro[0] != "") {
        
       $registro = Array(
                    'nombre' => utf8_encode($registro[0]),
                    'clave' => $registro[1],
                    'email' => $registro[2],
                    'cedula' => $registro[3],
                    'telefono1' => $registro[4],
                    'telefono2' => $registro[5],
                    'telefono3' => $registro[6],
                    'direccion' => utf8_encode($registro[7]),
                    'recibos' => $registro[8],
                    'email_alternativo' => $registro[9]
           );
       
       $r = $propietario->insertar($registro);
       if($r["suceed"]==FALSE){
           if (!MOROSO) {
                echo "<b>Archivo Propietario: ".$archivo.' - '.$r['stats']['errno']."-".$r['stats']['error']."</b>".'<br/>'.$r['query'].'<br/>';
                die($r['query']);
           }
        }
            /*}
        }*/
    }
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Propiedades">
$archivo = ACTUALIZ . ARCHIVO_PROPIEDADES;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propiedades = new propiedades();
if (!MOROSO) {
    $mensaje.= "procesar archivo Propiedades (".count($lineas).")<br />";
    echo "procesar archivo Propiedades (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);

    if ($registro[0] != "") {
        $registro = Array(
            'cedula'        => $registro[0],
            'id_inmueble'   => $registro[1],
            'apto'          => $registro[2],
            'alicuota'      => $registro[3],
            'meses_pendiente' => $registro[4],
            'deuda_total'   => $registro[5],
            'deuda_usd'     => str_replace("\r", "", $registro[6])
               );
        
        $r = $propiedades->insertar($registro);
        if($r["suceed"]==FALSE){
            if (!MOROSO) {
                echo "<b>Archivo Propiedades: ".$r['stats']['errno']."-".$r['stats']['error']."</b><br />".'<br/>'.$r['query'].'<br/>';
            }
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Facturas">
$archivo = ACTUALIZ . ARCHIVO_FACTURA;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$facturas = new factura();
if (!MOROSO) {
    $mensaje.= "procesar archivo Facturas (".count($lineas).")<br />";
    echo "procesar archivo Facturas (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {
    
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        
        $registro = Array(
            'id_inmueble'   => $registro[0],
            'apto'          => $registro[1],
            'numero_factura'=> $registro[2],
            'periodo'       => $registro[3],
            'facturado'     => $registro[4],
            'abonado'       => $registro[5],
            'fecha'         => $registro[6],
            'facturado_usd' => $registro[7]
                );
        
        $r = $facturas->insertar($registro);
                
        if(!$r["suceed"]){
            if (!MOROSO) {
                echo($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
            }
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Detalle Factura">
$archivo = ACTUALIZ . ARCHIVO_FACTURA_DETALLE;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
if (!MOROSO) {
    $mensaje.="procesar archivo Detalle Factura (".count($lineas).")<br />";
    echo "procesar archivo Detalla Factura (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);


    if ($registro[0] != "") {

        $registro = Array(
            "id_factura" => $registro[0],
            "detalle" => utf8_encode($registro[1]),
            "codigo_gasto" => $registro[2],
            "comun" => $registro[3],
            "monto" => str_replace("\r","",$registro[4])
                );
        
        $r = $facturas->insertar_detalle_factura($registro);
        
        if($r["suceed"]==FALSE){
            die($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="Procesamos el archivo Inmueble Estado Cuenta">
$archivo = ACTUALIZ . ARCHIVO_EDO_CTA_INM;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
if (!MOROSO) {
    $mensaje.="procesar archivo estado de cuenta inmueble (".count($lineas).")<br />";
    echo "procesar archivo estado de cuenta inmueble (".count($lineas).")<br />";
}
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);


    if ($registro[0] != "") {

        $registro = Array(
            "id_inmueble"   => $registro[0],
            "apto"          => $registro[1],
            "propietario"   => utf8_encode($registro[2]),
            "recibos"       => $registro[3],
            "deuda"         => $registro[4],
            'deuda_usd'     => str_replace("\r", "", $registro[5])
        );

        $r = $inmueble->insertarEstadoDeCuentaInmueble($registro);


        if ($r["suceed"] == FALSE) {
            die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="graficos">

// <editor-fold defaultstate="collapsed" desc="facturacion mensual">
if (GRAFICO_FACTURACION == 1) {
    $archivo = ACTUALIZ . "FACTURACION_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    if (!MOROSO) {
        $mensaje.="procesar archivo grafico facturación mensual (" . count($lineas) . ")<br />";
        echo "procesar archivo grafico facturacion mensual (" . count($lineas) . ")<br />";
    }
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "facturado" => $registro[2]
            );

            $r = $inmueble->insertarFacturacionMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="cobranza mensual">
if (GRAFICO_COBRANZA == 1) {
    $archivo = ACTUALIZ . "COBRANZA_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    if (!MOROSO) {
        $mensaje.="procesar archivo grafico facturación mensual (" . count($lineas) . ")<br />";
        echo "procesar archivo grafico cobranza mensual (" . count($lineas) . ")<br />";
    }
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "monto" => $registro[2]
            );

            $r = $inmueble->insertarCobranzaMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}
// </editor-fold>

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="cancelacion de gastos">
$archivo = ACTUALIZ . "CANCELACION_GASTOS.txt";
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
if (!MOROSO) {
    $mensaje.="procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    echo "procesar archivo cancelacion de gastos (".count($lineas).")<br />";
}
$pago = new pago();
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);

    if ($registro[0] != "") {

        $registro = Array(
            "fecha_movimiento" => $registro[0],
            "monto" => $registro[1],
            "descripcion" => utf8_encode($registro[2]),
            "id_inmueble" => $registro[3],
            "id_apto" => $registro[4],
            "periodo" => $registro[5],
            "numero_factura" => str_replace("\r","",$registro[6])            
        );

        $r = $pago->insertarCancelacionDeGastos($registro);


        if ($r["suceed"] == FALSE) {
            die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
        }
    }
}// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="gestiones">
$archivo = ACTUALIZ . "GESTIONES.txt";
if (file_exists($archivo)) {
    $gestion = new cobranza();
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    if (!MOROSO) {
        $mensaje .= "procesar archivo Gestiones de Cobranza (" . count($lineas) . ")<br />";
        echo "procesar archivo Gestiones de Cobranza (" . count($lineas) . ")<br />";
    }
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);
        if ($registro[0] != "") {
            $registro = Array(
                "id_inmueble" => $registro[0],
                "apto" => $registro[1],
                "telefono" => $registro[2],
                "contacto" => $registro[3],
                "resultado" => utf8_encode($registro[4]),
                "fecha_hora" => $registro[5],
                "usuario" => $registro[6],
                "tipo" => $registro[7],
                "sincronizado" => 1
            );
            $r = $gestion->insertar($registro);
            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "-" . $r['stats']['error'] . '<br/>' . $r['query'] . '<br/>');
            }
        }
    }
}
// </editor-fold>


if (MOROSO) {
    $mensaje = 'Estimado cliente, a la presenta fecha presenta '.RECIBOS_PENDIENTES.
            ' recibo(s) pendiente(s) de pago, por un monto de Bs.'.DEUDA.
            '<br>Si ya efectuó el pago correspondiente por favor enviar los datos '
            . 'del mismo, vía Whatsapp al 0424-9569266 o al correo electronico '
            . 'info@administracion-condominio.com.ve para actualizar nuestros registros.<br>';
    echo $mensaje;
}
$fecha = JFILE::read(ACTUALIZ."ACTUALIZACION.txt");

echo "****FIN DEL PROCESO DE ACTUALIZACION****<br />";
echo "Fecha: ".$fecha."<br/>";

$mail = new mailto(SMTP);
$r = $mail->enviar_email(NOMBRE_APLICACION." ".$fecha,$mensaje, "", 'sintesisoutsourcing@gmail.com');
        
if ($r=="") {
    echo "Email de confirmación enviado con éxito<br />";
} else {
    echo "Falló el envio del emailo de ejecución del proceso<br />";
}
echo "Cierre esta ventana para finalizar.";