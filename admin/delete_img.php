<?php

session_start();
require_once ("../ressources/core.php");

$picture_info = null;

// check picture ID
$codePicture = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($codePicture) {
    $picture_info = getPictureInfo($codePicture);
}
if (!(isset($picture_info) && count($picture_info) > 0)) {
    exit("<p class='error'>Pas de photos pour la ville</p>");
}

// If user isn't logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("<p>Vous devez vous connecter pour accéder à cette partie.<br />");
}
// if file doesn't exist
if (!file_exists($picture_info['path'])) {
    exit("Fichier absent");
}

//remove file
unlink($picture_info['path']);

// remove file in BD
removePictureFromBD($codePicture) ? "Suppression réussie" : "Echec de suppression";
