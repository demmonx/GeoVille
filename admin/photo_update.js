$(document).ready(function() {
	var code = $("#code").val();
	
	/** * Formulaire de recherche ** */	
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault(); // Empeche de soumettre le formulaire
        var form = $(this); // L'objet jQuery du formulaire
        var formdata = (window.FormData) ? new FormData(form[0]) : null;
        var data = (formdata !== null) ? formdata : form.serialize();
 
	    var fichier = $('#uploadFile').val();  // fichier
		var titre = $('#titre').val();  // descripion
        $('#msgReturn').empty();
        
        // Vérifie pour éviter de lancer une requête fausse
        if(titre === '' || fichier === '') {
        	$('#msgReturn').append('Les champs doivent être remplis');
		} else {
            // Envoi de la requête HTTP en mode asynchrone
            $.ajax({
                url: form.attr('action'), // cible (formulaire)
                type: form.attr('method'), // méthode (formulaire)
                enctype: 'multipart/form-data',
                contentType: false, // obligatoire pour de l'upload
                processData: false, // obligatoire pour de l'upload
                data: data, // Envoie de toutes les données
                success: function(html) { // Récupération de la réponse
                    $('#msgReturn').append(html);  // affichage du résultat
                    // On efface si ok
                    if (html == "Ajout effectué avec succès") {
                    	$('#uploadFile').val('');
						$('#titre').val('');	
						$("#image_preview").find('.thumbnail').addClass('hidden');
						$("#photos").load('liste_photos.php?code=' + code);
                    }
                }
            });
        }
    });
    
    $('#uploadForm').find('input[name="fichier"]').on('change', function (e) {
        var files = $(this)[0].files;
 
        if (files.length > 0) {
            // On part du principe qu'il n'y qu'un seul fichier
            // étant donné que l'on a pas renseigné l'attribut "multiple"
            var file = files[0],
                $image_preview = $('#image_preview');
 
            // Ici on injecte les informations recoltées sur le fichier pour l'utilisateur
            $image_preview.find('.thumbnail').removeClass('hidden');
            $image_preview.find('img').attr('src', window.URL.createObjectURL(file));
            $image_preview.find('.caption p:first').html(Math.floor(file.size/1000) +' Ko');
        }
    });
 
    // Bouton "Annuler" pour vider le champ d'upload
    $('#image_preview').find('button[type="button"]').on('click', function (e) {
        e.preventDefault();
 
        $('#uploadForm').find('input[name="fichier"]').val('');
        $('#image_preview').find('.thumbnail').addClass('hidden');
    });
    
    
});  