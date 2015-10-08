$(document).ready(function () {

    // Liste les villes du département
    $('#departement').on('change'
            , function () {
                var code = $('#departement').val();
                $("#liste").html("");
                if (code !== '') {
                    var url = "liste_villes.php?code=" + code;
                    $("#liste").load(url);
                }
            });

    // Envoi du formulaire
    $('#fusion-commune').on('submit', function (e) {
        e.preventDefault(); // Empeche de soumettre le formulaire
        var form = $(this); // L'objet jQuery du formulaire
        var check = $("input:checked").length;
        var nom = $('#nom').val();  // Valeur du champ pseudo
        $('#msgReturn').empty();

        // Vérifie pour éviter de lancer une requête fausse
        if ($('#departement').val() === '') {
            $('#msgReturn').append('Vous devez choisir un département');
        } else if (nom === '' || check === 0) {
            $('#msgReturn').append('Les champs doivent êtres remplis');
        } else if (check < 2 || check > 6) {
            $('#msgReturn').append('Fusion possible entre 2 et 6 communes uniquement');
        } else {
            // Envoi de la requête HTTP en mode asynchrone
            $.ajax({
                url: form.attr('action'), // cible (formulaire)
                type: form.attr('method'), // méthode (formulaire)
                data: form.serialize(), // Envoie de toutes les données
                success: function (html) { // Récupération de la réponse
                    $('#msgReturn').append(html);  // affichage du résultat
                    if (html === 'Succès') {
                        form.get(0).reset();
                        $("#liste").load("liste_villes.php?code=" + $('#departement').val());
                    }
                }
            });
        }
    });
});

