<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;
use Environaut\Checks\PhpSettingCheck;

/**
 * This check compares PHP extensions and their version and settings against given values.
 * By default the name of the check will be used as the PHP extension name that is checked.
 *
 * All supported parameters are:
 * - "extension": name of extension to check (defaults to the "name" of the check)
 * - "custom_name": name to be used for messages if the check's name is used as extension name
 * - "loaded": boolean parameter to determine if the extension should be loaded or not
 * - "version": the version string the extension should match (e.g. ">=2.6.30" or ">1.0.2")
 * - "regex": regular expression(s) that should match on the extension's info (see phpinfo)
 * - "help": message to display when the extension does not fulfil the "version", "regex" and/or "loaded" parameters
 * - "debug": var_dump's extension name, version and info string for regex analysis
 *
 * As the version of a PHP extension may be empty or some weird value you can use a version comparison of a phpinfo()
 * string by using a nested "version" parameter like this:
 *
 * <parameter name="version">
 *     <parameter name="regex"><![CDATA[#libXML (Compiled )?Version => (?P<version>\d+.+?)\n#]]></parameter>
 *     <parameter name="value"><![CDATA[>=2.6.30]]></parameter>
 * </parameter>
 *
 * Notice, that you need a NAMED CAPTURING GROUP "version" in you regular expression. The "value" then specifies the
 * version comparison operation that should be done with that matching group.
 */
class PhpExtensionCheck extends Check
{
    public function run()
    {
        $params = $this->getParameters();

        $extension = $params->get('extension', $this->getName());
        if (empty($extension)) {
            throw new \InvalidArgumentException(
                'Parameter "extension" must be a php extension name to check on class "' . get_class($this) . '".'
            );
        }

        $default_custom_name = $extension === $this->getName() ? 'PHP Extensions' : $this->getName();
        $custom_name = $params->get('custom_name', $default_custom_name);
        $help = $params->get('help');

        $wanted_version = $params->get('version');
        $loaded = $params->get('loaded');
        $regex = $params->get('regex');
        $debug_mode = $params->get('debug', false);

        $okay = true;

        try {
            // GATHER DATA
            $extension_class = new \ReflectionExtension($extension);
            $extension_version = $extension_class->getVersion();
            ob_start();
            $extension_class->info();
            $info = ob_get_clean();

            if ($debug_mode) {
                var_dump($extension, $extension_version, $info, '============================================');
            }

            // OPTIONS CHECK
            if (null !== $regex) {
                if (is_array($regex)) { // multiple regular expressions
                    foreach ($regex as $key => $test) {
                        if (!is_string($test)) {
                            throw new \InvalidArgumentException(
                                'The extension requirements must be strings that are valid regular expressions.'
                            );
                        }

                        if (strpos($test, '(?P<contains>') !== false && !is_numeric($key)) {
                            // explode "name" attribute & match each item in the named capturing group "contains" w/ it
                            $values = explode(',', $key);
                            $values = array_map('trim', $values);
                            $regex_matches = preg_match($test, $info, $matches);
                            if ($regex_matches) {
                                $pool = $matches['contains'];
                                foreach ($values as $value) {
                                    if (strpos($pool, $value) === false) {
                                        $this->addError(
                                            'The extension "' . $extension . '" does not have "' .
                                            $value . '" support.',
                                            $custom_name
                                        );
                                        $okay = false;
                                    } else {
                                        $this->addInfo(
                                            'The extension "' . $extension . '" does have "' . $value . '" support.',
                                            $custom_name
                                        );
                                    }
                                }
                            }
                        } else { // just preg_match the given regex string
                            if (!preg_match($test, $info)) {
                                $this->addError(
                                    'The extension "' . $extension . '" does not match the requirement: ' . $test,
                                    $custom_name
                                );
                                $okay = false;
                            } else {
                                $this->addInfo(
                                    'The extension "' . $extension . '" does match the requirement: ' . $test,
                                    $custom_name
                                );
                            }
                        }
                    }
                } else { // single regex to test
                    if (!preg_match($regex, $info)) {
                        $this->addError(
                            'The extension "' . $extension . '" does not match the requirement: ' . $regex,
                            $custom_name
                        );
                        $okay = false;
                    } else {
                        $this->addInfo(
                            'The extension "' . $extension . '" does match the requirement: ' . $regex,
                            $custom_name
                        );
                    }
                }
            }

            // VERSION COMPARISON
            if (null !== $wanted_version) {
                if (is_array($wanted_version) && array_key_exists('regex', $wanted_version) &&
                    array_key_exists('value', $wanted_version)) {

                    $regex_matches = preg_match($wanted_version['regex'], $info, $matches);

                    if (!$regex_matches || !array_key_exists('version', $matches)) {
                        $this->addError(
                            'Version information of "' . $extension . '" could not be determined, as ' .
                            'the given regular expression did not match: "' . $wanted_version['regex'] .
                            'Remember that you need a valid named capturing group "version" in the ' .
                            'regexp, e.g.: #libXML (Compiled )?Version => (?P<version>\d+.+?)\n#',
                            $custom_name
                        );
                        $okay = false;
                    } elseif ($regex_matches) {
                        $operator = PhpSettingCheck::getOperator($wanted_version['value']);
                        $wanted_version_without_operator = ltrim($wanted_version['value'], '<>!=');
                        if (!version_compare($matches['version'], $wanted_version_without_operator, $operator)) {
                            $this->addError(
                                'Version of "' . $extension . '" should be "' . $wanted_version['value'] .
                                '", but is: "' . $matches['version'] . '"',
                                $custom_name
                            );
                            $okay = false;
                        } else {
                            $this->addInfo(
                                'Version of extension "' . $extension . '" is "' . $matches['version'] .
                                '" ("' . $wanted_version['value'] . '").',
                                $custom_name
                            );
                        }
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
                    } else {
                        $this->addInfo(
                            'Version of extension "' . $extension . '" is "' . $extension_version . '"' .
                            ' ("' . $wanted_version. '").',
                            $custom_name
                        );
                    }
                } else {
                    throw new \InvalidArgumentException(
                        'A nested version parameter needs exactly two keys: ' . PHP_EOL .
                        '- "regex" with one matching named capturing group "version" (e.g. ' .
                        '"#libXML (Compiled )?Version => (?P<version>\d+.+?)\n#") and ' . PHP_EOL .
                        '- "value" to compare the named capturing group content against' .
                        '(version comparison, e.g. ">=2.6.26").'
                    );
                }
            }

            // LOADED CHECK
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
            $this->addInfo('Extension "' . $extension . '" is available and correct.', $custom_name);
        }

        return $okay;
    }
}
