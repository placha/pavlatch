# Pavlatch - extremely simple storage api

I wanted to replace AWS S3 with some cheaper solution for small project.

Only two features:
- receiving file and store it locally
- checking if file already exist

## index.php

```
<?php

include __DIR__ . '/vendor/autoload.php';

$config = include __DIR__ . '/config.php';

if ($config['env'] === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

new \pavlatch\Server($config);

```
