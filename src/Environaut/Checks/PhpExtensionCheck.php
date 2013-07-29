<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;
use Environaut\Checks\PhpSettingCheck;

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
 * infite value via the "infinite" parameter. The comparison operation is "equals" by default and
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
class PhpExtensionCheck extends Check
{
    public function run()
    {
        $params = $this->getParameters();

        $extension = $params->get('extension', $this->getName());
        if (empty($extension)) {
            throw new \InvalidArgumentException(
                'Parameter "extension" must be a (valid) php extension name to check on class "' . get_class($this) . '".'
            );
        }

        $custom_name = $params->get('custom_name', $extension === $this->getName() ? 'PHP Extensions' : $this->getName());
        $help = $params->get('help');

        $wanted_version = $params->get('version');
        $loaded = $params->get('loaded');
        $regex = $params->get('regex');

        $okay = true;

        try {
            $extension_class = new \ReflectionExtension($extension);
            $extension_version = $extension_class->getVersion();
            ob_start();
            $extension_class->info();
            $info = ob_get_clean();

//var_dump($extension, $extension_version, $wanted_version, $info, '===============================================================================');

            if (null !== $wanted_version) {
                if (is_array($wanted_version) &&
                    array_key_exists('regex', $wanted_version) &&
                    array_key_exists('value', $wanted_version)) {
                    $regex_matches = preg_match($wanted_version['regex'], $info, $matches);
                    if (!array_key_exists('version', $matches)) {
                        throw new \InvalidArgumentException(
                            'you need a valid named capturing group "version" in the ' .
                            'regexp, e.g.: #libXML (Compiled )?Version => (?P<version>\d+.+?)\n#'
                        );
                    }

                    if ($regex_matches) {
                        $operator = PhpSettingCheck::getOperator($wanted_version['value']);
                        $wanted_version_without_operator = ltrim($wanted_version['value'], '<>!=');
                        if (!version_compare($matches['version'], $wanted_version_without_operator, $operator)) {
                            $this->addError(
                                'Version of "' . $extension . '" should be "' . $wanted_version['value'] .
                                '", but is: "' . $matches['version'] . '"',
                                $custom_name
                            );
                            $okay = false;
                        }
                    } else {
                        $this->addError(
                            'Version information of "' . $extension . '" could not be determined, as' .
                            'the given regular expression "' . $wanted_version['regex'] . '" did not match.'
                        );
                        $okay = false;
                    }
                } elseif (is_string($wanted_version)) {
                    $operator = PhpSettingCheck::getOperator($wanted_version);
                    $wanted_version_without_operator = ltrim($wanted_version, '<>!=');
                    if (!version_compare($extension_version, $wanted_version_without_operator, $operator)) {
                        $this->addError(
                            'Version of "' . $extension . '" should be "' . $wanted_version .
                            '", but is: "' . $extension_version . '"',
                            $custom_name
                        );
                        $okay = false;
                    }
                } else {
                    $this->addError(
                        'A nested version parameter needs exactly two keys: ' .
                        '"regex" (with one matching group) and "value" (version comparison).'
                    );
                    $okay = false;
                }
            }

            if (null !== $loaded) {
                if ($loaded != extension_loaded($extension)) {
                    $loaded_string = $loaded ? 'not loaded, but should be.' : 'loaded, but should not be.';
                    $this->addError('The extension "' . $extension . '" is ' . $loaded_string, $custom_name);
                    $okay = false;
                } else {
                    $this->addInfo('The extension "' . $extension . '" is loaded.', $custom_name);
                }
            }
        } catch (\ReflectionException $e) {
            $this->addError('There is no extension with the name "' . $extension . '".', $custom_name);
            $okay = false;
        }


        if (!$okay && $help !== null) {
            $this->addNotice($help, $custom_name);
        }

        if ($okay) {
            $this->addInfo('Extension "' . $extension . '" is available.', $custom_name);
        }

        return $okay;
    }
}
