<?php

require 'app.php';

function incluirTemplate( string $nombre, bool $inicio = false){
    include TEMPLATES_URL."/${nombre}.php";
}

function estadoAutenticado(): bool {
    session_start();

    $auth = $_SESSION['login'];

    if($auth) {
        return true;
    }
        return false;

   
}