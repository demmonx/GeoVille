$(document).ready(function () {
    /** * Formulaire de connexion ** */
    $('#login').on('submit', function (e) {
        e.preventDefault(); // Empeche de soumettre le formulaire
        var form = $(this); // L'objet jQuery du formulaire

        var pseudo = $('#pseudo').val();  // Valeur du champ pseudo
        var pass = $('#pass').val();  // valeur du champ pass
        $('#msgReturn').empty();
        // Vérifie pour éviter de lancer une requête fausse
        if (pseudo === '' || pass === '') {
            $('#msgReturn').append('Les champs doivent êtres remplis');
        } else {
            // Envoi de la requête HTTP en mode asynchrone
            $.ajax({
                url: form.attr('action'), // cible (formulaire)
                type: form.attr('method'), // méthode (formulaire)
                data: form.serialize(), // Envoie de toutes les données
                success: function (html) { // Récupération de la réponse
                    $('#msgReturn').append(html);  // affichage du résultat
                    form.get(0).reset();

                }
            });
        }
    });
});
