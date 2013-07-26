<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class YourCheckName extends Check
{
    public function run()
    {
        // useful to ask users for values
        $dialog = $this->getDialogHelper();

        // useful to output text to CLI
        $output = $this->getOutputStream();

        // get parameters from the check configuration in the environaut config
        $name = $this->parameters->get('setting', uniqid($this->getName()));

        // do some stuff here...

        // add messages with different severities to the result of this check
        $this->addInfo('Successfully configured "' . $this->getName() . '".');
        $this->addNotice('Warning! Something fishy happened. You better check that.');
        $this->addError('Omgomgomg, errors!');

        // add a setting in the "default" group for the export
        $this->addSetting($name, 'value-for-config-export');

        return true;
    }
}

