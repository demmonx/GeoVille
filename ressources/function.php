<?php

// Connexion à la base de donnée (PDO)
function connexionBD() {
    try {
        $db = new PDO('pgsql:host=localhost;dbname=geo', "visiteur",
            "jevisite/*78");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Échec lors de la connexion : ' . $e->getMessage();
    }
    return $db;
}

// Connexion à la base de donnée (PDO)
function connexionBDAdmin() {
    try {
        $db = new PDO('pgsql:host=localhost;dbname=geo', "gerant",
            "jesuislechef%*89");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Échec lors de la connexion : ' . $e->getMessage();
    }
    return $db;
}

// Supprime les accentes d'une chaine de caractère
function wd_remove_accents($str, $charset = 'utf-8') {
    $str1 = htmlentities($str, ENT_NOQUOTES, $charset);

    $str2 = preg_replace(
        '#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#',
        '\1', $str1);
    $str3 = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str2);
    $str4 = preg_replace('#&[^;]+;#', '', $str3); // supprime les autres
// caractères

    return $str4;
}

/**
 * Calcul la distance entre deux ville
 */
function distance($lat_a, $lon_a, $lat_b, $lon_b) {
    $a = M_PI / 180;
    $lat1 = $lat_a * $a;
    $lat2 = $lat_b * $a;
    $lon1 = $lon_a * $a;
    $lon2 = $lon_b * $a;

    $t1 = sin($lat1) * sin($lat2);
    $t2 = cos($lat1) * cos($lat2);
    $t3 = cos($lon1 - $lon2);
    $t4 = $t2 * $t3;
    $t5 = $t1 + $t4;
    $tempo = sqrt(- $t5 * $t5 + 1);
    $rad_dist = $tempo > 0 ? atan(- $t5 / $tempo) + 2 * atan(1) : 0;

    return ($rad_dist * 3437.74677 * 1.1508) * 1.6093470878864446;
}

// Remove a picture from database
function removePictureFromBD($codePicture) {

// add picture into DB
    $sql = "DELETE FROM villes_photos
    		WHERE photo_id = :id";
    $db = connexionBDAdmin();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':id', $codePicture, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Change l'ordre d'une photo
 *
 * @param integer $codePicture
 *            Le code de la photo
 * @param boolean $sens
 *            true si on monte le rang, false si on descend
 * @return true si la mise à jour à réussi, false sinon
 */
function changePictureOrder($codePicture, $sens) {
    if (!checkPicture($codePicture)) {
        return false;
    }

// Calculate the new rank
    $picData = getPictureInfo($codePicture);
    $picture = picByCityAndRank($picData["ville"], $picData["rang"], $sens);

// if same rank, don't change
    if ($picture["rang"] != $picData["rang"]) {
        $rang = $picture["rang"];
    } else {
        return true;
    }

    $db = connexionBDAdmin();

// change other photo rank with this rank
    $sql2 = "UPDATE villes_photos
		    SET rang = :rang
		    WHERE photo_id = :id";
    $stmt2 = $db->prepare($sql2);

    $stmt2->bindValue(':id', $picture["id"], PDO::PARAM_INT);
    $stmt2->bindValue(':rang', $picData["rang"], PDO::PARAM_INT);

// update DB
    $sql = "UPDATE villes_photos
		    SET rang = :rang
		    WHERE photo_id = :id";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':id', $codePicture, PDO::PARAM_INT);
    $stmt->bindValue(':rang', $rang, PDO::PARAM_INT);

    return $stmt->execute() && $stmt2->execute();
}

// Get basic information about a picture (return empty array if no data
// extracted
// from SQL request)
function getPictureInfo($codePicture) {
    $returnArray = array();

    $db = connexionBD();

    $sql = 'SELECT *
	        FROM villes_photos
	        WHERE photo_id = ?';

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $codePicture, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() == 1) {
        $row = $response->fetch();
// fill the return array
        $returnArray = array(
            'path' => $row['photo_path'],
            'title' => $row['photo_desc'],
            'rang' => $row['rang'],
            'ville' => $row['photo_ville'],
            'id' => $row['photo_id']
        );
    }

    return $returnArray;
}

// Get basic information about a city (return empty array if no data extracted
// from SQL request)
function getCityInfo($cityCode) {
    $returnArray = array();

    $db = connexionBD();

    $sql = "SELECT *
		FROM villes_france_free V, departements D, regions R
		WHERE D.num_departement = V.ville_departement
                                    AND ville_statut = 'A'
		AND R.num_region = D.num_region
		AND V.ville_id = ?";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $cityCode, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() > 0) {
        $returnArray = extractCityInfoFromARow($response->fetch());
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return $returnArray;
}

/**
 * Renvoie la regex pour avoir un code de département valide
 */
function getCodeDepartementRegex() {
    return "/^([0-9])?([0-9A-B])?([0-9]){0,1}$/";
}

