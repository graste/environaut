<?php

namespace Environaut\Report\Results\Settings;

use Environaut\Checks\ICheck;
use Environaut\Report\Results\Settings\ISetting;

/**
 * Simple value holder with a name as key and a group name to be used to group/namespace settings.
 */
class Setting implements ISetting
{
    /**
     * @var string setting name
     */
    protected $name;

    /**
     * @var string name of group this setting belongs to
     */
    protected $group;

    /**
     * @var mixed value of this setting
     */
    protected $value;

    /**
     * @var int value of this setting
     */
    protected $flag;

    /**
     * Create a new setting instance.
     *
     * @param string $name name of setting
     * @param mixed $value value for that key
     * @param string $group group name that may be used to namespace/group settings
     */
    public function __construct($name, $value, $group = ICheck::DEFAULT_GROUP_NAME, $flag = ISetting::NORMAL)
    {
        $this->name = $name;
        $this->value = $value;
        $this->group = $group;
        $this->flag = $flag;
    }

    /**
     * Returns the name or key of this setting.
     *
     * @return string name of this setting
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the value of this setting.
     *
     * @return mixed value of this setting
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the group name of this setting that may be useful for separating settings by group on export.
     *
     * @return string group name
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Returns the type of setting.
     *
     * @return int flag
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * Returns an array with the group containing the name and value of the setting.
     *
     * @return array representation of this setting
     */
    public function toArray()
    {
        return array(
            $this->group => array(
                $this->name => $this->value
            )
        );
    }
}
