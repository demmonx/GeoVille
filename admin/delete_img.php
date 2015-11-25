<?php

session_start();
require_once ("../ressources/core.php");

// check picture ID
$codePicture = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($codePicture) {
    $picture_info = getPictureInfo($codePicture);
}
if (!(isset($picture_info) && count($picture_info) > 0)) {
    exit("La photo n'existe pas");
}

// If user isn't logged
if (!loginOk($_SESSION)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}
// if file doesn't exist
if (!file_exists($picture_info['path'])) {
    exit("Fichier absent");
}

//remove file
unlink($picture_info['path']);

// remove file in BD
echo removePictureFromBD($codePicture) ? "Suppression réussie" : "Echec de suppression";
