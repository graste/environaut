<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class Configurator extends Check
{
    public function process()
    {
        $dialog = $this->command->getHelperSet()->get('dialog');
        $autocomplete = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
        $name = $dialog->ask(
            $this->command->getOutput(),
            'Please enter the name of a bundle (defaults to FooBundle; try A...): ',
            'FooBundle',
            $autocomplete
        );
        $this->addInfo('Successfully got "bundle"!');

        $key = isset($this->parameters['keyname']) ? $this->parameters['keyname'] : "$name.default_key_name";
        $this->addSetting($key, $name);

        return $this->result;
    }
}

