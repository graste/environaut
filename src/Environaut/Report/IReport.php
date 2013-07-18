<?php

namespace Environaut\Report;

use Environaut\Report\Results\IResult;

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
     * Returns all results currently compiled
     * for the report.
     *
     * @return array of IResult instances
     */
    public function getResults();

    /**
     * Sets the given results on this report.
     *
     * @param array $results array of IResult instances
     */
    public function setResults(array $results);

    /**
     * Returns an associative array of settings
     * collected from the results of this report.
     *
     * @return array of settings
     */
    public function getSettings();
}

