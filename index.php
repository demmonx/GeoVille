<?php
$titre = "Villes de France";
$js = "index.js";
require_once ("ressources/header.php");
require_once ("ressources/core.php");

// Récupère la valeur passé en paramètre
$region = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT,
    array("options" => array("min_range" => 1)));
if ($region) {
    $region = getRegionByID($region);
} else {
    $region["nom"] = "Midi-Pyrénnées"; // Par défaut
    $region["code_region"] = 16;
}

// Selecteur pour la région à afficher
echo "Choisir une région : <select id='region_choix'>";
$listeRegion = getRegion();
displayInputOptionRegion($listeRegion, $region["code_region"]);
echo "</select>\n";
echo "<a href='search_city.php'><button>Recherche</button></a>\n";

// Get the departements from the region
$depIntoRegion = getDepartementFromRegion($region["code_region"]);
// If list is empty
if ($depIntoRegion == null || count($depIntoRegion) <= 0) {
    exit("Aucune information trouvée pour cette région.</body></html>");
}

echo extractFromPattern("<h1>Région : {nom_region}</h1>", $depIntoRegion[0]);
// list the departements and show related informations
foreach ($depIntoRegion as $dep) {
    echo extractFromPattern("<a href='#' class='spoiler'><h2>{nom_dep}</h2></a>", $dep);
    echo "<div class='spoil spoil-accueil'>";
    displayInfoDep($dep);
    echo "<br>Plus grandes communes : <br>";

    // List biggest city from departement
    $ville = getBiggestCityOfDep($dep["code_departement"]);
    if ($ville == null || count($ville) <= 0) {
        echo "Pas de villes pour le département";
    } else { 
        echo "<table>";

        // Show city info
        foreach ($ville as $city) {
            $pattern = "<tr><td><a href=\"ville.php?code={code}\">{nom}</a>"
                    . "</td><td>{population} habitants<td></tr>";
            echo extractFromPattern($pattern, $city);
        }

        echo "</table><a href='lister_communes.php?code=" . $dep["code_departement"] .
        "' class='list-commune'><button>Voir toutes</button></a>";
    }
    echo "</div><br>";
}
?>

<!-- On appelle la fonction spoiler ici, sinon elle ne trouve pas les éléments -->
<script type="text/javascript">
    /*** Spoiler ***/
    // Clique sur élément
    $(".spoiler").click(function () {
        $(this).next().toggle(400); // inverse l'état de l'élément suivant en 4ms
        return false;  // bloque la fonction par défaut
    });
</script>
</body>
</html>
