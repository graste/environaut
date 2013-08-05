<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Report\Results\Messages\Message;
use Environaut\Export\Formatter\BaseFormatter;

/**
 * Simple formatter that takes messages from the results
 * of the given report and enhances them according to their
 * severity etc.
 */
class ConsoleMessageFormatter extends BaseFormatter
{
    /**
     * Default sprintf compatible format for messages. May be changed via parameters.
     */
    const DEFAULT_FORMAT = '[%1$s] [%2$s] %3$s';

    /**
     * Returns a formatted string consisting of all
     * messages from the results of the given report.
     *
     * @param IReport $report report to take results (and messages) from
     *
     * @return string messages formatted with the configured format
     */
    public function format(IReport $report)
    {
        $output = '';
        $format = $this->getParameters()->get('format', self::DEFAULT_FORMAT);

        $results = $report->getResults();
        foreach ($results as $result) {
            $messages = $result->getMessages();
            foreach ($messages as $message) {
                switch($message->getSeverity()) {
                    case Message::SEVERITY_FATAL:
                    case Message::SEVERITY_ERROR:
                        $output .= sprintf(
                            $format,
                            $message->getGroup(),
                            $message->getName(),
                            '<error>' . $message->getText() . '</error>'
                        );
                        break;
                    case Message::SEVERITY_NOTICE:
                    case Message::SEVERITY_WARN:
                        $output .= sprintf(
                            $format,
                            $message->getGroup(),
                            $message->getName(),
                            '<comment>' . $message->getText() . '</comment>'
                        );
                        break;
                    case Message::SEVERITY_INFO:
                        $output .= sprintf(
                            $format,
                            $message->getGroup(),
                            $message->getName(),
                            '<info>' . $message->getText() . '</info>'
                        );
                        break;
                    case Message::SEVERITY_DEBUG:
                    default:
                        $output .= sprintf(
                            $format,
                            $message->getGroup(),
                            $message->getName(),
                            $message->getText()
                        );
                        break;
                }

                $output .= PHP_EOL;
            }
        }

        return $output;
    }
}
