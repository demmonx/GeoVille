<?php

require_once ("ressources/core.php");
// Récupère la valeur passé en paramètre
$codeDep = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($codeDep) {
    $listeDep = getDepartementFromRegion($codeDep);
} else {
    $listeDep = getDepartement();
}

// Generate the list
if ($listeDep != null && count($listeDep) > 0) {
    echo "\t<option selected></option>\n";
    foreach ($listeDep as $dep) {
        echo "\t<option value='" . $dep["code"] . "'>" . $dep["code"] . " - " . $dep['nom'] . "</option>\n";
    }
} else {
    echo "\t<option>Région invalide</option>\n";
}