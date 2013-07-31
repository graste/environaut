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
     * Returns an associative array of settings
     * collected from the results of this report.
     *
     * @return array of settings
     */
    public function getSettings();

    /**
     * Runtime parameters to configure the report behaviour.
     *
     * @param Parameters $parameters runtime parameters to use
     */
    public function setParameters(Parameters $parameters);
}
