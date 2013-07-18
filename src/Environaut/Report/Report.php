<?php

namespace Environaut\Report;

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

        foreach ($this->results as $result)
        {
            $settings = array_merge($settings, $result->getSettings());
        }

        return $settings;
    }
}
