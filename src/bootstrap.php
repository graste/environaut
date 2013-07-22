<?php

$file = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($file)) {
    echo 'Unable to locate vendor directory. The project dependencies are missing.' . PHP_EOL;
    echo 'Please run: "make install-dependencies-dev"' . PHP_EOL;
    exit(1);
}

return include($file);
