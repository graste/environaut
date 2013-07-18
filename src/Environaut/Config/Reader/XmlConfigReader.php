<?php

namespace Environaut\Config\Reader;

use Environaut\Config\Reader\IConfigReader;

/**
 * Reads configuration data from XML files.
 */
class XmlConfigReader implements IConfigReader
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
        throw new \Exception('Not yet implemented.');
    }
}
