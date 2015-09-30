<?php
$titre = "Villes de France";
require_once ("ressources/header.php");
require_once ("ressources/function.php");
define("NB_COL", 4);
$codeDep = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_REGEXP,
    array("options" => array("regexp" => getCodeDepartementRegex())));
if ($codeDep) {
    $cities = getCityFromDepartement($codeDep);
}

// Cas d'échec
if (!isset($cities) || count($cities) <= 0) {
    exit("Aucune ville ne correspond à ce département</body></html>");
} // else département ok

$cities = getCityFromDepartement($codeDep);
echo "<table>";

// Génere le tableau résultat
for ($z = 0; $z < count($cities); $z ++) {
    if ($z % NB_COL == 0) echo "<tr>\n";
    echo "\t<td><a href='ville.php?code=" . $cities[$z]["code"] . "'>" .
    $cities[$z]["nom"] . "</a><td>\n";
    if ($z % NB_COL == NB_COL - 1) echo "</tr>\n";
}

// On finit par mettre des cases propres à la fin
$val = $z % NB_COL;
while ($val > 0 && $val <= NB_COL) {
    if ($val < NB_COL) echo "\t<td></td>\n";
    else echo "</tr>\n";
    $val ++;
}
echo "</table>";
?>
</body>
</html>