<?php
session_start();
$titre = 'Modifier une ville';
$js = 'update_ville.js';
require_once ("../ressources/header.php");
require_once ("../ressources/function.php");

$ville = null;
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
?>
<h1>Modification de la commune : <?php echo $city_informations['nom']; ?></h1>
<form id="updateVille" method="post" action="update_ville_trait.php">
    <fieldset>
        <legend>Administration</legend>
        <table>
            <tr>
                <td class="titre">Département :</td>
                <td><?php echo $city_informations['departement']; ?></td>
            </tr>
            <tr>
                <td class="titre">Région :</td>
                <td><?php echo $city_informations['region']; ?></td>
            </tr>
            <tr>
                <td class="titre">Code Postal :</td>
                <td><input type="number" name="codePostal" id="codePostal" min=0
                           value="<?php echo $city_informations['postalCode']; ?>" /></td>
            </tr>
        </table>
    </fieldset>

    <fieldset>
        <legend>Démographie</legend>
        <table>
            <tr>
                <td class="titre">Population :</td>
                <td><input type="number" name="population" id="population" min=0
                           value="<?php echo $city_informations['population']; ?>" />
                    habitants</td>
            </tr>
            <tr>
                <td class="titre">Densité :</td>
                <td><span id="densite"><?php echo $city_informations['densitePop']; ?></span>
                    hab/km²</td>
            </tr>
        </table>
    </fieldset>

    <fieldset>
        <legend>Géographie</legend>
        <table>
            <tr>
                <td class="titre">Superficie :</td>
                <td><input type="number" name="superficie" id="superficie"
                           step="any" min=0
                           value="<?php echo $city_informations['superficie']; ?>" /> km²</td>
            </tr>
            <tr>
                <td class="titre">Altitude mini :</td>
                <td><input type="number" name="altMin" id="altMin" min=0
                           value="<?php echo $city_informations['alt_min']; ?>" /> m</td>
            </tr>
            <tr>
                <td class="titre">Altitude maxi :</td>
                <td><input type="number" name="altMax" id="altMax" min=0
                           value="<?php echo $city_informations['alt_max'] ?>" /> m</td>
            </tr>
            <tr>
                <td class="titre">Latitude :</td>
                <td><?php echo $city_informations['latitude'] ?>°</td>
            </tr>
            <tr>
                <td class="titre">Longitude :</td>
                <td><?php echo $city_informations['longitude'] ?>°</td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Description</legend>
        <textarea id="description" name="description"><?php echo $city_informations['description'] ?></textarea>
    </fieldset>
    <fieldset>
        <legend>Photos</legend>
        <a href='gestion_image.php?code=<?php echo $ville_id ?>'>Accéder à
            l'interface</a>
    </fieldset>
    <input type="hidden" name="code" value="<?php echo $ville_id; ?>" /> <input
        type="submit" id="envoi" value="Modifier" />
</form>
<div id="msgReturn"></div>
</body>
</html>
