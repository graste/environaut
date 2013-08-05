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
     * Determines whether this setting's flag value is contained in the given flag value.
     *
     * For example: If this setting has a flag of ISetting::NORMAL (1) a given $flag value
     * of (ISetting::SENSITIVE | ISetting::WHATEVER) would not match.
     *
     * @param int $flag setting type value to check against, default is null and matches always
     *
     * @return boolean true if this setting's flag is in the given flag
     */
    public function matchesFlag($flag = null)
    {
        if (null !== $flag) {
            return ($this->flag & $flag);
        }

        return true;
    }

    /**
     * Determines whether this setting's group matches the given groups.
     *
     * @param mixed $groups string or array with group names, default null matches always
     *
     * @return boolean true when this setting's group matches the given groups
     */
    public function matchesGroup($groups = null)
    {
        if (null !== $groups) {
            $group_names = self::getGroupNames($groups);
            return in_array($this->group, $group_names);
        }

        return true;
    }

    /**
     * Returns a flat array with four keys (name, group, value and flag).
     *
     * @return array flat associative array representation of this setting
     */
    public function toArray()
    {
        return array(
            'name' => $this->name,
            'value' => $this->value,
            'group' => $this->group,
            'flag' => $this->flag,
        );
    }

    /**
     * @param mixed $groups string with comma separated group names or an array of group names
     *
     * @return array of group names
     */
    public static function getGroupNames($groups = null)
    {
        $group_names = array();

        if (null === $groups || (!is_string($groups) && !is_array($groups))) {
            return array();
        }

        if (is_string($groups)) {
            $groups = explode(',', $groups);
        }

        foreach ($groups as $group_name) {
            $group_names[] = trim($group_name);
        }

        return $group_names;
    }
}
