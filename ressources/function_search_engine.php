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
    echo $str;
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
    return null;
}
