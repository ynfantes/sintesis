<?php

/**
 * Description of junta_condominio
 *
 * @author emessia
 */
class junta_condominio extends db implements crud{
    const tabla = "junta_condominio";
    
    public function actualizar($id, $data) {
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return db::delete(self::tabla, array("id" => $id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function insertar($data) {
        return db::insert(self::tabla, $data);
    }

    public function listar() {
        return db::select("*", self::tabla);
    }

    public function ver($id) {
        return db::select("*",self::tabla,array("id"=>$id));
    }
    
    public function listarJuntaPorInmueble($id_inmueble) {
        $consulta = "SELECT p.cedula, p.nombre, p.telefono1, p.email, c.descripcion, pro.apto, pro.id_inmueble  
            FROM junta_condominio jc join propietarios p
            on jc.cedula = p.cedula
            join cargo_jc c on jc.id_cargo = c.id
            JOIN propiedades pro ON p.cedula = pro.cedula 
            where jc.id_inmueble ='".$id_inmueble."'
            ORDER BY c.id";
        
        return db::query($consulta);
    }
}

?>
