<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class Configurator extends Check
{
    public function process()
    {
        $dialog = $this->getDialogHelper();

        $name = $this->parameters->get('setting_name', $this->getName() . 'default_key_name');
        $autocomplete_values = $this->parameters->get('setting_autocomplete_values', array());
        $default_value = $this->parameters->get('setting_default_value', null);
        $question = $this->parameters->get('setting_question', '"setting_question" is not set');
        if (null !== $default_value) {
            $question .= " (Default: $default_value): ";
        }
        else {
            $question .= ": ";
        }
        $hidden = (bool) $this->parameters->get('hidden', false);
        $allow_fallback = (bool) $this->parameters->get('allow_fallback', false);

        if ($hidden) {
            $value = $dialog->askHiddenResponse($this->command->getOutput(), $question, $allow_fallback);
        }
        else {
            $value = $dialog->ask($this->command->getOutput(), $question,  $default_value, $autocomplete_values);
        }

        $this->addInfo('Successfully got value for "' . $name . '".');

        $this->addSetting($name, $value);

        return $this->result;
    }
}

