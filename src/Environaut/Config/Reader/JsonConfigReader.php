<?php

namespace Environaut\Config\Reader;

class JsonConfigReader implements IConfigReader
{
    public function getConfigData($location)
    {
        if (is_dir($location)) {
            $location = $location . DIRECTORY_SEPARATOR . 'environaut.json';
        }

        if (!is_readable($location)) {
            throw new \InvalidArgumentException("Configuration file not readable: $location");
        }

        $content = file_get_contents($location);
        $config = json_decode($content, true);

        return $config;
    }
}
