<?php

namespace Environaut\Config;

/**
 * Interface all Environaut configs must implement.
 */
interface IConfig
{
    /**
     * Parameter name in a check, export or similar defintion to define the implementor to use.
     */
    const PARAM_CLASS = '__class';

    /**
     * Parameter name in a check, export or similar definition to e.g. name a check.
     */
    const PARAM_NAME = '__name';

    /**
     * Parameter name in a check, export or similar definition to e.g. assign a check to a group.
     */
    const PARAM_GROUP = '__group';

    /**
     * @return array of parameters to create checks
     */
    public function getCheckDefinitions();

    /**
     * Defaults to "Environaut\Export\Export".
     *
     * @return string (namespaced) class name to use for exporting a report
     */
    public function getExportImplementor();

    /**
     * Defaults to "Environaut\Report\Report".
     *
     * @return string (namespaced) class name to use for a report
     */
    public function getReportImplementor();

    /**
     * Defaults to "Environaut\Runner\Runner".
     *
     * @return string (namespaced) class name to use for running checks read from the config
     */
    public function getRunnerImplementor();

    /**
     * Returns the config value for the given key.
     *
     * @param string $key name of config key
     * @param mixed $default value to return if key doesn't exist
     *
     * @return mixed value for that key or default given
     */
    public function get($key, $default = null);

    /**
     * Returns whether the config key exists or nor.
     *
     * @param string $key name of config key to check
     *
     * @return bool true if key exists; false otherwise
     */
    public function has($key);

    /**
     * Sets a given value to the config under the specified key.
     *
     * @param string $key name of config entry
     * @param mixed $value value to set for the given key
     *
     * @return mixed the value set
     */
    public function set($key, $value);

    /**
     * Returns the config data as an associative array.
     *
     * @return array with all config data
     */
    public function toArray();
}
