<?php

namespace Environaut\Report\Formatter;

use Environaut\Report\Formatter\IReportFormatter;
use Environaut\Report\IReport;
use Environaut\Report\Messages\Message;

class ConsoleFormatter implements IReportFormatter
{
    const DEFAULT_FORMAT = '[%1$s] [%2$s] %3$s';
    protected $format;

    public function __construct($format = null)
    {
        if ($format !== null) {
            $this->format = $format;
        }
        else {
            $this->format = self::DEFAULT_FORMAT . PHP_EOL;
        }
    }

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

