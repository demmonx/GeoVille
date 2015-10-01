$(document).ready(function () {
    /** * Formulaire de recherche ** */
    $('#search').on('submit', function (e) {
        e.preventDefault(); // Empeche de soumettre le formulaire
        var form = $(this); // L'objet jQuery du formulaire

        var nom = $('#nom').val().trim();  // Valeur du champ nom
        var cp = $('#codePostal').val();  // valeur du champ code postal
        var dep = $('#departement').val() > 0 ? $('#departement').val() : null;
        var reg = $('#region').val() > 0 ? $('#region').val() : null;
        var popMin = $('#popMin').val();
        var popMax = $('#popMax').val();
        $('#msgReturn').empty();
        // Vérifie pour éviter de lancer une requête fausse
        if (nom === '' && cp === '' && dep === '' && reg === '' && popMin === '' && popMax === '') {
            $('#msgReturn').append('Au moins un des champs doit être rempli');
        } else if (cp !== '' && isNaN(cp)) {  // Code postal au format numérique
            $('#msgReturn').append('Le format du code postal est invalide');
        } else if (dep !== '' && isNaN(dep)) {
            $('#msgReturn').append('Le format du code département est invalide');
        } else if (reg !== '' && isNaN(reg)) {
            $('#msgReturn').append('Le format du code région est invalide');
        } else if (popMin !== '' && (isNaN(popMin) || popMin < 0)) {
            $('#msgReturn').append('La valeur de la population minimale est invalide');
        } else if (popMax !== '' && (isNaN(popMax) || popMax < 0)) {
            $('#msgReturn').append('La valeur de la population maximale est invalide');
        } else if (popMax !== '' && popMin !== '' && popMax < popMin) {
            $('#msgReturn').append("La valeur de la population maximale doit être "
                    + "supérieure à celle de la population minimale");
        } else {
            // Envoi de la requête HTTP en mode asynchrone
            $.ajax({
                url: form.attr('action'), // cible (formulaire)
                type: form.attr('method'), // méthode (formulaire)
                data: form.serialize(), // Envoie de toutes les données
                success: function (html) { // Récupération de la réponse
                    $('#msgReturn').append(html);  // affichage du résultat
                }
            });
        }
    });

    $('#region').on('change', function (e) {
        var code = $('#region').val();
        var url = "get_departement.php";
        if (code !== '') {
            url += "?code=" + code;
        }
        $("#departement").html("");
        $("#departement").load(url);

    });
});