// Get the DOM of a page of wikipedia
function getDOM($cityName) {
    $page = null;

// Get wiki page name with JSON and check we are working on correct city
// <=> Reseach with REGEX,
// <=> Recherche avancée via moteur REGEX, then url conversion into String
// (strip accents) to compare
    $search = @file_get_contents(
            "http://en.wikipedia.org/w/api.php?action=opensearch&search=" .
            $cityName . "&namespace=0");
    if (!$search) {
        throw new Exception("Impossible d'accéder à Wiki"); // If URL is not
// reachable
    }
    $url_search = json_decode($search, true);
    for ($i = 0; $i < sizeof($url_search[3]); $i ++) {
// Check url integrity
        if (preg_match(
                "/^https:\/\/en.wikipedia.org\/wiki\/" . $cityName . "$/i",
                wd_remove_accents(rawurldecode($url_search[3][$i])))) {
            $page = $url_search[3][$i];
        }
    }

// Changing url to get an french page instead of english
    $page = substr($page, 0, 8) . "fr" . substr($page, 10);

// Getting wikipedia page (about the specified city)
    require_once ('simple_html_dom.php');
    $doc = new DOMDocument();
    @$doc->loadHTMLFile($page);

    return $doc;
}

// Formatte pour URL encoding
function format2url($string) {
// Replace spaces by "_" for url encoding
    if (0 < strrpos($string, " ")) {
        $string = str_replace(" ", "_", $string);
    }
    return $string;
}

// Coupe le nom de la ville pour récupérer le début et la fin
// Permet de faire des comparaisons avec des REGEX
function cutCityName($cityName) {
    $ok = false;

    $fin = $cityName;
    $debut = $cityName;

// Coupe au niveau des ''', des ' ' et des '-' pour rechercher dans l'url de
// l'image
    while (!$ok) {
        if (0 < strrpos($fin, "'") || 0 < strrpos($debut, "'")) {
            $fin = substr($fin, strrpos($fin, "'") + 1, strlen($fin));
            $debut = substr($debut, 0, strrpos($debut, "'"));
        } else
        if (0 < strrpos($fin, "-") || 0 < strrpos($debut, "-")) {
            $fin = substr($fin, strrpos($fin, "-") + 1, strlen($fin));
            $debut = substr($debut, 0, strrpos($debut, "-"));
        } else
        if (0 < strrpos($fin, " ") || 0 < strrpos($debut, " ")) {
            $fin = substr($fin, strrpos($fin, " ") + 1, strlen($fin));
            $debut = substr($debut, 0, strrpos($debut, " "));
        } else {
            $ok = true;
        }
    }

// Retour des deux variables
    $tab['debut'] = $debut;
    $tab['fin'] = $fin;
    return $tab;
}

// Récupération du DOM de wiki
function getFromWiki($cityName) {
// coupe du nom de la ville
    $tab = cutCityName($cityName);
// récupération du tableau
    $retour['debut'] = $tab['debut'];
    $retour['fin'] = $tab['fin'];
// Formatage de l'url
// Récupération du DOM
    $retour['dom'] = getDOM(format2url($cityName));
// Renvoi du tableau avec tous les éléments
    return $retour;
}

// Return a short description about the city from wikipedia
function getDescriptionFromWiki($cityName) {
    $tab = getFromWiki($cityName);
    $message = null;

// City description
    foreach ($tab['dom']->getElementsByTagName('p') as $txt) {
        $message = $txt->nodeValue;

// Show first paragraph that contain the city
        if (preg_match(
                "/" . $cityName . '|' . $tab['fin'] . '|' . $tab['debut'] . "/i",
                $message)) {
            $message = preg_replace("/\[\d+\]/i", '', $message);
            break;
        }
    }

    return $message;
}

// Return a short description about the city from database
function getDescriptionFromDB($cityCode) {
    $message = null;

    $db = connexionBD();

    $sql = 'SELECT ville_description
	            FROM villes_france_free
	            WHERE ville_id = ?';

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $cityCode, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() > 0) {
// get description
        $message = $response->fetch()['ville_description'];
    }

    return $message;
}

// Get description about a city
function getCityDescription($cityName, $cityID) {
    try {
// $message = getDescriptionFromWiki($cityName);
        $message = getDescriptionFromDB($cityID);
    } catch (Exception $e) {
        $message = getDescriptionFromDB($cityID);
    }

    return $message;
}

// Get image about a city from wikipedia
function getPicturesFromWiki($cityName) {
    $tab = getFromWiki($cityName);

    $imgTab = array();

// Count how many image to show (0 to break;)
    $c = 0;
// Getting images
    foreach ($tab['dom']->getElementsByTagName('img') as $image) {
        $chemin = $image->getAttribute('src');

// Getting only image about the city (and not thing like blazon)
        if (preg_match("/" . $tab['debut'] . '|' . $tab['fin'] . "/i", $chemin) &&
            !preg_match("/blason|plan|carte|logo/i", $chemin)) {
            $desc = $image->getAttribute('alt');

// Show specified image
            $imgTab[$c]['path'] = $chemin;
            $imgTab[$c]['title'] = $desc;
            $c ++;
        }
// Stopping the loop at 5 (5 images show)
        if ($c == 5) {
            break;
        }
    }
    return $imgTab;
}

// Return a short description about the city from database
function getPicturesFromDB($cityCode) {
    $imgTab = array();

// Count how many image to show (0 to break;)
    $c = 0;
// Getting images
    $db = connexionBD();

    $sql = 'SELECT *
	        FROM villes_photos
	        WHERE photo_ville = ?
            ORDER BY rang DESC';

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $cityCode, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {
// Show specified image
            $imgTab[$c]['path'] = $row['photo_path'];
            $imgTab[$c]['title'] = $row['photo_desc'];
            $imgTab[$c]['rang'] = $row['rang'];
            $imgTab[$c]['id'] = $row['photo_id'];
            $imgTab[$c]['ville'] = $row['photo_ville'];
            $c ++;
        }
    }
    $response->closeCursor();
    return $imgTab;
}

