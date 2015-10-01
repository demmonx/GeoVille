<?php

function displayPhoto($list, $nb) {
    for ($z = 0; $z < count($list); $z ++) {
        if ($z == 1) { // show only 1 picture
            echo "<div class='spoil'>";
        }

// picture display
        echo "<figure><a href='" . $list[$z]['path'] . "'>";
        echo "<img src='" . $list[$z]['path'] . "' alt='" .
        $list[$z]['title'] . "' />";
        echo "<figcaption>" . $list[$z]['title'] . "</figcaption>";
        echo "</a></figure>";

// end of div
        if ($z == count($list) - 1) {
            echo "</div>";
        } else
        if ($z < 1 && count($list) > 1) { // spoiler
// command
            echo "<a href='#' class='spoiler'><button>Voir plus</button></a>";
        }
// Stopping the loop at 5 (5 images show)
        if ($z == $nb) {
            break;
        }
    }
}

/**
 * Affiche la liste des villes avec distance
 * @param string $titre Le titre de la rubrique
 * @param string $page La page à ouvrir lors de la consultation
 * @param array $closestCity Liste des villes avec une notion de distance
 */
function displayCloseCityFromList($titre, $page, $closestCity) {

// title
    if (count($closestCity) > 0) {
        echo "<h2>" . $titre . "</h2>";
    }

// display the region
    for ($region = 0; $region < count($closestCity); $region ++) {
        echo "<h3>" . $closestCity[$region][0][0]['nom_region'] . "</h3>";

// display the county
        for ($departement = 0; $departement < count($closestCity[$region]); $departement ++) {
            echo "<h4>" . $closestCity[$region][$departement][0]['num_departement'];
            echo " - " . $closestCity[$region][$departement][0]['nom_departement'] .
            "</h4>";

// display the city
            for ($ville = 0; $ville < count($closestCity[$region][$departement]); $ville ++) {
// Show the city and distance between this city and the current
// working city
                echo "<a href='" . $page . "?code=";
                echo $closestCity[$region][$departement][$ville]['id_ville'];
                echo "' >";
                echo $closestCity[$region][$departement][$ville]['nom_ville'];
                echo " ( ";
                echo round($closestCity[$region][$departement][$ville]['distance'], 2);
                echo "km )</a><br>";
            }
        }
    }
}

// Show the city from a list but not an array
function displayCity($title, $page, $listeCity) {

// List city
    $city = convertCityListToArray($listeCity);
// title
    echo "<h2>$title</h2>";

    if ($city == null || count($city) <= 0) {
        exit("Pas de villes disponibles");
    }

// display the region
    for ($region = 0; $region < count($city); $region ++) {
        echo "<h3>" . $city[$region][0][0]['nom_region'] . "</h3>";

// display the county
        for ($departement = 0; $departement < count($city[$region]); $departement ++) {
            echo "<h4>" . $city[$region][$departement][0]['num_departement'];
            echo " - " . $city[$region][$departement][0]['nom_departement'] .
            "</h4>";

// display the city
            for ($ville = 0; $ville < count($city[$region][$departement]); $ville ++) {
// Show the city and distance between this city and the current
// working city
                echo "<a href='" . $page . "?code=";
                echo $city[$region][$departement][$ville]['id_ville'];
                echo "' >";
                echo $city[$region][$departement][$ville]['nom_ville'];
                echo "</a><br>";
            }
        }
    }
}

// Get the city from SQL request and show it
function displayCityBySQL($title, $page, $sql) {
    displayCity($title, $page, getCity($sql));
}

// Affiche la liste des villes sous forme de tableau
function displayCityAsTable($title, $page, $listeCity) {
    if ($listeCity == null || count($listeCity) <= 0) {
        exit("Aucune ville à afficher");
    }
    echo "<table id='table-sort' class='table table-striped table-bordered'>
    <thead>
    <tr>
    <th>Ville</th>
    <th>Département</th>
    <th>Région</th>
    <th>Code Postal</th>
    <th>Population</th>
    </tr>
    </thead>
    <tbody>";
    foreach ($listeCity as $row) {

        echo "<tr>
            <td>" . $row["ville_nom"] . "</td>
            <td>" . $row["nom"] . "</td>
            <td>" . $row["nom_r"] . "</td>
            <td>" .
        $row["ville_code_postal"] . "</td>
            <td>" .
        $row["ville_population_2012"] . "</td>
            </tr>";
    }
    echo "</tbody>
	</table>


	<script type='text/javascript'>

	$(document).ready( function () {
	    $('#table-sort').DataTable( {
        'lengthMenu': [[35, 45, 50, -1], [35, 45, 50, 'All']]
    } );
	} );

    </script>";
}

/**
 * Affiche la liste des villes en fonction de paramètres établis
 * @param array $list Liste des villes à afficher
 * @param int $nb Nombre de colonne
 */
function displayCityByColumn($list, $nb, $pattern) {
    echo "<table>";

// Génere le tableau résultat
    // Création des colonnes
    for ($z = 0; $z < count($list); $z ++) {
        if ($z % $nb == 0) {
            echo "<tr>\n";
        }
        // On convertit le pattern à afficher avec les valeurs de la chaine
        $message = extractFromPatternAboutACity($pattern, $list[$z]);
        // Puis des lignes
        echo "\t<td>" . $message . "<td>\n";
        // Cloture des colonnes    
        if ($z % $nb == $nb - 1) {
            echo "</tr>\n";
        }
    }

// On finit par mettre des cases propres à la fin
    $val = $z % $nb;
    while ($val > 0 && $val <= $nb) {
        echo $val < $nb ? "\t<td></td>\n" : "</tr>\n";
        $val ++;
    }
    echo "</table>";
}

/**
 * Remplace les informations du pattern par les informations de la ville correspondante et renvoie la chaine à afficher
 * @param  string $pattern
 * @param array $elem
 */
function extractFromPatternAboutACity($pattern, $elem) {
    $str = preg_replace('/\{nom\}/', $elem["nom"], $pattern);
    $str2 = preg_replace('/\{code\}/', $elem["code"], $str);
    return $str2;
}
