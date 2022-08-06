<?php
/**
 * Description of cobranza
 *
 * @author Valoriza2
 */
class cobranza extends db implements crud {
    public $tabla = "gestion_cobranza";
    
    public function actualizar($id, $data) {
        return $this->update($this->tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return $this->delete($this->tabla, array("id" => $id));
    }

    public function borrarTodo() {
        return $this->delete($this->tabla);
    }

    public function insertar($data) {
        $resultado = array();
        $resultado = $this->insert($this->tabla, $data);
        if ($resultado['suceed']) {
            $resultado['mensaje'] = 'Gestión de cobranza registrada con éxito';
        } else {
            $resultado['mensaje'] = 'Ocurrió un error mientras se registraba la gestión.';
        }
        return $resultado;
    }

    public function listar() {
        return $this->select("*", $this->tabla,Array("eliminar"=>0));
    }

    public function ver($id) {
        return $this->select("*",$this->tabla,array("id"=>$id));
    }
    
    public function listarGestionesPorPropiedad($id_inmueble,$apto) {
        return $this->select('*', $this->tabla, array("id_inmueble"=>$id_inmueble,"apto"=>$apto),'',array("fecha_hora"=>"DESC"));
    }
    
    public function listarGestionesPorSincronizar() {
        return $this->select('*', $this->tabla, array("sincronizado"=>0),'',array("fecha_hora"=>"DESC"));
    }
}
