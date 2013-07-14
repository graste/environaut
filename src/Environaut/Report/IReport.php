<?php

namespace Environaut\Report;

use Environaut\Report\IResult;

interface IReport
{
    public function addResult(IResult $result);
    public function getResults();
    public function setResults(array $results);
    public function getSettings();
    public function getFormatted($formatter);
}

