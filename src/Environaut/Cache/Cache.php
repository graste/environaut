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
     * @return boolean true when cache file writing succeeded
     *
     * @throws \Exception if content could not be encoded
     */
    public function save()
    {
        $location = $this->location;

        // no location given from commandline -> use config values or fallback to default location
        if (empty($location)) {
            $location = $this->parameters->get(
                'write_location',
                $this->parameters->get(
                    'location',
                    $this->getDefaultLocation()
                )
            );
        }

        $this->location = $location;

        $data = array();
        foreach ($this->settings as $setting) {
            $data[] = $setting->toArray();
        }

        $flags = 0;
        if ($this->parameters->get('pretty', true) && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $content = json_encode($data, $flags);

        if (false === $content) {
            throw new \Exception('Could not json_encode cachable settings. Nothing written to cache.');
        }

        $success = (false !== file_put_contents($location, $content));

        $success &= chmod($location, 0600); // only current user should read/write potentially sensitive info

        return $success;
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
