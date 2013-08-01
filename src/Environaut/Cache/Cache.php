<?php

namespace Environaut\Cache;

use Environaut\Cache\ICache;
use Environaut\Config\Parameters;
use Environaut\Report\Results\Settings\ISetting;
use Environaut\Report\Results\Settings\Setting;

class Cache implements ICache
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
     * Add the given setting to the cache.
     *
     * @param \Environaut\Report\Results\Settings\ISetting $setting
     */
    public function add(ISetting $setting)
    {
        $this->settings[] = $setting;
    }

    public function get($name, $group = null, $flag = ISetting::ALL)
    {
        $found = null;

        foreach ($this->settings as $setting) {
            if ($setting->getName() === $name && $group === $setting->getGroup()) {
                return $setting;
            }
        }

        return $found;
    }

    /**
     * Returns an array of settings matching the given parameters.
     *
     * @param mixed $groups name of a group or array of group names or null for all settings regardless of group
     * @param type $flag only return settings that have match the given flag value
     *
     * @return array of setting instances matching the specification
     *
     * @throws \InvalidArgumentException if $groups is neither null nor a string or an array
     */
    public function getAll($groups = null, $flag = ISetting::ALL)
    {
        $found = array();

        if (null === $groups) {
            foreach ($this->settings as $setting) {
                $found[] = $setting;
            }
        } elseif (is_string($groups)) {
            $groups = array($groups);
            foreach ($this->settings as $setting) {
                $group = $setting->getGroup();
                if (in_array($group, $groups)) {
                    $found[] = $setting;
                }
            }
        } else {
            throw new \InvalidArgumentException(
                'Only a null value, a single string as group name or an array of group names are supported.'
            );
        }

        return $found;
    }

    public function load(array $options = array())
    {
        $location = $this->location;

        if (empty($location)) {
            $location = $this->getCurrentWorkingDirectory() . DIRECTORY_SEPARATOR .
                self::DEFAULT_PATH . self::DEFAULT_NAME . self::DEFAULT_EXTENSION;
        }

        if (!is_readable($location)) {
            throw new \Exception('Cache is not readable: ' . $location);
        }

        if (!is_file($location)) {
            throw new \Exception('Cache is not a readable file: ' . $location);
        }

        $content = file_get_contents($location);

        $result = json_decode($content, true);

        if (null === $result) {
            throw new \Exception('Cache could not be decoded from file: ' . $location);
        }

        $items = array();
        foreach ($result as $setting) {
            $items[] = new Setting($setting['name'], $setting['value'], $setting['group'], $setting['flag']);
        }

        $this->settings = $items;
    }

    public function save(array $options = array())
    {
        $location = $this->location;

        if (empty($location)) {
            $location = $this->getCurrentWorkingDirectory() . DIRECTORY_SEPARATOR .
                self::DEFAULT_PATH . self::DEFAULT_NAME . self::DEFAULT_EXTENSION;
        }

        $this->location = $location;

        $content = json_encode($this->getCachableSettingsAsArray());

        if (false === $content) {
            throw new \Exception('Could not json_encode cachable settings. No Cache written.');
        }

        if (false === file_put_contents($content)) {
            throw new Exception('Could not write cachable settings to ' . $location);
        }
    }

    public function setLocation($location)
    {
        if (!is_writeable($location)) {
            throw new \InvalidArgumentException('Given cache location "' . $location . '" is not writable.');
        }

        $this->location = $location;
    }

    /**
     * Runtime parameters to configure the cache operations.
     *
     * @param Parameters $parameters runtime parameters
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;
    }

    protected function getCachableSettingsAsArray()
    {
        $settings = array();

        foreach ($this->settings as $item) {
            $settings[] = $item->toArray();
        }

        return $settings;
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