// Get image about a city
function getCityPhotos($cityName, $cityID) {
// On cherche les images dans la BDD s'il y en a
    $picture = getPicturesFromDB($cityID);
    if (count($picture) > 0) {
        return $picture;
    }

// Sinon on va voir sur Wiki
    try {
        $picture2 = getPicturesFromWiki($cityName);
    } catch (Exception $e) {
// do nothing
        $e->getMessage();
    }

    return isset($picture2) ? $picture2 : null;
}

// Get closest city from an other one (x km)
function getCloseCity($latitude, $longitude, $cityCode, $distance) {
    $km = 0.015060; // Valeur en degré d'un km
    $ecart = $km * $distance; // ecart entre les villes en degré


    $db = connexionBD();

    $sql = "SELECT nom_r, num_departement, ville_departement, ville_id, ville_nom, nom, ville_latitude_deg, ville_longitude_deg
		FROM villes_france_free V, departements D, regions R
		WHERE ville_id <> ?
                                    AND ville_statut = 'A'
		AND ville_latitude_deg BETWEEN ? AND ?
		AND ville_longitude_deg BETWEEN ? AND ?
	                  AND ville_departement = num_departement
		AND D.num_region = R.num_region
		ORDER BY nom_r, num_departement, ville_nom";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $cityCode, PDO::PARAM_INT);
    $response->bindValue(2, $latitude - $ecart, PDO::PARAM_INT);
    $response->bindValue(3, $latitude + $ecart, PDO::PARAM_INT);
    $response->bindValue(4, $longitude - $ecart, PDO::PARAM_INT);
    $response->bindValue(5, $longitude + $ecart, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() > 0) {
        $retour = getCloseCityInfo($response, $latitude, $longitude, $distance);
    }
    $response->closeCursor();
    return isset($retour) ? $retour : $retour;
}

/**
 * Renvoie un tableau de villes exploitables avec les distances
 * @param stmt $response La réponse de la base de données
 */
function getCloseCityInfo($response, $latitude, $longitude, $distance) {
    $dep_num = "00"; // Numero de département
    $reg_nom = null; // nom de region
    $region = 0; // Compteur de région
    $departement = 0; // Compteur de département pour la région
    $ville = 0; // Compteur de ville pour le département

    $returnArray = array();
    while ($row = $response->fetch()) {

        $size = distance($latitude, $longitude, $row["ville_latitude_deg"],
            $row["ville_longitude_deg"]); // Get exact distance between city
        if ($size == 0 || $size > $distance) { // If distance is higher
            continue;
        }
// Rank by region
        if ($row["nom_r"] != $reg_nom) {
            $reg_nom = $row["nom_r"];
            $region ++;
            $departement = 0;
        }

// And rank by county
        if ($row["ville_departement"] != $dep_num) {
            $dep_num = $row["ville_departement"];
            $departement ++;
            $ville = 0;
        }

// Add item to return tab
        $returnArray[$region - 1][$departement - 1][$ville]['nom_region'] = $row["nom_r"];
        $returnArray[$region - 1][$departement - 1][$ville]['num_departement'] = $row["ville_departement"];
        $returnArray[$region - 1][$departement - 1][$ville]['nom_departement'] = $row["nom"];
        $returnArray[$region - 1][$departement - 1][$ville]['id_ville'] = $row["ville_id"];
        $returnArray[$region - 1][$departement - 1][$ville]['nom_ville'] = $row["ville_nom"];
        $returnArray[$region - 1][$departement - 1][$ville]['distance'] = $size;
        $ville ++;
    }
    return $returnArray;
}

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
        for ($departement = 0; $departement < count($closestCity[$region]);
                $departement ++) {
            echo "<h4>" . $closestCity[$region][$departement][0]['num_departement'];
            echo " - " . $closestCity[$region][$departement][0]['nom_departement'] .
            "</h4>";

// display the city
            for ($ville = 0; $ville < count($closestCity[$region][$departement]);
                    $ville ++) {
// Show the city and distance between this city and the current
// working city
                echo "<a href='" . $page . "?code=";
                echo $closestCity[$region][$departement][$ville]['id_ville'];
                echo "' >";
                echo $closestCity[$region][$departement][$ville]['nom_ville'];
                echo " ( ";
                echo round($closestCity[$region][$departement][$ville]['distance'],
                    2);
                echo "km )</a><br>";
            }
        }
    }
}

// List cities from specified sql
function getCity($sqlRq) {
    $db = connexionBD();

    $sql = "SELECT nom_r, num_departement, ville_departement, ville_id, ville_nom, nom
	            FROM villes_france_free V, departements D, regions R
	            WHERE ville_departement = num_departement
	            AND D.num_region = R.num_region
                              AND ville_statut = 'A'";
    $sql .= $sqlRq;
    $sql .= ' ORDER BY nom_r, num_departement, ville_nom';

    $response = $db->query($sql);

    $returnArray = getCityListFromStatement($response);

// close cursor
    $response->closeCursor();
    return $returnArray;
}

