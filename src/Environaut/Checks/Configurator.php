<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

/**
 * Basic check to ask the user for environment configuration settings.
 *
 * Supported parameters are:
 * - introduction: text to display before this check
 * - question: text to ask user for a value
 * - setting: name of setting variable
 * - choices: autocomplete values or choices for selection
 * - default: default value if none is given by the user
 * - hidden: hidden question to user (e,g. for credentials)
 * - allow_fallback: allow fallback to visible input if hidden input does not work
 * - validator: class/method to use for validation of value - must return valid value or throw helpful exception
 * - max_attempts: maximum attempts if validator is specified
 * - confirm: simple yes/no confirmation question (only y/n are accepted answers)
 * - select: select value from the list of choices
 */
class Configurator extends Check
{
    public function run()
    {
        $dialog = $this->getDialogHelper();
        $output = $this->getOutputStream();

        $output->writeln(PHP_EOL); // to get some margin to the progress bar

        $introduction = $this->parameters->get('introduction', false);
        if (false !== $introduction) {
            $output->writeln($introduction . PHP_EOL);
        }

        $name = $this->parameters->get('setting', $this->getName());
        $choices = $this->parameters->get('choices', array());
        $default = $this->parameters->get('default', null);
        $validator = $this->parameters->get('validator', false);
        $hidden = (bool) $this->parameters->get('hidden', false);
        $allow_fallback = (bool) $this->parameters->get('allow_fallback', false);
        $max_attempts = $this->parameters->get('max_attempts', false);
        $confirm = (bool) $this->parameters->get('confirm', false);
        $select = (bool) $this->parameters->get('select', false);

        $question = '<question>' . $this->parameters->get('question', '"setting_question" is not set');

        // a simple yes(no confirmation dialog
        if ($confirm) {
            $default = (bool) ($default === null ? true : $default);
            $default_text = ($default ? 'y' : 'n');
            $question .= "</question> (Type [y/n/<return>], default=$default_text): ";
            $value = $dialog->askConfirmation($output, $question, $default);
            $default_text = ($default ? 'enabled' : 'disabled');
            $this->addSetting($name, $value);
            $this->addInfo($name . ' is ' . $default_text);

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
            $this->addSetting($name, $choices[$value]);
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
        $this->addSetting($name, $value);

        $output->writeln('');

        return true;
    }
}
