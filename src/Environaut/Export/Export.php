<?php

namespace Environaut\Export;

use Environaut\Export\IExport;
use Environaut\Report\IReport;
use Environaut\Command\Command;
use Environaut\Export\Formatter\IReportFormatter;
use Environaut\Export\Formatter\ConsoleMessageFormatter;

/**
 * Default export that is used for reports after checks are run.
 * Outputs messages to the CLI and exports accumulated settings
 * as JSON.
 */
class Export implements IExport
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var IReport
     */
    protected $report;

    /**
     * Construct a new exporter for the given report.
     *
     * @param IReport $report report to be handled by this export
     * @param Command $command command to get access to input, output etc.
     */
    public function __construct(IReport $report, Command $command)
    {
        $this->report = $report;
        $this->command = $command;
    }

    /**
     * Export current report as follows:
     *
     * 1. Display messages on CLI
     * 2. Display settings as JSON on CLI
     */
    public function run()
    {
        $output = $this->command->getOutput();

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Report follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $console_report_text = $this->getFormatted(new ConsoleMessageFormatter());
        $output->writeln($console_report_text);

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Config follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $output->writeln(json_encode($this->report->getSettings(), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));
    }

    /**
     * Uses the given formatter to format the current report.
     * By default the ConsoleMessageFormatter will be utilized.
     *
     * @param IReportFormatter $formatter
     *
     * @return mixed result of the formatting operation
     */
    protected function getFormatted($formatter = null)
    {
        if (null !== $formatter && $formatter instanceof IReportFormatter) {
            return $formatter->getFormatted($this->report);
        }

        $formatter = new ConsoleMessageFormatter();
        return $formatter->getFormatted($this->report);
    }

    /**
     * Set report to be handled by this exporter.
     *
     * @param IReport $report
     */
    public function setReport(IReport $report)
    {
        $this->report = $report;
    }

    /**
     * Environaut command for access to input and output.
     *
     * @param Command $command
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }
}
