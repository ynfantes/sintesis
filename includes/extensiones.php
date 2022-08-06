<?php
include_once dirname(dirname(dirname(__FILE__))).'/framework/twig/lib/Twig/ExtensionInterface.php';
include_once dirname(dirname(dirname(__FILE__))).'/framework/twig/lib/Twig/Extension.php';

class extensiones extends Twig_Extension {

    public function getName() {
        return 'MiExtension';
    }

    /**
     * Trunca un texto a una longitud determinada sin cortar las palabras y agrega puntos suspensivos
     * @param String $input texto a truncar
     * @param Integer $length longitud
     * @return String el texto truncado 
     */
    public static function trim_text($input, $length) {
        return misc::trim_text($input, $length);
    }

    public static function url_sortable($campo="id", $direccion="desc") {
        return misc::url_sortable($campo, $direccion);
    }
    public static function format_number($numero){
        return misc::number_format($numero);
    }
    public static function format_date($fecha){
        return misc::date_format($fecha);
    }
    public static function formato_periodo($id_factura) {
        return Misc::factura_a_periodo($id_factura);
    }

    public static function formato_date_periodo($periodo) {
        return Misc::date_periodo_format($periodo);
    }

    public function getFunctions() {
        return array(
            'format_date'=> new Twig_Function_Method($this, 'format_date'),
            'format_number'=>new Twig_Function_Method($this,'format_number'),
            'url_sortable' => new Twig_Function_Method($this, 'url_sortable'),
            'trim_text' => new Twig_Function_Method($this, 'trim_text'),
            'formato_periodo' => new Twig_Function_Method($this,'formato_periodo'),
            'formato_date_periodo'=> new Twig_Function_Method($this,'formato_date_periodo')
        );
    }

}