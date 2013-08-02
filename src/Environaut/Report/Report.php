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
     *
     * @return Report this instance for fluent API support
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
     *
     * @return Report this instance for fluent API support
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
     * Returns an array of ISetting instances that match the given criterias from the results of this report.
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of ISetting instances
     */
    public function getSettings($groups = null, $flag = null)
    {
        $settings = array();

        foreach ($this->results as $result) {
            $settings = array_merge($settings, $result->getSettings($groups, $flag));
        }

        return $settings;
    }

    /**
     * Returns an array of cachable ISetting instances that match the given criterias from the results of this report.
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of ISetting instances
     */
    public function getCachableSettings($groups = null, $flag = null)
    {
        $settings = array();

        foreach ($this->results as $result) {
            $settings = array_merge($settings, $result->getCachableSettings($groups, $flag));
        }

        return $settings;
    }

    /**
     * Returns an array of associative arrays for each ISetting instance
     * that matches the given criterias (from the results of this report).
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of associative arrays for each ISetting instance that matched
     */
    public function getSettingsAsArray($groups = null, $flag = null)
    {
        $settings = array();

        foreach ($this->results as $result) {
            $new_settings = $result->getSettingsAsArray($groups, $flag);
            foreach ($new_settings as $s) {
                $settings[] = $s;
            }
        }

        return $settings;
    }

    /**
     * Returns an array of associative arrays for each cachable ISetting instance
     * that matches the given criterias (from the results of this report).
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of associative arrays for each ISetting instance that matched
     */
    public function getCachableSettingsAsArray($groups = null, $flag = null)
    {
        $settings = array();

        foreach ($this->results as $result) {
            $settings[] = array_merge($settings, $result->getCachableSettingsAsArray($groups, $flag));
        }

        return $settings;
    }

    /**
     * Runtime parameters to configure the report behaviour.
     *
     * @param Parameters $parameters runtime parameters to use
     *
     * @return Report this instance for fluent API support
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
