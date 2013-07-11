<?php

function includeIfExists($file)
{
    if (file_exists($file))
    {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')))
{
    echo 'The project dependencies are missing. Please run Composer:' . PHP_EOL .
        'curl -sS https://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL;
    exit(1);
}

return $loader;