// Transforme un curseur de base de données en liste de ville exploitable
function getCityListFromStatement($response) {
    $returnArray = null;
    $dep_num = "00"; // Numero de département
    $reg_nom = null; // nom de region
    $region = 0; // Compteur de région
    $departement = 0; // Compteur de département pour la région
    $ville = 0; // Compteur de ville pour le département
// Génère la liste
    if ($response != null && $response->rowCount() > 0) {
        while ($row = $response->fetch()) {

// Rank by region
            if ($row["nom_r"] != $reg_nom) {
                $reg_nom = $row["nom_r"];
                $region ++;
                $departement = 0;
            }

// And rank by county
            if ($row["ville_departement"] != $dep_num) {
                $dep_num = $row["ville_departement"];
                $departement ++;
                $ville = 0;
            }

// Add item to return tab
            $returnArray[$region - 1][$departement - 1][$ville]['nom_region'] = $row["nom_r"];
            $returnArray[$region - 1][$departement - 1][$ville]['num_departement']
                = $row["ville_departement"];
            $returnArray[$region - 1][$departement - 1][$ville]['nom_departement']
                = $row["nom"];
            $returnArray[$region - 1][$departement - 1][$ville]['id_ville'] = $row["ville_id"];
            $returnArray[$region - 1][$departement - 1][$ville]['nom_ville'] = $row["ville_nom"];
            $ville ++;
        }
    }
    return $returnArray;
}

