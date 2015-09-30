<?php

session_start();
require_once ("../ressources/function.php");

// if user is logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}

// If valide data
$title = filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$code = filter_input(INPUT_POST, 'code', FILTER_VALIDATE_INT);
$poids_max = 1000000; // Poids max de l'image en octets
// Cas d'erreur
if (!isset($_FILES['fichier']) || !$title || !$code) {
    exit("Les champs doivent être remplis");
} else if (strlen($title) > 100) {
    exit("Le titre doit faire moins de 100 caractères");
}

// Vérification du code de la commune
$city = getCityInfo($code);
if ($city == null || count($city) <= 0) {
    exit("Identifiant de ville invalide");
} else if ($_FILES['fichier']['type'] != 'image/png' &&
    $_FILES['fichier']['type'] != 'image/jpeg' &&
    $_FILES['fichier']['type'] != 'image/jpg') { // On vérifit le
// type du fichier
    exit('Le fichier doit être au format *.jpeg, *.jpg, *.png, .');
}

// On vérifit le poids de l'image
elseif ($_FILES['fichier']['size'] > $poids_max) {
    exit("L'image doit être inférieur à " . $poids_max / 1024 .
        "Ko.");
}

$repertoire = "../images/" . $code . "/";

// On vérifit si le répertoire d'upload existe sinon on le créé
if (!file_exists($repertoire)) {
    mkdir($repertoire, 0777, true);
}

// Supression des caractères accentués
$nom_fichier = strtr(trim($_FILES['fichier']['name']),
    'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
    'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');

// Cas où le fichier est déjà là
if (file_exists($repertoire . $nom_fichier)) {
    exit("Un fichier avec le même nom existe déjà.");
}

// On upload le fichier sur le serveur et on met à jour la BD
echo (move_uploaded_file($_FILES['fichier']['tmp_name'],
    $repertoire . $nom_fichier) &&
 photoAjout($code, $repertoire . $nom_fichier, $title)) ? 'Ajout effectué avec succès'
        : "Échec de l'enregistrement !";


