$(document).ready(function () {
    var code = $("#code").val();
    $('.delete-pic').on('click', function (e) {
        e.preventDefault(); // bloque le click sur le lien
        // confirmation
        if (confirm("Supprimer la photo selectionnée ?")) {
            // requete de suppression
            $.ajax({
                url: $(this).attr("href"),
                type: 'GET',
                success: function (html) { // Récupération de la réponse
                    // recharge la liste des images si ok
                    if (html === "Suppression réussie") {
                        $("#photos").load('liste_photos.php?code=' + code);
                    } else {
                        alert(html);
                    }
                }
            });
        }
    });

    $('.sens-edit').on('click', function (e) {
        e.preventDefault(); // bloque le click sur le lien
        // requete de modification
        $.ajax({
            url: $(this).attr("href"),
            type: 'GET',
            success: function (html) { // Récupération de la réponse
                // recharge la liste des images si ok
                if (html === "Succès") {
                    $("#photos").load('liste_photos.php?code=' + code);
                } else {
                    alert(html);
                }
            }
        });
    });

    $('.name-edit').on('submit', function (e) {
        e.preventDefault(); // bloque le click sur le lien
        var form = $(this); // L'objet jQuery du formulaire
        var name = form.find('input[name="title"]').val();  // Valeur du champ pseudo
        if (name === '') {
            alert('Le champ doit être  rempli');
        } else {
            // requete de modification
            $.ajax({
                url: form.attr('action'), // cible (formulaire)
                type: form.attr('method'), // méthode (formulaire)
                data: form.serialize(), // Envoie de toutes les données
                success: function (html) { // Récupération de la réponse
                    // recharge la liste des images si ok
                    if (html === "Succès") {
                        $("#photos").load('liste_photos.php?code=' + code);
                    }
                    alert(html);
                }
            });
        }
    });
});