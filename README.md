# Pavlatch - extremely simple storage api

I wanted to replace AWS S3 with some cheaper solution for small project.

Only two features:
- receiving file and store it locally
- checking if file already exist

## Example of usage

```
include __DIR__ . '/vendor/autoload.php';

new \pavlatch\Server([
    'dir' => 'storage',
    'secureKey' => '6a4068f2-2cde-494d-90e1-08ba5827a677',
    'imageOnly' => true,
]);

```
