<?php
include 'mailto.php';
// se envia el email de confirmación
$ini = parse_ini_file('emails.ini');
$mail = new mailto();

$propietario = "edgar";
$forma_pago = 'DEPOSITO';

$mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_RECEPCION_CONFIRMACION'], 
        $propietario,
        $forma_pago,
        'numero_documento',
        'banco_destino',
        'cuenta_destino',
        'monto',
        'fecha_documento',
        '',
        '');

$mensaje.= $ini['PIE_MENSAJE_PAGO_RECEPCION_CONFIRMACION'];

$r = $mail->enviar_email("Pago de Condominio", $mensaje, "", "ynfantes@gmail.com");

if ($r=="") {
    echo "mensaje enviado con exito";
} else {
    echo "fallo durante el envio";
}
?>
