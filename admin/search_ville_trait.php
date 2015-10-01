<?php

session_start();
$titre = 'Chercher une ville';
require_once ("../ressources/function.php");
require_once ("../ressources/function_display.php");

// if user is logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}

// If valide data
$nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
$codePostal = filter_input(INPUT_POST, 'codePostal', FILTER_VALIDATE_INT);
$codeDep = filter_input(INPUT_POST, 'departement', FILTER_VALIDATE_REGEXP,
    array("options" => array("regexp" => getCodeDepartementRegex())));

// Check form data
if (!$nom && !$codePostal && !$codeDep) {
    exit("Au moins un des champs doit être rempli");
}

// partial request
$sql = "";

// to do research with invalid or empty data
// store data -> add request fragment
if ($nom) {
    $sql .= " AND ville_nom LIKE UPPER('%" . strtoupper($nom) . "%') ";
}
if ($codePostal) {
    $sql .= " AND ville_code_postal LIKE '%" . $codePostal . "%'";
}
if ($codeDep) {
    $sql .= " AND ville_departement = '" . $codeDep . "'";
}
// search and display
$title = "Résultats de la recherche :";
$page = "update_ville.php";

displayCityBySQL($title, $page, $sql);

