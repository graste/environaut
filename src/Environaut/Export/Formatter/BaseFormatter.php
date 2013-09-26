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

    /**
     * Like vsprintf, but accepts $args keys instead of order index.
     * Both numeric and strings matching /[a-zA-Z0-9_-]+/ are allowed.
     *
     * Base version of the method:
     * @see http://www.php.net/manual/de/function.vsprintf.php#110666
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
