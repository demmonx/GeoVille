<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

// Formatte pour URL encoding
function format2url($string) {
// Replace spaces by "_" for url encoding
    if (0 < strrpos($string, " ")) {
        $string = str_replace(" ", "_", $string);
    }
    return $string;
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

/**
 * Vérifie si la connexion est valide ou pas
 * @param array $tab Tableau des valeurs à regarder
 */
function loginOk($tab) {
    return isset($tab['name']) && $tab['name'] != null;
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
 * Ajoute un fichier sur le serveur
 *
 * @param String $basedir
 *            Repertoire dans lequel ajouté le fichier
 * @param String $prefix
 *            Prefixe à ajouter au fichier
 * @param array $format
 *            Format de fichiers autorisés
 * @param array $file
 *            Le fichier à upload
 */
function upload_file($basedir, $format, $file, $prefix = "",
    $poids_max = 1000000) {
    // nom du fichier choisi:
    $nomFichier = str_replace(' ', '', $file["name"]);
    // nom temporaire sur le serveur:
    $nomTemporaire = str_replace(' ', '', $file["tmp_name"]);
    // type du fichier choisi:
    $typeFichier = $file["type"];

    if ($file['size'] > $poids_max) {
        throw new InvalidArgumentException("L'image doit être inférieur à " . $poids_max
        / 1024 .
        "Ko.");
    }

    // Créé le dossier s'il n'existe pas
    if (!file_exists($basedir)) {
        mkdir($basedir, 0777, true);
    }

    // On préfixe comme il faut
    if (!empty($prefix)) $prefix = $prefix . "_";

    // Supression des caractères accentués
    $nom_fichier = strtr(trim($nomFichier),
        'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
        'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');

    $emplacement = $basedir . $prefix . $nom_fichier;

    // Vérification du format de fichier
    if ($format[array_search($typeFichier, $format)] != $typeFichier) {
        throw new InvalidArgumentException("Le type de l'image est invalide");
    }

    // Cas où le fichier est déjà là
    if (file_exists($emplacement)) {
        throw new InvalidArgumentException(
        "Un fichier avec le même nom existe déjà");
    }

    // Vérification de l'ajout
    if (move_uploaded_file($nomTemporaire, $emplacement)) {
        return ($emplacement);
    } else {
        throw new Exception("Erreur lors de la création du fichier");
    }
}
