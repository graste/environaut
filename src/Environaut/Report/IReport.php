<?php

namespace Environaut\Report;

use Environaut\Report\Results\IResult;
use Environaut\Config\Parameters;

/**
 * Interface all reports must implement.
 */
interface IReport
{
    /**
     * Adds the specified result to the report.
     *
     * @param IResult $result
     */
    public function addResult(IResult $result);

    /**
     * Runtime parameters to configure the report behaviour.
     *
     * @param Parameters $parameters runtime parameters to use
     */
    public function setParameters(Parameters $parameters);

    /**
     * Sets the given results on this report.
     *
     * @param array $results array of IResult instances
     */
    public function setResults(array $results);

    /**
     * Returns all results currently compiled
     * for the report.
     *
     * @return array of IResult instances
     */
    public function getResults();

    /**
     * Returns an array of ISetting instances that match the given criterias from the results of this report.
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of ISetting instances
     */
    public function getSettings($groups = null, $flag = null);

    /**
     * Returns an array of associative arrays for each ISetting instance
     * that matches the given criterias (from the results of this report).
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of associative arrays for each ISetting instance that matched
     */
    public function getSettingsAsArray($groups = null, $flag = null);

    /**
     * Returns an array of cachable ISetting instances that match the given criterias from the results of this report.
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of ISetting instances
     */
    public function getCachableSettings($groups = null, $flag = null);

    /**
     * Returns an array of associative arrays for each cachable ISetting instance
     * that matches the given criterias (from the results of this report).
     *
     * @param mixed $groups string or array of group names settings should match, the default null matches always
     * @param integer $flag type of settings to get
     *
     * @return array of associative arrays for each ISetting instance that matched
     */
    public function getCachableSettingsAsArray($groups = null, $flag = null);
}
