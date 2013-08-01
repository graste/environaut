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
     * @return Result this instance
     */
    public function addMessage(IMessage $message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Adds the given setting to the internal list of settings.
     *
     * @param \Environaut\Report\Results\Settings\ISetting $setting
     * @param bool $cachable whether or not the setting may be put into a cache for reuse on re-execution of the check
     *
     * @return Result this instance
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
     * Replaces the internal list of settings with the given list.
     *
     * @param array $settings array with ISetting implementing instances
     *
     * @return Result this instance
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Replaces the internal list of messages with the given list.
     *
     * @param array $messages array with IMessage implementing instances
     *
     * @return Result this instance
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
     * @return array of ISetting implementing instances
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Returns the internal list of cachable settings emitted by the processed check.
     *
     * @return array of ISetting implementing instances
     */
    public function getCachableSettings()
    {
        return $this->cachable_settings;
    }

    /**
     * Return all settings or the settings of the specified group as an array.
     *
     * @param string $group group name of settings to return
     *
     * @return array all settings (for the specified group); empty array if group doesn't exist.
     */
    public function getSettingsAsArray($group = null)
    {
        return $this->getAsArray($this->settings, $group);
    }

    /**
     * Return all cachable settings or the cachable settings of the specified group as an array.
     *
     * @param string $group group name of cachable settings to return
     *
     * @return array all cachable settings (for the specified group); empty array if group doesn't exist.
     */
    public function getCachableSettingsAsArray($group = null)
    {
        return $this->getAsArray($this->cachable_settings, $group);
    }

    /**
     * Sets the status of the current result (which may be of interest for the later export and cache writing).
     *
     * @param string $status one of the constants from IResult (like IResult::SUCCESS or IResult::FAIL)
     *
     * @return \Environaut\Report\Results\Result this instance
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
     * @return Result this instance
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
     * Returns the all or just the settings of the given group as an associative array.
     *
     * @param array $all_settings array of ISetting implementing setting instances
     * @param string $group name of group to filter given settings for
     *
     * @return array associative array of settings content (name, value etc.)
     */
    protected function getAsArray(array $all_settings, $group = null)
    {
        $settings = array();

        foreach ($all_settings as $setting) {
            $settings = array_merge_recursive($settings, $setting->toArray());
        }

        if (null === $group) {
            return $settings;
        } elseif (null !== $group && isset($settings[$group])) {
            return $settings[$group];
        } else {
            return array();
        }
    }
}
