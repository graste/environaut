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
     * Construct a new exporter for the given report.
     *
     * @param IReport $report report to be handled by this export
     * @param Command $command command to get access to input, output etc.
     * @param array $parameters runtime configuration parameters
     */
    public function __construct(IReport $report, Command $command, array $parameters = array())
    {
        $this->report = $report;
        $this->command = $command;
        $this->parameters = new Parameters($parameters);
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

        foreach ($this->parameters->get('files', array(array('format' => 'json'))) as $options) {
            $this->exportSettings($options);
        }
    }

    protected function exportSettings(array $options)
    {
        $output = $this->command->getOutput();

        $options = new Parameters($options);
        $format = strtolower($options->get('format', 'json'));

        $output->writeln('Exporting settings from checks:');
        switch ($format) {
            case 'xml':
                $this->exportXmlFile($options);
                break;
            case 'json':
                $this->exportJsonFile($options);
                break;
            default:
                $output->writeln(
                    '<error>Format "' . $format . '" is not supported for settings file export.' .
                    'Use "xml" or "json" instead.</error>'
                );
                break;
        }
    }

    protected function exportJsonFile(Parameters $options)
    {
        $output = $this->command->getOutput();

        $file = $options->get('file', 'config.json');
        $pretty = $options->get('pretty', true);
        $groups = $options->get('groups', array());

        if (is_writable($file)) {
            $output->write('<comment>Overwriting</comment> ');
        } else {
            $output->write('Writing ');
        }

        if (empty($groups)) {
            $output->write('all groups ');
        } else {
            $output->write('group(s) "' . implode(', ', $groups) . '" ');
        }

        $output->write('to file "<comment>' . $file . '</comment>"...');

        $flags = JSON_FORCE_OBJECT;
        if ($pretty && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $content = json_encode($this->report->getSettingsAsArray($groups), $flags);

        $ok = file_put_contents($file, $content, LOCK_EX);

        if ($ok !== false) {
            $output->writeln('<info>ok</info>.');
        } else {
            $output->writeln('<error>FAILED</error>.');
        }
    }

    protected function exportXmlFile(Parameters $options)
    {
        $output = $this->command->getOutput();

        $file = $options->get('file', 'config.xml');
        $pretty = $options->get('pretty', true);
        $groups = $options->get('groups', array());

        if (is_writable($file)) {
            $output->write('<comment>Overwriting</comment> ');
        } else {
            $output->write('Writing ');
        }

        if (empty($groups)) {
            $output->write('all groups ');
        } else {
            $output->write('group(s) "' . implode(', ', $groups) . '" ');
        }

        $default_file_template = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<ae:configurations
    xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.0"
    xmlns:xi="http://www.w3.org/2001/XInclude"
    xmlns="http://agavi.org/agavi/config/parts/settings/1.0"
>
    <ae:configuration>
%group_template\$s
    </ae:configuration>
</ae:configurations>

EOT;

        $default_group_template = <<<EOT

        <settings prefix="%group_name\$s.">%setting_template\$s
        </settings>

EOT;

        $default_setting_template = <<<EOT

            <setting name="%setting_name\$s">%setting_value\$s</setting>
EOT;

        $file_template = $options->get('file_template', $default_file_template);
        $group_template = $options->get('group_template', $default_group_template);
        $setting_template = $options->get('setting_template', $default_setting_template);

        $all_settings = $this->report->getSettingsAsArray($groups);

        $group_content = '';
        foreach ($all_settings as $group_name => $settings) {
            $settings_content = '';

            foreach ($settings as $key => $value) {
                $settings_content .= self::vksprintf(
                    $setting_template,
                    array(
                        'setting_name' => htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
                        'setting_value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                        'group_name' => htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8')
                    )
                );
            }

            $group_content .= self::vksprintf(
                $group_template,
                array(
                    'setting_template' => $settings_content,
                    'group_name' => htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8')
                )
            );
        }

        $content = self::vksprintf(
            $file_template,
            array(
                'group_template' => $group_content
            )
        );

        $output->write('to file "<comment>' . $file . '</comment>"...');

        $ok = file_put_contents($file, $content, LOCK_EX);

        if ($ok !== false) {
            $output->writeln('<info>ok</info>.');
        } else {
            $output->writeln('<error>FAILED</error>.');
        }
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

    /**
     * Runtime parameters to configure the export operations.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters = array())
    {
        $this->parameters = new Parameters($parameters);
    }

    /**
     * Base version of the method
     * @see http://www.php.net/manual/de/function.vsprintf.php#110666
     *
     * Like vsprintf, but accepts $args keys instead of order index.
     * Both numeric and strings matching /[a-zA-Z0-9_-]+/ are allowed.
     *
     * @example: vskprintf('y = %y$d, x = %x$1.1f, key = %key$s', array('x' => 1, 'y' => 2, 'key' => 'MyKey'))
     * Result:  'y = 2, x = 1.0'
     *
     * '%s' without argument name works fine too. Everything vsprintf() can do
     * is supported.
     *
     * @author Josef Kufner <jkufner(at)gmail.com>
     * @author Oskar Stark <oskar.stark@exozet.com>
     */
    public static function vksprintf($str, array $args)
    {
        if (empty($args)) {
            return $str;
        }

        $map = array_flip(array_keys($args));

        $new_str = preg_replace_callback(
            '/(^|[^%])%([a-zA-Z0-9_-]+)\$/',
            function ($m) use ($map) {
                if (isset($map[$m[2]])) {
                    return $m[1] . '%' . ($map[$m[2]] + 1) . '$';
                } else {
                    /*
                     * HACK!
                     * vsprintf all time removes '% and the following character'
                     *
                     * so we add 6 x # to the string.
                     * vsprintf will remove '%#' and later we remove the rest #
                     */
                    return $m[1] . '%######' . $m[2][0] . '%' . $m[2] . '$';
                }
            },
            $str
        );

        $replaced_str = vsprintf($new_str, $args);

        return str_replace('#####', '%', $replaced_str);
    }
}
