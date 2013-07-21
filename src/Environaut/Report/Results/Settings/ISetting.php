<?php

namespace Environaut\Report\Results\Settings;

/**
 * Interface that defines settings.
 */
interface ISetting
{
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
