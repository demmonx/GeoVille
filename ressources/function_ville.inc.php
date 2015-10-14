<?php




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
        $message = htmlspecialchars_decode($response->fetch()['ville_description'],
            ENT_QUOTES);
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


// Get closest city from an other one (x km)
function getCloseCity($latitude, $longitude, $cityCode) {
    $km = 0.015060; // Valeur en degré d'un km
    $ecart = $km * getConfigFile()["rayon_ville"]; // ecart entre les villes en degré


    $db = connexionBD();

    $sql = "SELECT *
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
        $retour = getCloseCityInfo($response, $latitude, $longitude);
    }
    $response->closeCursor();
    return isset($retour) ? $retour : $retour;
}

/**
 * Renvoie un tableau de villes exploitables avec les distances
 * @param stmt $response La réponse de la base de données
 */
function getCloseCityInfo($response, $latitude, $longitude) {
    $distance = getConfigFile()["rayon_ville"];
    $i = 0; // Compteur de ville pour le département

    $returnArray = array();
    while ($row = $response->fetch()) {

        $size = distance($latitude, $longitude, $row["ville_latitude_deg"],
            $row["ville_longitude_deg"]); // Get exact distance between city
        if ($size == 0 || $size > $distance) { // If distance is higher
            continue;
        }
        $returnArray[$i] = extractCityInfoFromARow($row);
        $returnArray[$i]['distance'] = $size;
        $i ++;
    }
    return $returnArray;
}

// List cities from specified sql
function getCity($sqlRq) {
    $db = connexionBD();

    $sql = "SELECT *
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

// Convertit un curseur de base de données en tableau de ville
function getCityListFromStatement($response) {
    if ($response == null || $response->rowCount() <= 0) {
        return null;
    }

    $returnArray = array();
    $i = 0;
    while ($row = $response->fetch()) {
        $returnArray[$i++] = extractCityInfoFromARow($row);
    }
    return $returnArray;
}

// Transforme un curseur de base de données en liste de ville exploitable
function convertCityListToArray($list) {
    $returnArray = null;
    $dep_num = "00"; // Numero de département
    $reg_nom = null; // nom de region
    $region = 0; // Compteur de région
    $departement = 0; // Compteur de département pour la région
    $ville = 0; // Compteur de ville pour le département
    if ($list == null || count($list) <= 0) {
        return false;
    }

    // Convertit la liste
    foreach ($list as $row) {

// Rank by region
        if ($row["code_region"] != $reg_nom) {
            $reg_nom = $row["code_region"];
            $region ++;
            $departement = 0;
        }

// And rank by county
        if ($row["code_departement"] != $dep_num) {
            $dep_num = $row["code_departement"];
            $departement ++;
            $ville = 0;
        }

// Add item to return tab
        $returnArray[$region - 1][$departement - 1][$ville] = $row;
        $ville ++;
    }
    return $returnArray;
}

// Retourne une liste de n villes comprises entre pop-marge et pop+marge
function getSameSizeCity($cityCode) {
    $marge = getConfigFile()["population_coeff"];
    $nb = getConfigFile()["nombre_ville_pop"];

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
        PDO::PARAM_STR);
    $response->bindValue(':id', $cityCode, PDO::PARAM_INT);
    $response->bindValue(':region', $info["code_region"], PDO::PARAM_INT);

    $response->execute();

    $returnArray = getCityListFromStatement($response);

// close cursor
    $response->closeCursor();
    return $returnArray;
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
    $stmt->bindValue(':codePostal', $param[0], PDO::PARAM_STR);
    $stmt->bindValue(':population', $param[1], PDO::PARAM_INT);
    $stmt->bindValue(':densite', $param[3], PDO::PARAM_INT);
    $stmt->bindValue(':superficie', $param[2], PDO::PARAM_INT);
    $stmt->bindValue(':altMin', $param[4], PDO::PARAM_INT);
    $stmt->bindValue(':altMax', $param[5], PDO::PARAM_INT);
    $stmt->bindValue(':desc', $param[6], PDO::PARAM_LOB);

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
    $response->bindValue(':departement', $codeDep, PDO::PARAM_STR);

    $response->execute();

    if ($response->rowCount() == 1) {
        $row = $response->fetch();

        $returnArray = array(
            "superficie" => $row["surface"],
            "population" => $row["pop"],
            "densite" => $row["densite"],
            "departement" => $row["nom"],
            "code_departement" => $row["num_departement"]
        );
    } // else NO DATA FOUND
    $response->closeCursor();

    return isset($returnArray) ? $returnArray : null;
}

// Retourne les informations des n plus grandes communes d'un département
function getBiggestCityOfDep($codeDep) {
    $nb = getConfigFile()["nb_grande_ville_dep"];
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
    $response->bindValue(':departement', $codeDep, PDO::PARAM_STR);
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
            $returnArray[$i]['region'] = $row["nom_r"];
            $returnArray[$i]['departement'] = $row["nom"];
            $returnArray[$i]['code_departement'] = $row["num_departement"];
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
        "nom" => htmlspecialchars_decode($aRow["ville_nom"]),
        "longitude" => $aRow["ville_longitude_deg"],
        "latitude" => $aRow["ville_latitude_deg"],
        "departement" => $aRow["nom"],
        "code_departement" => $aRow["num_departement"],
        "code_region" => $aRow["num_region"],
        "region" => $aRow["nom_r"],
        "code_postal" => htmlspecialchars_decode($aRow["ville_code_postal"]),
        "population" => $aRow["ville_population_2010"],
        "densite" => $aRow["ville_densite_2010"],
        "superficie" => $aRow["ville_surface"],
        "alt_min" => $aRow["ville_zmin"],
        "alt_max" => $aRow["ville_zmax"],
        "description" => nl2br(html_entity_decode($aRow["ville_description"]))
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
    $response->bindValue(':departement', $codeDep, PDO::PARAM_STR);

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
            "region" => $row["nom_r"],
            "code_region" => $row["num_region"]
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
                "region" => $row["nom_r"],
                "code_region" => $row["num_region"]
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
                "departement" => $row["nom"],
                "code_region" => $row["num_region"],
                "code_departement" => $row["num_departement"]
            );
        }
    } // else NO DATA FOUND
// close the cursor
    $response->closeCursor();

    return $returnArray;
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
    $stmt->bindValue(":densite", floor($info["densite"]), PDO::PARAM_INT);
    $stmt->bindValue(":cp", $info["code_postal"], PDO::PARAM_STR);
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
        "code_postal" => $aOldCity["code_postal"],
        "population" => $infoZone["population"],
        "densite" => $infoZone["densite"],
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
    $response->bindValue(':dep', $codeDep, PDO::PARAM_STR);
    $response->execute();
    return substr($response->fetch()[0] + 1, -($codeDep < 100 ? 3 : 2));
}
