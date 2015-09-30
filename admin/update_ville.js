$(document).ready(function () {
    /** * Formulaire de connexion ** */
    $('#updateVille').on('submit', function (e) {
        e.preventDefault(); // Empeche de soumettre le formulaire
        var form = $(this); // L'objet jQuery du formulaire

        var codePostal = $('#codePostal').val();
        var population = $('#population').val();
        var superficie = $('#superficie').val();
        var altMin = $('#altMin').val();
        var altMax = $('#altMax').val();
        $('#msgReturn').empty();

        error = [[], [], [], [], [], []];

        // check data format
        error[0][0] = !isNaN(codePostal) && codePostal > 0;
        error[0][1] = "Le code postal est invalide.";
        error[1][0] = !isNaN(population) && population > 0;
        error[1][1] = "Le nombre d'habitants est invalide.";
        error[2][0] = !isNaN(superficie) && superficie > 0;
        error[2][1] = "La superficie est invalide.";
        error[3][0] = !isNaN(altMin) && altMin >= 0;
        error[3][1] = "L'altitude minimale est invalide.";
        error[4][0] = !isNaN(altMax) && altMax > 0;
        error[4][1] = "L'altitude maximale est invalide.";
        error[5][0] = altMax >= altMin;
        error[5][1] = "L'altitude maximale doit être supérieure ou égale à l'altitude minimale";


        // display error if there are
        var sansErreur = true;
        for (var i = 0; i < error.length; i++) {
            sansErreur = error[i][0];
            if (!sansErreur) {
                $('#msgReturn').append(error[i][1]);
                break;
            }
        }

        // Vérifie pour éviter de lancer une requête fausse
        if (sansErreur) {
            // Envoi de la requête HTTP en mode asynchrone
            $.ajax({
                url: form.attr('action'), // cible (formulaire)
                type: form.attr('method'), // méthode (formulaire)
                data: form.serialize(), // Envoie de toutes les données
                success: function (html) { // Récupération de la réponse
                    $('#densite').empty().append(Math.floor(population / superficie));
                    $('#msgReturn').append(html);  // affichage du résultat
                }
            });
        }
    });
});
