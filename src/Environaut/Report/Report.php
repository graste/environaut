<?php

namespace Environaut\Report;

use Environaut\Config\Parameters;
use Environaut\Report\Results\IResult;

/**
 * Default class that holds results from checks.
 */
class Report implements IReport
{
    protected $results = array();

    public function __construct(array $results = array())
    {
        $this->results = $results;
    }

    public function addResult(IResult $result)
    {
        $this->results[] = $result;
    }

    public function setResults(array $results)
    {
        $this->results = $results;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getSettings()
    {
        $settings = array();

        foreach ($this->results as $result) {
            $settings = array_merge_recursive($settings, $result->getSettingsAsArray());
        }

        return $settings;
    }


    /**
     * Return all settings or the settings of the specified group as an array.
     *
     * @param mixed $groups group names of settings to return
     *
     * @return array all settings (for the specified group); empty array if group doesn't exist.
     */
    public function getSettingsAsArray($groups = null)
    {
        $all_settings = $this->getSettings();
        $group_names = $this->getGroupNames($groups);

        if (null === $group_names) {
            return $all_settings;
        }

        $settings = array();

        foreach ($group_names as $group_name) {
            if (isset($all_settings[$group_name]) || array_key_exists($group_name, $all_settings)) {
                $settings[$group_name] = $all_settings[$group_name];
            }
        }

        return $settings;
    }

    protected function getGroupNames($groups)
    {
        $group_names = array();

        if (empty($groups)) {
            return null;
        }

        if (is_string($groups)) {
            $groups = explode(',', $groups);
        }

        foreach ($groups as $group_name) {
            $group_names[] = trim($group_name);
        }

        return $group_names;
    }
}
