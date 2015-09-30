<?php

require("ressources/function.php");
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

plainTextSearch("Villefranche de rouergue | castres");
//DROP TRIGGER tsvectorupdate ON villes_france_free;
//CREATE TRIGGER tsvectorupdate BEFORE INSERT OR UPDATE ON villes_france_free FOR EACH ROW EXECUTE PROCEDURE tsvector_update_trigger('texte_vectorise', 'pg_catalog.english', 'ville_nom');


