<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;
use Environaut\Config\Parameters;

class PhpSettingCheck extends Check
{
    public function run()
    {
        $output = $this->getOutputStream();
        $params = $this->getParameters();

        $setting = $params->get('setting', $this->getName());
        if (empty($setting)) {
            throw new \InvalidArgumentException(
                'Parameter "setting" must be a (valid) php.ini setting to check on class "' . get_class($this) . '".'
            );
        }

        $custom_name = $params->get('custom_name', 'PHP Settings');
        $help = $params->get('help');
        $setting_value = $params->get('value');
        $infinite_value = $params->get('infinite');
        $comparison = strtolower($params->get('comparison', 'equals'));

        if (!in_array($comparison, $this->getSupportedComparisons())) {
            throw new \InvalidArgumentException(
                'Unsupported comparison name: "' . $comparison .
                '". Supported are: ' . implode(', ', $this->getSupportedComparisons())
            );
        }

        /**
         * The value of the configuration option as
         * - a string on success or
         * - an empty string for null values or
         * - false if the setting doesn't exist
         */
        $value = ini_get($setting);

        //var_dump($setting, $value, $comparison, $setting_value, $infinite_value);

        $okay = true;
        if (false === $value) {
            $this->addError('There is no setting with the name "' . $setting . '".', $custom_name);
            $okay = false;
        } else {
            switch ($comparison) {
                case 'integer':
                    if (null !== $infinite_value && $value != $infinite_value) {
                        $operator = $this->getOperator($setting_value);
                        $actual = $this->getIntegerValue($value);
                        $expected = $this->getIntegerValue($setting_value);
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
                                $okay = $actual < $expected;
                                break;
                            case '!=':
                            case '<>':
                                $okay = $actual != $expected;
                                break;
                            case '=':
                            default:
                                $okay = $actual == $expected;
                                break;
                        }
                        $this->addError(
                            'Value of "' . $setting . '" should be "' . $setting_value .
                            '", but is: "' . $value . '"',
                            $custom_name
                        );
                        $okay = false;
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
                    $operator = $this->getOperator($setting_value);
                    if (version_compare($value, $setting_value, $operator)) {
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

                case 'equals':
                default:
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
            $this->addInfo('Value of "' . $setting . '" is okay: "' . $value . '"', $custom_name);
        }

        return $okay;
    }

    public static function getSupportedComparisons()
    {
        return array(
            'equals',
            'regex',
            'notempty',
            'null',
            'version',
            'integer',
        );
    }

    protected function getOperator($value)
    {
        $operator = strpos($value, '>') === 0 ? '>' : null;
        if (null === $operator) {
            $operator = strpos($value, '>=') === 0 ? '>=' : null;
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
            $operator = strpos($value, '<>') === 0 ? '!=' : null;
        }
        if (null === $operator) {
            $operator = '>=';
        }

        return $operator;
    }

    protected function getIntegerValue($value)
    {
        if (!is_numeric($value)) {
            $len = strlen($value);
            $quantity = substr($value, 0, $len - 1);
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

        return $value;
    }
}
