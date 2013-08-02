<?php

namespace Environaut\Report\Results;

use Environaut\Checks\ICheck;
use Environaut\Report\Results\IResult;
use Environaut\Report\Results\Messages\IMessage;
use Environaut\Report\Results\Settings\ISetting;

/**
 * Result that checks provide for a report.
 */
class Result implements IResult
{
    /**
     * Check this result belongs to.
     *
     * @var ICheck check instance
     */
    protected $check;

    /**
     * Messages from the check.
     *
     * @var array of IMessage instances
     */
    protected $messages = array();

    /**
     * Settings from the check.
     *
     * @var array of ISetting instances
     */
    protected $settings = array();

    /**
     * Settings from the check that should be available from cache on re-execution of the check.
     *
     * @var array of ISetting instances
     */
    protected $cachable_settings = array();

    /**
     * Status of the result. See available constants on IResult.
     *
     * @var string status of this result
     */
    protected $status = self::UNEXECUTED;

    /**
     * Create a new Result instance.
     */
    public function __construct()
    {
        $this->check = null;
        $this->messages = array();
        $this->settings = array();
        $this->cachable_settings = array();
        $this->status = self::UNEXECUTED;
    }

    /**
     * Adds the given message to the internal list of messages.
     *
     * @param \Environaut\Report\Results\Messages\IMessage $message
     *
     * @return Result this instance for fluent API support
     */
    public function addMessage(IMessage $message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Adds the given setting to the internal lists of settings.
     *
     * @param ISetting $setting setting to add to internal lists of settings
     * @param bool $cachable whether or not the setting may be put into a cache for reuse on re-execution of the check
     *
     * @return Result this instance for fluent API support
     */
    public function addSetting(ISetting $setting, $cachable = true)
    {
        $this->settings[] = $setting;

        if (true === $cachable) {
            $this->cachable_settings[] = $setting;
        }

        return $this;
    }

    /**
     * Adds the given setting to the internal list of settings.
     *
     * @param array $settings array with ISetting implementing instances to add
     * @param bool $cachable whether or not the setting may be put into a cache for reuse on re-execution of the check
     *
     * @return Result this instance for fluent API support
     */
    public function addSettings(array $settings, $cachable = true)
    {
        foreach ($settings as $setting) {
            if ($setting instanceof ISetting) {
                $this->addSetting($setting);
            }
        }

        return $this;
    }

    /**
     * Replaces the internal list of messages with the given list.
     *
     * @param array $messages array with IMessage implementing instances
     *
     * @return Result this instance for fluent API support
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Returns the internal list of messages emitted by the processed check.
     *
     * @return array of IMessage implementing instances
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns the internal list of settings emitted by the processed check.
     *
     * @param string $groups group names of settings to return
     * @param integer $flag flag value the settings should belong to
     *
     * @return array of ISetting implementing instances matching the arguments
     */
    public function getSettings($groups = null, $flag = null)
    {
        return $this->getFiltered($this->settings, $groups, $flag);
    }

    /**
     * Returns the internal list of cachable settings emitted by the processed check.
     *
     * @param string $groups group names of settings to return
     * @param integer $flag flag value the settings should belong to
     *
     * @return array of ISetting implementing instances matching the arguments
     */
    public function getCachableSettings($groups = null, $flag = null)
    {
        return $this->getFiltered($this->cachable_settings, $groups, $flag);
    }

    /**
     * Return all settings or the settings of the specified groups as an array.
     *
     * @param string $groups group names of settings to return
     * @param integer $flag flag value the settings should belong to
     *
     * @return array all settings (for the specified groups); empty array if groups don't exist.
     */
    public function getSettingsAsArray($groups = null, $flag = null)
    {
        return $this->getFilteredAsArray($this->settings, $groups, $flag);
    }

    /**
     * Return all cachable settings or the cachable settings of the specified groups as an array.
     *
     * @param string $groups group names of cachable settings to return
     * @param integer $flag flag value the settings should belong to
     *
     * @return array all cachable settings (for the specified groups); empty array if groups don't exist.
     */
    public function getCachableSettingsAsArray($groups = null, $flag = null)
    {
        return $this->getFilteredAsArray($this->cachable_settings, $groups, $flag);
    }

    /**
     * Sets the status of the current result (which may be of interest for the later export and cache writing).
     *
     * @param string $status one of the constants from IResult (like IResult::SUCCESS or IResult::FAIL)
     *
     * @return Result this instance for fluent API support
     */
    public function setStatus($status = self::INVALID)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns the current status of this result (usually one of the IResult::SUCCESS or IResult::FAIL constants).
     *
     * @return string status of this result instance
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the check instance this result belongs to.
     *
     * @param \Environaut\Checks\ICheck $check check this result belongs to
     *
     * @return Result this instance for fluent API support
     */
    public function setCheck(ICheck $check)
    {
        $this->check = $check;

        return $this;
    }

    /**
     * Returns the check instance this result belongs to.
     *
     * @return \Environaut\Checks\ICheck check instance this result belongs to
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * Returns all settings from the given array that match the groups and flag.
     *
     * @param array $all_settings array of ISetting implementing classes
     * @param string $groups group names of settings to return
     * @param integer $flag flag value the settings should belong to
     *
     * @return array of entries from all_settings that match the given groups and flag
     */
    protected function getFiltered(array $all_settings = array(), $groups = null, $flag = null)
    {
        $settings = array();

        foreach ($all_settings as $setting) {
            if ($setting->matchesGroup($groups) && $setting->matchesFlag($flag)) {
                $settings[] = $setting;
            }
        }

        return $settings;
    }

    /**
     * Returns all or just the matching settings of the given groups as an associative array.
     *
     * @param array $all_settings array of ISetting implementing setting instances
     * @param string $group name of group to filter given settings for
     * @param integer $flag flag value the setting must be matching
     *
     * @return array with associative arrays (for each setting)
     */
    protected function getFilteredAsArray(array $all_settings, $groups = null, $flag = null)
    {
        $settings = array();

        foreach ($all_settings as $setting) {
            if ($setting->matchesGroup($groups) && $setting->matchesFlag($flag)) {
                $settings[] = $setting->toArray();
            }
        }

        return $settings;
    }
}
