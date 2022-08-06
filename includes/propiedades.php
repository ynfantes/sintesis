
<?php
/**
 * Clase que mantiene la tabla propiedades
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */

class propiedades extends db implements crud {
    const tabla = "propiedades";

    public function actualizar($id, $data){
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return db::delete(self::tabla, array("id" => $id));
    }

    /**
     * Inserta el contenido en la tabla propietarios
     *
     * @param	Array	$data	Arreglo con la data
     * 
     * @return	Array	Retorna arreglo con parámetos del resultado
     * @since	1.0
     */
    public function insertar($data){
        return db::insert(self::tabla, $data);
    }

    public function listar(){
       return db::select("*", self::tabla);
    }
    
    public function ver($cedula){
        return db::select("*",self::tabla,array("cedula"=>$cedula));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function propiedadesPropietario($cedula) {
        
        $result = db::query("select * from propiedades where cedula = ".$cedula);
        return $result;
    }
    
    public function inmueblePorPropietario($cedula) {
        return db::query("SELECT id_inmueble FROM propiedades WHERE cedula =".$cedula. " order by id_inmueble");
    }
    
    public function verPropiedad($inmueble, $apto) {
        return db::dame_query("select * from ".self::tabla." where id_inmueble='$inmueble' and apto='$apto'");
    }
}