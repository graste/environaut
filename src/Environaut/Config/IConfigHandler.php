<?php

namespace Environaut\Config;

/**
 * Default interface all Environaut config handlers
 * must implement.
 */
interface IConfigHandler
{
    /**
     * Returns the currently (read and merged) config data
     * as a concrete IConfig implementating class.
     *
     * @return IConfig
     */
    public function getConfig();

    /**
     * Adds the given location to the set of locations to check for configs.
     *
     * @param mixed $location location to check for config files (usually a file/directory path)
     *
     * @throws \InvalidArgumentException if given location is not readable
     */
    public function addLocation($location);

    /**
     * Set the locations to check for config files.
     *
     * @param array $locations set of locations (usually file/directory paths)
     */
    public function setLocations(array $locations);

    /**
     * @return array of locations used for config file lookups
     */
    public function getLocations();
}
