<?php

function getRootPath() {
    $path = getcwd();
    if (strstr($path, "/admin"))
            $path = substr($path, 0, strlen($path) - strlen("/admin"));
    return $path;
}

/*
 * Recherche de tous les fichiers de fonction disponibles
 */
foreach (scandir(getRootPath() . '/ressources') as $filename) {
    $path = getRootPath() . "/ressources/" . $filename;
    // on inclut tous les fichiers en .inc.php
    if (is_file($path) && strstr($filename, ".inc.php")) {
        require_once $path;
    }
}
/*
require "ressources/function.inc.php";
require "ressources/function_config.inc.php";
require "ressources/function_picture.inc.php";
require "ressources/function_search_engine.inc.php";
require "ressources/function_ville.inc.php";
require "ressources/function_wiki.inc.php";*/

