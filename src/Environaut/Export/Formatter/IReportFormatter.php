<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;

interface IReportFormatter
{
    public function getFormatted(IReport $report);
}
