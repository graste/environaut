<?php

namespace Environaut\Report\Results\Settings;

/**
 * Interface that defines settings.
 */
interface ISetting
{
    /**
     * Flag for a normal setting.
     */
    const NORMAL = 1;

    /**
     * Flag for settings that contain sensitive values (like credentials)
     * that should perhaps handled with care and e.g. not be shown on CLI.
     */
    const SENSITIVE = 2;

    /**
     * Flag for all settings
     */
    const ALL = 32768;

    /**
     * Returns the name of the setting (that is, a key for the setting)
     *
     * @return string name of the setting
     */
    public function getName();

    /**
     * Returns the value of the setting.
     *
     * @return mixed value of the setting
     */
    public function getValue();

    /**
     * Returns the group name of this setting that may be useful to group/namespaace settings.
     *
     * @return string group name
     */
    public function getGroup();

    /**
     * Returns an associative array with the group containing an array with name and value of the setting.
     *
     * @return array representation of this setting
     */
    public function toArray();
}
