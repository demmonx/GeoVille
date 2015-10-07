<?php

/**
 * Affiche un nombre donné de photos par ville
 * @param array $list Liste d'image et de descriptions à afficher
 * @param int $nb Nombre de photos à afficher
 */
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
        echo extractFromPattern("<h3>{nom_region}</h3>", $city[$region][0][0]);

// display the county
        for ($departement = 0; $departement < count($city[$region]);
                $departement ++) {
            echo extractFromPattern("<h4>{code_dep} - {nom_dep}</h4>",
                $city[$region][$departement][0]);

// display the city
            for ($ville = 0; $ville < count($city[$region][$departement]);
                    $ville ++) {
// Show the city and distance between this city and the current
// working city
                $pattern = "<a href=\"" . $page . "?code={code}\">{nom}";
                $pattern .= isset($city[$region][$departement][$ville]["distance"])
                        ? " ({distance} km)" : "";
                $pattern .= "</a><br>";
                echo extractFromPattern($pattern,
                    $city[$region][$departement][$ville]);
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
    echo "<h2>" . $title . "</h2>";
    echo "<table id='table-sort' class='table table-striped table-bordered'>
    <thead>
    <tr>
    <th>Ville</th><th>Département</th><th>Région</th>
    <th>Code Postal</th><th>Population</th>
    </tr>
    </thead>
    <tbody>";
    foreach ($listeCity as $row) {

        $pattern = "<tr><td><a href=\"" . $page . "?code={code}\">{nom}</a></td><td>{nom}</td>"
            . "<td>{nom_region}</td><td>{code_postal}</td> <td>{population}</td> </tr>";
        echo extractFromPattern($pattern, $row);
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
        echo "\t<td>" . extractFromPattern($pattern, $list[$z]) . "<td>\n";
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
 * Renvoie les départements sous forme d'options exploitables dans un select html
 * @param array $listeDep La liste des départements à afficher
 * @param mixed $default  Si renseigné, sera selectionné la valeur de la liste égale à celle-ci
 */
function displayInputOptionDepartement($listeDep, $default = null) {
    // Generate the list
    if ($listeDep != null && count($listeDep) > 0) {
        echo "\t<option value='0' selected>Choisir un département</option>\n";
        foreach ($listeDep as $dep) {
            $pattern = "\t<option " . ($dep['code_departement'] == $default ? "selected"
                        : "") .
                " value=\"{code_dep}\">{code_dep} - {nom_dep}</option>\n";
            echo extractFromPattern($pattern, $dep);
        }
    } else {
        echo "\t<option>Aucun département</option>\n";
    }
}

/**
 * Renvoie les régions sous forme d'options exploitables dans un select html
 * @param array $listeReg La liste des départements à afficher
 * @param mixed $default  Si renseigné, sera selectionné la valeur de la liste égale à celle-ci
 */
function displayInputOptionRegion($listeReg, $default = null) {
    // Generate the list
    if ($listeReg != null && count($listeReg) > 0) {
        echo "\t<option value='0' selected>Choisir une région</option>\n";
        foreach ($listeReg as $reg) {
            $pattern = "\t<option " . ($reg['code_region'] == $default ? "selected"
                        : "") .
                " value=\"{code_region}\">{nom_region}</option>\n";
            echo extractFromPattern($pattern, $reg);
        }
    } else {
        echo "\t<option>Aucun département</option>\n";
    }
}

/**
 * Remplace les informations du pattern par les informations de la ville correspondante et renvoie la chaine à afficher
 * @param  string $pattern
 * @param array $elem
 */
function extractFromPattern($pattern, $elem) {
    $str = $pattern;
    // nom de la ville
    $str = isset($elem["nom"]) ? preg_replace('/\{nom\}/', $elem["nom"], $str) : $str;

    // code de la ville
    $str = isset($elem["code"]) ? preg_replace('/\{code\}/', $elem["code"], $str)
            : $str;

    // code du département
    $str = isset($elem["code_departement"]) ? preg_replace('/\{code_dep\}/',
            $elem["code_departement"], $str) : $str;

    // nom du département
    $str = isset($elem["departement"]) ? preg_replace('/\{nom_dep\}/',
            $elem["departement"], $str) : $str;

    // nom de la région
    $str = isset($elem["region"]) ? preg_replace('/\{nom_region\}/',
            $elem["region"], $str) : $str;

    // code de la région
    $str = isset($elem["code_region"]) ? preg_replace('/\{code_region\}/',
            $elem["code_region"], $str) : $str;

    // code postal
    $str = isset($elem["postalCode"]) ? preg_replace('/\{code_postal\}/',
            $elem["postalCode"], $str) : $str;

    // population de la ville
    $str = isset($elem["population"]) ? preg_replace('/\{population\}/',
            $elem["population"], $str) : $str;

    // densite de population
    $str = isset($elem["densitePop"]) ? preg_replace('/\{densite\}/',
            $elem["densitePop"], $str) : $str;

    // altitude mini
    $str = isset($elem["alt_min"]) ? preg_replace('/\{alt_min\}/',
            $elem["alt_min"], $str) : $str;

    // Superficie
    $str = isset($elem["nom"]) ? preg_replace('/\{superficie\}/',
            $elem["superficie"], $str) : $str;

    // altitude Maxi
    $str = isset($elem["alt_max"]) ? preg_replace('/\{alt_max\}/',
            $elem["alt_max"], $str) : $str;

    // distance par rapport à la ville
    $str = isset($elem["distance"]) ? preg_replace('/\{distance\}/',
            round($elem["distance"], 2), $str) : $str;

    return $str;
}
