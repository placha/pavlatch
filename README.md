# Pavlatch - extremely simple storage api

I wanted to replace AWS S3 with some cheaper solution for small project.

Only two features:
- receiving file and store it locally
- checking if file already exist

## Requirements

Space on hosting with php :smile:

## index.php

```
<?php

include __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

try {
    new \pavlatch\Server($config);
} catch (\pavlatch\Exception\ServerException $e) {
    echo $e->getMessage();
}

```

## Client
Use https://github.com/kacperplacha/pavlatch-client
