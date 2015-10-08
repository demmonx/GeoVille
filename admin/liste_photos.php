<?php

@session_start();
require_once ("../ressources/core.php");

// If user isn't logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("<p>Vous devez vous connecter pour accéder à cette partie.</p>");
}

// check city ID
$cityCode = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($cityCode) {
    $ville_id = $cityCode;
}
if (isset($ville_id) && is_numeric($ville_id)) {
    $city_informations = getCityInfo($ville_id);
}

// check if it's a valid city
if (!isset($city_informations) || count($city_informations) <= 0) {
    exit("Erreur, aucunne donnée trouvée pour cette ville...");
}
// get the photos
$imageAboutThisCity = getPicturesFromDB($ville_id);
if ($imageAboutThisCity == null || count($imageAboutThisCity) <= 0) {
    exit("Pas de photos pour la ville");
}
displayPictureOption($imageAboutThisCity);

echo "<script type='text/javascript'>";
require ('liste_photos.js');
echo "</script>";
