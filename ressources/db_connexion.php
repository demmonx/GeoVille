<?php

// Connexion à la base de donnée (PDO)
function connexionBD() {
    try {
        $db = new PDO('pgsql:host=localhost;dbname=geo', "visiteur", "jevisite/*78");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Échec lors de la connexion : ' . $e->getMessage();
    }
    return $db;
}

// Connexion à la base de donnée (PDO)
function connexionBDAdmin() {
    try {
        $db = new PDO('pgsql:host=localhost;dbname=geo', "gerant", "jesuislechef%*89");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Échec lors de la connexion : ' . $e->getMessage();
    }
    return $db;
}
