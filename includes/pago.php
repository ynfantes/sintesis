<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pago
 *
 * @author emessia
 */
class pago extends db implements crud {
    const tabla = "pagos";

    public function actualizar($id, $data){
        return $this->update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return $this->delete(self::tabla, array("id" => $id));
    }

    /**
     * Inserta el contenido en la tabla pagos
     *
     * @param	Array	$data	Arreglo con la data
     * 
     * @return	Array	Retorna arreglo con parámetos del resultado
     * @since	1.0
     */
    public function insertar($data){
        return $this->insert(self::tabla, $data);
    }
    public function insertarDetallePago($data) {
        return $this->insert("detalle_pago", $data);
    }
    public function listar(){
       return $this->select("*", self::tabla);
    }
    
    public function ver($id){
        return $this->select("*",self::tabla,array("id"=>$id));
    }

    public function borrarTodo() {
        return $this->delete(self::tabla);
    }
    
    public function pagoYaRegistrado($data) {
        return $this->select("*", self::tabla, Array(
            "tipo_pago"         => $data['tipo_pago'],
            "numero_documento"  => $data['numero_documento'],
            "numero_cuenta"     => $data['numero_cuenta'],
            "banco_destino"     => $data['banco_destino']));
    }

    public function listarPagosPendientes($inmueble=null){
        if ($inmueble=='sac') {
            $condicion = Array("estatus"=>"'p'");
        } else  {
            $condicion = Array("estatus"=>"p","d.id_inmueble"=>$inmueble);
        }
        return $this->select("distinct p.*", "pagos p join pago_detalle d on p.id = d.id_pago", $condicion,null,Array("d.id_inmueble"=>"ASC"));
        //return $this->select("*", self::tabla, Array("estatus"=>"'p'"));
    }
    
    public function detallePagoPendiente($id_pago) {
        return $this ->select("*", "pago_detalle", Array("id_pago"=>$id_pago),null,array("id"=>"ASC"));
    }

    public function detalleTodosPagosPendientes() {
        $query = "select * from pago_detalle where id_pago in 
            (select id from pagos where estatus='p')";
        return $this->dame_query($query);
        
    }
    
