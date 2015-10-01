<?php

require_once("db_connexion.php");

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
