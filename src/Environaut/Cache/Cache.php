<?php

namespace Environaut\Cache;

use Environaut\Cache\ICache;
use Environaut\Config\Parameters;
use Environaut\Report\Results\Settings\ISetting;

class Cache implements ICache
{
    /**
     * @var string location of the cache file
     */
    protected $location;

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
        $this->location = '';
        $this->parameters = new Parameters();
        $this->settings = array();
    }

    public function add(ISetting $setting)
    {
        $this->settings[] = $setting;
    }

    public function get($name, $group, $flag = ISetting::ALL)
    {
        $found = null;

        foreach ($this->settings as $setting) {
            if ($setting->getName() === $name && $group === $setting->getGroup()) {
                return $setting;
            }
        }

        return $found;
    }

    public function getAll($groups, $flag)
    {
        if (!is_array($groups) && !is_string($groups)) {
            throw new \InvalidArgumentException('Only a single group name or an array of group names are supported.');
        }

        if (is_string($groups)) {
            $groups = array($groups);
        }

        $found = array();
        foreach ($this->settings as $setting) {
            $group = $setting->getGroup();
            if (in_array($group, $groups)) {
                $found[] = $setting;
            }
        }

        return $found;
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
}
