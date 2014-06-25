<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

/**
 * This check compares PHP runtime configuration options (from php.ini etc.) against given values.
 * The following comparison operations are supported:
 * - "equals": the default comparison; checks whether the setting is exactly as given (e.g. "foo")
 * - "null": checks whether the setting is NULL
 * - "notempty": checks whether the setting has a value set (that is, not null or empty string)
 * - "version": checks how versions compare (e.g. "=>2.6.30")
 * - "regex": checks whether the PHP setting's value matches the given regular expression (e.g. "/file\.ini/"
 * - "integer": checks whether the integer or byte value of the PHP setting is correct (e.g. ">30" or ">=256M")
 *
 * By default the name of the check will be used as the PHP runtime setting name to check. For integer
 * or byte values that may be set to infinite values (like "-1" or "0") you can specify the setting's
 * infinite value via the "infinite" parameter. The comparison operation is "equals" by default and
 * may be specified via the "comparison" parameter.
 *
 * All supported parameters are:
 * - "setting": name of setting to check (defaults to the "name" of the check)
 * - "custom_name": name to be used for messages if the check's name is used as setting name
 * - "value": the value to compare the setting's value against
 * - "infinite": the infinite value of the setting to check (e.g. "-1" or "0" for integer or byte values)
 * - "comparison": the comparison operation to use (see above)
 * - "help": message to display when the setting's value is not correct
 */
class PhpSettingCheck extends Check
{
    /**
     * Default group name used in messages of the report.
     * By default also used as default setting group name if not customized.
     */
    const DEFAULT_CUSTOM_GROUP_NAME = 'PHP Settings';

    /**
     * Returns the default group name this check uses when none is specified.
     *
     * @return string default group name of the check
     */
    public function getDefaultGroupName()
    {
        return self::DEFAULT_CUSTOM_GROUP_NAME;
    }

