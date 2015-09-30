<?php

session_start();
require_once ("../ressources/function.php");

$picture_info = null;

// check picture ID
$codePicture = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($codePicture) {
    $picture_info = getPictureInfo($codePicture);
}
if (!(isset($picture_info) && count($picture_info) > 0)) {
    exit("<p class='error'>Pas de photos pour la ville</p>");
}

// If user is logged
if (isset($_SESSION['name']) && $_SESSION['name'] != null) {
    // remove file if exist
    if (file_exists($picture_info['path'])) {
        unlink($picture_info['path']);
        // remove file like in BD
        if (removePictureFromBD($codePicture)) {
            echo "Suppression réussie";
        } else {
            echo "Echec de suppression";
        }
    } else {
        echo "Fichier absent";
    }
} else {
    echo "<p>Vous devez vous connecter pour accéder à cette partie.<br />";
    echo "Vous serez redirigé automatiquement vers la page de connexion dans 5 secondes.<br />";
    echo "[ <a href='login.php'>Se connecter</a> ]</p>";
    header("refresh:5;url=login.php");
}
?>
