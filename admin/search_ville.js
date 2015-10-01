$(document).ready(function () {
    /** * Formulaire de recherche ** */
    $('#search').on('submit', function (e) {
        e.preventDefault(); // Empeche de soumettre le formulaire
        var form = $(this); // L'objet jQuery du formulaire

        var nom = $('#nom').val();  // Valeur du champ nom
        var cp = $('#codePostal').val();  // valeur du champ code postal
        var dep = $('#departement').val() > 0 ? $('#departement').val() : null;
        $('#msgReturn').empty();
        // Vérifie pour éviter de lancer une requête fausse
        if (nom === '' && cp === '' && dep === '') {
            $('#msgReturn').append('Au moins un des champs doit être rempli');
        } else if (cp !== '' && isNaN(cp)) {  // Code postal au format numérique
            $('#msgReturn').append('Le format du code postal est invalide');
        } else if (dep !== '' && isNaN(dep)) {
            $('#msgReturn').append('Le format du code département est invalide');
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
});
