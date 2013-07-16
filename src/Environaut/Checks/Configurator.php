<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class Configurator extends Check
{
    public function process()
    {
        $dialog = $this->getDialogHelper();

        $introduction = $this->parameters->get('introduction', false);
        if (false !== $introduction) {
            $this->command->getOutput()->writeln($introduction);
        }

        $name = $this->parameters->get('setting_name', $this->getName() . 'default_key_name');
        $choices = $this->parameters->get('choices', array());
        $default = $this->parameters->get('default', null);
        $validator = $this->parameters->get('validator', false);
        $hidden = (bool) $this->parameters->get('hidden', false);
        $allow_fallback = (bool) $this->parameters->get('allow_fallback', false);
        $max_attempts = $this->parameters->get('max_attempts', false);
        $confirm = (bool) $this->parameters->get('confirm', false);

        $question = '<question>' . $this->parameters->get('question', '"setting_question" is not set');

        if ($confirm) {
            $default = (bool) ($default === null ? true : $default);
            $default_text = ($default ? 'Y' : 'N');
            $question .= "</question> (Type [Y/N/Return], default=$default_text): ";
            $value = $dialog->askConfirmation($this->command->getOutput(), $question, $default);
            $default_text = ($default ? 'enabled' : 'disabled');
            $this->addSetting($name, $value);
            $this->addInfo($name . ' is ' . $default_text);

            return $this->result;
        }

        if (null !== $default) {
            $question .= "</question> (Default: $default)";
        }
        else {
            $question .=  '</question>';
        }
        $question .= ': ';

        if (false !== $validator) // use value validation?
        {
            if ($hidden) {
                $value = $dialog->askHiddenResponseAndValidate($this->command->getOutput(), $question, $validator, $max_attempts, $allow_fallback);
            }
            else {
                $value = $dialog->askAndValidate($this->command->getOutput(), $question, $validator, $max_attempts, $default, $choices);
            }
        } else { // do not use value validation
            if ($hidden) {
                $value = $dialog->askHiddenResponse($this->command->getOutput(), $question, $allow_fallback);
            }
            else {
                $value = $dialog->ask($this->command->getOutput(), $question,  $default, $choices);
            }
        }

        $this->addInfo('Successfully configured "' . $name . '".');

        $this->addSetting($name, $value);

        return $this->result;
    }
}
