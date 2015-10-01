<?php

require_once ("ressources/core.php");

// If valide data
$nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
$codePostal = filter_input(INPUT_POST, 'codePostal', FILTER_VALIDATE_INT);
$codeDep = filter_input(INPUT_POST, 'departement', FILTER_VALIDATE_REGEXP,
    array("options" => array("regexp" => getCodeDepartementRegex())));
$codeReg = filter_input(INPUT_POST, 'region', FILTER_VALIDATE_INT);
$popMin = filter_input(INPUT_POST, 'popMin', FILTER_VALIDATE_INT);
$popMax = filter_input(INPUT_POST, 'popMax', FILTER_VALIDATE_INT);

// To prevent invalid data from option default value
if (isset($codeDep) && $codeDep <= 0) {
    $codeDep = null;
}
if (isset($codeReg) && $codeReg <= 0) {
    $codeReg = null;
}

// Check form data
if (!$nom && !$codePostal && !$codeDep && !$codeReg && !$popMin && !$popMax) {
    exit("Au moins un des champs doit être rempli");
}

// Check if population is correct
if ($popMin <= $popMax) {
    $popValide = $popMin <= $popMax;
}

if (isset($popValide) && !$popValide) {
    exit("La population maximum doit être supérieure à la borne minimale");
}

$aChercher = array(
    "nom" => $nom,
    "codePostal" => $codePostal,
    "code_departement" => $codeDep,
    "code_region" => $codeReg,
    "popMin" => $popMin,
    "popMax" => $popMax
);
// partial request
$sql = "";

// to do research with invalid or empty data
// store data -> add request fragment
//if ($nom) {
//    $sql .= " AND ville_nom LIKE UPPER('%" . strtoupper($nom) . "%') ";
//    //  $sql .= " AND to_tsvector('ville_nom')  @@ to_tsquery('" . strtoupper($nom) . "')";
//}
//if ($codePostal) {
//    $sql .= " AND ville_code_postal LIKE '%" . $codePostal . "%'";
//}
//if ($codeDep) {
//    $sql .= " AND ville_departement = '" . $codeDep . "'";
//}
//if ($codeReg) {
//    $sql .= " AND R.num_region = '" . $codeReg . "'";
//}
//if ($popMin && !$popMax) {
//    $sql .= " AND ville_population_2010 >= " . $popMin;
//}
//if ($popMax && !$popMin) {
//    $sql .= " AND ville_population_2010 <= " . $popMax;
//}
//if (isset($popValide) && $popValide) {
//    $sql .= " AND ville_population_2010 BETWEEN " . $popMin .
//        " AND " . $popMax;
//}
// search and display
$title = "Résultats de la recherche :";
$page = "ville.php";
$cities = search($aChercher);

displayCity($title, $page, $cities);

