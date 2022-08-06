<?php

/**
 * Interfaz para la interacción con la base de datos
 * @author anyul
 */
interface crud {
    function ver($id);
    function insertar($data);
    function borrar($id);
    function actualizar($id, $data);
    function listar();
    function borrarTodo();
}

