
<body>
    <?php
//important des functions
    require_once ("ressources/core.php");
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
    <h1>Informations sur <?php echo extractFromPattern("{nom}",
        $city_informations); ?></h1>
    <form>
        <fieldset>
            <legend>Administration</legend>
            <table>
                <tr>
                    <td class="titre">Département :</td>
                    <td><?php echo extractFromPattern("{nom_dep}",
        $city_informations); ?></td>
                </tr>
                <tr>
                    <td class="titre">Région :</td>
                    <td><?php echo extractFromPattern("{nom_region}",
        $city_informations); ?></td>
                </tr>
                <tr>
                    <td class="titre">Code Postal :</td>
                    <td><?php echo extractFromPattern("{code_postal}",
        $city_informations); ?></td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Démographie</legend>
            <table>
                <tr>
                    <td class="titre">Population :</td>
                    <td><?php echo extractFromPattern("{population}",
        $city_informations); ?> habitants</td>
                </tr>
                <tr>
                    <td class="titre">Densité :</td>
                    <td><?php echo extractFromPattern("{densite}",
        $city_informations); ?> hab/km²</td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Géographie</legend>
            <table>
                <tr>
                    <td class="titre">Superficie :</td>
                    <td><?php echo extractFromPattern("{superficie}",
        $city_informations); ?> km²</td>
                </tr>
                <tr>
                    <td class="titre">Altitude mini :</td>
                    <td><?php echo extractFromPattern("{alt_min}",
        $city_informations); ?> m</td>
                </tr>
                <tr>
                    <td class="titre">Altitude maxi :</td>
                    <td><?php echo extractFromPattern("{alt_max}",
        $city_informations); ?> m</td>
                </tr>
                <tr>
                    <td class="titre">Latitude :</td>
                    <td><?php echo extractFromPattern("{latitude}",
        $city_informations); ?>°</td>
                </tr>
                <tr>
                    <td class="titre">Longitude :</td>
                    <td><?php echo extractFromPattern("{longitude}",
        $city_informations); ?>°</td>
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
    displayPhoto($imageAboutThisCity);


    echo "<div class='deux-colonnes'>";
// List closest city
    $closestCity = getCloseCity($city_informations['latitude'],
        $city_informations['longitude'], $ville_id);

    if (count($closestCity) > 0) {
        echo "<div class='colonne'>";
        displayCity("Villes voisines à moins de " . getConfigFile()["rayon_ville"] . " km", "ville.php", $closestCity);
        echo "</div>";
    }

    echo "<div class='colonne'>";
    displayCity("Villes de même taille", "ville.php",
        getSameSizeCity($ville_id));
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
