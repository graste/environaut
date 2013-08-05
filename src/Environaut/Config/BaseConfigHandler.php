<?php

namespace Environaut\Config;

use Environaut\Config\IConfigHandler;
use Environaut\Config\Config;
use Environaut\Config\Parameters;

/**
 * Base class that may be used for custom config handlers.
 * Implements basic config data merging from multiple locations.
 */
abstract class BaseConfigHandler implements IConfigHandler
{
    /**
     * @var array of locations (usually file/directory paths)
     */
    protected $locations;

    /**
     * @var Parameters array of data to customize config handler wrapped in a Parameters instance
     */
    protected $parameters;

    /**
     * Create new instance of the concrete config handler.
     *
     * @param array $locations set of locations to lookup configs to merge
     * @param array $parameters to customize the config file lookup/handling
     */
    public function __construct(array $locations = array(), array $parameters = array())
    {
        $this->locations = $locations;
        $this->parameters = new Parameters($parameters);
    }

    /**
     * Returns the currently (read and merged) config data
     * as a concrete IConfig implementating class.
     *
     * @return IConfig
     */
    public function getConfig()
    {
        return new Config($this->getMergedConfig());
    }

    /**
     * Adds the given location to the set of locations to check for configs.
     *
     * @param mixed $location location to check for config files (usually a file/directory path)
     *
     * @throws \InvalidArgumentException if given location is not readable
     */
    public function addLocation($location)
    {
        if (!is_readable($location)) {
            throw new \InvalidArgumentException("Given location is not readable: $location");
        }

        $this->locations[] = $location;
    }

    /**
     * Set the locations to check for config files.
     *
     * @param array $locations set of locations (usually file/directory paths)
     */
    public function setLocations(array $locations)
    {
        foreach ($locations as $location) {
            $this->addLocation($location);
        }
    }

    /**
     * @return array of locations used for config file lookups
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @return array of config data
     */
    protected function getMergedConfig()
    {
        $config_data = array();

        foreach ($this->getLocations() as $location) {
            $more_config_data = $this->readLocation($location);
            if (is_array($more_config_data)) {
                $config_data = array_merge_recursive($config_data, $more_config_data);
            }
        }

        return $config_data;
    }
}