    public function run()
    {
        $params = $this->getParameters();

        $setting = $params->get('setting', $this->getName());
        if (empty($setting)) {
            throw new \InvalidArgumentException(
                'Parameter "setting" must be a (valid) php.ini setting to check on class "' . get_class($this) . '".'
            );
        }

        $custom_name = $params->get('custom_name', $this->getName());
        $help = $params->get('help');
        $setting_value = $params->get('value');
        $infinite_value = $params->get('infinite');
        $comparison = strtolower($params->get('comparison', 'equals'));

        if (!in_array($comparison, self::getSupportedComparisons())) {
            throw new \InvalidArgumentException(
                'Unsupported comparison name: "' . $comparison .
                '". Supported are: ' . implode(', ', self::getSupportedComparisons())
            );
        }

        /**
         * The value of the configuration option as
         * - a string on success or
         * - an empty string for null values or
         * - false if the setting doesn't exist
         */
        $value = ini_get($setting);

        $okay = true;
        if (false === $value) {
            $this->addError('There is no setting with the name "' . $setting . '".', $custom_name);
            $okay = false;
        } else {
            switch ($comparison) {
                case 'integer':
                    // bail out here directly to not compare float values like "2.75M" or "1e3"
                    if ($value !== trim($value) || strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                        $this->addError(
                            'Value of "' . $setting . '" should be "' . $setting_value .
                            '", but is: "' . $value . '". Leading whitespace or ' .
                            'decimal values will fail in practice as PHP treats e.g. "2.75M" as "2M"!',
                            $custom_name
                        );
                        $okay = false;
                        break;
                    }

                    if ((null !== $infinite_value && $value != $infinite_value) || null === $infinite_value) {
                        $okay = self::compareIntegers($value, $setting_value);
                        if (!$okay) {
                            $this->addError(
                                'Value of "' . $setting . '" should be "' . $setting_value .
                                '", but is: "' . $value . '"',
                                $custom_name
                            );
                        }
                    }
                    break;
                case 'regex':
                    if (!preg_match($setting_value, $value)) {
                        $this->addError(
                            'Value of "' . $setting . '" does not match "' . $setting_value .
                            '": ' . $value . '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                    break;
                case 'version':
                    $operator = self::getOperator($setting_value);
                    $setting_value_without_operator = ltrim($setting_value, '<>!=');
                    if (!version_compare($value, $setting_value_without_operator, $operator)) {
                        $this->addError(
                            'Version of "' . $setting . '" should be "' . $setting_value .
                            '", but is: "' . $value . '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                    break;
                case 'notempty':
                    if ($value === null || $value === "") {
                        $this->addError(
                            'Value of "' . $setting . '" should not be NULL or empty string, but is: "' . $value .
                            '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                    break;
                case 'null':
                    if ($value !== "") {
                        $this->addError(
                            'Value of "' . $setting . '" must be NULL, but is: "' . $value . '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                    break;
                case 'notequals':
                    if ($value === $setting_value) {
                        $this->addError(
                            'Value of "' . $setting . '" should not be "' . $setting_value . '", but is: "' . $value .
                            '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                    break;
                case 'equals':
                default:
                    if ($value === "" && $setting_value === "0") {
                        break; // boolean value should be off (and can be reported as "" or "0" according to php docs)
                    }
                    if ($value !== $setting_value) {
                        $this->addError(
                            'Value of "' . $setting . '" should be "' . $setting_value . '", but is: "' . $value .
                            '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                    break;
            }
        }

        if (!$okay && $help !== null) {
            // TODO depending on local/global value and access level we could generate
            //  a small help here where to change the ini setting
            $this->addNotice($help, $custom_name);
        }

        if ($okay) {
            // TODO give better info depending on comparison type?
            $this->addInfo('Value of "' . $setting . '" is okay: "' . $value . '"', $custom_name);
        }

        return $okay;
    }

    /**
     * Returns the supported comparison names that may be used in comparison operations.
     *
     * @return array of strings (supported comparison names)
     */
    public static function getSupportedComparisons()
    {
        return array(
            'equals',
            'notequals',
            'regex',
            'notempty',
            'null',
            'version',
            'integer',
        );
    }

    /**
     * Compares the given values according to the comparison operator the second
     * value might have and returns whether the evaluation is true.
     *
     * @param string $value integer value that is set in php.ini (e.g. "8M")
     * @param string $setting_value comparison value as string (e.g. ">=8M")
     *
     * @return bool true if comparison is okay; false otherwise.
     */
    public static function compareIntegers($value, $setting_value)
    {
        $okay = false;

        $actual = self::getIntegerValue($value);
        $operator = self::getOperator($setting_value);
        $expected = self::getIntegerValue($setting_value);
        switch ($operator) {
            case '>':
                $okay = $actual > $expected;
                break;
            case '>=':
                $okay = $actual >= $expected;
                break;
            case '<':
                $okay = $actual < $expected;
                break;
            case '<=':
                $okay = $actual <= $expected;
                break;
            case '!=':
                $okay = $actual != $expected;
                break;
            case '=':
            default:
                $okay = $actual == $expected;
                break;
        }

        return $okay;
    }

    /**
     * Returns the comparison operator part of the given value. Valid known
     * comparison operators are: "<", ">", "<=", ">=", "!=" and "=".
     * By default a ">=" will be returned (if no comparison operator is found).
     *
     * @param string $value string that might contain a comparison operator (e.g. "<=2M")
     *
     * @return string operator string found
     */
    public static function getOperator($value)
    {
        // TODO make this smarter and perhaps support "eq", "ne", "le", "ge" etc.
        $operator = strpos($value, '>=') === 0 ? '>=' : null;
        if (null === $operator) {
            $operator = strpos($value, '>') === 0 ? '>' : null;
        }
        if (null === $operator) {
            $operator = strpos($value, '=') === 0 ? '=' : null;
        }
        if (null === $operator) {
            $operator = strpos($value, '<=') === 0 ? '<=' : null;
        }
        if (null === $operator) {
            $operator = strpos($value, '<') === 0 ? '<' : null;
        }
        if (null === $operator) {
            $operator = strpos($value, '!=') === 0 ? '!=' : null;
        }
        if (null === $operator) {
            $operator = '>=';
        }

        return $operator;
    }

    /**
     * Returns the integer value of the given value.
     *
     * @param mixed $value string to interpret (e.g. ">=2.75M")
     *
     * @return int integer value as PHP interprets it on ini settings
     */
    public static function getIntegerValue($value)
    {
        if (!is_numeric($value)) {
            // strip excessive whitespace
            $value = trim($value);
            // strip comparison operator
            $value = ltrim($value, '<>!=');
            // remove the K|M|G suffix if necessary
            $len = strlen($value);
            if (!is_numeric($value)) {
                $quantity = substr($value, 0, $len - 1);
                // we need this, as PHP sets a memory_limit of 0 if the ini value is 0.25M
                // another example: 2.75M will be 2M for PHP (e.g. on post_max_size)
                $quantity = intval($quantity);
            } else {
                $quantity = $value;
            }
            // get unit if this string represents a byte value
            $unit = strtolower(substr($value, $len - 1));
            switch ($unit) {
                case 'k':
                    $quantity *= 1024;
                    break;
                case 'm':
                    $quantity *= 1048576;
                    break;
                case 'g':
                    $quantity *= 1073741824;
                    break;
            }

            return $quantity;
        }

        return intval($value);
    }
}
