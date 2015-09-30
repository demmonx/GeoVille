<?php

require_once ("ressources/function.php");
// If valide data
$nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
$codePostal = filter_input(INPUT_POST, 'codePostal', FILTER_VALIDATE_INT);
$codeDep = filter_input(INPUT_POST, 'departement', FILTER_VALIDATE_REGEXP,
    array("options" => array("regexp" => getCodeDepartementRegex())));
$codeReg = filter_input(INPUT_POST, 'region', FILTER_VALIDATE_INT);
$popMin = filter_input(INPUT_POST, 'popMin', FILTER_VALIDATE_INT);
$popMax = filter_input(INPUT_POST, 'popMax', FILTER_VALIDATE_INT);

// Check form data
if (!$nom && !$codePostal && !$codeDep && !$codeReg && !$popMin && !$popMax) {
    exit("Au moins un des champs doit être rempli");
}

// Check if population is correct
if ($popMin && $popMax) {
    $popValide = $popMin <= $popMax;
}

// partial request
$sql = "";

// to do research with invalid or empty data
// store data -> add request fragment
if ($nom) {
    $sql .= " AND ville_nom LIKE UPPER('%" . strtoupper($nom) . "%') ";
    //  $sql .= " AND to_tsvector('ville_nom')  @@ to_tsquery('" . strtoupper($nom) . "')";
}
if ($codePostal) {
    $sql .= " AND ville_code_postal LIKE '%" . $codePostal . "%'";
}
if ($codeDep) {
    $sql .= " AND ville_departement = '" . $codeDep . "'";
}
if ($codeReg) {
    $sql .= " AND R.num_region = '" . $codeReg . "'";
}
if ($popMin && !$popMax) {
    $sql .= " AND ville_population_2010 >= " . $popMin;
}
if ($popMax && !$popMin) {
    $sql .= " AND ville_population_2010 <= " . $popMax;
}
if (isset($popValide) && $popValide) {
    $sql .= " AND ville_population_2010 BETWEEN " . $popMin .
        " AND " . $popMax;
}
// search and display
$title = "Résultats de la recherche :";
$page = "ville.php";

displayCityBySQL($title, $page, $sql);

