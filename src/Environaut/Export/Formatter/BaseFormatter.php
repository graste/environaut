<?php

namespace Environaut\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Export\Formatter\IReportFormatter;
use RuntimeException;

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

    /**
     * Like vsprintf, but accepts keys instead of an order index.
     * The allowed format of named arguments is: /[a-zA-Z0-9_-]+/
     *
     * For the base version of this method by Josef Kufner see:
     * @see http://www.php.net/manual/de/function.vsprintf.php#110666
     *
     * @example vskprintf(
     *      '%param$s must be between %min$03d and %max$03d.',
     *      array('param' => 'Value', 'min' => 3, 'max' => 99)
     *  ) // gives: 'Value must be between 003 and 099.'
     *
     * '%s' without argument name and positional directives like '%1$s' do work.
     * Everything vsprintf() can do is still supported.
     *
     * @param string $format input string with formatting directives
     * @param array $args arguments to use for formatting directives
     *
     * @return formatted string according to given format and arguments
     *
     * @throws RuntimeException in case of non-string format string
     *
     * @author Josef Kufner <jkufner(at)gmail.com>
     * @author Steffen Gransow <agavi@mivesto.de>
     */
    public static function vksprintf($format, array $args)
    {
        if (!is_string($format)) {
            throw new RuntimeException('Only strings are acceptable as input format.');
        }

        if (empty($args)) {
            return $format;
        }

        $map = array_flip(array_keys($args));

        $str = preg_replace_callback(
            '/(^|[^%])%([a-zA-Z0-9_\-\.]+)\$/',
            function ($m) use ($map) {
                $key = $m[2];
                if (!is_numeric($key) && array_key_exists($key, $map)) {
                    return $m[1] . '%' . ($map[$key] + 1) . '$';
                }
                return $m[1] . '%' . $key . '$';
            },
            $format
        );

        return vsprintf($str, $args);
    }
}
