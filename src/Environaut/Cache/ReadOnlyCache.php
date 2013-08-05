<?php

namespace Environaut\Cache;

use Environaut\Cache\IReadOnlyCache;
use Environaut\Config\Parameters;
use Environaut\Report\Results\Settings\Setting;

class ReadOnlyCache implements IReadOnlyCache
{
    const DEFAULT_PATH = './';
    const DEFAULT_NAME = '.environaut';
    const DEFAULT_EXTENSION = '.cache';

    /**
     * @var string location of the cache file
     */
    protected $location = null;

    /**
     * @var Parameters runtime parameters
     */
    protected $parameters;

    /**
     * @var array of ISetting implementing settings
     */
    protected $settings = array();

    public function __construct()
    {
        $this->location = null;
        $this->parameters = new Parameters();
        $this->settings = array();
    }

    /**
     * Returns whether a cached value for a setting from the loaded cache exists, that matches the given criterias.
     *
     * @param string $name key of wanted setting
     * @param mixed $groups group name of setting, default is null and matches always
     * @param integer $flag setting type to match, default is null and matches always
     *
     * @return boolean true if value for that setting is in cache; false otherwise
     */
    public function has($name, $groups = null, $flag = null)
    {
        foreach ($this->settings as $setting) {
            if (($setting->getName() === $name) && $setting->matchesGroup($groups) && $setting->matchesFlag($flag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the first setting from the loaded cache that matches the given criterias.
     *
     * @param string $name key of wanted setting
     * @param mixed $groups group name of setting, default is null and matches always
     * @param integer $flag setting type to match, default is null and matches always
     *
     * @return ISetting|null matching setting
     */
    public function get($name, $groups = null, $flag = null)
    {
        $found = null;

        foreach ($this->settings as $setting) {
            if (($setting->getName() === $name) && $setting->matchesGroup($groups) && $setting->matchesFlag($flag)) {
                return $setting;
            }
        }

        return $found;
    }

    /**
     * Returns an array of settings matching the given criterias.
     *
     * @param mixed $groups name of a group or array of group names or null for all settings regardless of group
     * @param type $flag only return settings that match the given flag value
     *
     * @return array of setting instances matching the specification
     *
     * @throws \InvalidArgumentException if $groups is neither null nor a string or an array
     */
    public function getAll($groups = null, $flag = null)
    {
        $settings = array();

        if (null === $groups) {
            foreach ($this->settings as $setting) {
                $settings[] = $setting;
            }
        } elseif (is_string($groups) || is_array($groups)) {
            foreach ($this->settings as $setting) {
                if ($setting->matchesGroup($groups) && $setting->matchesFlag($flag)) {
                    $settings[] = $setting;
                }
            }
        } else {
            throw new \InvalidArgumentException(
                'Only a null value, a single string as group name or an array of group names are supported.'
            );
        }

        return $settings;
    }

    /**
     * Loads the settings from the configured or default location.
     *
     * @return boolean true, if reading from cache succeeded; false otherwise.
     *
     * @throws \InvalidArgumentException on invalid or broken cache file
     */
    public function load()
    {
        $location = $this->location;

        // no location given from commandline -> use config values or fallback to default location
        if (empty($location)) {
            $location = $this->parameters->get(
                'read_location',
                $this->parameters->get(
                    'location',
                    $this->getDefaultLocation()
                )
            );
        }

        $this->location = $location;

        if (!is_readable($location) || !is_file($location)) {
            return false;
        }

        $content = file_get_contents($location);

        if (false === $content) {
            return false;
        }

        $result = json_decode($content, true);

        if (null === $result) {
            throw new \InvalidArgumentException('Cache could not be decoded from file: ' . $location);
        }

        $items = array();
        foreach ($result as $setting) {
            $items[] = new Setting($setting['name'], $setting['value'], $setting['group'], $setting['flag']);
        }

        $this->settings = $items;

        return true;
    }

    /**
     * Sets the given location as the cache location to use.
     *
     * @param string $location cache file path
     *
     * @return ReadOnlyCache this instance for fluent API support
     *
     * @throws \InvalidArgumentException if location is not readable
     */
    public function setLocation($location)
    {
        if (!is_readable($location)) {
            throw new \InvalidArgumentException('Given cache location "' . $location . '" is not readable.');
        }

        $this->location = $location;

        return $this;
    }

    /**
     * Returns the location currently set on the cache.
     *
     * @return string cache file location currently configured
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Runtime parameters to configure the cache operations.
     *
     * @param Parameters $parameters runtime parameters
     *
     * @return ReadOnlyCache this instance for fluent API support
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Returns the default location used for the cache file. Defaults to "./.environaut.cache".
     *
     * @return string file path and name
     */
    protected function getDefaultLocation()
    {
        return $this->getCurrentWorkingDirectory() . DIRECTORY_SEPARATOR .
            self::DEFAULT_PATH . self::DEFAULT_NAME . self::DEFAULT_EXTENSION;
    }

    /**
     * @return string current working directory
     */
    protected function getCurrentWorkingDirectory()
    {
        $dir = getcwd();

        if (false === $dir) {
            $dir = __DIR__; // fallback to folder of current class in case of strange errors
        }

        return $dir;
    }
}
