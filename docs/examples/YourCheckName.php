<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class YourCheckName extends Check
{
    /**
     * Default group of settings that this check stores in the result.
     */
    const DEFAULT_SETTING_GROUP_NAME = 'config';

    /**
     * Default group name used in messages of the report.
     * By default also used as default setting group name if not customized.
     */
    const DEFAULT_CUSTOM_GROUP_NAME = 'YourCustomGroup';

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

    public function run()
    {
        // useful to ask users for values
        $dialog = $this->getDialogHelper();

        // useful to output text to CLI
        $output = $this->getOutputStream();

        // get parameters from the check configuration in the environaut config
        $name = $this->parameters->get('setting', $this->getName());
        $setting_group = $this->parameters->get('setting_group');

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

        //
        // DO YOUR CHECK STUFF HERE...
        //

        $something_weird_happened = true;

        if ($something_weird_happened) {
            return false; // check did not succeed, result will be marked as FAIL
        }

        // add messages with different severities to the result of this check
        $this->addInfo('Successfully configured "' . $this->getName() . '".');
        $this->addNotice('Warning! Something fishy happened. You better check that.');
        $this->addError('Omgomgomg, errors!');

        // add a cachable setting in the default setting group for the export
        $this->addCachableSetting($name, 'value-for-config-export', $setting_group);
        // non-cachable settings may be added as well:
        $this->addSetting('cachable', 'no');

        return true; // check ran successfully and result is perhaps filled with messages and settings
    }
}

