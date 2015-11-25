<?php

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
            'titre' => $row['photo_desc'],
            'rang' => $row['rang'],
            'ville' => $row['photo_ville'],
            'id' => $row['photo_id']
        );
    }

    return $returnArray;
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
            $imgTab[$c]['titre'] = $row['photo_desc'];
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

    $response = $db->prepare($sql);

// Change ? into the correct value
    $response->bindValue(':ville', $cityCode, PDO::PARAM_INT);
    $response->bindValue(':rang', $pictureRang, PDO::PARAM_INT);

    $response->execute();

// Picture doesn't exist
    $id = $response->rowCount() > 0 ? $response->fetch()["photo_id"] : null;

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
