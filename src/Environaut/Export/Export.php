<?php

namespace Environaut\Export;

use Environaut\Command\Command;
use Environaut\Config\Parameters;
use Environaut\Export\IExport;
use Environaut\Export\Formatter\IReportFormatter;
use Environaut\Export\Formatter\ConsoleMessageFormatter;
use Environaut\Report\IReport;

/**
 * Default export that is used for reports after checks are run.
 * Outputs messages to the CLI and exports accumulated settings
 * as JSON.
 */
class Export implements IExport
{
    /**
     * Default filename to be used when no export file locations are specified.
     */
    const DEFAULT_EXPORT_LOCATION = 'environaut-config.json';

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var IReport
     */
    protected $report;

    /**
     * @var Parameters
     */
    protected $parameters;

    /**
     * @var array supported export file extensions
     */
    protected $supported_export_file_extensions = array('json', 'xml', 'php', 'sh');

    /**
     * Export current report as follows:
     *
     * 1. Display messages on CLI
     * 2. Run all formatters and display there text results on CLI
     *
     * If no formatters have been specified a JsonSettingsWriter will be used,
     * that writes all settings to a JSON file.
     */
    public function run()
    {
        $output = $this->command->getOutput();

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Report follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $formatter = new ConsoleMessageFormatter();
        $console_report_text = $formatter->format($this->report);
        $output->writeln($console_report_text);

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Export follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $default_formatter = array('location' => self::DEFAULT_EXPORT_LOCATION);
        $formatter_definitions = $this->parameters->get('formatters', array($default_formatter));
        foreach ($formatter_definitions as $formatter_definition) {
            $params = new Parameters($formatter_definition);

            $location = $params->get('location', self::DEFAULT_EXPORT_LOCATION);

            if ($params->has('__class')) {
                $formatter_class = $params->get('__class');
            } else {
                $formatter_class = $this->getFormatterByExtension($location);
            }

            $formatter = new $formatter_class();
            $formatter->setParameters($params);

            $output->writeln('Starting export via "' . $formatter_class . '".');

            $export_text = $formatter->format($this->report);
            $output->writeln($export_text);
        }

        $output->writeln('');
    }

    /**
     * Returns a specific formatter instance depending on the file
     * extension of the given export file location.
     *
     * @param string $location
     *
     * @return IReportFormatter
     *
     * @throws \InvalidArgumentException in case of unsupported file extensions
     */
    protected function getFormatterByExtension($location)
    {
        $ext = pathinfo($location, PATHINFO_EXTENSION);

        $formatter = null;

        switch ($ext) {
            case 'json':
                $formatter = 'Environaut\Export\Formatter\JsonSettingsWriter';
                break;
            case 'xml':
                $formatter = 'Environaut\Export\Formatter\XmlSettingsWriter';
                break;
            case 'php':
                $formatter = 'Environaut\Export\Formatter\PhpSettingsWriter';
                break;
            case 'sh':
                $formatter = 'Environaut\Export\Formatter\ShellSettingsWriter';
                break;

            case 'txt':
            default:
                $formatter = 'Environaut\Export\Formatter\PlainTextSettingsWriter';
        }

        return $formatter;
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

    /**
     * Runtime parameters to configure the export operations.
     *
     * @param Parameters $parameters runtime parameters
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;
    }
}
