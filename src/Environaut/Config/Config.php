<?php

namespace Environaut\Config;

use Environaut\Config\IConfig;
use Environaut\Config\Parameters;

/**
 * Default representation of the config file data.
 */
class Config implements IConfig
{
    /**
     * @var Parameters config data as array wrapped in a Parameters instance
     */
    protected $config;

    /**
     * Create new instance with some default config data.
     *
     * @param array $config_data
     */
    public function __construct(array $config_data = array())
    {
        $this->config = new Parameters($config_data);
    }

    /**
     * @return array of parameters to create checks
     */
    public function getCheckDefinitions()
    {
        return $this->config->get('checks', array());
    }

    /**
     * Defaults to "Environaut\Export\Export".
     *
     * @return string (namespaced) class name to use for exporting a report
     */
    public function getExportImplementor()
    {
        $export = new Parameters($this->config->get('export', array()));
        return $export->get(self::PARAM_CLASS, 'Environaut\Export\Export');
    }

    /**
     * Defaults to "Environaut\Report\Report".
     *
     * @return string (namespaced) class name to use for a report
     */
    public function getReportImplementor()
    {
        $report = new Parameters($this->config->get('report', array()));
        return $report->get(self::PARAM_CLASS, 'Environaut\Report\Report');
    }

    /**
     * Defaults to "Environaut\Runner\Runner".
     *
     * @return string (namespaced) class name to use for running checks read from the config
     */
    public function getRunnerImplementor()
    {
        $runner = new Parameters($this->config->get('runner', array()));
        return $runner->get(self::PARAM_CLASS, 'Environaut\Runner\Runner');
    }

    /**
     * Defaults to "Environaut\Cache\ReadOnlyCache".
     *
     * @return string (namespaced) class name to use for cache given to checks
     */
    public function getReadOnlyCacheImplementor()
    {
        $cache = new Parameters($this->config->get('cache', array()));
        return $cache->get('readonly_class', 'Environaut\Cache\ReadOnlyCache');
    }

    /**
     * Defaults to "Environaut\Cache\Cache".
     *
     * @return string (namespaced) class name to use for writing cachable settings after report generation
     */
    public function getCacheImplementor()
    {
        $cache = new Parameters($this->config->get('cache', array()));
        return $cache->get(self::PARAM_CLASS, 'Environaut\Cache\Cache');
    }

    /**
     * Returns the config value for the given key.
     *
     * @param string $key name of config key
     * @param mixed $default value to return if key doesn't exist
     *
     * @return mixed value for that key or default given
     */
    public function get($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * Returns whether the config key exists or not.
     *
     * @param string $key name of config key to check
     *
     * @return bool true, if key exists; false otherwise
     */
    public function has($key)
    {
        return $this->config->has($key);
    }

    /**
     * Sets a given value to the config under the specified key.
     *
     * @param string $key name of config entry
     * @param mixed $value value to set for the given key
     *
     * @return mixed the value set
     */
    public function set($key, $value)
    {
        return $this->config->set($key, $value);
    }

    /**
     * Returns the config data as an associative array.
     *
     * @return array with all config data
     */
    public function toArray()
    {
        return $this->config->getAll();
    }
}
