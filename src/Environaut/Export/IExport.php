<?php

namespace Environaut\Export;

use Environaut\Report\IReport;

/**
 * Interface all report exporters must implement.
 */
interface IExport
{
    /**
     * Starts the exporter. It should then analyze the
     * report and display or output files according to
     * its own rules and parameters.
     */
    public function run();

    /**
     * Set report to be handled by this exporter.
     *
     * @param IReport $report report to be handled by the exporter
     */
    public function setReport(IReport $report);
}
