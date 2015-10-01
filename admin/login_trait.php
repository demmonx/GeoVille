<?php

session_start();
$titre = 'Connexion';
//require_once("../ressources/header.php");
require_once("../ressources/core.php");

$pseudo = filter_input(INPUT_POST, 'pseudo', FILTER_SANITIZE_SPECIAL_CHARS);
$pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_SPECIAL_CHARS);

// Check form data
if (!$pseudo || !$pass) {
    exit("Les champs doivent être remplis");
}

// try to login
if (login($pseudo, $pass)) {
    echo 'Connexion réussie.';
    echo "<p>[ <a href='index.php'>Accéder à l'interface</a> ]</p>";
    $_SESSION['name'] = $pseudo;
} else { // login fail
    echo "Les informations n'ont pas permis de vous identifier.";
}

