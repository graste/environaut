<?php

namespace Environaut\Config;

use Environaut\Checks\Parameters;

abstract class BaseConfigHandler implements IConfigHandler
{
    protected $locations;
    protected $parameters;

    public function __construct(array $locations = array(), array $parameters = array())
    {
        $this->locations = $locations;
        $this->parameters = new Parameters($parameters);
    }

    public function getConfig()
    {
        return new Config($this->getMergedConfig());
    }

    public function addLocation($location)
    {
        if (!is_readable($location)) {
            throw new \InvalidArgumentException("Given location is not readable: $location");
        }

        $this->locations[] = $location;
    }

    public function setLocations(array $locations)
    {
        foreach ($locations as $location) {
            $this->addLocation($location);
        }
    }

    public function getLocations()
    {
        return $this->locations;
    }

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

    /**
     * Resolves dots and double slashes in relative paths to get
     * nicer paths: "dev/data/assets/../../foo" will be "dev/foo/"
     * and "foo/./bar" will be "foo/bar" etc.
     *
     * @return string
     */
    public function fixRelativePath($path_with_dots)
    {
        do
        {
            $path_with_dots = preg_replace('#[^/\.]+/\.\./#', '', $path_with_dots, -1, $count);
        }
        while ($count);

        $path_with_dots = str_replace(array('/./', '//'), '/', $path_with_dots);

        return $path_with_dots;
    }

    /**
     * Appends '/' to the path if necessary.
     *
     * @param string $path file system path
     *
     * @return string path with suffix '/'
     */
    public function fixPath($path)
    {
        if (empty($path))
        {
            return $path;
        }

        if ('/' != $path{strlen($path) - 1})
        {
            $path .= '/';
        }

        return $path;
    }
}
