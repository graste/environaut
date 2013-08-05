<?php

namespace Environaut\Cache;

use Environaut\Config\Parameters;

interface IReadOnlyCache
{
    /**
     * Sets the given location as the cache location to use.
     *
     * @param string $location cache file path
     *
     * @throws \InvalidArgumentException if location is not readable
     */
    public function setLocation($location);

    /**
     * Returns the location currently set on the cache.
     *
     * @return string cache file location currently configured
     */
    public function getLocation();

    /**
     * Runtime parameters to configure the cache operations.
     *
     * @param Parameters $parameters runtime parameters
     */
    public function setParameters(Parameters $parameters);

    /**
     * Loads the settings from the configured or default location.
     *
     * @throws \InvalidArgumentException on invalid cache location (non-readable, non-existant etc.)
     */
    public function load();

    /**
     * Returns the first setting from the loaded cache that matches the given criterias.
     *
     * @param string $name key of wanted setting
     * @param mixed $groups group name of setting, default is null and matches always
     * @param integer $flag setting type to match, default is null and matches always
     *
     * @return \Environaut\Report\Results\Settings\ISetting|null matching setting
     */
    public function get($name, $groups = null, $flag = null);

    /**
     * Returns whether a cached value for a setting from the loaded cache exists, that matches the given criterias.
     *
     * @param string $name key of wanted setting
     * @param mixed $groups group name of setting, default is null and matches always
     * @param integer $flag setting type to match, default is null and matches always
     *
     * @return boolean true if value for that setting is in cache; false otherwise
     */
    public function has($name, $groups = null, $flag = null);

    /**
     * Returns an array of settings matching the given criterias.
     *
     * @param mixed $groups name of a group or array of group names or null for all settings regardless of group
     * @param type $flag only return settings that match the given flag value
     *
     * @return array of ISetting instances matching the specification
     */
    public function getAll($groups = null, $flag = null);
}
