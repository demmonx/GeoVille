<?php

/** Retourne le contenu du fichier de configuration */
function getConfigFile() {
    $filename = getcwd() . "/ressources/config.ini";
    return file_exists($filename) ? parse_ini_file($filename) : null;
}
