<?php


function conectarDB() {
    $db = mysqli_connect('localhost', 'root', '', 'bienesraices'); /* Para conectar colocar el servidor, usuario, contraseña y la base de datos */

    if(!$db){
        echo "Error no se pudo conectar";
        exit;
    }
    
    return $db;
}   