// Show the city from a list generated by a preview request
function displayCity($title, $page, $listeCity) {
// List city
    $city = $listeCity;
// title
    echo "<h2>$title</h2>";

    if ($city == null || count($city) == 0) {
        echo "Pas de villes disponibles";
    }

// display the region
    for ($region = 0; $region < count($city); $region ++) {
        echo "<h3>" . $city[$region][0][0]['nom_region'] . "</h3>";

// display the county
        for ($departement = 0; $departement < count($city[$region]);
                $departement ++) {
            echo "<h4>" . $city[$region][$departement][0]['num_departement'];
            echo " - " . $city[$region][$departement][0]['nom_departement'] .
            "</h4>";

// display the city
            for ($ville = 0; $ville < count($city[$region][$departement]);
                    $ville ++) {
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

// Retourne une liste de n villes comprises entre pop-marge et pop+marge
function getSameSizeCity($cityCode, $marge, $nb) {
// check city
    $info = getCityInfo($cityCode);
    if (count($info) <= 0 || $nb <= 0 || $marge < 0 ||
        $marge >= 1) {
        return null;
    }
    $popMin = floor($info["population"] * (1 - $marge));
    $popMax = floor($info["population"] * (1 + $marge));

    $db = connexionBD();

    $sql = "SELECT *
		    FROM villes_france_free V, departements D, regions R
		    WHERE R.num_region = D.num_region
            AND D.num_departement = V.ville_departement
            AND ville_id <> :id
            AND ville_id IN ((
                SELECT ville_id
	            FROM villes_france_free
                WHERE ville_departement = :departement
                AND ville_id <> :id
                AND ville_population_2010 BETWEEN :popMin AND :popMax
                LIMIT :number)
            UNION
                (
                 SELECT ville_id
	             FROM villes_france_free V2, departements D2, regions R2
                 WHERE R2.num_region = D2.num_region
                 AND D2.num_departement = V2.ville_departement
                 AND R2.num_region = :region
                 AND ville_id <> :id
                 AND ville_statut = 'A'
                 AND ville_population_2010 BETWEEN :popMin AND :popMax
                 AND :number > (SELECT COUNT(*)
			         FROM villes_france_free
					 WHERE ville_departement = :departement
                     AND ville_id <> :id
                     AND ville_statut = 'A'
					 AND ville_population_2010 BETWEEN :popMin AND :popMax)
		         LIMIT :number)
             UNION (
                 SELECT ville_id
		         FROM villes_france_free V2
                 WHERE ville_population_2010 BETWEEN :popMin AND :popMax
                 AND ville_id <> :id
                 AND ville_statut = 'A'
                 AND :number > (SELECT COUNT(*)
				     FROM villes_france_free V3, departements D3, regions R3
					 WHERE R3.num_region = D3.num_region
					 AND D3.num_departement = V3.ville_departement
					 AND R3.num_region = :region
                                         AND ville_statut = 'A'
                     AND ville_id <> :id
					 AND ville_population_2010 BETWEEN :popMin AND :popMax)
		         LIMIT :number)
             )
             ORDER BY nom_r, num_departement, ville_nom
             LIMIT :number";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':popMin', $popMin, PDO::PARAM_INT);
    $response->bindValue(':popMax', $popMax, PDO::PARAM_INT);
    $response->bindValue(':number', $nb, PDO::PARAM_INT);
    $response->bindValue(':departement', $info["code_departement"],
        PDO::PARAM_INT);
    $response->bindValue(':id', $cityCode, PDO::PARAM_INT);
    $response->bindValue(':region', $info["code_region"], PDO::PARAM_INT);

    $response->execute();

    $returnArray = getCityListFromStatement($response);

// close cursor
    $response->closeCursor();
    return $returnArray;
}

// Affiche la liste des villes sous forme de tableau en fonction de la requete
function displayCityTable($title, $page, $sqlRq) {
    $db = connexionBD();

    $sql = "SELECT nom_r, num_departement, ville_departement, ville_id, ville_nom, nom,
                 ville_code_postal, ville_population_2012
	            FROM villes_france_free V, departements D, regions R
	            WHERE ville_departement = num_departement
	            AND D.num_region = R.num_region
                    AND ville_statut = 'A'";
    $sql .= $sqlRq;
    $sql .= ' ORDER BY nom_r, num_departement, ville_nom';

    $response = $db->query($sql);
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
    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {

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
// close cursor
    $response->closeCursor();
}

// Check if the people exist in DB or not and if he's log is correct
function login($pseudo, $pass) {
    $db = connexionBD();

    $sql = "SELECT a_pass
		FROM t_admin
		WHERE a_pseudo = ?";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $pseudo, PDO::PARAM_STR);

    $response->execute();
    if ($response->rowCount() == 1) {
        $retour = password_verify($pass, $response->fetch()["a_pass"]);
    }
    $response->closeCursor();

    return $retour;
}

// Update few informations about a city
function updateCity($cityCode, $param) {
// error
    if (!count($param) == 7 || !checkCity($cityCode)) {
        return false;
    }

// update DB
    $sql = "UPDATE villes_france_free
		    SET ville_code_postal = :codePostal,
                ville_population_2010 = :population,
                ville_densite_2010 = :densite,
                ville_surface = :superficie,
                ville_zmin = :altMin,
                ville_zmax = :altMax,
                ville_description = :desc
		    WHERE ville_id = :id";
    $db = connexionBDAdmin();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':id', $cityCode, PDO::PARAM_INT);
    $stmt->bindValue(':codePostal', $param[0], PDO::PARAM_INT);
    $stmt->bindValue(':population', $param[1], PDO::PARAM_INT);
    $stmt->bindValue(':densite', $param[3], PDO::PARAM_INT);
    $stmt->bindValue(':superficie', $param[2], PDO::PARAM_INT);
    $stmt->bindValue(':altMin', $param[4], PDO::PARAM_INT);
    $stmt->bindValue(':altMax', $param[5], PDO::PARAM_INT);
    $stmt->bindValue(':desc', $param[6], PDO::PARAM_LOB);

    return $stmt->execute();
}

/* Add a picture for a city */

function photoAjout($cityCode, $path, $title) {
    if (!checkCity($cityCode)) {
        return false;
    }

// add picture into DB
    $sql = "INSERT INTO villes_photos
    		(photo_path, photo_desc, photo_ville, rang)
    		VALUES (:path, :title, :city, :rang)";
    $db = connexionBDAdmin();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':city', $cityCode, PDO::PARAM_INT);
    $stmt->bindValue(':path', $path, PDO::PARAM_LOB);
    $stmt->bindValue(':title', $title, PDO::PARAM_LOB);
    $stmt->bindValue(':rang', rangMaxPhoto($cityCode) + 100, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Vérifie si une ville existe dans la base de donnée ou pas
 *
 * @return boolean Vrai si la ville existe, false sinon
 */
function checkCity($cityCode) {
    $db = connexionBD();

// Check if city exist
    $sql = "SELECT *
		FROM villes_france_free
		WHERE ville_id = ?";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $cityCode, PDO::PARAM_INT);

    $response->execute();

// City doesn't exist
    if ($response->rowCount() != 1) {
        return false;
    }
    $response->closeCursor();
    return true;
}

/**
 * Vérifie si une photo existe dans la base de donnée ou pas
 *
 * @return boolean Vrai si la photo existe, false sinon
 */
function checkPicture($codePicture) {
// check if the picture exist
    $db = connexionBD();

    $sql = 'SELECT *
	        FROM villes_photos
	        WHERE photo_id = ?';

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $codePicture, PDO::PARAM_INT);

    $response->execute();

// picture doesn't exist
    if ($response->rowCount() != 1) {
        return false;
    }
    $response->closeCursor();
    return true;
}

/**
 * Renvoie Le rang de la première photo de cette ville
 *
 * @param $cityCode Le
 *            code de la ville
 * @return int La position du rang de la première photo pour cette ville, -1 si
 *         absent
 */
function rangMaxPhoto($cityCode) {
    $db = connexionBDAdmin();

// Check the maxi rang
    $sql = "SELECT MAX(rang)
		FROM villes_photos
		WHERE photo_ville = ?";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(1, $cityCode, PDO::PARAM_INT);

    $response->execute();

// Get the rank
    $rang = $response->rowCount() == 1 ? $response->fetch()[0] : -1;

    $response->closeCursor();
    return $rang;
}

/**
 * Renvoie les infos de la photo suivante ou précédente suivant la ville et le
 * paramétrage
 *
 * @param $cityCode Le
 *            code de la ville
 * @param $pictureRang Le
 *            rang de la photo
 * @param $sens true
 *            on cherche le rang suivant, false le précédent
 * @return Les infos de la photo précédente ou suivante, ou null si erreur
 */
function picByCityAndRank($cityCode, $pictureRang, $sens) {
    $db = connexionBDAdmin();

    $sql = "SELECT *
    FROM villes_photos
    WHERE photo_ville = :ville ";

// Check the previous rang
    if ($sens) {
        $sql .= "AND rang > :rang"
                . " ORDER BY rang ASC ";
    } else {
        $sql .= "AND rang < :rang "
                . " ORDER BY rang DESC ";
    }
    $sql .= "LIMIT 1";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':ville', $cityCode, PDO::PARAM_INT);
    $response->bindValue(':rang', $pictureRang, PDO::PARAM_INT);

    $response->execute();

// Picture doesn't exist
    $id = $response->rowCount() == 1 ? $response->fetch()["photo_id"] : null;

    return getPictureInfo($id);
}

/**
 * Change le titre d'une image
 *
 * @param integer $codePicture
 *            Le code de la photo a mettre à jour
 * @param string $name
 *            Le nom de l'image
 * @return true si l'image a été modifié, false sinon
 */
function updatePictureName($codePicture, $name) {
    if (strlen($name) <= 0 || !checkPicture($codePicture)) {
        return false;
    }

// update DB
    $sql = "UPDATE villes_photos
		    SET photo_desc = :title
		    WHERE photo_id = :id";
    $db = connexionBDAdmin();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':id', $codePicture, PDO::PARAM_INT);
    $stmt->bindValue(':title', $name, PDO::PARAM_STR);

    return $stmt->execute();
}

// Retourne les informations du département : population, superficie, densite,
// nom et code
function getDepartementInfo($codeDep) {
    $db = connexionBD();

    $sql = "SELECT SUM(ville_surface) AS surface, SUM (ville_population_2010) AS pop,
             AVG(ville_densite_2010) AS densite, nom, num_departement
		FROM villes_france_free, departements
		WHERE ville_departement = :departement
        AND ville_departement = num_departement
        AND ville_statut = 'A'
        GROUP BY nom, num_departement";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':departement', $codeDep, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() == 1) {
        $row = $response->fetch();

        $returnArray = array(
            "superficie" => $row["surface"],
            "population" => $row["pop"],
            "densite" => $row["densite"],
            "nom" => $row["nom"],
            "code" => $row["num_departement"]
        );
    } // else NO DATA FOUND
    $response->closeCursor();

    return isset($returnArray) ? $returnArray : null;
}

// Retourne les informations des n plus grandes communes d'un département
function getBiggestCityOfDep($codeDep, $nb) {
    if ($nb <= 0) {
        return null;
    }
    $db = connexionBD();

    $sql = "SELECT *
		FROM villes_france_free V, departements D, regions R
		WHERE D.num_departement = :departement
		AND R.num_region = D.num_region
        AND D.num_departement = V.ville_departement
        AND ville_statut = 'A'
        ORDER BY ville_population_2010 DESC
        LIMIT :nombre";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':departement', $codeDep, PDO::PARAM_INT);
    $response->bindValue(':nombre', $nb, PDO::PARAM_INT);

    $response->execute();

    $i = 0;
    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {
            $returnArray[$i ++] = extractCityInfoFromARow($row);
        }
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return isset($returnArray) ? $returnArray : null;
}

// Retourne les infos d'une région et les départements qu'elle contient
function getDepartementFromRegion($codeRegion) {
    $db = connexionBD();

    $sql = "SELECT SUM(ville_surface) AS surface, SUM (ville_population_2010) AS pop,
            AVG(ville_densite_2010) AS densite, nom, num_departement, nom_r
	        FROM departements D, regions R, villes_france_free V
	        WHERE D.num_region = R.num_region
            AND D.num_departement = V.ville_departement
            AND R.num_region = :region
            AND ville_statut = 'A'
            GROUP BY num_departement, nom, nom_r
            ORDER BY num_departement";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':region', $codeRegion, PDO::PARAM_INT);

    $response->execute();
    $i = 0;
    $returnArray = null;
    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {
            $returnArray[$i]['nom_region'] = $row["nom_r"];
            $returnArray[$i]['nom'] = $row["nom"];
            $returnArray[$i]['code'] = $row["num_departement"];
            $returnArray[$i]['superficie'] = $row["surface"];
            $returnArray[$i]['population'] = $row["pop"];
            $returnArray[$i]['densite'] = $row["densite"];
            $i ++;
        }
    } // else NO DATA FOUND
    $response->closeCursor();

    return isset($returnArray) ? $returnArray : null;
}

