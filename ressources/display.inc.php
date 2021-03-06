<?php

/**
 * Affiche un nombre donné de photos par ville
 * @param array $list Liste d'image et de descriptions à afficher
 * @param int $nb Nombre de photos à afficher
 */
function displayPhoto($list) {
    $nb = isset(getConfigFile()["nombre_photos"]) ? getConfigFile()["nombre_photos"]
            : 5;
    for ($z = 0; $z < count($list); $z ++) {
        if ($z == 1) { // show only 1 picture
            echo "<div class='spoil'>";
        }

// picture display
        $pattern = "<figure><a href=\"{path}\"><img src='{path}' alt=\"{titre}\" /><figcaption>{titre}</figcaption></a></figure>";
        echo extractFromPattern($pattern, $list[$z]);
// end of div
        if ($z == count($list) - 1 || $z == $nb) {
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

    // Tableau association pattern => valeur
    $alias = array(
        "nom" => isset($elem["nom"]) ? $elem["nom"] : "",
        "code" => isset($elem["code"]) ? $elem["code"] : "",
        "code_dep" => isset($elem["code_departement"]) ? $elem["code_departement"]
                : "",
        "nom_dep" => isset($elem["departement"]) ? $elem["departement"] : "",
        "nom_region" => isset($elem["region"]) ? $elem["region"] : "",
        "code_region" => isset($elem["code_region"]) ? $elem["code_region"] : "",
        "code_postal" => isset($elem["code_postal"]) ? $elem["code_postal"] : "",
        "population" => isset($elem["population"]) ? $elem["population"] : "",
        "densite" => isset($elem["densite"]) ? round($elem["densite"], 2) : "",
        "alt_min" => isset($elem["alt_min"]) ? $elem["alt_min"] : "",
        "superficie" => isset($elem["superficie"]) ? round($elem["superficie"],
                2) : "",
        "alt_max" => isset($elem["alt_max"]) ? $elem["alt_max"] : "",
        "distance" => isset($elem["distance"]) ? round($elem["distance"], 2) : "",
        "latitude" => isset($elem["latitude"]) ? $elem["latitude"] : "",
        "longitude" => isset($elem["longitude"]) ? $elem["longitude"] : "",
        "path" => isset($elem["path"]) ? $elem["path"] : "",
        "titre" => isset($elem["titre"]) ? $elem["titre"] : "",
        "id_photo" => isset($elem["id"]) ? $elem["id"] : "",
        "rang" => isset($elem["rang"]) ? $elem["rang"] : "",
        "description" => isset($elem["description"]) ? $elem["description"] : "",
        "ville" => isset($elem["ville"]) ? $elem["ville"] : "",
    );

    // Association
    foreach ($alias as $key => $value) {
        $str = isset($value) ? preg_replace("/\{" . $key . "\}/", $value, $str) : $str;
    }

    return $str;
}

/**
 * Affiche les informations sur le département
 */
function displayInfoDep($dep) {
    $pattern = "<table>
        <tr>
            <td class='titre'>Population :</td>
            <td>{population} habitants</td>
        </tr>
	<tr>
            <td class='titre'>Densité :</td>
            <td>{densite} hab/km²</td>
	</tr>
	<tr>
            <td class='titre'>Superficie :</td>
            <td>{superficie} km²</td>
	</tr>
	</table>";
    echo extractFromPattern($pattern, $dep);
}

/**
 * Affiche la liste des photos avec les options de modification associée
 * @param liste $imageAboutThisCity Liste des images à afficher
 */
function displayPictureOption($imageAboutThisCity) {
    // show the options with pictures
    for ($z = 0; $z < count($imageAboutThisCity); $z ++) {
        $pattern = "<div class='liste-pic'><form class='name-edit' action='update_picture_name.php' method='post'>";
        $pattern .= "<table>\n<tr>\n\t<td><input type='text' name='title' value='{titre}' />";
        $pattern .= "<input type='hidden' name='code' value='{id_photo}' /></td>"
            . "\n\t<td><input type='submit' value='Modifier'/>\n\t</td>\n</tr>";
        $pattern .= "\n<tr>\n\t<td rowspan='" . ($z == 0 || $z == count($imageAboutThisCity)
            - 1 ? 2 : 3) . "'>";
        $pattern .= "<figure><a href={path}><img src='{path}' alt='{titre}' /></a></figure>\n\t</td>"
            . "\n\t<td class='action-pic'><a class='delete-pic' href='delete_img.php?code={id_photo}'>"
            . "<button>Supprimer</button></a>\n\t</td>\n</tr>";
        if ($z > 0) {
            $pattern .= "\n<tr>\n\t<td class='action-pic'><a class='sens-edit' href='change_image_rang.php"
                . "?code={id_photo}&sens=1'><button>Monter</button></a>\n\t</td>\n</tr>";
        }
        if ($z < count($imageAboutThisCity) - 1) {
            $pattern .= "\n<tr>\n\t<td class='action-pic'><a class='sens-edit' href='change_image_rang.php"
                . "?code={id_photo}&sens=0'><button>Descendre</button></a>\n\t</td>\n</tr>";
        }
        $pattern .= '</table></form></div>';
        echo extractFromPattern($pattern, $imageAboutThisCity[$z]);
    }
}
