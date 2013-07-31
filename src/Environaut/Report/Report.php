<?php

namespace Environaut\Report;

use Environaut\Report\Results\IResult;
use Environaut\Config\Parameters;

/**
 * Default class that holds results from checks.
 */
class Report implements IReport
{
    /**
     * Holds the results of all checks.
     *
     * @var array for results of processed checks
     */
    protected $results = array();

    /**
     * @var Parameters runtime parameters
     */
    protected $parameters;

    /**
     * Creates a new Report instance.
     *
     * @param array $results array of IResult instances to prefill the instance
     * @param array $parameters array of runtime parameters
     */
    public function __construct(array $results = array(), array $parameters = array())
    {
        $this->results = $results;
        $this->parameters = new Parameters($parameters);
    }

    /**
     * Adds the given result to this report.
     *
     * @param \Environaut\Report\Results\IResult $result result to add to this report
     */
    public function addResult(IResult $result)
    {
        $this->results[] = $result;
        return $this;
    }

    /**
     * Replaces the current results with the given IResult instances.
     *
     * @param array $results array of IResult instances
     */
    public function setResults(array $results)
    {
        $this->results = $results;
        return $this;
    }

    /**
     * Returns all results of the already processed checks.
     *
     * @return array of IResult instances
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Returns all settings of all results of all processed checks as an associative nested array.
     *
     * @return array of settings
     */
    public function getSettings()
    {
        $settings = array();

        foreach ($this->results as $result) {
            $settings = array_merge_recursive($settings, $result->getSettingsAsArray());
        }

        return $settings;
    }

    /**
     * Runtime parameters to configure the report behaviour.
     *
     * @param Parameters $parameters runtime parameters to use
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;
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

    /**
     * @param mixed $groups string with comma separated group names or an array of group names
     *
     * @return array of group names or null if empty groups were given
     */
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
