<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class PhpSetting extends Check
{
    public function run()
    {
        $setting_name = $this->parameters->get('setting_name');
        if (empty($setting_name)) {
            throw new \InvalidArgumentException('Parameter "setting_name" must be a valid php.ini setting to check on class "' . get_class($this) . '".');
        }
        $setting_value = $this->parameters->get('setting_value');
        $infinite_value = $this->parameters->get('infinite_value');
        $operator = $this->parameters->get('operator');
        $comparison = $this->parameters->get('comparison', 'simple');

        /**
         * The value of the configuration option as
         * - a string on success, or
         * - an empty string for null values.
         * - Returns FALSE if the configuration option doesn't exist.
         */
        $value = ini_get($setting_name);

        var_dump(ini_get_all());

        $this->addInfo('Successfully got value for "' . $setting_name . '".');

        return $this->result;
    }
}