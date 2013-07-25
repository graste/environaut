<?php

namespace Environaut\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Export\Formatter\IReportFormatter;

/**
 * Simple formatter that takes messages from the results
 * of the given report and enhances them according to their
 * severity etc.
 */
abstract class BaseFormatter implements IReportFormatter
{
    /**
     * @var Parameters options for formatting
     */
    protected $options;

    /**
     * Create new instance of the formatter.
     *
     * @param array $options options string $format sprintf compatible format for the result messages
     */
    public function __construct(array $options = array())
    {
        $this->options = new Parameters($options);
    }

    /**
     * Sets the given options on the formatter.
     *
     * @param array $options associative array with options understood by this formatter
     */
    public function setOptions(array $options = array())
    {
        $this->options = new Parameters($options);
    }

    public function getOptions()
    {
        return $this->options;
    }
}
