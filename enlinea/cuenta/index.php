<?php
include_once '../../includes/constants.php';


propietario::esPropietarioLogueado();
        
$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";
$session = $_SESSION;

$inmuebles = new inmueble();
$propiedad = new propiedades();
$bitacora = new bitacora();

// <editor-fold defaultstate="collapsed" desc="promedio facturacion">

$factura = null;
$cobro = null;
$monto = 0;
$monto_c = 0;
$total = 0;
$total_c = 0;
$promedio_facturacion = 0;
$promedio_cobranza = 0;
$i = 0;
$n = 0;
$direccion_facturacion = "right";
$direccion_cobranza = "right";

//$propiedades = $propiedad->inmueblePorPropietario($_SESSION['usuario']['cedula']);
//
//if ($propiedades['suceed']) {
//    
//    $facturacion = $inmuebles->movimientoFacturacionMensual($propiedades['data'][0]['id_inmueble']);
//    if ($facturacion['suceed']) {
//
//        foreach ($facturacion['data'] as $r) {
//            $i++;
//            $direccion_facturacion = $r['facturado'] > $monto ? "up" : "down";
//
//            $monto = $r['facturado'];
//            $total += $monto;
//            $factura .= $factura != '' ? ',' : '';
//            $factura .= (int) $r['facturado'];
//                    }
//                    if ($i>0) {
//                        $promedio_facturacion = (int) ($total / $i);
//                    }
//    }
//
//    $cobranza = $inmuebles->movimientoCobranzaMensual($propiedades['data'][0]['id_inmueble']);
//    if ($cobranza['suceed']) {
//        
//        foreach ($cobranza['data'] as $c) {
//            $n++;
//            $direccion_cobranza = $c['monto'] > $monto_c ? "up" : "down";
//            $monto_c = $c['monto'];
//            $total_c += $monto_c;
//            $cobro .= $cobro != '' ? ',' : '';
//            $cobro .= (int)$c['monto'];
//        }
//        if ($n>0) {$promedio_cobranza = (int)($total_c / $n);}
//        
//    }
//    
//}

// </editor-fold>

switch ($accion) {
    
    // <editor-fold defaultstate="collapsed" desc="listar">
    case "listar":
    default :
        
        $facturas = new factura();

        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);

        $cuenta = Array();

        if ($propiedades['suceed'] == true) {


            foreach ($propiedades['data'] as $propiedad) {

                $bitacora->insertar(Array(
                    "id_sesion"=>$session['id_sesion'],
                    "id_accion"=>1,
                    "descripcion"=>$propiedad['id_inmueble']." - ".$propiedad['apto'],
                ));
                
                $inmueble = $inmuebles->ver($propiedad['id_inmueble']);
                $f = $facturas->estadoDeCuenta($propiedad['id_inmueble'], $propiedad['apto']);
                
                if ($f['suceed'] == true) {

                    for ($index = 0; $index < count($f['data']); $index++) {
                        $filename = "../avisos/" . $f['data'][$index]['numero_factura'] . ".pdf";
                        $f['data'][$index]['aviso'] = file_exists($filename);
                    }

                    $cuenta[] = Array("inmueble" => $inmueble['data'][0],
                        "propiedades" => $propiedad,
                        "cuentas" => $f['data']);
                }
            }
        }
        
        echo $twig->render('enlinea/cuenta/formulario.html.twig', array("session" => $session,
            "cuentas" => $cuenta,
            "movimiento_facturacion" => $factura,
            "promedio_facturacion" => $promedio_facturacion,
            "direccion_facturacion" => $direccion_facturacion,
            "promedio_cobranza" => $promedio_cobranza,
            "direccion_cobranza" => $direccion_cobranza
        ));

        break; // </editor-fold>

        
}
