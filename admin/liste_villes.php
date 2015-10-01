<?php

session_start();
require_once ("../ressources/core.php");

// if user is not logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}
define("NB_COL", 4);
// check city ID
$codeDep = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_REGEXP,
    array("options" => array("regexp" => getCodeDepartementRegex())));

if ($codeDep) {
    $cities = getCityFromDepartement($codeDep);
}

// Cas d'échec
if (!isset($cities) || count($cities) <= 0) {
    exit("Aucune ville ne correspond à ce département");
} // else département ok
echo "<h2>Sélectionner les villes à fusionner (maximum 6) :</h2>";
$pattern = "<input type='checkbox' name='ville[]' value='{code}' /> {nom}";
displayCityByColumn($cities, NB_COL, $pattern);
