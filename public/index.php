<?php

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__.$_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

// set timezone for timestamps etc
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

// Run app
$app = (new Pyjac\NaijaEmoji\App(__DIR__.'/../'))->get();

$app->run();
