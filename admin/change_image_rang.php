<?php

session_start();
require_once ("../ressources/core.php");

// Check sens
$sens = filter_input(INPUT_GET, 'sens', FILTER_VALIDATE_BOOLEAN,
    FILTER_NULL_ON_FAILURE);
if (!isset($sens)) {
    exit("Sens invalide");
}

// check picture ID
$codePicture = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($codePicture) {
    $picture_info = getPictureInfo($codePicture);
}

if (!(isset($picture_info) && count($picture_info) > 0)) {
    exit("<p class='error'>Pas de photos pour la ville</p>");
}

// If user isn't logged
if (!loginOk($_SESSION)) {
    exit("<p>Vous devez vous connecter pour accéder à cette partie.</p>");
}
// call the request
$ok = changePictureOrder($codePicture, $sens);
echo $ok ? "Succès" : "Echec de mise à jour";
