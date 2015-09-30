<?php

@session_start();
require_once ("../ressources/function.php");

// If user isn't logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    exit("<p>Vous devez vous connecter pour accéder à cette partie.</p>");
}

// check city ID
$cityCode = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($cityCode) {
    $ville_id = $cityCode;
}
if (isset($ville_id) && is_numeric($ville_id)) {
    $city_informations = getCityInfo($ville_id);
}

// check if it's a valid city
if (!isset($city_informations) || count($city_informations) <= 0) {
    exit("Erreur, aucunne donnée trouvée pour cette ville...");
}
// get the photos
$imageAboutThisCity = getPicturesFromDB($ville_id);
if ($imageAboutThisCity == null || count($imageAboutThisCity) <= 0) {
    exit("Pas de photos pour la ville");
}
// show the options with pictures
for ($z = 0; $z < count($imageAboutThisCity); $z ++) {
    echo "<div class='liste-pic'><form class='name-edit' action='update_picture_name.php' method='post'>
                    <table>
                    <tr><td>
                    <input type='text' name='title' value='" .
    $imageAboutThisCity[$z]['title'] . "' />
                             		<input type='hidden' name='code' value='" .
    $imageAboutThisCity[$z]["id"] . "' /></td><td><input type='submit' value='Modifier'/></td></tr>
                        ";
    echo "<tr><td rowspan='" .
    ($z == 0 || $z == count($imageAboutThisCity) - 1 ? 2 : 3) .
    "'><figure><a href='" . $imageAboutThisCity[$z]['path'] . "'>
            <img src='" .
    $imageAboutThisCity[$z]['path'] . "' alt='" .
    $imageAboutThisCity[$z]['title'] .
    "' />
            </a></figure></td><td class='action-pic'><a class='delete-pic' href='delete_img.php?code=" .
    $imageAboutThisCity[$z]['id'] . "'>
                     <button>Supprimer</button></a></td></tr>";
    if ($z > 0)
            echo "<tr><td class='action-pic'><a class='sens-edit' href='change_image_rang.php?code=" .
        $imageAboutThisCity[$z]['id'] .
        "&sens=1'><button>Monter</button></a></td></tr>";
    if ($z < count($imageAboutThisCity) - 1)
            echo "<tr><td class='action-pic'><a class='sens-edit' href='change_image_rang.php?code=" .
        $imageAboutThisCity[$z]['id'] .
        "&sens=0'><button>Descendre</button></a></td></tr>";
    echo '</table></form></div>';
}

echo "<script type='text/javascript'>";
require ('liste_photos.js');
echo "</script>";
