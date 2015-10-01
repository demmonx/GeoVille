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
echo "<table>";

// Génere le tableau résultat
for ($z = 0; $z < count($cities); $z ++) {
    if ($z % NB_COL == 0) {
        echo "<tr>\n";
    }
    echo "\t<td><input type='checkbox' name='ville[]' value='" . $cities[$z]["code"] . "' />\n " .
    $cities[$z]["nom"] . "<td>\n";
    if ($z % NB_COL == NB_COL - 1) {
        echo "</tr>\n";
    }
}

// On finit par mettre des cases propres à la fin
$val = $z % NB_COL;
while ($val > 0 && $val <= NB_COL) {
    echo $val < NB_COL ? "\t<td></td>\n" : "</tr>\n";
    $val ++;
}
echo "</table>";
