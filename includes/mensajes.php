<?php
/**
 * Description of mensajes
 *
 * @author Edgar
 */
class mensajes extends db implements crud {
    const tabla = "mensajes";
    
    public function actualizar($id, $data){
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return db::delete(self::tabla, array("id" => $id));
    }

    public function insertar($data){
        return db::insert(self::tabla, $data);
    }

    public function listar(){
       return db::select("*", self::tabla);
    }
    
    public function ver($id){
        return db::select("*",self::tabla,array("id"=>$id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }
    
    public function mostrarMensajesPorPropietario($cedula,$start=0,$limit=30) {
        if ($start < 0) {
                $start = 0;
        }
        if ($limit < 1) {
                $limit = 1;
        }	
        //LIMIT " . (int)$start . "," . (int)$limit
        $sql = "select *, contenido as msg from mensajes where cedula=".(int)$cedula ." and eliminado = 0 order by fecha desc";
        
        return db::dame_query($sql);
    }
    
    public function obtenerTotalMensajes() {
        $sql = "select count(*) as total from mensajes where cedula=".(int)$_SESSION['usuario']['cedula']." and eliminado=0";
        $result =db::dame_query($sql);
        return $result['row']['total'];
    }
    
    public function cantidadMensajesSinLeerPorPropietario($cedula) {
        $total = 0;
        $resultado = db::select("*",  self::tabla,Array("cedula"=>$cedula,"leido"=>false));
        if ($resultado['suceed']) {
            $total = count($resultado['data']);
        }
        return $total;
    }

}

?>
