<?php

// TODO Fusionner ce fichier avec liste ville de l'admin
$titre = "Villes de France";
require_once ("ressources/header.php");
require_once ("ressources/core.php");
define("NB_COL", 4);
$codeDep = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_REGEXP,
    array("options" => array("regexp" => getCodeDepartementRegex())));
if ($codeDep) {
    $cities = getCityFromDepartement($codeDep);
}

// Cas d'échec
if (!isset($cities) || count($cities) <= 0) {
    exit("Aucune ville ne correspond à ce département</body></html>");
} // else département ok

$pattern = "<a href='ville.php?code={code}'>{nom}</a>";
$cities = getCityFromDepartement($codeDep);
displayCityByColumn($cities, NB_COL, $pattern);
