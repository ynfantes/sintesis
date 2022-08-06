<?php

class cartelera extends db implements crud {
    public $tabla = "cartelera_general";
    
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
        return $this->insert($this->tabla, $data);
    }

    public function listar() {
        return $this->select("*", $this->tabla,Array("eliminar"=>0));
    }
    
    public function listarCarteleraInmueble($inmueble) {
        return $this->select("*", $this->tabla,Array("eliminar"=>0,"inmueble"=>$inmueble));
    }
    
    public function ver($id) {
        return $this->select("*",$this->tabla,array("id"=>$id));
    }
            
}
