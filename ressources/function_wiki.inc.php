<?php
// Get the DOM of a page of wikipedia
function getDOM($cityName) {
    $page = null;

// Get wiki page name with JSON and check we are working on correct city
// <=> Reseach with REGEX,
// <=> Recherche avancée via moteur REGEX, then url conversion into String
// (strip accents) to compare
    $search = @file_get_contents(
                    "http://en.wikipedia.org/w/api.php?action=opensearch&search=" .
                    $cityName . "&namespace=0");
    if (!$search) {
        throw new Exception("Impossible d'accéder à Wiki"); // If URL is not
// reachable
    }
    $url_search = json_decode($search, true);
    for ($i = 0; $i < sizeof($url_search[3]); $i ++) {
// Check url integrity
        if (preg_match(
                        "/^https:\/\/en.wikipedia.org\/wiki\/" . $cityName . "$/i", wd_remove_accents(rawurldecode($url_search[3][$i])))) {
            $page = $url_search[3][$i];
        }
    }

// Changing url to get an french page instead of english
    $page = substr($page, 0, 8) . "fr" . substr($page, 10);

// Getting wikipedia page (about the specified city)
    require_once ('simple_html_dom.php');
    $doc = new DOMDocument();
    @$doc->loadHTMLFile($page);

    return $doc;
}

// Récupération du DOM de wiki
function getFromWiki($cityName) {
// coupe du nom de la ville
    $tab = cutCityName($cityName);
// récupération du tableau
    $retour['debut'] = $tab['debut'];
    $retour['fin'] = $tab['fin'];
// Formatage de l'url
// Récupération du DOM
    $retour['dom'] = getDOM(format2url($cityName));
// Renvoi du tableau avec tous les éléments
    return $retour;
}

// Return a short description about the city from wikipedia
function getDescriptionFromWiki($cityName) {
    $tab = getFromWiki($cityName);
    $message = null;

// City description
    foreach ($tab['dom']->getElementsByTagName('p') as $txt) {
        $message = $txt->nodeValue;

// Show first paragraph that contain the city
        if (preg_match(
                        "/" . $cityName . '|' . $tab['fin'] . '|' . $tab['debut'] . "/i", $message)) {
            $message = preg_replace("/\[\d+\]/i", '', $message);
            break;
        }
    }

    return $message;
}

// Get image about a city from wikipedia
function getPicturesFromWiki($cityName) {
    $tab = getFromWiki($cityName);

    $imgTab = array();

// Count how many image to show (0 to break;)
    $c = 0;
// Getting images
    foreach ($tab['dom']->getElementsByTagName('img') as $image) {
        $chemin = $image->getAttribute('src');

// Getting only image about the city (and not thing like blazon)
        if (preg_match("/" . $tab['debut'] . '|' . $tab['fin'] . "/i", $chemin) &&
                !preg_match("/blason|plan|carte|logo/i", $chemin)) {
            $desc = $image->getAttribute('alt');

// Show specified image
            $imgTab[$c]['path'] = $chemin;
            $imgTab[$c]['title'] = $desc;
            $c ++;
        }
// Stopping the loop at 5 (5 images show)
        if ($c == 5) {
            break;
        }
    }
    return $imgTab;
}
