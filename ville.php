
<body>
    <?php
//important des functions
    require_once ("ressources/function.php");
    define("DISTANCE", 10); // Distance entre deux villes proches
    define("NB_IMAGE", 5); // Nombre d'images maxi à afficher
    define("MARGE_POP", 0.2); // Marge à partir de laquelle on considère une ville
    // comme voisine
    define("NB_VILLE", 5); // Nombre de villes à afficher dans villes de même taille
// check city ID
    $ville_id = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
    if ($ville_id) {
        $city_informations = getCityInfo($ville_id);
    }

    // Get the name
    $titre = isset($city_informations) && count($city_informations) > 0 ? $city_informations['nom']
            : "Ville inconnue";
    require_once ("ressources/header.php");

    if (!isset($city_informations) || count($city_informations) <= 0) {
        exit("Erreur, aucunne donnée trouvée pour cette ville");
    }
    ?>
    <h1>Informations sur <?php echo $city_informations['nom']; ?></h1>
    <form>
        <fieldset>
            <legend>Administration</legend>
            <table>
                <tr>
                    <td class="titre">Département :</td>
                    <td><?php echo $city_informations['departement']; ?></td>
                </tr>
                <tr>
                    <td class="titre">Région :</td>
                    <td><?php echo $city_informations['region']; ?></td>
                </tr>
                <tr>
                    <td class="titre">Code Postal :</td>
                    <td><?php echo $city_informations['postalCode']; ?></td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Démographie</legend>
            <table>
                <tr>
                    <td class="titre">Population :</td>
                    <td><?php echo $city_informations['population']; ?> habitants</td>
                </tr>
                <tr>
                    <td class="titre">Densité :</td>
                    <td><?php echo $city_informations['densitePop']; ?> hab/km²</td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Géographie</legend>
            <table>
                <tr>
                    <td class="titre">Superficie :</td>
                    <td><?php echo $city_informations['superficie']; ?> km²</td>
                </tr>
                <tr>
                    <td class="titre">Altitude mini :</td>
                    <td><?php echo $city_informations['alt_min']; ?> m</td>
                </tr>
                <tr>
                    <td class="titre">Altitude maxi :</td>
                    <td><?php echo $city_informations['alt_max'] ?> m</td>
                </tr>
                <tr>
                    <td class="titre">Latitude :</td>
                    <td><?php echo $city_informations['latitude'] ?>°</td>
                </tr>
                <tr>
                    <td class="titre">Longitude :</td>
                    <td><?php echo $city_informations['longitude'] ?>°</td>
                </tr>
            </table>
        </fieldset>
    </form>
<?php
$msg = getCityDescription($city_informations['nom'], $ville_id);
if (strlen($msg) > 0) {
    echo "<h2> Description : </h2><p>" . $msg . "</p>";
}

$imageAboutThisCity = getCityPhotos($city_informations['nom'], $ville_id);
if (count($imageAboutThisCity) > 0) {
    echo "<h2>Photo(s) :</h2>";
}
displayPhoto($imageAboutThisCity, NB_IMAGE);


echo "<div class='deux-colonnes'>";
// List closest city
$closestCity = getCloseCity($city_informations['latitude'],
    $city_informations['longitude'], $ville_id, DISTANCE);
if (count($closestCity) > 0) {
    echo "<div class='colonne'>";
    displayCloseCityFromList("Villes voisines à moins de " . DISTANCE . " km",
        "ville.php", $closestCity);
    echo "</div>";
}

echo "<div class='colonne'>";
displayCity("Villes de même taille", "ville.php",
    getSameSizeCity($ville_id, MARGE_POP, NB_VILLE));
echo "</div>";
echo "</div>"
?>

    <!-- On appelle la fonction spoiler ici, sinon elle ne trouve pas le s éléments -->
    <script t     ype="text/javascript">
        /*** Spoiler ***/
        // Clique sur élément
        $(".spoiler").click(function () {
            $(this).next().toggle(400); // inverse l'état de l' élément suivant en 4ms
            return false;  // bloque la fonction par défaut
        });
    </script>
</body>
</html>
