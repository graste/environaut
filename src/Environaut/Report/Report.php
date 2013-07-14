<?php

namespace Environaut\Report;

use Environaut\Report\IResult;
use Environaut\Report\Formatter\IReportFormatter;
use Environaut\Report\Formatter\ConsoleFormatter;

class Report implements IReport
{
    protected $results = array();

    public function __construct(array $results = array())
    {
        $this->results = $results;
        $this->formatter = new ConsoleFormatter();
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

    public function getFormatted($formatter = null)
    {
        if (null !== $formatter && $formatter instanceof IReportFormatter)
        {
            $this->formatter = $formatter;
        }

        return $this->formatter->getFormatted($this);
    }
}

