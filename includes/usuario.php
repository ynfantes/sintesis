<?php
class usuario extends db implements crud  {
    
    const tabla = "usuarios";
    
    public function actualizar($id, $data) {
        return $this->update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return $this->delete(self::tabla, array("id" => $id));
    }

    public function borrarTodo() {
         return $this->delete(self::tabla);
    }

    public function insertar($data) {
        return $this->insert(self::tabla, $data);
    }

    public function listar() {
        return $this->select("*", self::tabla);
    }

    public function ver($id) {
        return $this->select("*",self::tabla,array("id"=>$id));
    }   
    
    public function seleccionarUsuario($condicion=null) {
        return $this->select("*",self::tabla,$condicion);
    }
    
    public function login($usuario,$password) {
        
        if ($usuario!="" && $password!="") {
        
            $result = db::select("*",self::tabla,Array("nombre"=>"'".$usuario."'"));
            unset($result['query']);
            if ($result['suceed'] == 'true' && count($result['data']) > 0) {
                
                if ($result['data'][0]['clave']==$password) {
                    session_start();
                    $sesion = $this->generarIdInicioSesion($result['data'][0]['id']);
                    if ($sesion['suceed']) {
                        $_SESSION['id_sesion'] = $sesion['insert_id'];
                    } 
                    $_SESSION['usuario'] = $result['data'][0];
                    $_SESSION['status'] = 'logueado';
                    $result['directorio'] = $result['data'][0]['directorio'];
                    unset($result['data']);
                    //header("location:" . URL_INTRANET . "/" . $result['data'][0]['directorio'] );
                    
                } else {
                    unset($result['data']);
                    $result['suceed'] = false;
                    $result['error'] = "Contraseña inválida";
                    
                }
            } else {
                $result['suceed'] = false;
                $result['error'] = "Usuario no registrado.";
            }
            return $result;
        } else {
            $result['suceed'] = false;
            $result['error'] = "Nombre de usuario y/o contraseña requerida.";
            return $result;
        }
    }

    public static function esUsuarioLogueado() {
        session_start();
        if (!isset($_SESSION['status']) || $_SESSION['status'] != 'logueado' || !isset($_SESSION['usuario'])) {
            header("location:" . ROOT . "intranet.php");
            die();
        }
    }
    
    public static  function logout() {
        //session_start();
        if (isset($_SESSION['status'])) {
            unset($_SESSION['status']);
            unset($_SESSION['usuario']);
            session_unset();
            session_destroy();
            if (isset($_COOKIE[session_name()]))
                setcookie(session_name(), '', time() - 1000);
            header("location:" . ROOT . "intranet.php");
        }
    }
    
    public function generarIdInicioSesion($cedula) {
        $sql = "insert into sesion(cedula,inicio,fin) values(".$cedula.",now(),now())";
        return db::exec_query($sql);
    }
}