// Renvoie les informations d'une ville à partir d'une ligne de base de données
// sur la table des villes
function extractCityInfoFromARow($aRow) {
    return array(
        "code" => $aRow["ville_id"],
        "nom" => $aRow["ville_nom"],
        "longitude" => $aRow["ville_longitude_deg"],
        "latitude" => $aRow["ville_latitude_deg"],
        "ville" => $aRow["ville_nom"],
        "departement" => $aRow["nom"],
        "code_departement" => $aRow["num_departement"],
        "code_region" => $aRow["num_region"],
        "region" => $aRow["nom_r"],
        "postalCode" => $aRow["ville_code_postal"],
        "population" => $aRow["ville_population_2010"],
        "densitePop" => $aRow["ville_densite_2010"],
        "superficie" => $aRow["ville_surface"],
        "alt_min" => $aRow["ville_zmin"],
        "alt_max" => $aRow["ville_zmax"],
        "description" => nl2br($aRow["ville_description"])
    );
}

// Renvoi la liste des villes de ce département avec les infos associées
function getCityFromDepartement($codeDep) {
    $returnArray = null;
    $i = 0;

    $db = connexionBD();

    $sql = "SELECT *
		FROM villes_france_free V, departements D, regions R
		WHERE D.num_departement = :departement
		AND R.num_region = D.num_region
        AND D.num_departement = V.ville_departement
        AND ville_statut = 'A'
        ORDER BY ville_nom";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':departement', $codeDep, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {
            $returnArray[$i ++] = extractCityInfoFromARow($row);
        }
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return $returnArray;
}

