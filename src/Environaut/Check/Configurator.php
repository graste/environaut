<?php

namespace Environaut\Check;

class Configurator extends Check
{
    public function process()
    {
        $dialog = $this->command->getHelperSet()->get('dialog');
        $autocomplete = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
        $name = $dialog->ask(
            $this->command->getOutput(),
            'Please enter the name of a bundle: ',
            'FooBundle',
            $autocomplete
        );
        $this->addInfo('Successfully got "bundle"!');
        $this->addSetting('bundle', $name);

        return $this->result;
    }
}

