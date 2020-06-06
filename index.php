<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include __DIR__ . '/vendor/autoload.php';

new \pavlatch\Server(['secureKey' => '6a4068f2-2cde-494d-90e1-08ba5827a677']);
