<?php

/**
 * Effectue une recherche PleinText sur le nom d'une ville
 * @param string $keyWord Le nom de la ville
 * @return array La liste des idenfiants des villes correspondants à la requête
 */
function plainTextSearch($keyWord) {
    $db = connexionBD();
    $sql = "SELECT ville_id "
            . "FROM villes_france_free "
            . "WHERE texte_vectorise @@ to_tsquery(:mot)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':mot', generateSQLSearchRequest($keyWord), PDO::PARAM_STR);
    try {
        $stmt->execute();
        // Generate retuin array
        $returnArray = null;
        $i = 0;
        while ($row = $stmt->fetch()) {
            // do nothing
            $returnArray[$i++] = $row["ville_id"];
        }
        $stmt->closeCursor();
    } catch (Exception $e) {
        $e->getMessage();
    }
    return isset($returnArray) ? $returnArray : null;
}

/**
 * Génère la chaine de recherche PlainText à partir d'une chaine standard
 * @param str $keyWord La chaine à rechercher
 * @return str La chaine de recherche au bon format
 */
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
    // Renvoie la chaine de requete
    return $str;
}

/**
 * 
 * @param array toSearch Les informations sur les villes à rechercher
 * @return array La liste des villes trouvées par la recherche
 */
function search($toSearch) {
    // Effectue une recherche de base pour commencer
    $resultat = basicSearch($toSearch);
    // si on trouve on renvoie les résultats
   if ($resultat != null && count($resultat) > 0) {
        return $resultat;
     }
    // Si on trouve pas et qu'un nom est défini, recherche PlainText
    if (isset($toSearch["nom"])) {
        $idList = plainTextSearch($toSearch["nom"]);
    }
    // Si on fait une requete PlainText et qu'il y a des valeurs valides, on fait une recherche basique dessus, sinon on renvoie rien
    return isset($idList) && count($idList) > 0 ? basicSearch($toSearch, $idList) : null;
}

/**
 * Effectue une recherche simple avec des paramètres spécifiés
 * @param array $toSearch Les informations sur les villes à rechercher
 * @param  array $ville_id Liste des identifiants sur lesquels récupérer les informations
 * @return array Le résultat de la requête
 */
function basicSearch($toSearch, $ville_id = null) {
    if ($toSearch == null) {
        return null;
    }
    $returnArray = null;
    $sql = getSqlSearchRequest($toSearch, $ville_id);
    $db = connexionBD();
    $stmt = $db->prepare($sql);
    setSqlSearchRequestParam($toSearch, $stmt, $ville_id);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $i = 0;
        while ($row = $stmt->fetch()) {
            $returnArray[$i++] = extractCityInfoFromARow($row);
        }
    }

    return $returnArray;
}

function getSqlSearchRequest($toSearch, $ville_id = null) {
    $sql = "SELECT *  
           FROM villes_france_free V, departements D, regions R
	   WHERE D.num_departement = V.ville_departement
           AND ville_statut = 'A'
	   AND R.num_region = D.num_region ";

    // Génération de la requête SQL en fonction des paramètres valides ou pas
    if ($toSearch["nom"] != null && $ville_id == null) {
        $sql .= " AND ville_nom LIKE UPPER(:nom) ";
    } else if ($toSearch["nom"] != null && $ville_id != null) {
        $str = '';
        for ($i = 0; $i < count($ville_id); $i++) {
            $str .= ':ville' . $i;
            if ($i + 1 != count($ville_id)) {
                $str .= ",";
            }
        }
        $sql .= " AND ville_id IN (" . $str . ")";
    }
    if ($toSearch["codePostal"] != null) {
        $sql .= " AND ville_code_postal LIKE :cp";
    }
    if ($toSearch["code_departement"] != null) {
        $sql .= " AND ville_departement = :dep";
    }
    if ($toSearch["code_region"] != null) {
        $sql .= " AND R.num_region = :reg";
    }
    if ($toSearch["popMin"] != null && $toSearch["popMax"] == null) {
        $sql .= " AND ville_population_2010 >= :popMin";
    }
    if ($toSearch["popMax"] != null && $toSearch["popMin"] == null) {
        $sql .= " AND ville_population_2010 <= :popMax";
    }
    if ($toSearch["popMin"] != null && $toSearch["popMax"] != null) {
        $sql .= " AND ville_population_2010 BETWEEN :popMin AND :popMax";
    }

    $sql .= " ORDER BY nom_r, num_departement, ville_nom";
    return $sql;
}

function setSqlSearchRequestParam($toSearch, $stmt, $ville_id = null) {
    // Génération de la requête SQL en fonction des paramètres valides ou pas
    if ($toSearch["nom"] != null && $ville_id == null) {
        $stmt->bindValue(':nom', "%" . $toSearch["nom"] . "%", PDO::PARAM_STR);
    } else if ($toSearch["nom"] != null && $ville_id != null) {
    // List les élements à passer en param
        foreach ($ville_id as $k => $id) {
            $stmt->bindValue(':ville' . $k, $id, PDO::PARAM_INT);
        }
    }
    if ($toSearch["codePostal"] != null) {
        $stmt->bindValue(':cp', "%" . $toSearch["codePostal"] . "%", PDO::PARAM_STR);
    }
    if ($toSearch["code_departement"] != null) {
        $stmt->bindValue(':dep', $toSearch["code_departement"], PDO::PARAM_STR);
    }
    if ($toSearch["code_region"] != null) {
        $stmt->bindValue(':reg', $toSearch["code_region"], PDO::PARAM_INT);
    }
    if ($toSearch["popMin"] != null && $toSearch["popMax"] == null) {
        $stmt->bindValue(':popMin', $toSearch["popMin"], PDO::PARAM_INT);
    }
    if ($toSearch["popMax"] != null && $toSearch["popMin"] == null) {
        $stmt->bindValue(':popMax', $toSearch["popMax"], PDO::PARAM_INT);
    }
    if ($toSearch["popMin"] != null && $toSearch["popMax"] != null) {
        $stmt->bindValue(':popMin', $toSearch["popMin"], PDO::PARAM_INT);
        $stmt->bindValue(':popMax', $toSearch["popMax"], PDO::PARAM_INT);
    }
    return $stmt;
}
