$(document).ready(function () {

    $('#region_choix').on('change', function () {
        window.location.href = "index.php?code=" + $('#region_choix').val();
    });

});