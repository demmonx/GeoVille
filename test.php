<?php

require("ressources/function.php");
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define('CHARSET', 'UTF-8');
define('REPLACE_FLAGS', ENT_QUOTES);
$var = "Castres (occitanÂ : Castras) est une commune franÃ§aise situÃ©e dans le dÃ©partement du Tarn en rÃ©gion Midi-PyrÃ©nÃ©es.";
$var = htmlspecialchars_decode($var, REPLACE_FLAGS, CHARSET);
echo $var;
//plainTextSearch("Villefranche de rouergue | castres");
//DROP TRIGGER tsvectorupdate ON villes_france_free;
//CREATE TRIGGER tsvectorupdate BEFORE INSERT OR UPDATE ON villes_france_free FOR EACH ROW EXECUTE PROCEDURE tsvector_update_trigger('texte_vectorise', 'pg_catalog.english', 'ville_nom');


