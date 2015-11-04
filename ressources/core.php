<?php

// Chargement du fichier de config situé au bon endroit
if (!@include_once("ressources/function_config.inc.php")) {
    if (!@include_once("../ressources/function_config.inc.php")) {
        die("Impossible de charger les ressources");
    }
}

/*
 * Recherche de tous les fichiers de fonction disponibles
 * Importation de ceux qui ne sont pas déjà présents
 */
foreach (scandir(getRootPath() . '/ressources') as $filename) {
    $path = getRootPath() . "/ressources/" . $filename;
    // on inclut tous les fichiers en .inc.php
    if (is_file($path) && strstr($filename, ".inc.php")) {
        require_once $path;
    }
}

