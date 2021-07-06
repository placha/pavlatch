<?php

if ($_GET['debug'] === '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

include __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

try {
    $server = new \pavlatch\Server($config);
    $server->run();
    echo $server->getResponse();
} catch (\pavlatch\Exception\ServerException $e) {
    echo $e->getResponse();
}
