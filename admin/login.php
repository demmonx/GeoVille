<?php
$titre = 'Connexion';
$js = 'login.js';
require_once ("../ressources/header.php");
?>
<h1>Connexion</h1>
<form id="login" action="login_trait.php" method="post">
	<input type="text" name="pseudo" id="pseudo" placeHolder="Pseudo" /><br>
	<input type="password" name="pass" id="pass" placeHolder="password" /><br>
	<input type="submit" value="Connexion" />
</form>
<div id="msgReturn"></div>
</body>
</html>
