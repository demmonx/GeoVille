<?php
$titre = "Villes de France";
$js = "index.js";
require_once ("ressources/header.php");
require_once ("ressources/core.php");
define("VILLE_PAR_DEP", 12);

// Récupère la valeur passé en paramètre
$region = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT,
    array("options" => array("min_range" => 1)));
if ($region) {
    $region = getRegionByID($region);
} else {
    $region["nom"] = "Midi-Pyrénnées"; // Par défaut
    $region["code"] = 16;
}

// Selecteur pour la région à afficher
echo "Choisir une région : <select id='region_choix'>";
$listeRegion = getRegion();
displayInputOptionRegion($listeRegion, $region["code"]);
echo "</select>\n";
echo "<a href='search_city.php'><button>Recherche</button></a>\n";

// Get the departements from the region
$depIntoRegion = getDepartementFromRegion($region["code"]);
// If list is empty
if ($depIntoRegion == null || count($depIntoRegion) <= 0) {
    exit("Aucune information trouvée pour cette région.</body></html>");
}

echo "<h1>Région : " . $depIntoRegion[0]["region"] . "</h1>";
// list the departements and show related informations
foreach ($depIntoRegion as $dep) {
    echo "<a href='#' class='spoiler'><h2>" . $dep["departement"] . "</h2></a>";
    echo "<div class='spoil spoil-accueil'>";
    echo "<table>
				<tr>
					<td class='titre'>Population :</td>
					<td>" . $dep['population'] . " habitants</td>
				</tr>
				<tr>
					<td class='titre'>Densité :</td>
					<td>" . round($dep['densite'], 2) . " hab/km²</td>
				</tr>
					        				<tr>
					<td class='titre'>Superficie :</td>
					<td>" . round($dep['superficie'], 2) . " km²</td>
				</tr>
			</table>
              <br>Plus grandes communes : <br>";

    // List biggest city from departement
    $ville = getBiggestCityOfDep($dep["code_departement"], VILLE_PAR_DEP);
    if ($ville == null || count($ville) <= 0) {
        echo "Pas de villes pour le département";
    } else { // show city information
        echo "<table>";
        foreach ($ville as $city) {
            echo "<tr><td><a href='ville.php?code=" . $city["code"] . "'>" .
            $city["nom"] . "</a></td><td>" . $city["population"] .
            " habitants<td></tr>";
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
