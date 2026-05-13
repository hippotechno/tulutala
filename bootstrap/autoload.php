<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register Core Helpers
|--------------------------------------------------------------------------
|
| We cannot rely on Composer's load order when calculating the weight of
| each package. This line ensures that the core global helpers are
| always given priority one status.
|
*/

$helperPaths = [
    __DIR__.'/../vendor/hippo/storm/src/Support/helpers.php',
    __DIR__.'/../vendor/winter/storm/src/Support/helpers.php',
];

$helperPath = null;
foreach ($helperPaths as $candidatePath) {
    if (file_exists($candidatePath)) {
        $helperPath = $candidatePath;
        break;
    }
}

if ($helperPath === null) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Missing Storm helper files. Expected hippo/storm to provide vendor/hippo/storm/src/Support/helpers.php.'.PHP_EOL;
    echo 'Tried:'.PHP_EOL;
    foreach ($helperPaths as $candidatePath) {
        echo ' - '.$candidatePath.PHP_EOL;
    }
    echo 'Run "composer install" or verify the hippo/storm package name, repository, and autoload files.'.PHP_EOL;
    exit(1);
}

require $helperPath;

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';
