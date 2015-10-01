<?php
session_start();
$titre = 'Chercher une ville';
$js = "search_ville.js";
require_once ("../ressources/header.php");

// If user isn't logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    header("refresh:5;url=login.php");
    exit("<p>Vous devez vous connecter pour accéder à cette partie.<br />
    Vous serez redirigé automatiquement vers la page de connexion dans 5 secondes.<br />
    [ <a href='login.php'>Se connecter</a> ]</p>");
}
echo "Bonjour <strong>" . $_SESSION['name'] . "</strong>";
?>
<a href="logout.php"><button>Déconnexion</button></a>
<h1>Rechercher une ville</h1>
<form id="search" action="search_ville_trait.php" method="post">
    <table>
        <tr>
            <td class="titre">Nom :</td>
            <td><input type='text' name="nom" id="nom" /></td>
        </tr>
        <tr>
            <td class="titre">Département :</td>
            <td><select name="departement" id="departement">
                    <?php
                    $listeDep = getDepartement();
                    displayInputOptionDepartement($listeDep);
                    ?>
                </select></td>
        </tr>
        <tr>
            <td class="titre">Code Postal :</td>
            <td><input type='number' id="codePostal" name="codePostal" /></td>
        </tr>

    </table>
    <input type="submit" value="Rechercher" />
</form>
<div id="msgReturn"></div>
</body>
</html>
