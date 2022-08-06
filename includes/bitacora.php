<?php

class bitacora extends db implements crud {
    const tabla = "bitacora";
    
    public function actualizar($id, $data) {
         return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return db::delete(self::tabla, array("id" => $id));
    }

    public function borrarTodo() {
        //return db::delete(self::tabla);
        return false;
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
    
    public function obtenerBitacoraPorPropietario($cedula, $start=0, $limit=10) {
        if ($start < 0) {   
            $start = 0;
        }
        $limit = $start + $limit;
        if ($limit < 1) {
            $limit = 1;
        }	
        //LIMIT " . (int)$start . "," . (int)$limit
        $sql = "select b.*, a.descripcion as nombre, a.id
            from bitacora b join accion a on b.id_accion = a.id where b.id_sesion in 
            (select id from sesion where cedula=".(int)$cedula .") order by id_sesion DESC, fecha ASC 
             limit $start,$limit";
        
        return db::dame_query($sql);
    }
    
    public function totalRegistrosBitacora($cedula) {
        $sql = "select count(b.id_sesion) as total from bitacora b 
            join accion a on b.id_accion = a.id where b.id_sesion in (select id from sesion where cedula=".(int)$cedula .")";
        $result = db::dame_query($sql);
        
        return $result['row']['total'];
    }
}

?>
