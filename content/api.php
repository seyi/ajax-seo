<?php

// --------------------------------------------------
// API
// --------------------------------------------------



// Simulate API slow responding
// --------------------------------------------------
// sleep(3);



header('X-Robots-Tag: nosnippet');



// Check if url exist
if (mysql_num_rows($result)) {
    // HTTP header caching
    include 'content/cache.php';

    $datemod = new datemod();
    $datemod -> date(array(
        '.htaccess',
        'index.php',
        'content/.htaccess',
        'content/api.php',
        'content/cache.php',
        'content/connect.php'
    ), MYSQL_TABLE, $url);
    $datemod -> cache($datemod -> gmtime);

    $isset_jsonp = isset($_GET['callback']) ? true : false;

    if ($isset_jsonp) {
        header('Content-Type: application/javascript; charset=utf-8');
    } else {
        header('Content-Type: application/json; charset=utf-8');
    }

    while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
        $row[] = array(
            'row' => array_map('htmlspecialchars', $row)
        );
        $urlid = strip_tags($row['url']);
        $meta_title  = strip_tags($row['meta-title']);
        $title = strip_tags($row['title']);

        if (strlen($title) > 0) {
            $fn = ($meta_title !== $title) ? $title : $meta_title;
        } else {
            $fn = $meta_title;
        }

        $pagetitle = $meta_title . ' - AJAX SEO';

        // SEO page title improvement for the root page
        if (strlen($url) == 0) {
            $pagetitle = 'AJAX SEO';
        }

        $array = array(
            'url' => $urlid,
            'pagetitle' => $pagetitle,
            'title' => $meta_title,
            'content' => "<h1>$fn</h1>\n<p>{$row['content']}</p>\n"
        );

        // Use for latest PHP standards for http://php.net/json-encode
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            // Add option "JSON_PRETTY_PRINT" in case you care more readability than to save some bits
            $json = json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $json = str_replace('\\/', '/', json_encode($array));
        }

        echo $isset_jsonp ? $_GET['callback'] . '(' . $json . ')' : $json;
    }
    mysql_close($con);
} else {
    header('Content-Type: text/plain');

    // If URL does not exist, return 404 error
    http_response_code(404);
    exit('404 Not Found');
}