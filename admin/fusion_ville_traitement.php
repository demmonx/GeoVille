<?php

session_start();
require_once ("../ressources/function.php");

// if user is not logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}

// Check request data
$nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
$ville = filter_input(INPUT_POST, 'ville', FILTER_VALIDATE_INT,
    FILTER_REQUIRE_ARRAY);
if (!$nom && $ville) {
    exit("Les champs doivent être remplis");
}

// Test le nom de la ville
if (strlen($nom) > 100 || strlen(trim($nom)) < 2) {
    exit("Le nom de la commune doit être compris entre 2 et 100 caractères.");
}
// Fusion de trop ou pas assez de commune
if (count($ville) < 2 || count($ville) > 6) {
    exit("Fusion possible entre 2 et 6 communes uniquement");
}

// Par défaut toutes les villes sont valides
$villes_valides = true;
// On test si tous les IDs reçu sont valides
foreach ($ville as $city) {
    $villes_valides = $villes_valides && checkCity($city);
}

if (!$villes_valides) {
    exit("Au moins une des communes sélectionnées est invalide.");
}

echo mergeCity($nom, $ville) ? "Succès" : "Echec";


