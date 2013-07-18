<?php

namespace Environaut\Config\Reader;

use Environaut\Config\Reader\IConfigReader;

/**
 * Reads a configuration from a JSON file that is
 * decoded into an associative config data array.
 */
class JsonConfigReader implements IConfigReader
{
    /**
     * Reads the config from the given location and
     * returns the data as an associative array.
     *
     * @param mixed $location location to read config data from (usually file/directory path)
     *
     * @return array config data as associative array
     *
     * @throws \InvalidArgumentException in case of problems handling the given location
     */
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
