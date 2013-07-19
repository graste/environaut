<?php

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php'))) {
    echo 'Unable to locate vendor directory. The project dependencies are missing.' . PHP_EOL;
    echo 'Please run: "make install-dependencies-dev"' . PHP_EOL;
    exit(1);
}

return $loader;
