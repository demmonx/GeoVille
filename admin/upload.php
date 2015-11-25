<?php

session_start();
require_once ("../ressources/core.php");

// if user is logged
if (!loginOk($_SESSION)) {
    exit("Vous devez vous connecter pour accéder à cette partie.");
}

// If valide data
$title = filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_SPECIAL_CHARS);
$code = filter_input(INPUT_POST, 'code', FILTER_VALIDATE_INT);
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
}



$repertoire = "../images/" . $code . "/";
$file = $_FILES["fichier"];
try {
// Ajout du fichier sur le serveur
    $image = upload_file($repertoire,
        array(
        "image/png",
        "image/x-png",
        "image/jpeg",
        "image/pjpeg",
        "image/gif"
        ), $file);
// On upload le fichier sur le serveur et on met à jour la BD
    echo (photoAjout($code, $image, $title)) ? 'Ajout effectué avec succès' : "Échec de l'enregistrement !";
} catch (Exception $ex) {
    exit($e->getMessage());
}