<?php
session_start();
$titre = 'Fusion de communes';
$js = "fusion_villes.js";
require_once ("../ressources/header.php");
require_once ("../ressources/core.php");

// if user is not logged
if (!(isset($_SESSION['name']) && $_SESSION['name'] != null)) {
    header("refresh:5;url=login.php");

    exit("<p>Vous devez vous connecter pour accéder à cette partie.<br />
                Vous serez redirigé automatiquement vers la page de connexion dans 5 secondes.<br />
                [ <a href='login.php'>Se connecter</a> ]</p>");
}

echo "<h1>Fusionner des communes</h1>";
// Selecteur pour le département à afficher
echo "Choisir un département : <select id='departement'>";
$listeDep = getDepartement();
echo "<option></option>";
foreach ($listeDep as $dep) {
    echo "<option value='" . $dep["code"] . "'>" . $dep["code"] . " - " . $dep['nom'] . "</option>\n";
}
echo "</select>";
?>
<form action="fusion_ville_traitement.php" method="post" id="fusion-commune">
    <input type="text" name="nom" placeholder="Nom de la nouvelle commune" required />
    <input type="submit" value="Fusionner" />
    <span id="msgReturn"></span>
    <div id="liste" > </div>
</form>
</body>
</html>