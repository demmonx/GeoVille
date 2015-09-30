<?php
$titre = "Rechercher une commune";
$js = "search.js";
require_once ("ressources/header.php");
require_once ("ressources/function.php");
?>
<form method='post' action='search_city_trait.php' id='search'>
    <table>
        <tr>
            <td class="titre">Nom :</td>
            <td><input type='text' name="nom" id="nom" /></td>
        </tr>
        <tr>
            <td class="titre">Region :</td>
            <td><select name="region" id="region">
                    <option></option>
                    <?php
                    $listeReg = getRegion();
                    foreach ($listeReg as $reg) {
                        echo "<option value='" . $reg["code"] . "'>" . $reg['nom'] .
                        "</option>\n";
                    }
                    ?>
                </select></td>
        </tr>
        <tr>
            <td class="titre">Département :</td>
            <td><select name="departement" id="departement">
                    <option></option>
                    <?php
                    $listeDep = getDepartement();
                    foreach ($listeDep as $dep) {
                        echo "<option value='" . $dep["code"] . "'>" . $dep['nom'] .
                        "</option>\n";
                    }
                    ?>
                </select></td>
        </tr>
        <tr>
            <td class="titre">Code Postal :</td>
            <td><input type='number' id="codePostal" name="codePostal" /></td>
        </tr>
        <tr>
            <td class="titre">Population :</td>
            <td>De <input type='number' name="popMin" id="popMin" /> à <input
                    type='number' name="popMax" id="popMax" /> habitants
            </td>
        </tr>

    </table>
    <input type="submit" value="Rechercher" />
</form>
<div id='msgReturn'></div>

</body>
</html>