    public function registrarPago($data) {
        $resultado = Array();
        $this->exec_query("START TRANSACTION");
        try {
            $res = $this->pagoYaRegistrado($data);
            
            if ($res['suceed'] && !count($res['data']) > 0 ) {
                
                $pago_detalle = Array();
                $pago_detalle['id_factura']     = $data['facturas'];
                $pago_detalle['id_inmueble']    = $data['id_inmueble'];
                $pago_detalle['apto']           = $data['id_apto'];
                $pago_detalle['monto']          = $data['montos'];
                $pago_detalle['periodo']        = $data['periodo'];
                $pago_detalle['id']             = $data['id'];
                
                if (isset($data['id_usuario'])) {
                    $id_usuario = $data['id_usuario'];
                    unset($data['id_usuario']);
                }
                if (isset($data['honorarios'])) {
                    $honorarios = $data['hono_monto'];
                    $pago_detalle['id_factura'][]   = $data['hono_fact'];
                    $pago_detalle['id_inmueble'][]  = $data['hono_inm'];
                    $pago_detalle['apto'][]         = $data['hono_apto'];
                    $pago_detalle['monto'][]        = Misc::format_mysql_number($data['honorarios']);
                    $pago_detalle['periodo'][]      = $data['hono_periodo'];
                    $pago_detalle['id'][]           = $data['hono_id'];
                    
                    unset($data['honorarios'],
                            $data['hono_inm'],
                            $data['hono_apto'],
                            $data['hono_fact'],
                            $data['hono_periodo'],
                            $data['hono_monto'],
                            $data['hono_id']);
                }
                unset($data['facturas'], $data['montos'], $data['id_inmueble'], $data['id_apto'],$data['periodo'],$data['id']);
                
                $data['fecha_documento'] = Misc::format_mysql_date($data['fecha_documento']);
                $data['monto'] = Misc::format_mysql_number($data['monto']);
                $data['tipo_pago'] = strtoupper($data['tipo_pago']);
                $data['banco_destino'] = strtoupper($data['banco_destino']);
                
                if (isset($data['banco_origen'])) {
                    $data['banco_origen'] = strtoupper($data['banco_origen']);
                }
                
                $data['numero_cuenta'] = str_replace(" ", "", $data['numero_cuenta']);
                
                $resultado['pago'] = $this->insertar($data);
                unset($resultado['pago']['query']);
                if ($resultado['pago']['suceed']) {

                    $id_pago = $resultado['pago']['insert_id'];
                    $resultado['pago_detalle'] = Array();
                    
                    if (isset($id_usuario)) {
                        $r = $this->insert("pago_usuario", Array("id_usuario"=>$id_usuario,"id_pago"=>$id_pago));
                    }
                    if (isset($honorarios)) {
                        $r = $this->insert("pago_honorarios",array(
                            "id_pago"       => $id_pago,
                            "honorarios"    => $honorarios));
                    }
                    
                    for ($i = 0; $i < count($pago_detalle['id']); $i++) {
                        
                        $j = (int)$pago_detalle['id'][$i];
                        
                        $resultado_detalle = $this->insert("pago_detalle", Array(
                            "id_pago"       => $id_pago, 
                            "id_factura"    => $pago_detalle['id_factura'][$j],
                            "id_inmueble"   => $pago_detalle['id_inmueble'][$j],
                            "id_apto"       => $pago_detalle['apto'][$j],
                            "monto"         => $pago_detalle['monto'][$j],
                            "periodo"       =>  Misc::format_mysql_date($pago_detalle['periodo'][$j])));
                        
                        unset($resultado_detalle['query']);
                        array_push($resultado['pago_detalle'], $resultado_detalle);

                        $resultado['suceed'] = $resultado_detalle['suceed'];
                        
                        if (!$resultado_detalle['suceed']) {
                            $resultado['mensaje'] = "Ha ocurrido un error al procesar el pago";
                        } else {
                            $this->exec_query("COMMIT");
                            $resultado['suceed'] = true;
                            $resultado['mensaje'] = "Pago proceso con éxito! En un plazo máximo de 48 horas será aplicado a su cuenta de condominio.";
                            // se envia el email de confirmación
                            if (ENTORNO==='PRODUCCION') {
                                $ini = parse_ini_file('emails.ini');
                                $mail = new mailto(SMTP);
                                $propietario = $_SESSION['usuario']['nombre']=='cajerocaracas'? 'Propietario(a)':$_SESSION['usuario']['nombre'];
                                $forma_pago = $data['tipo_pago']=='D'? 'DEPOSITO':'TRANSFERENCIA';
                                $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_RECEPCION_CONFIRMACION'], 
                                        $propietario,
                                        $forma_pago,
                                        $data['numero_documento'],
                                        $data['banco_destino'],
                                        $data['numero_cuenta'],
                                        Misc::number_format($data['monto']),
                                        Misc::date_format($data['fecha_documento']),
                                        $data['email'],'');
                                $mensaje.=$ini['PIE_MENSAJE_PAGO'];
                                $r = $mail->enviar_email("Pago de Condominio", $mensaje, "", $data['email']);
                                if ($r=="") {
                                    $this->actualizar($id_pago, Array("enviado"=>1));
                                } else {
                                    $resultado['envio']=$r;
                                }
                            } else {
                                $resultado['envio'] = '';
                            }
                        }
                        
                    }
                } else {
                    $resultado = $resultado['pago'];
                    $this->exec_query("ROLLBACK");
                    $resultado['mensaje'] = "Error mientras se procesaba el pago maestro.";
                }
            } else {
                $resultado['suceed'] = false;
                $resultado['mensaje'] = "Estimado propietario:\n\nEste pago ya fue registrado con anterioridad, en fecha ".  Misc::date_format($res['data'][0]['fecha'].".");
                if ($res['data'][0]['estatus']=='p') {
                    $resultado['mensaje'].= "\nActualmente está pendiente de ser aplicado a su cuenta.";
                }
                if ($res['data'][0]['estatus']=='a') {
                    $resultado['mensaje'].= "\nEL pago ya fue aplicado a su cuenta.";
                }
                if ($res['data'][0]['estatus']=='a') {
                    $resultado['mensaje'].= "\nEl pago ya fue rechazado. Si considera que es un error nuestro, escríbanos a info@sintesisonline.com.ve";
                }
            }
        } catch (Exception $exc) {
            $resultado['suceed'] = false;
            $this->exec_query("ROLLBACK");
            $resultado['mensaje'] = "Error inesperado, contacte con el administrador del sistema";
            echo $exc->getTraceAsString();
        }
        return $resultado;
     }

    public function procesarPago($id,$estatus) {
        
        $this->actualizar($id, array("estatus"=>$estatus));
        $r = $this->ver($id);
        if ($r['suceed']==true) {
            if (count($r['data'])>0) {
                $tipo_pago = $r['data'][0]['tipo_pago'] == 'D' ? "DEPOSITO" : "TRANSFERENCIA";
                $data = Array(
                    "administradora"=>TITULO,
                    "forma_pago"=>$tipo_pago,
                    "numero_documento"=>$r['data'][0]['numero_documento'],
                    "banco"=>$r['data'][0]['banco_destino'],
                    "cuenta"=>$r['data'][0]['numero_cuenta'],
                    "monto"=>$r['data'][0]['monto'],
                    "fecha"=>$r['data'][0]['fecha'],
                    "email"=>$r['data'][0]['email']
                    );
                return $this->enviarEmailPagoProcesado($id, $estatus,$data);
            }
            
        }
        return "Falló";
        
    }
    
    public function enviarEmailPagoRegistrado($id) {
        $data = $this->ver($id);
        if ($data['suceed'] == TRUE && count($data['data'])>0) {
            $ini = parse_ini_file('emails.ini');
            $mail = new mailto(SMTP);
            $propietario = 'Propietario(a)';
            $forma_pago = $data['data'][0]['tipo_pago']='D'? 'DEPOSITO':'TRANSFERENCIA';
            $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_RECEPCION_CONFIRMACION'], 
                    $propietario,
                    $forma_pago,
                    $data['data'][0]['numero_documento'],
                    $data['data'][0]['banco_destino'],
                    $data['data'][0]['numero_cuenta'],
                    Misc::number_format($data['data'][0]['monto']),
                    Misc::date_format($data['data'][0]['fecha_documento']),
                    $data['data'][0]['email'],'');
                    $mensaje.=$ini['PIE_MENSAJE_PAGO'];
            
            $r = $mail->enviar_email("Pago de Condominio", $mensaje, "", $data['data'][0]['email']);

            if ($r=="") {
                $this->actualizar($id, Array("enviado"=>1));
                echo "Email enviado a ".$data['data'][0]['email']." Ok!";
            } else {
                echo($r);
            }
        } else {
            echo 'No se consigue la informaci&oacute;n del pago ID: '.$id;
        }
    }
    
    public function enviarEmailPagoProcesado($id,$estatus,$data) {
        $ini = parse_ini_file('emails.ini');
        $mail = new mailto(SMTP);
        
        $s = $estatus=='A' ? "CONFIRMACION" : "RECHAZO";
        $m = $estatus=='A' ? "CONFIRMACION" : "RECHAZO";
        $destinatario = $data['email'];
        $subject = sprintf($ini['ASUNTO_MENSAJE_PAGO_PROCESADO_'.$s]);
        $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_PROCESADO_'.$m],
                $data['administradora'],
                $data['forma_pago'],
                $data['numero_documento'],
                $data['banco'],
                $data['cuenta'],
                Misc::number_format($data['monto']),
                Misc::date_format($data['fecha']),
                $ini['CUENTA_PAGOS']
                );
        
        //$adjunto="";
        $can = array();
        
        // <editor-fold defaultstate="collapsed" desc="si el pago es aplicado">
        if ($estatus == 'A') {

            $r = $this->detallePagoPendiente($id);
            
            if ($r['suceed'] == true) {
                if (count($r['data']) > 0) {
                    $n = 0;
                    foreach ($r['data'] as $factura) {
                        
                        //$con = $adjunto == "" ? "" : ",";
                        $factura = '../../cancelacion.gastos/' . $factura["id_factura"] . '.pdf';
                        $factura = realpath($factura);
                        if ($factura != "") {
                            $n = $n + 1;
                            $can[] = $factura;
                        }
                        //$adjunto.= $con . $factura;
                    }
                    
                    if ($n < count($r['data'])) {
                        $mensaje.="<br>Falta(ron) factura(s) por adjuntar.";
                    } else {
                        $mensaje.= "Hemos adjuntado " . $n . " factura(s).";
                    }
                }
            }
        }// </editor-fold>
        
        $mensaje.= $ini['PIE_MENSAJE_PAGO'];
        
        $r = $mail->enviar_email($subject, $mensaje, "", $destinatario,"",$can);
        
        if ($r=="") {
            $this->actualizar($id, Array("confirmacion"=>1));
            return "Ok";
        }
        return "Falló";
        
    }
    
    public static function listarPagosProcesados($inmueble, $apartamento, $n) {
        $consulta = "select p.fecha, d.* from pagos p join pago_detalle d on 
            p.id = d.id_pago where d.id_inmueble='".$inmueble."' and id_apto='".$apartamento."' 
               and p.estatus='a' order by d.periodo desc LIMIT 0,".$n;
        return db::query($consulta);
    }

    public static function detalleCancelacionDeGastos($id_factura) {
         $consulta = "select f.*, d.*, i.nombre_inmueble, pro.nombre, p.alicuota
            from facturas f join factura_detalle d on f.numero_factura =
            d.id_factura join inmueble i on i.id = f.id_inmueble 
            JOIN propiedades p ON p.id_inmueble = i.id
            AND p.apto = f.apto
            JOIN propietarios pro ON pro.cedula = p.cedula
            where f.numero_factura ='".$id_factura."' order by d.codigo_gasto ";
        
        return db::query($consulta);
    }
    
    public static function numeroRecibosCanceladosPorPropitario($cedula) {
        $sql = "select count(p.id) as cantidad from pagos as p join pago_detalle as d on d.id_pago = p.id
            join propiedades as pro on pro.id_inmueble = d.id_inmueble and pro.apto = d.idapto
            where estatus='A' and pro.cedula=".$cedula;
        
        return db::query($sql);
    }
    
    public static function facturaPendientePorProcesar($periodo,$inmueble,$apto) {
        $sql = "SELECT * FROM pagos p join pago_detalle pd on p.id = pd.id_pago where p.estatus='p' and pd.periodo='".$periodo."' and id_inmueble='".$inmueble."' and id_apto='".$apto."'";
        $r = db::query($sql);
        if ($r['suceed']) {
            if (count($r['data'])>0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
        
    }
    
    public static function listarPagosProcesadosWeb() {
        $consulta = "select d.id_factura,d.id_inmueble,d.id_apto from pagos p join pago_detalle d on p.id = d.id_pago "
                . "where p.estatus='a' and left(d.id_factura,1)='0' and d.id_inmueble in "
                . "(select id from inmueble) order by d.id_inmueble, d.id_apto, d.periodo DESC";
        
        return db::query($consulta);
    }
    
    public function listarCancelacionDeGastos($inmueble, $apartamento) {
        return $this->select("*,CONCAT('01-', periodo ) AS fPeriodo", "cancelacion_gastos",Array("id_inmueble"=>$inmueble,"id_apto"=>$apartamento),null,Array("fecha_movimiento"=>"DESC"));
    }
    
    public function insertarCancelacionDeGastos($data) {
        return db::insert("cancelacion_gastos",$data);
    }
    
    public function cancelacionExisteEnBaseDeDatos($cancelacion) {
        $cancelacion = str_replace(".pdf","",$cancelacion);
        $query = "select numero_factura from cancelacion_gastos where numero_factura='".$cancelacion."'";
        $r=0;
        $result = $this->dame_query($query);
        if ($result['suceed']==true) {
            if (count($result['data'])>0) {
                $r=1;
            }
        }
        return $r;       
    }
}