// Renvoie les informations d'une region
function getRegionByID($codeReg) {
    $returnArray = null;

    $db = connexionBD();

    $sql = "SELECT *
		FROM regions R
		WHERE R.num_region = :region";

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':region', $codeReg, PDO::PARAM_INT);

    $response->execute();

    if ($response->rowCount() == 1) {
        $row = $response->fetch();
        $returnArray = array(
            "nom" => $row["nom_r"],
            "code" => $row["num_region"]
        );
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return $returnArray;
}

// Renvoie la liste des regions
function getRegion() {
    $returnArray = null;
    $i = 0;

    $db = connexionBD();

    $sql = "SELECT *
		FROM regions R
		ORDER BY nom_r";

    $response = $db->prepare($sql);

    $response->execute();

    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {
            $returnArray[$i ++] = array(
                "nom" => $row["nom_r"],
                "code" => $row["num_region"]
            );
        }
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return $returnArray;
}

// Renvoie la liste des départements
function getDepartement() {
    $returnArray = null;
    $i = 0;

    $db = connexionBD();

    $sql = "SELECT *
		FROM departements
		ORDER BY num_departement";

    $response = $db->prepare($sql);

    $response->execute();

    if ($response->rowCount() > 0) {
        while ($row = $response->fetch()) {
            $returnArray[$i ++] = array(
                "nom" => $row["nom"],
                "region" => $row["num_region"],
                "code" => $row["num_departement"]
            );
        }
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return $returnArray;
}

// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.
function better_crypt($input, $rounds = 10) {
    $crypt_options = array(
        'cost' => $rounds
    );
    return password_hash($input, PASSWORD_BCRYPT, $crypt_options);
}

/**
 * Fusionne les communes du tableau list en une unique commune
 * @param String $name Nom de la nouvelle
 * @param vint[] $list Liste des identifiants des communes à fusionner
 * @return true si l'opération a réussi, false sinon
 */
function mergeCity($name, $list) {

    if (count($list) <= 0 || strlen($name) <= 0) {
        return false;
    }

// Toutes les informations qui seront ajoutées lors de l'insertions
    $info = newCityInfo(oldCityInfo($list), $name, getCityInfo($list[0]));
    $db = connexionBDAdmin();
    try {
        $db->beginTransaction(); // En cas d'erreur on valide pas
        insertNewCity($info, $db);  // création de la nouvelle
        deleteOldCity($list, $info["codeCommune"], $db);  // retrait et archivage des anciennes
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
// do nothing with $e
        $e->getMessage();
        return false;
    }

    return true;
}

/**
 * Rajoute une ville dans la base de donnée et renvoi son identifiant
 * @param array $info Les infos de la ville
 * @param stmt $db Une connexion active à la base de donnée
 */
function insertNewCity($info, $db) {
    $sql = "INSERT INTO villes_france_free"
        . "(ville_code_commune, ville_nom, ville_longitude_deg, ville_latitude_deg, ville_departement, ville_zmin, ville_zmax,"
        . " ville_population_2010, ville_surface, ville_densite_2010, ville_code_postal, ville_statut, ville_id) "
        . "VALUES(:commune, :nom, :longitude, :latitude, :departement, :zmin, :zmax, :pop, :surface, :densite, :cp, :statut, (SELECT MAX(ville_id)+1 FROM villes_france_free))";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":commune", $info["codeCommune"], PDO::PARAM_STR);
    $stmt->bindValue(":nom", $info["nom"], PDO::PARAM_STR);
    $stmt->bindValue(":longitude", $info["longitude"], PDO::PARAM_INT);
    $stmt->bindValue(":latitude", $info["latitude"], PDO::PARAM_INT);
    $stmt->bindValue(":departement", $info["code_departement"], PDO::PARAM_STR);
    $stmt->bindValue(":zmin", $info["alt_min"], PDO::PARAM_INT);
    $stmt->bindValue(":zmax", $info["alt_max"], PDO::PARAM_INT);
    $stmt->bindValue(":pop", $info["population"], PDO::PARAM_INT);
    $stmt->bindValue(":surface", $info["superficie"], PDO::PARAM_INT);
    $stmt->bindValue(":densite", floor($info["densitePop"]), PDO::PARAM_INT);
    $stmt->bindValue(":cp", $info["postalCode"], PDO::PARAM_STR);
    $stmt->bindValue(":statut", 'A', PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Génère les infos de la nouvelle commune à partir des infos de la zone et d'une commune existante à fusionner
 * @param array $infoZone Les infos statistiques des communes à fusionner
 * @param String $name Le nom de la ville
 * @param array $aOldCity Une des communes à fusionner
 */
function newCityInfo($infoZone, $name, $aOldCity) {
    return array(
        "nom" => strtoupper($name),
        "codeCommune" => $aOldCity["code_departement"] . getNumberCityFromADep($aOldCity["code_departement"]),
        "longitude" => $aOldCity["longitude"],
        "latitude" => $aOldCity["latitude"],
        "code_departement" => $aOldCity["code_departement"],
        "postalCode" => $aOldCity["postalCode"],
        "population" => $infoZone["population"],
        "densitePop" => $infoZone["densite"],
        "superficie" => $infoZone["superficie"],
        "alt_min" => $infoZone["zmin"],
        "alt_max" => $infoZone["zmax"]
    );
}

/**
 * Retire les villes en formant une nouvelle de la base et les place dans celle d'archivage
 * @param array $list Liste des communes à retirer
 * @param int $codeNew La code commune de la ville nouvellement créée
 * @param stmt $db La connexion à la base de donnée
 */
function deleteOldCity($list, $codeNew, $db) {
// Génération des paramètres de la requête SQL
    $inQuery = implode(',', array_fill(0, count($list), '?'));

// Passage du statut des communes de Actif à Supprimé
    $sql1 = "UPDATE villes_france_free SET ville_statut='S' WHERE ville_id IN (" . $inQuery . ")";

// Ajout des communes dans la table d'archivage
    $sql2 = "INSERT INTO ville_archive (source, cible, date_fusion) "
        . "VALUES (:oldCity, ("
        . "SELECT ville_id "
        . "FROM villes_france_free "
        . "WHERE ville_code_commune = :newCity)"
        . ", NOW())";
    $response = $db->prepare($sql1);
    $response2 = $db->prepare($sql2);
    $response2->bindValue(':newCity', $codeNew, PDO::PARAM_INT);
// List les élements à passer en param
    foreach ($list as $k => $id) {
        $response->bindValue(($k + 1), $id, PDO::PARAM_INT);
        $response2->bindValue(':oldCity', $id, PDO::PARAM_INT);
        $response2->execute(); // Insère les n lignes
    }
    $response->execute();
}

/**
 * Récupère les informations clés des villes à fusionner
 * @param array $list La liste des villes à fusionner
 */
function oldCityInfo($list) {
// Génération des paramètres de la requête SQL
    $inQuery = implode(',', array_fill(0, count($list), '?'));

    $db = connexionBD();
    $sql = "SELECT AVG(ville_densite_2010) AS densite, SUM(ville_surface) AS superficie,
                MIN(ville_zmin) AS zmin, MAX (ville_zmax) AS zmax, SUM(ville_population_2010) AS population
                FROM villes_france_free
                WHERE ville_id IN (" . $inQuery . ")";
    $response = $db->prepare($sql);

// List les élements à passer en param
    foreach ($list as $k => $id) {
        $response->bindValue(($k + 1), $id, PDO::PARAM_INT);
    }

    $response->execute();
    if ($response->rowCount() > 0) {
        $row = $response->fetch();
        $returnArray = array(
            'densite' => round($row['densite'], 2),
            'superficie' => $row['superficie'],
            'zmin' => $row['zmin'],
            'zmax' => $row['zmax'],
            'population' => $row['population']
        );
    }
    $response->closeCursor();
    return isset($returnArray) ? $returnArray : null;
}

/**
 * Retourne le nombre de ville présente dans un département donné
 * @param int $codeDep Un code de département
 */
function getNumberCityFromADep($codeDep) {

    $sql = "SELECT MAX(ville_code_commune) FROM villes_france_free WHERE ville_departement = :dep";
    $db = connexionBD();
    $response = $db->prepare($sql);
    $response->bindValue(':dep', $codeDep, PDO::PARAM_INT);
    $response->execute();
    return substr($response->fetch()[0] + 1, -($codeDep < 100 ? 3 : 2));
}

/**
 * Test de recherche plainText
 * @param str $keyWord Le mot à rechercher
 */
function plainTextSearch($keyWord) {

    $db = connexionBD();
    $sql = "SELECT * "
        . "FROM villes_france_free "
        . "WHERE texte_vectorise @@ to_tsquery(:mot)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':mot', generateSQLSearchRequest($keyWord), PDO::PARAM_STR);
    try {
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            // do nothing
            echo $row["ville_id"] . "<br>";
        }
        $stmt->closeCursor();
    } catch (Exception $e) {
        $e->getMessage();
    }
}

function generateSQLSearchRequest($keyWord) {
    // Découpe à tous les espaces
    $tab = explode(' ', $keyWord);
    $str = ''; // chaine de retour
    $compt = 0; // Compteur d'occurence d'opérande
    // On cherche la première chaine sans opérande
    while ($compt < count($tab) && ($tab[$compt] == '|' || $tab[$compt] == '&')) {
        $compt++;
    }

    // Parcours tous les éléments du tableau à partir de la première occurence valide
    for ($i = $compt; $i < count($tab); $i++) {
        // Si premier indice ou on a placé un opérateur avant, on met pas d'opérande
        if ($i == $compt || ($i > 0 && ($tab[$i - 1] == '|' || $tab[$i - 1] == '&'))) {
            $str .= $tab[$i];
        } else { // ajoute une opérande si ce n''est pas une opérande, sinon met tel quel
            $str .= $tab[$i] != '|' ? " & " . $tab[$i] : $tab[$i];
        }
    }
    echo $str;
    // Renvoie la chaine de requete
    return $str;
}
