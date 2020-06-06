<?php

include __DIR__ . '/vendor/autoload.php';

$config = include __DIR__ . '/config.php';

if ($config['env'] === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

new \pavlatch\Server($config);
