<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class Configurator extends Check
{
    public function process()
    {
        $dialog = $this->getDialogHelper();

        $name = $this->parameters->get('setting_name', $this->getName() . 'default_key_name');
        $autocomplete_values = $this->parameters->get('autocomplete_values', array());
        $default_value = $this->parameters->get('default_value', null);
        $hidden = (bool) $this->parameters->get('hidden', false);
        $allow_fallback = (bool) $this->parameters->get('allow_fallback', false);
        $max_attempts = $this->parameters->get('max_attempts', false);
        $introduction = $this->parameters->get('introduction', false);

        $question = '<question>' . $this->parameters->get('question', '"setting_question" is not set');
        if (null !== $default_value) {
            $question .= "</question> (Default: $default_value): ";
        }
        else {
            $question .=  '</question>: ';
        }

        if (false !== $introduction) {
            $this->command->getOutput()->writeln($introduction);
        }

        $setting_validator = $this->parameters->get('validator', false);
        if (false !== $setting_validator) // use value validation?
        {
            if ($hidden) {
                $value = $dialog->askHiddenResponseAndValidate($this->command->getOutput(), $question, $setting_validator, $max_attempts, $allow_fallback);
            }
            else {
                $value = $dialog->askAndValidate($this->command->getOutput(), $question, $setting_validator, $max_attempts, $default_value, $autocomplete_values);
            }
        } else { // do not use value validation
            if ($hidden) {
                $value = $dialog->askHiddenResponse($this->command->getOutput(), $question, $allow_fallback);
            }
            else {
                $value = $dialog->ask($this->command->getOutput(), $question,  $default_value, $autocomplete_values);
            }
        }

        $this->addInfo('Successfully configured "' . $name . '".');

        $this->addSetting($name, $value);

        return $this->result;
    }
}

