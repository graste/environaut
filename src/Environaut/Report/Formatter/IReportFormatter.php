<?php

namespace Environaut\Report\Formatter;

use Environaut\Report\IReport;

interface IReportFormatter
{
    public function getFormatted(IReport $report);
}

