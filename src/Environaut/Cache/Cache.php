<?php

namespace Environaut\Cache;

use Environaut\Cache\ICache;
use Environaut\Report\Results\Settings\ISetting;

class Cache extends ReadOnlyCache implements ICache
{
    /**
     * Add the given setting to the cache. Remember it is not persistent without a call to save().
     *
     * @param \Environaut\Report\Results\Settings\ISetting $setting
     */
    public function add(ISetting $setting)
    {
        $this->settings[] = $setting;

        return $this;
    }

    /**
     * Adds the given ISetting instances to the cache. Remember that the settings are not persistent
     * without a call to save().
     *
     * @param array $settings array of ISetting implementing instances
     *
     * @return \Environaut\Cache\Cache this instance for fluent API support
     */
    public function addAll(array $settings)
    {
        foreach ($settings as $setting) {
            if ($setting instanceof ISetting) {
                $this->settings[] = $setting;
            }
        }

        return $this;
    }

    /**
     * Writes the current set of ISetting instances to the cache file location.
     *
     * @return \Environaut\Cache\Cache this instance for fluent API support
     *
     * @throws \Exception if content could not be encoded or written to the cache file
     */
    public function save()
    {
        $location = $this->location;

        if (empty($location)) {
            $location = $this->getCurrentWorkingDirectory() . DIRECTORY_SEPARATOR .
                self::DEFAULT_PATH . self::DEFAULT_NAME . self::DEFAULT_EXTENSION;
        }

        $this->location = $location;

        $data = array();
        foreach ($this->settings as $setting) {
            $data[] = $setting->toArray();
        }

        $content = json_encode($data);

        if (false === $content) {
            throw new \Exception('Could not json_encode cachable settings. Nothing written to cache.');
        }

        if (false === file_put_contents($location, $content)) {
            throw new \Exception('Could not write cache: ' . $location);
        }

        return $this;
    }

    /**
     * Sets the file path to use as cache file location when saving settings.
     *
     * @param string $location cache file path
     *
     * @throws \InvalidArgumentException in case of non-writable cache file path location
     */
    public function setLocation($location)
    {
        if (!is_writable($location)) {
            throw new \InvalidArgumentException('Given cache location "' . $location . '" is not writable.');
        }

        $this->location = $location;
    }
}
