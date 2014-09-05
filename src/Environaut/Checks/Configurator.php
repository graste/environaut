<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

/**
 * Basic check to ask the user for environment configuration settings.
 *
 * Supported parameters are:
 * - "introduction": text to display before this check
 * - "question": text to ask user for a value
 * - "setting": name of setting variable
 * - "setting_group": group the configured setting belongs to (for export; defaults to "config")
 * - "choices": array of autocomplete values or choices for selection (depending on check configuration)
 * - "default": default value if none is given by the user
 * - "hidden": hidden question to user (e,g. for credentials)
 * - "allow_fallback": allow fallback to visible input if hidden input does not work
 * - "validator": class/method to use for validation of value - must return valid value or throw helpful exception
 * - "max_attempts": maximum attempts if validator is specified
 * - "confirm": simple yes/no confirmation question (only y/n are accepted answers)
 * - "select": select value from the list of choices
 */
class Configurator extends Check
{
    /**
     * Default group of settings that this check stores in the result.
     */
    const DEFAULT_SETTING_GROUP_NAME = 'config';

    /**
     * Default group name used in messages of the report.
     * By default also used as default setting group name if not customized.
     */
    const DEFAULT_CUSTOM_GROUP_NAME = 'Configuration';

    /**
     * Returns the default group name this check uses when none is specified.
     *
     * @return string default group name of the check
     */
    public function getDefaultGroupName()
    {
        return self::DEFAULT_CUSTOM_GROUP_NAME;
    }

    /**
     * Returns the default group name this check uses for settings when none is specified.
     *
     * @return string default group name of settings of this check
     */
    public function getDefaultSettingGroupName()
    {
        if ($this->group !== self::DEFAULT_CUSTOM_GROUP_NAME) {
            return $this->group;
        }

        return self::DEFAULT_SETTING_GROUP_NAME;
    }

    /**
     * Asks the user a question and sets the answer as a setting on the result.
     *
     * @return boolean true if configuration could be added to the result; false otherwise
     *
     * @throws \InvalidArgumentException on setup errors like "choices" can't be interpreted
     * @throws \RuntimeException in case "allow_fallback" is deactivated but the response can't be "hidden"
     * @throws \RuntimeException if there is no data to read in the input stream
     * @throws \Exception when the maximum number of attempts has been reached and no valid response has been given
     */
    public function run()
    {
        $dialog = $this->getDialogHelper();
        $output = $this->getOutputStream();

        $output->writeln(PHP_EOL); // to get some margin to the progress bar

        $introduction = $this->parameters->get('introduction', false);
        if (false !== $introduction) {
            $output->writeln($introduction);
            $output->writeln('');
        }

        $name = $this->parameters->get('setting', $this->getName());
        $setting_group = $this->parameters->get('setting_group');
        $choices = $this->parameters->get('choices', array());
        $default = $this->parameters->get('default', null);
        $validator = $this->parameters->get('validator', false);
        $hidden = (bool) $this->parameters->get('hidden', false);
        $allow_fallback = (bool) $this->parameters->get('allow_fallback', false);
        $max_attempts = $this->parameters->get('max_attempts', false);
        $confirm = (bool) $this->parameters->get('confirm', false);
        $select = (bool) $this->parameters->get('select', false);

        if ($this->cache->has($name, $setting_group)) {
            $cached_setting = $this->cache->get($name, $setting_group);
            $this->addInfo(
                "Setting [" . $cached_setting->getGroup() . "][$name] already configured. Using " .
                'value: ' . var_export($cached_setting->getValue(), true)
            );
            // add cached value directly as cachable setting to the result of this check
            $this->result->addSetting($cached_setting);

            return true;
        }

        $question = '<question>' . $this->parameters->get('question', '"setting_question" is not set');

        // a simple yes/no confirmation dialog
        if ($confirm) {
            $default = (bool) ($default === null ? true : $default);
            $default_text = ($default ? 'y' : 'n');
            $question .= "</question> (Type [y/n/<return>], default=$default_text): ";

            $value = $dialog->askConfirmation($output, $question, $default);

            $default_text = ($default ? 'enabled' : 'disabled');
            $this->addInfo($name . ' is ' . $default_text);

            $this->addCachableSetting($name, $value, $setting_group);

            return true;
        }

        // add default value to question if specified
        if (null !== $default) {
            $question .= "</question> (Default: $default)";
        } else {
            $question .=  '</question>';
        }
        $question .= ': ';

        // selection dialog to choose values from a list of choices
        if ($select) {
            $value = $dialog->select($output, $question, $choices, $default, $max_attempts);
            $this->addCachableSetting($name, $choices[$value], $setting_group);
            $this->addInfo('Selected value for "' . $name . '" is "' . $choices[$value] . '".');

            return true;
        }

        if (false !== $validator) { // use value validation?
            if ($hidden) {
                $value = $dialog->askHiddenResponseAndValidate(
                    $output,
                    $question,
                    $validator,
                    $max_attempts,
                    $allow_fallback
                );
            } else {
                $value = $dialog->askAndValidate(
                    $output,
                    $question,
                    $validator,
                    $max_attempts,
                    $default,
                    $choices
                );
            }
        } else { // do not use value validation
            if ($hidden) {
                $value = $dialog->askHiddenResponse($output, $question, $allow_fallback);
            } else {
                $value = $dialog->ask($output, $question, $default, $choices);
            }
        }

        $this->addInfo('Successfully configured "' . $name . '".');
        $this->addCachableSetting($name, $value, $setting_group);

        $output->writeln('');

        return true;
    }
}
