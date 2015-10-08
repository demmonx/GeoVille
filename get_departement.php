<?php

require_once ("ressources/core.php");
// Récupère la valeur passé en paramètre
$codeDep = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($codeDep) {
    $listeDep = getDepartementFromRegion($codeDep);
} else {
    $listeDep = getDepartement();
}

displayInputOptionDepartement($listeDep);
