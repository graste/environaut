<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Report\Results\Messages\Message;
use Environaut\Export\Formatter\IReportFormatter;

/**
 * Simple formatter that takes messages from the results
 * of the given report and enhances them according to their
 * severity etc.
 */
class ConsoleMessageFormatter implements IReportFormatter
{
    /**
     * Default sprintf compatible format for messages.
     */
    const DEFAULT_FORMAT = '[%1$s] [%2$s] %3$s';

    /**
     * @var string current format used for formatting
     */
    protected $format;

    /**
     * Create new instance of formatter.
     *
     * @param string $format sprintf compatible format for the result messages
     */
    public function __construct($format = null)
    {
        if ($format !== null) {
            $this->format = $format;
        }
        else {
            $this->format = self::DEFAULT_FORMAT . PHP_EOL;
        }
    }

    /**
     * Returns a formatted string consisting of all
     * messages from the results of the given report.
     *
     * @param IReport $report report to take results (and messages) from
     *
     * @return string messages formatted with the configured format
     */
    public function getFormatted(IReport $report)
    {
        $output = '';

        $results = $report->getResults();
        foreach ($results as $result) {
            $messages = $result->getMessages();
            foreach ($messages as $message) {
                switch($message->getSeverity()) {
                    case Message::SEVERITY_FATAL:
                    case Message::SEVERITY_ERROR:
                       $output .= sprintf($this->format, $message->getGroup(), $message->getName(), '<error>' . $message->getText() . '</error>');
                       break;
                    case Message::SEVERITY_NOTICE:
                    case Message::SEVERITY_WARN:
                    default:
                       $output .= sprintf($this->format, $message->getGroup(), $message->getName(), '<comment>' . $message->getText() . '</comment>');
                       break;
                    case Message::SEVERITY_INFO:
                    default:
                       $output .= sprintf($this->format, $message->getGroup(), $message->getName(), '<info>' . $message->getText() . '</info>');
                       break;
                    case Message::SEVERITY_DEBUG:
                    default:
                       $output .= sprintf($this->format, $message->getGroup(), $message->getName(), $message->getText());
                       break;
                }
            }
        }

        return $output;
    }
}
