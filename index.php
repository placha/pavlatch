<?php

include __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

try {
    new \pavlatch\Server($config);
} catch (\pavlatch\Exception\ServerException $e) {
    echo $e->getMessage();
}
