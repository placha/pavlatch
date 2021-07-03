<?php

include __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

try {
    $server = new \pavlatch\Server($config);
    $server->run();
    echo $server->getResponse();
} catch (\pavlatch\Exception\ServerException $e) {
    echo $e->getResponse();
}
