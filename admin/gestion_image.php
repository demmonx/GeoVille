<?php
session_start();
$titre = 'Modification des photos';
$js = "photo_update.js";
require_once ("../ressources/header.php");
require_once ("../ressources/function.php");

$city_informations = null;

// check city ID
$ville_id = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);
if ($ville_id) {
    $city_informations = getCityInfo($ville_id);
}

// If user isn't logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    header("refresh:5;url=login.php");
    exit("<p>Vous devez vous connecter pour accéder à cette partie.<br />
    Vous serez redirigé automatiquement vers la page de connexion dans 5 secondes.<br />
    [ <a href='login.php'>Se connecter</a> ]</p>");
}
// check if it's a valid city
if (!isset($city_informations) || count($city_informations) <= 0) {
    exit("Erreur, aucunne donnée trouvée pour cette ville");
}

echo "<h1>Modification des images : " . $city_informations['nom'] .
 " </h1>";
?>
<form id="uploadForm" action="upload.php" method="post"
      enctype="multipart/form-data">
    <fieldset>
        <legend>Ajouter</legend>
        <table class='img-modif'>
            <tr>
                <td><input id="uploadFile" name="fichier" type="file"
                           accept="image/*" /></td>
                <td rowspan=3>
                    <div id="image_preview">
                        <div class="thumbnail hidden">
                            <img src="" alt="">
                            <div class="caption">
                                <p></p>
                                <p>
                                    <button type="button" class="btn btn-default btn-danger">Annuler</button>
                                </p>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><textarea maxlength="100" required rows="4" cols="80" id="titre"
                              name="titre" placeHolder="Titre" /></textarea></td>
            </tr>
            <td><input type="hidden" name="code" id="code"
                       value="<?php echo $ville_id; ?>" /> <input id="uploadSubmit"
                       type="submit" value="Ajouter" /> Max 1Mo</td>
            </tr>
            <tr>
                <td colspan=2><span id="msgReturn"></span></td>
            </tr>
        </table>
    </fieldset>
</form>
<fieldset>
    <legend>Modifier</legend>
    <div id='photos'>
        <?php require "liste_photos.php"; ?>
    </div>
</fieldset>
</form>
</body>
</html>
