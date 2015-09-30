<?php

session_start();
require_once("../ressources/function.php");

// if user is not logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}
// read data
$codePostal = filter_input(INPUT_POST, 'codePostal', FILTER_VALIDATE_INT);
$code = filter_input(INPUT_POST, 'code', FILTER_VALIDATE_INT);
$population = filter_input(INPUT_POST, 'population', FILTER_VALIDATE_INT);
$superficie = filter_input(INPUT_POST, 'superficie', FILTER_VALIDATE_FLOAT);
$altMin = filter_input(INPUT_POST, 'altMin', FILTER_VALIDATE_INT);
$altMax = filter_input(INPUT_POST, 'altMax', FILTER_VALIDATE_INT);
$description = filter_input(INPUT_POST, 'description',
    FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$code || !$population || !$superficie || !isset($altMin) || !$altMax) {
    exit('Les champs doivent être remplis');
}

// Clear description
if (!$description) {
    $description = '';
}

$error = array();

// check from error
$error[0][0] = $codePostal > 0;
$error[0][1] = "Le code postal est invalide.";
$error[1][0] = $population > 0;
$error[1][1] = "Le nombre d'habitants est invalide.";
$error[2][0] = $superficie > 0;
$error[2][1] = "La superficie est invalide.";
$error[3][0] = $altMin >= 0;
$error[3][1] = "L'altitude minimale est invalide.";
$error[4][0] = $altMax > 0;
$error[4][1] = "L'altitude maximale est invalide.";
$error[5][0] = $altMax >= $altMin;
$error[5][1] = "L'altitude maximale doit être supérieure ou égale à l'altitude minimale";

// display error if there are
foreach ($error as $elem) {
    if (!$elem[0]) {
        exit($elem[1]);
    }
}

// calculate "densite"
$densite = floor($population / $superficie);

// update database
$param = array($codePostal, $population, $superficie, $densite, $altMin, $altMax,
    $description);

echo updateCity($code, $param) ? "Informations mise à jour avec succès !" : "Echec de mise à jour";
