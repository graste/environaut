<?php

namespace Environaut\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Report\IReport;

/**
 * Interface that report formatters must implement.
 */
interface IReportFormatter
{
    /**
     * This method takes the given report and then does something with it. This may include
     * writing the settings and messages to different files or send them somewhere.
     *
     * It may return strings that will be output to the console afterwards. The return
     * messages may be formatted with tags understood by the Symfony Console Component.
     *
     * @param \Environaut\Report\IReport $report
     *
     * @return string console messages to be displayed
     */
    public function format(IReport $report);

    /**
     * Sets the given runtime parameters on the formatter.
     *
     * @param Parameters $parameters options understood by the formatter
     */
    public function setParameters(Parameters $parameters);
}
