<?php

$file1 = __DIR__ . '/../vendor/autoload.php'; // lokal working copy
$file2 = __DIR__ . '/../../../../../autoload.php'; // installed via composer

$loader1 = file_exists($file1) ? include $file1 : false;
$loader2 = file_exists($file2) ? include $file2 : false;

if (!$loader1 && !$loader2) {
    echo 'The project dependencies are missing. Unable to locate the vendor autoload file.' . PHP_EOL;
    echo 'Paths tried:' . PHP_EOL;
    echo "- $file1" . PHP_EOL;
    echo "- $file2" . PHP_EOL;
    echo PHP_EOL;
    echo 'If you are developing Environaut, please run:' . PHP_EOL;
    echo '    make install-dependencies-dev' . PHP_EOL . PHP_EOL;
    echo 'If you are using Environaut via Composer please run:' . PHP_EOL;
    echo '    curl -sS https://getcomposer.org/installer | php' . PHP_EOL;
    echo '    php composer.phar install' . PHP_EOL;
    echo 'OR' . PHP_EOL;
    echo '    ./composer.phar require graste/environaut <version>' . PHP_EOL;
    exit(1);
}

if ($loader1) {
    echo "Loaded: $file1" . PHP_EOL;
    return $loader1;
} else {
    echo "Loaded: $file2" . PHP_EOL;
    return $loader2;
}
