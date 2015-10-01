<?php

session_start();
$titre = 'Chercher une ville';
require_once ("../ressources/core.php");

// if user is logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}

// If valide data
$nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
$codePostal = filter_input(INPUT_POST, 'codePostal', FILTER_VALIDATE_INT);
$codeDep = filter_input(INPUT_POST, 'departement', FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => getCodeDepartementRegex())));

// To prevent invalid data from option default value
if (isset($codeDep) && $codeDep <= 0) {
    $codeDep = null;
}

// Check form data
if (!$nom && !$codePostal && !$codeDep) {
    exit("Au moins un des champs doit être rempli");
}

$aChercher = array(
    "nom" => $nom,
    "codePostal" => $codePostal,
    "code_departement" => $codeDep
);

// search and display
$title = "Résultats de la recherche :";
$page = "update_ville.php";

$cities = search($aChercher);

displayCity($title, $page, $cities);

