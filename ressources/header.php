<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>

        <!-- CSS -->
        <!--<link
                href="https://cdn.datatables.net/1.10.9/css/dataTables.bootstrap.min.css"
                media="all" rel="stylesheet" type="text/css" />
        <link
                href="    //maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
                media="all" rel="stylesheet" type="text/css" />-->
        <link href="/ressources/style.css" media="all" rel="stylesheet"
              type="text/css" />

        <!-- Metadonnées -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- Titre -->
        <title><?php echo isset($titre) ? $titre : "Ville"; ?></title>

        <!-- Javascript -->
        <script type="text/javascript" charset="utf8"
        src="/ressources/jquery.js"></script>
        <!-- DataTables -->
        <script type="text/javascript" charset="utf8"
        src="//cdn.datatables.net/1.10.9/js/jquery.dataTables.js"></script>
        <script type="text/javascript"
        src="<?php echo isset($js) ? $js : ""; ?>"></script>

    </head>
    <body>
        <nav>
            <table class='menu'>
                <tr>
                    <td><a href='/index.php'>ACCUEIL</a></td>
                    <td>|</td>
                    <td><a href='/search_city.php'>RECHERCHE</a></td>
                    <td>|</td>
                    <td><a href='/admin/index.php'>MODIFICATION</a></td>
                    <td>|</td>
                    <td><a href='/admin/fusion_villes.php'>FUSION</a></td>
                </tr>
            </table>
        </nav>