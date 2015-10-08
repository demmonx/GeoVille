<?php

session_start();
require_once ("../ressources/core.php");

// if user is logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}

// check the name
$name = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$name) {
    exit("Titre invalide");
} else
if (strlen($name) > 100) {
    exit("Le titre doit faire moins de 100 caractères");
}

// check picture ID
$codePicture = filter_input(INPUT_POST, 'code', FILTER_VALIDATE_INT);
if ($codePicture) {
    $picture_info = getPictureInfo($codePicture);
}

if (!(isset($picture_info) && count($picture_info) > 0)) {
    exit("<p class='error'>Pas de photos pour la ville</p>");
}


// call the request
$ok = updatePictureName($codePicture, $name);
echo $ok ? "Succès" : "Echec de mise à jour";

