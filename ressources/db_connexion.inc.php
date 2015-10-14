<?php

/** Renvoie l'hote + la BD */
function getChaineConnexion() {
    return "pgsql:host=localhost;dbname=" . getConfigFile()["bd_nom"];
}

// Connexion à la base de donnée (PDO)
function connexionBD() {
    return connexion(getConfigFile()["bd_user"], "jevisite/*78");
}

// Connexion à la base de donnée en admin(PDO)
function connexionBDAdmin() {
    return connexion(getConfigFile()["bd_admin"], "jesuislechef%*89");
}

/**
 * Nouvelle connexion à la BD pour un couple user/pass donné
 * @param  String $user L'identifiant de l'utilisateur
 * @param String $pass Le mot de passe de l'utilisateur
 * @return stmt La connexion active ou null à la bd
 */
function connexion($user, $pass) {
    try {
        $db = new PDO(getChaineConnexion(), $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Échec lors de la connexion : ' . $e->getMessage();
        $db = null;
    }
    return $db;
}
