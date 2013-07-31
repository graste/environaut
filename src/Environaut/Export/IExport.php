<?php

namespace Environaut\Export;

use Environaut\Config\Parameters;
use Environaut\Report\IReport;
use Environaut\Command\Command;

/**
 * Interface all report exporters must implement.
 */
interface IExport
{
    /**
     * Set report to be handled by this exporter.
     *
     * @param IReport $report report to be handled by the exporter
     */
    public function setReport(IReport $report);

    /**
     * Environaut command for access to input and output.
     *
     * @param Command $command
     */
    public function setCommand(Command $command);

    /**
     * Runtime parameters to configure the export operations.
     *
     * @param Parameters $parameters
     */
    public function setParameters(Parameters $parameters);

    /**
     * Starts the exporter. It should then analyze the
     * report and display or output files according to
     * its own rules and parameters.
     */
    public function run();
}
