<?php

/** Retourne le chemin de la racine du projet */
function getRootPath() {
    $path = getcwd();
    if (strstr($path, "/admin"))
            $path = substr($path, 0, strlen($path) - strlen("/admin"));
    return $path;
}

/** Retourne le contenu du fichier de configuration */
function getConfigFile() {
    $filename = getRootPath() . "/ressources/config.ini";
    return file_exists($filename) ? parse_ini_file($filename) : null;
}
