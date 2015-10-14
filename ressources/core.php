<?php

/*
 * Recherche de tous les fichiers de fonction disponibles
 */
foreach (scandir("ressources") as $filename) {
    $path = "ressources/" . $filename;
    // on inclut tous les fichiers en .inc.php
    if (is_file($path) && strstr($filename, ".inc.php")) {
        require_once $path;
    }
}

