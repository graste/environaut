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
    protected $parameters;

    /**
     * Create new instance of the formatter.
     *
     * @param array $options options string $format sprintf compatible format for the result messages
     */
    public function __construct(array $options = array())
    {
        $this->parameters = new Parameters($options);
    }

    /**
     * Sets the given runtime parameters on the formatter.
     *
     * @param Parameters $parameters associative array with options understood by this formatter
     *
     * @return $this for fluent API support
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns all runtime parameters for this formatter from the config.
     *
     * @return Parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
